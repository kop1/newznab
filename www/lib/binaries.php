<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/nntp.php");
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/backfill.php");

class Binaries
{	
	const BLACKLIST_FIELD_SUBJECT = 1;
	const BLACKLIST_FIELD_FROM = 2;
	const BLACKLIST_FIELD_MESSAGEID = 3;

	function Binaries() 
	{
		if(isset($_SERVER['HTTP_USER_AGENT']) && strlen($_SERVER['HTTP_USER_AGENT']) > 0)
			$this->n = "\n<BR>";
		else
			$this->n = "\n";
			
		$s = new Sites();
		$site = $s->get();
		$this->compressedHeaders = ($site->compressedheaders == "1") ? true : false;	
		$this->messagebuffer = (!empty($site->maxmssgs)) ? $site->maxmssgs : 20000;
		$this->NewGroupScanByDays = ($site->newgroupscanmethod == "1") ? true : false;
		$this->NewGroupMsgsToScan = (!empty($site->newgroupmsgstoscan)) ? $site->newgroupmsgstoscan : 50000;
		$this->NewGroupDaysToScan = (!empty($site->newgroupdaystoscan)) ? $site->newgroupdaystoscan : 3;
		
		$this->blackList = array(); //cache of our black/white list
		$this->message = array();
	}
	
	function updateAllGroups() 
	{
		$n = $this->n;
		$groups = new Groups;
		$res = $groups->getActive();
		
		if ($res)
		{	
			$alltime = microtime(true);	
			echo 'Updating: '.sizeof($res).' groups - Using compression? '.(($this->compressedHeaders)?'Yes':'No').$n;
			
			$nntp = new Nntp();
			$nntp->doConnect();
			
			foreach($res as $groupArr) 
			{
				$this->message = array();
				$this->updateGroup($nntp, $groupArr);
			}
			
			$nntp->doQuit();
			echo 'Updating completed in '.number_format(microtime(true) - $alltime, 2).' seconds'.$n;
		}
		else
		{
			echo "No groups specified. Ensure groups are added to newznab's database for updating.$n";
		}		
	}
	
	function updateGroup($nntp, $groupArr)
	{
		$db = new DB();
		$backfill = new Backfill();
		$n = $this->n;
		$this->startGroup = microtime(true);
		
		echo 'Processing '.$groupArr['name'].$n;
		
		// Connect to server
		$data = $nntp->selectGroup($groupArr['name']);
		if (PEAR::isError($data))
		{
			echo "Could not select group (bad name?): {$groupArr['name']}$n";
			return;
		}

		//Get first and last part numbers from newsgroup
		$last = $grouplast = $data['last'];
		
		// For new newsgroups - determine here how far you want to go back.
		if ($groupArr['last_record'] == 0)
		{
			if ($this->NewGroupScanByDays) 
			{
				$first = $backfill->daytopost($nntp, $groupArr['name'], $this->NewGroupDaysToScan, true);
				if ($first == '')
				{
					echo "Skipping group: {$groupArr['name']}$n";
					return;
				}
			}
			else
			{
				if ($data['first'] > ($data['last'] - $this->NewGroupMsgsToScan))
					$first = $data['first'];
				else
					$first = $data['last'] - $this->NewGroupMsgsToScan;
			}
			$first_record_postdate = $backfill->postdate($nntp, $first, false);
			$db->query(sprintf("UPDATE groups SET first_record = %s, first_record_postdate = FROM_UNIXTIME(".$first_record_postdate.") WHERE ID = %d", $db->escapeString($first), $groupArr['ID']));
		}
		else
		{
			$first = $groupArr['last_record'] + 1;
		}
		
		// Generate postdates for first and last records, for those that upgraded
		if ((is_null($groupArr['first_record_postdate']) || is_null($groupArr['last_record_postdate'])) && ($groupArr['last_record'] != "0" && $groupArr['first_record'] != "0"))
			 $db->query(sprintf("UPDATE groups SET first_record_postdate = FROM_UNIXTIME(".$backfill->postdate($nntp,$groupArr['first_record'],false)."), last_record_postdate = FROM_UNIXTIME(".$backfill->postdate($nntp,$groupArr['last_record'],false).") WHERE ID = %d", $groupArr['ID']));

		// Deactivate empty groups
		if (($data['last'] - $data['first']) <= 5)
			$db->query(sprintf("UPDATE groups SET active = %s, last_updated = now() WHERE ID = %d", $db->escapeString('0'), $groupArr['ID']));
		
		// Calculate total number of parts
		$total = $grouplast - $first;
		
		// If total is bigger than 0 it means we have new parts in the newsgroup
		if($total > 0)
		{
			echo "Group ".$data["group"]." has ".number_format($total)." new parts.".$n;
			echo "First: ".$data['first']." Last: ".$data['last']." Local last: ".$groupArr['last_record'].$n;
			if ($groupArr['last_record'] == 0)
				echo "New group starting with ".(($this->NewGroupScanByDays) ? $this->NewGroupDaysToScan." days" : $this->NewGroupMsgsToScan." messages")." worth.".$n;
			
			$done = false;

			// Get all the parts (in portions of $this->messagebuffer to not use too much memory)
			while ($done === false)
			{
				$this->startLoop = microtime(true);

				if ($total > $this->messagebuffer)
				{
					if ($first + $this->messagebuffer > $grouplast)
						$last = $grouplast;
					else
						$last = $first + $this->messagebuffer;
				}
				
				echo "Getting ".number_format($last-$first)." parts (".$first." to ".$last.") - ".number_format($grouplast - $last)." in queue".$n;
				flush();
				
				//get headers from newsgroup
				$this->scan($nntp, $groupArr, $first, $last);
				$db->query(sprintf("UPDATE groups SET last_record = %s, last_updated = now() WHERE ID = %d", $db->escapeString($last), $groupArr['ID']));
				
				if ($last == $grouplast)
					$done = true;
				else
					$first = $last + 1;
			}
			
			$last_record_postdate = $backfill->postdate($nntp,$last,false);
			$db->query(sprintf("UPDATE groups SET last_record_postdate = FROM_UNIXTIME(".$last_record_postdate."), last_updated = now() WHERE ID = %d", $groupArr['ID']));	//Set group's last postdate
			$timeGroup = number_format(microtime(true) - $this->startGroup, 2);
			echo "Group processed in $timeGroup seconds $n $n";
		}
		else
		{
			echo "No new records for ".$data["group"]." (first $first last $last total $total) grouplast ".$groupArr['last_record'].$n.$n;

		}
	}
	
	function scan($nntp, $groupArr, $first, $last)
	{
		$db = new Db();
		$n = $this->n;
		$this->startHeaders = microtime(true);
		
		if ($this->compressedHeaders)
			$msgs = $nntp->getXOverview($first."-".$last, true, false);
		else
			$msgs = $nntp->getOverview($first."-".$last, true, false);
		
		if (PEAR::isError($msgs) && $msgs->code == 400)
		{
			echo "NNTP connection timed out. Reconnecting...$n";
			$nntp->doConnect();
			$nntp->selectGroup($groupArr['name']);
			if ($this->compressedHeaders)
				$msgs = $nntp->getXOverview($first."-".$last, true, false);
			else
				$msgs = $nntp->getOverview($first."-".$last, true, false);
		}
		$timeHeaders = number_format(microtime(true) - $this->startHeaders, 2);
		
		if(PEAR::isError($msgs))
		{
			echo "Error {$msgs->code}: {$msgs->message}$n";
			echo "Skipping group$n";
			return;
		}
	
		$this->startUpdate = microtime(true);
		if (is_array($msgs))
		{	
			//loop headers, figure out parts
			foreach($msgs AS $msg)
			{
				$pattern = '/\((\d+)\/(\d+)\)$/i';
				if (!isset($msg['Subject']) || !preg_match($pattern, $msg['Subject'], $matches)) // not a binary post most likely.. continue
					continue;
				
				//Filter binaries based on black/white list
				if ($this->isBlackListed($msg, $groupArr['name'])) 
				{
					continue;
				}
	
				if(is_numeric($matches[1]) && is_numeric($matches[2]))
				{
					array_map('trim', $matches);
					$subject = trim(preg_replace($pattern, '', $msg['Subject']));
		
					if(!isset($this->message[$subject]))
					{
						$this->message[$subject] = $msg;
						$this->message[$subject]['MaxParts'] = (int)$matches[2];
						$this->message[$subject]['Date'] = strtotime($this->message[$subject]['Date']);
					}
					if((int)$matches[1] > 0)
					{
						$this->message[$subject]['Parts'][(int)$matches[1]] = array('Message-ID' => substr($msg['Message-ID'],1,-1), 'number' => $msg['Number'], 'part' => (int)$matches[1], 'size' => $msg['Bytes']);
					}
				}
			}
			unset($msg);
			unset($msgs);
			$count = 0;
			$updatecount = 0;
			$partcount = 0;
	
			if(isset($this->message) && count($this->message))
			{
				//insert binaries and parts into database. when binary already exists; only insert new parts
				foreach($this->message AS $subject => $data)
				{
					if(isset($data['Parts']) && count($data['Parts']) > 0 && $subject != '')
					{
						$res = $db->queryOneRow(sprintf("SELECT ID FROM binaries WHERE name = %s AND fromname = %s AND groupID = %d", $db->escapeString($subject), $db->escapeString($data['From']), $groupArr['ID']));
						if(!$res)
						{
							$binaryID = $db->queryInsert(sprintf("INSERT INTO binaries (name, fromname, date, xref, totalparts, groupID, dateadded) VALUES (%s, %s, FROM_UNIXTIME(%s), %s, %s, %d, now())", $db->escapeString($subject), $db->escapeString($data['From']), $db->escapeString($data['Date']), $db->escapeString($data['Xref']), $db->escapeString($data['MaxParts']), $groupArr['ID']));
							$count++;
							if ($count%500==0) echo "$count bin adds...";
						}
						else
						{
							$binaryID = $res["ID"];
							$updatecount++;
							if ($updatecount%500==0) echo "$updatecount bin updates...";
						}
						
						foreach($data['Parts'] AS $partdata)
						{
							$partcount++;
							$pidata = $db->queryInsert(sprintf("INSERT INTO parts (binaryID, messageID, number, partnumber, size, dateadded) VALUES (%d, %s, %s, %s, %s, now())", $binaryID, $db->escapeString($partdata['Message-ID']), $db->escapeString($partdata['number']), $db->escapeString(round($partdata['part'])), $db->escapeString($partdata['size'])), false);
							if (!$pidata)
								echo $n."Error inserting part ".var_dump($partdata).$n;
						}
					}
				}
				if (($count >= 500) || ($updatecount >= 500)) echo $n;
			}	
			$timeUpdate = number_format(microtime(true) - $this->startUpdate, 2);
			$timeLoop = number_format(microtime(true)-$this->startLoop, 2);
			
			echo number_format($count).' new, '.number_format($updatecount).' updated, '.number_format($partcount).' parts.';			
			echo " $timeHeaders headers, $timeUpdate update, $timeLoop range.$n";
			unset($this->message);
			unset($data);	
		}
		else
		{
			echo "Error: Can't get parts from server (msgs not array) $n";
			echo "Skipping group$n";
			return;
		}

	}
	
	public function retrieveBlackList() 
	{
		if (is_array($this->blackList) && !empty($this->blackList)) { return $this->blackList; }
		$blackList = $this->getBlacklist(true);
		$result = array();
		foreach($blackList as $bl) 
		{
			$result[$bl['groupname']][$bl['optype']][] = $bl;
		}
		$this->blackList = $result;
		return $result;
	}
	
	public function isBlackListed($msg, $groupName) 
	{
		$blackList = $this->retrieveBlackList();
	
		$field = array();
		if (isset($msg["Subject"]))
			$field[Binaries::BLACKLIST_FIELD_SUBJECT] = $msg["Subject"];
			
		if (isset($msg["From"]))
			$field[Binaries::BLACKLIST_FIELD_FROM] = $msg["From"];
	
		if (isset($msg["Message-ID"]))
			$field[Binaries::BLACKLIST_FIELD_MESSAGEID] = $msg["Message-ID"];

		$omitBinary = false;
		//whitelist
		if (isset($blackList[$groupName][2])) 
		{
			foreach ($blackList[$groupName][2] as $wList) 
			{
				if (!preg_match('/'.$wList['regex'].'/i', $field[$wList['msgcol']]))
				{
					$omitBinary = true;
				}
			}
		}
		//blacklist
		if (isset($blackList[$groupName][1])) 
		{
			foreach ($blackList[$groupName][1] as $bList) 
			{
				if (preg_match('/'.$bList['regex'].'/i', $field[$bList['msgcol']]))
				{
					$omitBinary = true;
				}
			}
		}
		return $omitBinary;
	}
	
	public function search($search, $limit=1000)
	{			
		$db = new DB();

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the like match, but mandate that all items returned must start with the provided word
		//
		$words = explode(" ", $search);
		$searchsql = "";
		$intwordcount = 0;
		if (count($words) > 0)
		{
			foreach ($words as $word)
			{
				//
				// see if the first word had a caret, which indicates search must start with term
				//
				if ($intwordcount == 0 && (strpos($word, "^") === 0))
					$searchsql.= sprintf(" and b.name like %s", $db->escapeString(substr($word, 1)."%"));
				else
					$searchsql.= sprintf(" and b.name like %s", $db->escapeString("%".$word."%"));

				$intwordcount++;
			}
		}

		$res = $db->query(sprintf("
					SELECT b.*, 
					g.name AS group_name,
					r.guid
					FROM binaries b
					INNER JOIN groups g ON g.ID = b.groupID
					LEFT OUTER JOIN releases r ON r.ID = b.releaseID
					WHERE 1=1 %s order by DATE DESC LIMIT %d ", 
					$searchsql, $limit));		
		
		return $res;
	}	

	public function getForReleaseId($id)
	{			
		$db = new DB();
		return $db->query(sprintf("select binaries.* from binaries where releaseID = %d order by relpart", $id));		
	}

	public function getById($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select binaries.*, groups.name as groupname from binaries left outer join groups on binaries.groupID = groups.ID where binaries.ID = %d ", $id));		
	}

	public function getBlacklist($activeonly=true)
	{			
		$db = new DB();
		
		$where = "";
		if ($activeonly)
			$where = " where binaryblacklist.status = 1 ";
			
		return $db->query("SELECT binaryblacklist.ID, binaryblacklist.optype, binaryblacklist.status, binaryblacklist.description, binaryblacklist.groupname AS groupname, binaryblacklist.regex, 
												groups.ID AS groupID, binaryblacklist.msgcol FROM binaryblacklist 
												left outer JOIN groups ON groups.name = binaryblacklist.groupname 
												".$where."
												ORDER BY coalesce(groupname,'zzz')");		
	}

	public function getBlacklistByID($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from binaryblacklist where ID = %d ", $id));		
	}

	public function deleteBlacklist($id)
	{			
		$db = new DB();
		return $db->query(sprintf("delete from binaryblacklist where ID = %d", $id));		
	}		
	
	public function updateBlacklist($regex)
	{			
		$db = new DB();
		
		$groupname = $regex["groupname"];
		if ($groupname == "")
			$groupname = "null";
		else
			$groupname = sprintf("%s", $db->escapeString($regex["groupname"]));
			
		$db->query(sprintf("update binaryblacklist set groupname=%s, regex=%s, status=%d, description=%s, optype=%d, msgcol=%d where ID = %d ", $groupname, $db->escapeString($regex["regex"]), $regex["status"], $db->escapeString($regex["description"]), $regex["optype"], $regex["msgcol"], $regex["id"]));	
	}
	
	public function addBlacklist($regex)
	{			
		$db = new DB();
		
		$groupname = $regex["groupname"];
		if ($groupname == "")
			$groupname = "null";
		else
			$groupname = sprintf("%s", $db->escapeString($regex["groupname"]));
			
		return $db->queryInsert(sprintf("insert into binaryblacklist (groupname, regex, status, description, optype, msgcol) values (%s, %s, %d, %s, %d, %d) ", 
			$groupname, $db->escapeString($regex["regex"]), $regex["status"], $db->escapeString($regex["description"]), $regex["optype"], $regex["msgcol"]));	
		
	}	
}
?>

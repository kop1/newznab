<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/nntp.php");

class NZB 
{
	function NZB() 
	{
		//
		// TODO:Move all these to site table.
		//
		if(isset($_SERVER['HTTP_USER_AGENT']) && strlen($_SERVER['HTTP_USER_AGENT']) > 0)
			$this->n = "\n<BR>";
		else
			$this->n = "\n";
		$this->maxMssgs = 2000; //fetch this ammount of messages at the time
		$this->howManyMsgsToGoBackForNewGroup = 50000; //how far back to go, use 0 to get all
	}
	
	//
	// Get an nzb by its release guid
	//
	function getNZBforRelease($relguid)
	{
		$db = new DB();
		$binaries = array();
		$res = $db->query(sprintf("select binaries.ID from binaries inner join releases on releases.ID = binaries.releaseID where releases.guid = %s", $db->escapeString($relguid)));
		foreach($res as $binrow) 
			$binaries[] = $binrow["ID"];

		return $this->getNZB($binaries);
	}
	
	//
	// Get an nzb by its release guid
	//
	function getNZBforReleaseId($relid)
	{
		$db = new DB();
		$binaries = array();
		$res = $db->query(sprintf("select binaries.ID from binaries where releaseID = %d", $relid));
		if ($res)
		{
			foreach($res as $binrow) 
				$binaries[] = $binrow["ID"];

			return $this->getNZB($binaries);
		}
		return null;
	}

	//
	// Return a multi array of series of binaries and their parts.
	//
	function getNZB($selected)
	{
		$db = new DB();
		$binaries = array();
		if(count($selected) > 0) 
		{
			$selected = join(',',$selected);
			
			$res = $db->query("SELECT binaries.*, UNIX_TIMESTAMP(date) AS unixdate, groups.name as groupname FROM binaries inner join groups on binaries.groupID = groups.ID WHERE binaries.ID IN ({$selected}) ORDER BY binaries.name");
			foreach($res as $binrow) 
			{
				//
				// TODO:Move this into template
				//
				$binrow['name'] = preg_replace("/[^a-zA-Z0-9\(\)\! .]/",'', str_replace('"', '', $binrow['name']));
				$binrow['fromname'] = str_replace('(','',str_replace(')','',$binrow['fromname']));
				
				$parts = $db->query(sprintf("SELECT parts.* FROM parts WHERE binaryID = %d ORDER BY partnumber", $binrow["ID"]));
				$binaries[] = array ('binary' => $binrow, 'parts' => $parts);
			}
		}
		return $binaries;
	}

	//
	// Update all active groups categories and descriptions
	//
	function updateAllGroups() 
	{
		$groups = new Groups;
		$res = $groups->getActive();

		if ($res)
		{
			$nntp = new Nntp();
			$nntp->doConnect();

			foreach($res as $groupArr) 
			{
				$this->message = array();
				$this->updateGroup($nntp, $groupArr);
			}
			
			$nntp->doQuit();	
		}
		else
		{
			echo "No groups specified. Ensure site.groupfilter is populated and run group-update.$n";
		}		
	}	

	function updateGroup($nntp, $groupArr) 
	{
		$db = new DB();
		$n = $this->n;
		$attempts = 0;

		$data = $nntp->selectGroup($groupArr['name']);
		if(PEAR::isError($data)) 
		{
			echo "Could not select group: {$groupArr['name']}$n";
			die();
		}
		
		/*  Example newsgroup heading
 		Processing: alt.binaries.sounds.mp3.electronic
		Array
		(
			[group] => alt.binaries.sounds.mp3.electronic
			[first] => 5494095
			[last] =>  7111079
			[count] => 1616985
		)		
		*/
		
		//get first and last part numbers from newsgroup
		$last = $orglast = $data['last'];
		if($groupArr['last_record'] == 0) 
		{
			//
			// for new newsgroups - determine here how far you want to go back.
			//
			//$first = ($this->howManyMsgsToGoBackForNewGroup == 0 ? 
			//		$data['first'] : $data['last'] - $this->howManyMsgsToGoBackForNewGroup);
			if($data['first'] > ($data['last'] - $this->howManyMsgsToGoBackForNewGroup))
				$first = $data['first'];
			else
				$first = $data['last'] - $this->howManyMsgsToGoBackForNewGroup;	
		} else 
		{
			$first = $groupArr['last_record'] + 1;
		}

		//calculate total number of parts
		$total = $last - $first;

		//if total is bigger than 0 it means we have new parts in the newsgroup
		if($total > 0) 
		{

			echo "Group ".$data["group"]." has ".$data['first']." - ".$last." = {$total} (Total parts) - Local last = ".$groupArr['last_record'].$n;

			$done = false;

			//get all the parts (in portions of $this->maxMssgs to not use too much memory)
			while($done === false) 
			{
				if($total > $this->maxMssgs) 
				{
					if($first + $this->maxMssgs > $orglast) 
					{
						$last = $orglast;
					} 
					else 
					{
						$last = $first + $this->maxMssgs;
					}
				}

				if($last - $first < $this->maxMssgs) 
				{
					$fetchpartscount = $last - $first;
				} 
				else 
				{
					$fetchpartscount = $this->maxMssgs;
				}
				echo "Getting {$fetchpartscount} parts (".($orglast - $last)." in queue)";
				flush();

				//get headers from newsgroup
				echo " getting $first to $last: $n";
				$msgs = $nntp->getOverview($first."-".$last, true, false);

				/*   Example msg
				Array ( 
					[Number] => 5934117 
					[Subject] => RepostTechnoAcidAlbums2008VarBit18Albums"RepostTechnoAcidAlbums2008VarBit18Albums.part21.rar" yEnc (121/410) 
					[From] => FTDtechnoTEAM@ (-=Techno4Life=-) 
					[Date] => 11 Jan 2009 09:01:12 GMT 
					[Message-ID] => <4969b556$0$5824$2d805a3e@uploadreader.eweka.nl> 
					[References] => 
					[Bytes] => 396519 
					[Lines] => 3046 
					[Xref] => news-big.astraweb.com alt.binaries.mp3:83651138 alt.binaries.sounds.mp3.dance:25100194 alt.binaries.sounds.mp3.electronic:5934117 
					)
				*/

				//loop headers, figure out parts
				foreach($msgs AS $msg) 
				{
					$pos = strrpos($msg['Subject'], '(');
					$part = substr($msg['Subject'], $pos+1, -1);
					$part = explode('/',$part);

					if(is_numeric($part[0])) 
					{
						$subject = trim(substr($msg['Subject'], 0, $pos));
						if(!isset($this->message[$subject])) 
						{
							$this->message[$subject] = $msg;
							$this->message[$subject]['MaxParts'] = (isset($part[1]) ? $part[1] : 0);
							$this->message[$subject]['Date'] = strtotime($this->message[$subject]['Date']);
						}
						if($part[0] > 0) 
						{
							$this->message[$subject]['Parts'][$part[0]] = array('Message-ID' => substr($msg['Message-ID'],1,-1), 'number' => $msg['Number'], 'part' => $part[0], 'size' => $msg['Bytes']);
						}
					}
				}

				$count = 0;
				$updatecount = 0;
				$partcount = 0;

				if(count($this->message)) 
				{

					//insert binaries and parts into database. when binary already exists; only insert new parts
					foreach($this->message AS $subject => $data) 
					{
						if(isset($data['Parts']) && count($data['Parts']) > 0 && $subject != '') 
						{
							$res = $db->queryOneRow(sprintf("SELECT ID FROM binaries WHERE name = %s AND fromname = %s AND groupID = %d", $db->escapeString($subject), $db->escapeString($data['From']), $groupArr['ID']));
							if(!$res) 
							{
								$binaryID = $db->queryInsert(sprintf("INSERT INTO binaries (name, fromname, date, xref, totalparts, groupID) VALUES (%s, %s, FROM_UNIXTIME(%s), %s, %s, %d)", $db->escapeString($subject), $db->escapeString($data['From']), $db->escapeString($data['Date']), $db->escapeString($data['Xref']), $db->escapeString($data['MaxParts']), $groupArr['ID']));
								$count++;
							} 
							else 
							{
								$binaryID = $res["ID"];
								$updatecount++;
							}

							foreach($data['Parts'] AS $partdata) 
							{
								$partcount++;
								$db->queryInsert(sprintf("INSERT INTO parts (binaryID, messageID, number, partnumber, size) VALUES (%d, %s, %s, %s, %s)", $binaryID, $db->escapeString($partdata['Message-ID']), $db->escapeString($partdata['number']), $db->escapeString(round($partdata['part'])), $db->escapeString($partdata['size'])));
							}
						}
					}
					
					//
					// update the group with the last update record.
					//
					$db->query(sprintf("UPDATE groups SET last_record = %s, last_updated = now() WHERE ID = %d", $db->escapeString($last), $groupArr['ID']));
					
					echo "Received $count new binaries$n";
					echo "Updated $updatecount binaries$n";

					//when last = orglast; all headers are downloaded; not ? than go on with next $this->maxMssgs messages
					if($last == $orglast) 
						$done = true;
					else 
						$first = $last + 1;

					unset($this->message);
					unset($msgs);
					unset($msg);
					unset($data);
					
				} 
				else 
				{
					$attempts++;
					echo "Error fetching messages attempt {$attempts}...$n";
					if($attempts == 1) 
					{
						echo "Skipping group$n";
						break;
					}
					sleep(1);
				}
			}
			
		} 
		else 
		{
			echo "No new records for ".$data["group"]." (first $first last $last total $total) grouplast ".$groupArr['last_record']."$n";
		}
	}


	function scantest() 
	{
		$groups = new Groups;
		$res = $groups->getActive();
		$nntp = new Nntp();
		$nntp->doConnect();
		foreach($res as $groupArr)
		{
			if($groupArr['name']=='alt.binaries.teevee')
			{
				$this->update($nntp,$groupArr['name'],'68425551','68427551');
			}
			else
				continue;
		}
	}

	function update($nntp, $group, $begin, $end, $direction=TRUE) //scan headers; inputs need to be valid, freshness check upstream 
	{	$temp_begin = $begin;
		$temp_end = $end;
		$db = new DB();
		$attempts = 0;
		$data = $nntp->selectGroup($group);
		if(PEAR::isError($data)) 
		{
			echo "Could not open group $group or that group's details.  Check that it is a valid group.\n";
			die();
		}
		
		//$data example
		//[group] => alt.binaries.sounds.mp3.electronic
		//[first] => 5494095
		//[last] =>  7111079
		//[count] => 1616985
		//I lied, we're going to check for validity anyway.
		if($begin < $data['first'] || $end > $data['last'] || $end <= $first || $begin==0 || $end==0)
			{
			echo "Error seeking past hard limit, group $group first ".$data['first']." last ".$data['last']." begin $begin end $end$n"; 
			die();	
		}
		$done = false;
		//limit temporary queue
		if($direction==TRUE && $end > $begin + $this->maxMssgs)
			$temp_end = $begin + $this->maxMssgs;
		if($direction==FALSE && $begin < $end - $this->maxMssgs)
			$temp_end = $end - $this->maxMssgs;
		//get all the parts (in portions of $this->maxMssgs to not use too much memory)
		while($done === false) 
		{
			flush();

				//get headers from newsgroup
			$msgs = $nntp->getOverview($temp_begin."-".$temp_end, true, false);
			foreach($msgs AS $msg) 
			{
				$pos = strrpos($msg['Subject'], '(');
				$part = substr($msg['Subject'], $pos+1, -1);
				$part = explode('/',$part);
					if(is_numeric($part[0])) 
				{
					$subject = trim(substr($msg['Subject'], 0, $pos));
					if(!isset($this->message[$subject])) 
					{
						$this->message[$subject] = $msg;
						$this->message[$subject]['MaxParts'] = (isset($part[1]) ? $part[1] : 0);
						$this->message[$subject]['Date'] = strtotime($this->message[$subject]['Date']);
					}
					if($part[0] > 0) 
					{
						$this->message[$subject]['Parts'][$part[0]] = array('Message-ID' => substr($msg['Message-ID'],1,-1), 'number' => $msg['Number'], 'part' => $part[0], 'size' => $msg['Bytes']);
					}
				}
			}
			$count = 0;
			$updatecount = 0;
			$partcount = 0;
			if(count($this->message)) 
			{
				//insert binaries and parts into database. when binary already exists; only insert new parts
				foreach($this->message AS $subject => $data) 
				{
					if(isset($data['Parts']) && count($data['Parts']) > 0 && $subject != '') 
					{
						$res = $db->queryOneRow(sprintf("SELECT ID FROM binaries WHERE name = %s AND fromname = %s AND groupID = %d", $db->escapeString($subject), $db->escapeString($data['From']), $groupArr['ID']));
						if(!$res) 
						{
							$binaryID = $db->queryInsert(sprintf("INSERT INTO binaries (name, fromname, date, xref, totalparts, groupID) VALUES (%s, %s, FROM_UNIXTIME(%s), %s, %s, %d)", $db->escapeString($subject), $db->escapeString($data['From']), $db->escapeString($data['Date']), $db->escapeString($data['Xref']), $db->escapeString($data['MaxParts']), $groupArr['ID']));
							$count++;
						} 
						else 
						{
							$binaryID = $res["ID"];
							$updatecount++;
						}
							foreach($data['Parts'] AS $partdata) 
						{
							$partcount++;
							$db->queryInsert(sprintf("INSERT INTO parts (binaryID, messageID, number, partnumber, size) VALUES (%d, %s, %s, %s, %s)", $binaryID, $db->escapeString($partdata['Message-ID']), $db->escapeString($partdata['number']), $db->escapeString(round($partdata['part'])), $db->escapeString($partdata['size'])));
						}
					}
				}
				
				//
				// update the group with the last update record.
				//
				if($direction==TRUE)
					$db->query(sprintf("UPDATE groups SET last_record = %s, last_updated = now() WHERE ID = %d", $db->escapeString($temp_end), $groupArr['ID']));
				else
					$db->query(sprintf("UPDATE groups SET last_record = %s, last_updated = now() WHERE ID = %d", $db->escapeString($temp_begin), $groupArr['ID']));
				
				echo "Received $count new binaries, Updated $updatecount binaries$n";
					//when last = orglast; all headers are downloaded; not ? than go on with next $this->maxMssgs messages
				if(($direction==TRUE && $temp_end==$end) || ($direction==FALSE && $temp_begin==$begin))
					$done = true;
				else
				{
					if($direction==TRUE)
					{
						$temp_begin = $temp_end + 1;
						if($temp_end + $this->maxMssgs + 1 < $end)
							$temp_end = $temp_end + $this_maxMssgs + 1;
						else
							$temp_end = $end;
					}
					if($direction==FALSE)
						$temp_end = $temp_begin - 1;
					{
						if($temp_begin - $this->maxMssgs - 1 > $begin)
							$temp_begin = $temp_begin - $this->maxMssgs - 1;
						else
							$temp_begin = $begin;
					}
				}
				unset($this->message);
				unset($msgs);
				unset($msg);
				unset($data);
				
			} 
			else 
			{
				$attempts++;
				echo "Error fetching group $group messages $begin to $end$n";
				if($attempts == 1) 
				{
					break;
				}
				sleep(1);
			}
		} 
	}


	
}
?>

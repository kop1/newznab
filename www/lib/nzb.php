<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/config.php");
require_once "Net/NNTP/Client.php";

class NZB 
{
	const PROCSTAT_NEW = 0;
	const PROCSTAT_READYTORELEASE = 1;
	const PROCSTAT_WRONGPARTS = 2;
	const PROCSTAT_BADTITLEFORMAT = 3;
	const PROCSTAT_RELEASED = 4;

	function NZB() 
	{
		//
		// TODO:Move this to be configurable in the database.
		//
		$this->retention = 20; // number of days afterwhich binaries are deleted.
		$this->maxMssgs = 2000; //fetch this ammount of messages at the time
		$this->maxAttemptsToProcessBinaryIntoRelease = 3;
		$this->maxDaysToProcessWrongFormatBinaryIntoRelease = 7;
		$this->howManyMsgsToGoBackForNewGroup = 2000; //how far back to go, use 0 to get all
		$this->groupfilter = "alt.binaries.sounds.midi*|alt.binaries.sounds.lossless"; // regex of newsgroups to match on
	}

	function connect() 
	{
		$this->nntp = new Net_NNTP_Client;
		$ret = $this->nntp->connect(NNTP_SERVER);
		$ret2 = $this->nntp->authenticate(NNTP_USERNAME, NNTP_PASSWORD);
		if(PEAR::isError($ret) || PEAR::isError($ret2)) 
		{
			echo "Cannot connect to server - ".NNTP_SERVER." - ".NNTP_USERNAME." ($ret $ret2)";
			exit;
		}
	}

	function quit() 
	{
		$this->nntp->quit();
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
				$binrow['name'] = ereg_replace("[^a-zA-Z0-9\(\)\! .]",'', str_replace('"', '', $binrow['name']));
				$binrow['fromname'] = str_replace('(','',str_replace(')','',$binrow['fromname']));
				
				$parts = $db->query(sprintf("SELECT parts.* FROM parts WHERE binaryID = %d ORDER BY partnumber", $binrow["ID"]));
				$binaries[] = array ('binary' => $binrow, 'parts' => $parts);
			}
		}
		return $binaries;
	}

	function updateGroup($groupArr) 
	{

		$db = new DB();
		$attempts = 0;

		$data = $this->nntp->selectGroup($groupArr['name']);
		if(PEAR::isError($data)) 
		{
			echo "Could not select group: {$groupArr['name']}\n";
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
			$first = ($this->howManyMsgsToGoBackForNewGroup == 0 ? 
					$data['first'] : $data['last'] - $this->howManyMsgsToGoBackForNewGroup);
		} else 
		{
			$first = $groupArr['last_record'] + 1;
		}

		//calculate total number of parts
		$total = $last - $first;

		//if total is bigger than 0 it means we have new parts in the newsgroup
		if($total > 0) 
		{

			echo "Group has ".$data['first']." - ".$last." = {$total} (Total parts)\n";

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

				$starttime = getmicrotime();
				if($last - $first < $this->maxMssgs) 
				{
					$fetchpartscount = $last - $first;
				} 
				else 
				{
					$fetchpartscount = $this->maxMssgs;
				}
				echo "Getting {$fetchpartscount} parts (".($orglast - $last)." in queue)\n";
				flush();

				//get headers from newsgroup
				$msgs = $this->nntp->getOverview($first."-".$last, true, false);

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

					//insert binaries and parts into database. when binary allready exists; only insert new parts
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

							ksort($data['Parts']);
							reset($data['Parts']);
							foreach($data['Parts'] AS $partdata) 
							{
								$partcount++;
								$db->queryInsert(sprintf("INSERT INTO parts (binaryID, messageID, number, partnumber, size) VALUES (%d, %s, %s, %s, %s)", $binaryID, $db->escapeString($partdata['Message-ID']), $db->escapeString($partdata['number']), $db->escapeString(round($partdata['part'])), $db->escapeString($partdata['size'])));
							}
						}
					}
					echo "Received $count new binaries\n";
					echo "Updated $updatecount binaries\n";

					//update group table with last received message
					$countRes = $db->queryOneRow(sprintf("SELECT COUNT(ID) as num FROM binaries WHERE groupID = %d", $groupArr['ID']));
					$totingroup = $countRes["num"];

					$db->query(sprintf("UPDATE groups SET last_record = %s, last_updated = %s, postcount = %s WHERE ID = %d", $db->escapeString($last), $db->escapeString(date("Y-m-d H:m:i")), $db->escapeString($totingroup), $groupArr['ID']));

					//when last = orglast; all headers are downloaded; not ? than go on with next $this->maxMssgs messages
					if($last == $orglast) 
					{
						$done = true;
					} 
					else 
					{
						$first = $last + 1;
					}

					unset($this->message);
					unset($msgs);
					unset($msg);
					unset($data);
					
				} 
				else 
				{
					$attempts++;
					echo "Error fetching messages attempt {$attempts}...\n";
					if($attempts == 5) 
					{
						echo "Skipping group\n";
						break;
					}
					sleep(1);
				}
			}
		} 
		else 
		{
			echo "No new records\n";
		}
	}

	function updateAllGroups() 
	{
		$db = new DB();
		$res = $db->query("SELECT * FROM groups WHERE active = 1 ORDER BY name");

		foreach($res as $groupArr) 
		{
			echo "\nProcessing: ".$groupArr['name']."\n";
			flush();
			$this->message = array();
			$this->updateGroup($groupArr);
		}
	}

	//
	// update the list of newsgroups and return an array of messages.
	//
	function updateGroupList() 
	{
		$db = new DB();
		$groups = $this->nntp->getGroups();
		$ret = array();
		$miscgroupID = 0;
		
		$res = $db->queryOneRow("SELECT ID FROM category WHERE title = 'Misc' and parentID = 7");
		if ($res)
			$miscGroupID = $res["ID"];
			
		foreach($groups AS $group) 
		{
			$regfilter = "/^(" . str_replace (array ('.','*'), array ('\.','.*?'), $this->groupfilter) . ")/";
			if (preg_match ($regfilter, $group['group']) > 0)
			{
				$res = $db->query(sprintf("SELECT ID FROM groups WHERE name = %s ", $db->escapeString($group['group'])));
				if($res) 
				{
					if (isset($group['desc']))
					{
						$db->query(sprintf("UPDATE groups SET description = %s where ID = %d", $db->escapeString($group['desc']), $res["ID"]));
						$ret[] = array ('group' => $group['group'], 'msg' => 'Updated description');
					}
					else
					{
						$ret[] = array ('group' => $group['group'], 'msg' => 'Not updated');
					}
				} 
				else 
				{
					$desc = "";
					if (isset($group['desc']))
					{
						$desc = $group['desc'];
					}
					$db->queryInsert(sprintf("INSERT INTO groups (name, description, active, categoryID) VALUES (%s, %s, 1, %d)", $db->escapeString($group['group']), $db->escapeString($desc), $miscGroupID));
					$ret[] = array ('group' => $group['group'], 'msg' => 'Created');
				}
			}
		}

		return $ret;
	}

	function processReleases()
	{
		$db = new DB();

		$res = $db->query(sprintf("SELECT * from binaries where procstat = %d", NZB::PROCSTAT_NEW));
		
		//
		// should match fairly typical releases in format "relname [1/12] filename yenc"
		// handles brackets or square
		//
		$pattern = '/^([\s]*(.*)(?=(?:\[|\()(?:[\s]*0*)([\d]+)[\s]*(?:[^\d\]]{1,5})(?:[\s]*0*)([\d]+)[\s]*(?:\)|\])(?:[\s][^\w\"\']*)(\'|"?)([\W]*)(.*?)(\5)[\s]*(yEnc)?[\s]*$))/';

		$index = array();
		foreach ($res as $recordIndex => $record) 
		{
		    if (preg_match ($pattern, $record["name"], $matches) > 0) 
		    {
				
		       	//the number of parts and the number of parts which it's found in
				//$records and what the key of those parts in $records is for each
				//release is in index
		        $key = $matches[1] . '##' . $matches[4];
		        $index[$key][1] = $matches[4]; // How many parts
		        $index[$key][2][$matches[3]] = $recordIndex; // Which unique numbered parts avail in $records
				$res[$recordIndex]["matches"] = $matches;
		    }
		}
		
		//
		// determine every binary which is part of a complete release and move it on to next status
		// if the parts dont match up increment the number of attempts so these can be moved out of the way
		// later
		//
		foreach ($index as $keyStr => $info) 
		{
			if ($info[1] == count($info[2])) 
			{
				foreach ($info[2] as $partNumber => $recInd) 
				{
					$db->query(sprintf("update binaries set filename = %s, relname = %s, relpart = %d, reltotalpart = %d, procstat=%d where ID = %d", 
						$db->escapeString($res[$recInd]["matches"][7]), $db->escapeString($res[$recInd]["matches"][1]), $res[$recInd]["matches"][3], $res[$recInd]["matches"][4], NZB::PROCSTAT_READYTORELEASE, $res[$recInd]["ID"] ));
				}
			}
			else
			{
				foreach ($info[2] as $partNumber => $recInd) 
				{
					$db->query(sprintf("update binaries set procattempts = procattempts + 1 where ID = %d", $res[$recInd]["ID"] ));
				}
			}
		}
		
		//
		// Get out every binary which is ready to release and create the release header for it
		//
		$res = $db->query(sprintf("SELECT distinct relname, reltotalpart, groupID from binaries where procstat = %d", NZB::PROCSTAT_READYTORELEASE));
		foreach($res as $arr) 
		{
			$relsearchname = preg_replace (array ('/^\[[\d]{5,7}\]-?\[#[\w]+@[\w]+net\](-?\[full\])?/i', '/([^\w-]|_)/i', '/-/', '/\s[\s]+/', '/^([\W]|_)*/i', '/([\W]|_)*$/i', '/(\s)(19\d\d|20[012]\d)(?:\s|$)/'), array ('', ' ',' - ',' ', '', '', '\1(\2)\3'), $arr["relname"]);
			
			//
			// insert the header release with a clean name
			// 
			$relid = $db->queryInsert(sprintf("insert into releases (name, searchname, totalpart, groupID, adddate, guid) values (%s, %s, %d, %d, now(), md5(%s))", 
										$db->escapeString($arr["relname"]), $db->escapeString($relsearchname), $arr["reltotalpart"], $arr["groupID"], $db->escapeString(uniqid())));
			
			//
			// tag every binary for this release with its parent release id
			// remove the release name from the binary as its no longer required
			//
			$db->query(sprintf("update binaries set relname = null, procstat = %d, releaseID = %d where relname = %s and procstat = %d and releaseID is null and groupID = %d and reltotalpart = %d ", 
								NZB::PROCSTAT_RELEASED, $relid, $db->escapeString($arr["relname"]), NZB::PROCSTAT_READYTORELEASE, $arr["groupID"], $arr["reltotalpart"]));

		}

		//
		// calculate the total size of all releases
		// TODO: do something with this to make it a bit more scalable, 
		// its not really worthwhile updating the size of every release in the database :/
		//
		$db->query("update releases inner join
							(
							SELECT binaries.releaseID, sum(size) as size
							from parts
							inner join binaries on parts.binaryID = binaries.ID
							group by binaries.releaseID
							) p on p.releaseID = releases.ID and releases.size = 0
							set releases.size = p.size");	

		//
		// update the postdate and poster name of all new releases
		// TODO: like the size above, this could probably be done somewhere better
		//
		$db->query("update releases inner join
							(
							SELECT binaries.releaseID, binaries.fromname, max(date) as pdate
							from binaries
							where releaseID is not null
							group by binaries.releaseID
							) p on p.releaseID = releases.ID
							set releases.postdate = p.pdate, releases.fromname = p.fromname
							where postdate is null");				
		
		//
		// tidy away any binaries which have been attempted to be grouped into 
		// a release more than x times
		//
		$res = $db->query(sprintf("update binaries set procstat = %d where procstat = %d and procattempts > %d ", NZB::PROCSTAT_WRONGPARTS, NZB::PROCSTAT_NEW, $this->maxAttemptsToProcessBinaryIntoRelease));

		//
		// tidy away any binaries which have never been attempted to be grouped 
		// into a release and are now aging
		//
		$res = $db->query(sprintf("update binaries set procstat = %d where procstat = %d and date < now() - interval %d day", NZB::PROCSTAT_BADTITLEFORMAT, NZB::PROCSTAT_NEW, $this->maxDaysToProcessWrongFormatBinaryIntoRelease));
	}

	function delOldBinaries($groupID='') 
	{
		$db = new DB();

		$count = 0;
		$res = $db->query(sprintf("SELECT ID FROM binaries WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date)) / 3600 / 24 > %d %s ", $this->retention, (is_numeric($groupID) ? " AND groupID = {$groupID} " : "")));
		foreach($res as $arr) 
		{
			$db->query(sprintf("DELETE FROM parts WHERE binaryID = %d", $arr['ID']));
			$db->query(sprintf("DELETE FROM binaries WHERE ID = %d", $arr['ID']));
			$count++;
		}
		return "Deleted {$count} binaries\n";
	}
}

?>

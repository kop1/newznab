<?

/*
	5 feb 2005
	NZB Generator (c) 2005
	Written by Daniel Eiland daniel@bokko.nl

*/

class NZB {

	function NZB() {

		//Require the PEAR NNTP package
		require_once "Net/NNTP/Client.php";
		require_once "Net/NNTP/Header.php";
		require_once "Net/NNTP/Message.php";
		require_once "config.php";
		require_once "../www/config.php";
		//configuration
		$this->server = NNTP_Server;
		$this->maxMssgs = 20000; //fetch this ammount of messages at the time

		//initialize some stuff
		$this->downloadspeedArr = array();
		mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
		mysql_select_db(DB_NAME);


	}

	function connect() {

		//connect to server
		echo "Connecting to {$this->server}\n";
		$this->nntp = new Net_NNTP_Client;
		$ret = $this->nntp->connect($this->server);
		$ret2 = $this->nntp->authenticate(NNTP_Username,NNTP_Password);
		if(PEAR::isError($ret) || PEAR::isError($ret2)) {
			echo "Cannot connect to server\n";
			exit;
		}

	}

	function genNZB($selected) {
		$str = "<?xml version=\"1.0\" encoding=\"us-ascii\"?>\n";
		$str .= "<!DOCTYPE nzb PUBLIC \"-//newzBin//DTD NZB 0.9//EN\" \"http://www.newzbin.com/DTD/nzb/nzb-0.9.dtd\">\n";
		$str .= "<nzb xmlns=\"http://www.newzbin.com/DTD/2003/nzb\">\n";

		if(count($selected) > 0) {
			$selected = join(',',$selected);
			$sql = "SELECT *, UNIX_TIMESTAMP(date) AS unixdate FROM binaries WHERE ID IN ({$selected}) ORDER BY name";
			$res = mysql_query($sql);
			echo mysql_error();

			while($arr = mysql_fetch_assoc($res)) {
				$res2 = mysql_query("SELECT name FROM groups WHERE ID = {$arr['groupID']}");
				$group = mysql_result($res2,0,0);
				$arr['name'] = ereg_replace("[^a-zA-Z0-9\(\)\! .]",'', str_replace('"', '', $arr['name']));
				$arr['fromname'] = str_replace('(','',str_replace(')','',$arr['fromname']));
				$str .= "\t<file poster=\"who@no.com\" date=\"{$arr['unixdate']}\" subject=\"{$arr['name']} (1/{$arr['totalParts']})\">\n";
				$str .= "\t<groups>\n";
				$str .= "\t\t<group>{$group}</group>\n";
				$str .= "\t</groups>\n";
				$str .= "\t<segments>\n";
				$res2 = mysql_query("SELECT * FROM parts WHERE binaryID = {$arr['ID']} ORDER BY partnumber");
				while($arr2 = mysql_fetch_assoc($res2)) {
					$str .= "\t\t<segment bytes=\"{$arr2['size']}\" number=\"".round($arr2['partnumber'])."\">{$arr2['messageID']}</segment>\n";
				}
				$str .= "\t</segments>\n";
				$str .= "\t</file>\n";
			}
		}

		$str .= "</nzb>";

		return $str;
	}

	function updateGroup($groupArr) {

		//$this->delOldBinaries($groupArr['ID']);
		$attemts = 0;

		//select newsgroup
		$data = $this->nntp->selectGroup($groupArr['name']);
		if(PEAR::isError($data)) {
			echo "Could not select group: {$groupArr['name']}\n";
		}

		//get first and last part numbers from newsgroup
		$last = $orglast = $data['last'];
		if($groupArr['last_record'] == 0) {
			$first = $data['first'];
		} else {
			$first = $groupArr['last_record'] + 1;
		}

		//calculate total number op parts
		$total = $last - $first;

		//if total is bigger than 0 it means we have new parts in the newsgroup
		if($total > 0) {

			echo "Group has ".$data['first']." - ".$last." = {$total} (Total parts)\n";

			$done = false;

			//get all te parts (in portions of $this->maxMssgs to not use too much memory)
			while($done === false) {

				if($total > $this->maxMssgs) {
					if($first + $this->maxMssgs > $orglast) {
						$last = $orglast;
					} else {
						$last = $first + $this->maxMssgs;
					}
				}

				$starttime = $this->getmicrotime();
				if($last - $first < $this->maxMssgs) {
					$fetchpartscount = $last - $first;
				} else {
					$fetchpartscount = $this->maxMssgs;
				}
				echo "Getting {$fetchpartscount} parts (".($orglast - $last)." in queue)\n";
				flush();

				//get headers from newsgroup
				$msgs = $this->nntp->getOverview($first, $last);

				//loop headers, figure out parts
				foreach($msgs AS $msg) {
					$pos = strrpos($msg['Subject'], '(');
					$part = substr($msg['Subject'], $pos+1, -1);
					$part = explode('/',$part);

					if(is_numeric($part[0])) {
						$subject = trim(substr($msg['Subject'], 0, $pos));
						if(!isset($this->message[$subject])) {
							$this->message[$subject] = $msg;
							$this->message[$subject]['MaxParts'] = $part[1];
							$this->message[$subject]['Group'] = $group;
							$this->message[$subject]['Date'] = strtotime($this->message[$subject]['Date']);
						}
						if($part[0] > 0) {
							$this->message[$subject]['Parts'][$part[0]] = array('Message-ID' => substr($msg['Message-ID'],1,-1), 'number' => $msg['number'], 'part' => $part[0], 'size' => $msg['Bytes']);
						}
					}
				}

				$count = 0;
				$updatecount = 0;
				$partcount = 0;


				if(count($this->message)) {

					//insert binaries and parts into database. when binary allready exists; only insert new parts
					foreach($this->message AS $subject => $data) {
						if(count($data['Parts']) > 0 && $subject != '') {
							$subject = str_replace("'", "\'", $subject);
							$data['From'] = str_replace("'", "\'", $data['From']);
							$res = mysql_query("SELECT ID FROM binaries WHERE name = '{$subject}' AND fromname = '{$data['From']}' AND groupID = {$groupArr['ID']}");
							if(mysql_num_rows($res) == 0) {
								$sql = "INSERT INTO binaries (name, fromname, date, xref, totalparts, groupID) VALUES ('{$subject}', '{$data['From']}', FROM_UNIXTIME({$data['Date']}), '{$data['Xref']}', '{$data['MaxParts']}', {$groupArr['ID']})";
								mysql_query($sql);
								$binaryID = mysql_insert_id();
								$count++;
							} else {
								$binaryID = mysql_result($res,0,0);
								$updatecount++;
							}

							ksort($data['Parts']);
							reset($data['Parts']);
							foreach($data['Parts'] AS $partdata) {
								$sql = "INSERT INTO parts (binaryID, messageID, number, partnumber, size) VALUES ($binaryID, '{$partdata['Message-ID']}', '{$partdata['number']}', '".round($partdata['part'])."', '{$partdata['size']}')";
								$partcount++;
								mysql_query($sql);
							}
						}
					}
					echo "Received $count new binaries\n";
					echo "Updated $updatecount binaries\n";
					$endtime = $this->getmicrotime();

					//calculate speed
					$parsetime = $endtime - $starttime;
					$downloadspeed = $partcount/$parsetime;
					echo "Current download speed: ".round($downloadspeed)." parts/sec\n";
					$avgdownloadspeed = (array_sum($this->downloadspeedArr) + $downloadspeed) / (count($this->downloadspeedArr) + 1);
					echo "Average download speed: ".round($avgdownloadspeed)." parts/sec\n";
					if(round($downloadspeed) > 0) {
						$this->downloadspeedArr[] = $downloadspeed;
					}

					//update group table with last received message

					$countRes = mysql_query("SELECT COUNT(ID) FROM binaries WHERE groupID = {$groupArr['ID']}");
					$totingroup = mysql_result($countRes,0,0);

					mysql_query("UPDATE groups SET last_record = '{$last}', last_updated = '".date("Y-m-d H:m:i")."', postcount = '{$totingroup}' WHERE ID = {$groupArr['ID']}");

					//when last = orglast; all headers are downloaded; not ? than go on with next $this->maxMssgs messages
					if($last == $orglast) {
						$done = true;
					} else {
						$first = $last + 1;
					}

					//calculate estimated time left with current average speed
					if($parsetime > 0 && $done === false) {
						if($avgdownloadspeed > 0) {
							$ETA = round(($orglast - $first) / $avgdownloadspeed);
						} else {
							$ETA = 0;
						}
						$ETA = $this->sec2min($ETA);
						echo "Estimated time left: {$ETA}\n";
					}

					unset($this->message);
					unset($msgs);
					unset($msg);
					unset($data);
				} else {
					$attemts++;
					echo "Error fetching messages attemt {$attemts}...\n";
					if($attemts == 5) {
						echo "Skipping group\n";
						break;
					}
					sleep(1);
				}
			}

			//mysql_query("UNLOCK TABLES");

		} else {
			echo "No new records\n";
		}
	}

	function updateAllGroups() {

		$res = mysql_query("SELECT * FROM groups WHERE active = 1 ORDER BY name");

		while($groupArr = mysql_fetch_assoc($res)) {

			echo "\nProcessing: ".$groupArr['name']."\n";
			flush();

			$this->message = array();

			$this->updateGroup($groupArr);
		}

	}

	function updateGroupList() {
		echo "Getting grouplist from server...\n";

		$groups = $this->nntp->getGroups();
		echo "Processing grouplist\n";

		foreach($groups AS $group) {
			if(stristr($group['group'], 'alt.bin')) {
				$res = mysql_query("SELECT ID FROM groups WHERE name = '{$group['group']}'");
				if(mysql_num_rows($res) > 0) {
					$ID = mysql_result($res, 0, 0);
					mysql_query("UPDATE groups SET description = '{$group['desc']}' WHERE ID = {$ID}");
					echo "Updated {$group['group']}\n";
				} else {
					mysql_query("INSERT INTO groups (name, description, active) VALUES ('{$group['group']}', '{$group['desc']}', 0)");
					echo "New group {$group['group']}\n";
				}
			}
		}

		echo "Done\n";
	}

	function quit() {
		$this->nntp->quit();
	}

	function readablesize($bytes, $dec=1) {
		if($bytes >= 1099511627776) {
			$return = round($bytes / 1024 / 1024 / 1024 / 1024, $dec);
			$suffix = "TB";
		} elseif($bytes >= 1073741824) {
			$return = round($bytes / 1024 / 1024 / 1024, $dec);
			$suffix = "GB";
		} elseif($bytes >= 1048576) {
			$return = round($bytes / 1024 / 1024, $dec);
			$suffix = "MB";
		} elseif($bytes >= 1024) {
			$return = round($bytes / 1024, $dec);
			$suffix = "KB";
		} else {
			$return = $bytes;
			$suffix = "Byte";
		}
		$return = number_format($return,$dec,',','');
		$return .= " ".$suffix;
		return $return;
	}

	function getmicrotime() {
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

	function sec2min($diff) {
		$minsDiff = 0; $secsDiff = 0;
		$sec_in_a_min = 60;
		while($diff >= 60) {
			$minsDiff++;
			$diff -= 60;
		}
		$secsDiff = $diff;
		return ($minsDiff.'m '.$secsDiff.'s)');
	}

	function delOldBinaries($groupID='') {

		if(is_numeric($groupID)) {
			$gr = " AND groupID = {$groupID} ";
		}
		$retention = 20;
		$count = 0;
		echo "Deleting posts older than {$retention} days\n";
		$sql = "SELECT ID FROM binaries WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date)) / 3600 / 24 > {$retention} {$gr}";
		$res = mysql_query($sql);
		echo "Deleting ".mysql_num_rows($res)." binaries\n";
		while($arr = mysql_fetch_assoc($res)) {
			$sql = "DELETE FROM parts WHERE binaryID = {$arr['ID']}";
			mysql_query($sql);

			$sql = "DELETE FROM binaries WHERE ID = {$arr['ID']}";
			mysql_query($sql);
			$count++;
		}
		echo "Deleted {$count} binaries\n";
		echo "Done\n\n";

	}

	function optimimize() {
		echo "Optimizing table: binaries...\n";
		mysql_query("OPTIMIZE TABLE binaries");
		echo "Optimizing table: parts...\n";
		mysql_query("OPTIMIZE TABLE parts");
		echo "Done\n\n";
	}

} //end class

?>

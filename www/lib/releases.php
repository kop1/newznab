<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/category.php");

class Releases
{	
	const PROCSTAT_NEW = 0;
	const PROCSTAT_READYTORELEASE = 1;
	const PROCSTAT_WRONGPARTS = 2;
	const PROCSTAT_BADTITLEFORMAT = 3;
	const PROCSTAT_RELEASED = 4;

	//TODO: Move to site table
	const maxAttemptsToProcessBinaryIntoRelease = 3;
	const maxDaysToProcessWrongFormatBinaryIntoRelease = 7;
	
	public function get()
	{			
		$db = new DB();
		return $db->query("select releases.* from releases");		
	}

	public function getRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID order by adddate".$limit);		
	}
	
	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from releases");		
		return $res["num"];
	}
	
	public function delete($id)
	{			
		$db = new DB();
		$this->deleteCommentsForRelease($id);
		$db->query(sprintf("delete from releases where id = %d", $id));		
	}

	public function search($search)
	{			
		$db = new DB();
		$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) order by MATCH (searchname) AGAINST (%s IN BOOLEAN MODE) desc, adddate desc ", $db->escapeString($search), $db->escapeString($search)));		

		if (!$res)
				$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) order by MATCH (searchname) AGAINST (%s IN BOOLEAN MODE) desc, adddate desc ", $db->escapeString($search."*"), $db->escapeString($search."*")));		

		return $res;
	}	
	
	public function getByGuid($guid)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID  left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where guid = %s ", $db->escapeString($guid)));		
	}	
	
	public function getById($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select releases.*, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID where releases.ID = %d ", $id));		
	}	

	public function updateGrab($guid)
	{			
		$db = new DB();
		$db->queryOneRow(sprintf("update releases set grabs = grabs + 1 where guid = %s", $db->escapeString($guid)));		
	}
	
	function processReleases()
	{
		$db = new DB;
		$cat = new Category;
		$retcount = 0;

		$res = $db->query(sprintf("SELECT * from binaries where procstat = %d", Releases::PROCSTAT_NEW));
		
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
						$db->escapeString($res[$recInd]["matches"][7]), $db->escapeString($res[$recInd]["matches"][1]), $res[$recInd]["matches"][3], $res[$recInd]["matches"][4], Releases::PROCSTAT_READYTORELEASE, $res[$recInd]["ID"] ));
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
		$res = $db->query(sprintf("SELECT distinct relname, reltotalpart, groupID from binaries where procstat = %d", Releases::PROCSTAT_READYTORELEASE));
		foreach($res as $arr) 
		{
			$relsearchname = preg_replace (array ('/^\[[\d]{5,7}\]-?\[#[\w]+@[\w]+net\](-?\[full\])?/i', '/([^\w-]|_)/i', '/-/', '/\s[\s]+/', '/^([\W]|_)*/i', '/([\W]|_)*$/i', '/(\s)(19\d\d|20[012]\d)(?:\s|$)/'), array ('', ' ',' - ',' ', '', '', '\1(\2)\3'), $arr["relname"]);
			
			//
			// insert the header release with a clean name
			// 
			$relid = $db->queryInsert(sprintf("insert into releases (name, searchname, totalpart, groupID, adddate, guid, categoryID) values (%s, %s, %d, %d, now(), md5(%s), %d)", 
										$db->escapeString($arr["relname"]), $db->escapeString($relsearchname), $arr["reltotalpart"], $arr["groupID"], $db->escapeString(uniqid()), $cat->determineCategory($arr["groupID"], $arr["relname"]) ));
			
			//
			// tag every binary for this release with its parent release id
			// remove the release name from the binary as its no longer required
			//
			$db->query(sprintf("update binaries set relname = null, procstat = %d, releaseID = %d where relname = %s and procstat = %d and releaseID is null and groupID = %d and reltotalpart = %d ", 
								Releases::PROCSTAT_RELEASED, $relid, $db->escapeString($arr["relname"]), Releases::PROCSTAT_READYTORELEASE, $arr["groupID"], $arr["reltotalpart"]));

			$retcount++;
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
		// update any newly added binaries total file size from their parts.
		// denormalised to make file listing queries less intensive
		//
		$db->query(sprintf("UPDATE binaries
		INNER JOIN 
		(
			SELECT binaryID, SUM(parts.size) AS size
			FROM parts
			INNER JOIN binaries ON binaries.ID = parts.binaryID AND binaries.procstat = %d AND binaries.size = 0
			GROUP BY binaryID
		) X ON x.binaryID = binaries.ID
		SET binaries.size = x.size", Releases::PROCSTAT_RELEASED ));
		
		//
		// tidy away any binaries which have been attempted to be grouped into 
		// a release more than x times
		//
		$res = $db->query(sprintf("update binaries set procstat = %d where procstat = %d and procattempts > %d ", Releases::PROCSTAT_WRONGPARTS, Releases::PROCSTAT_NEW, Releases::maxAttemptsToProcessBinaryIntoRelease));

		//
		// tidy away any binaries which have never been attempted to be grouped 
		// into a release and are now aging
		//
		$res = $db->query(sprintf("update binaries set procstat = %d where procstat = %d and date < now() - interval %d day", Releases::PROCSTAT_BADTITLEFORMAT, Releases::PROCSTAT_NEW, Releases::maxDaysToProcessWrongFormatBinaryIntoRelease));
		
		return $retcount;
	}	

	public function getComments($id)
	{			
		$db = new DB();
		return $db->query(sprintf("SELECT releasecomment.*, users.username FROM releasecomment LEFT OUTER JOIN users ON users.ID = releasecomment.userID where releaseID = %d", $id));		
	}
	
	public function getCommentCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow(sprintf("select count(ID) as num from releasecomment"));		
		return $res["num"];
	}

	public function deleteComment($id)
	{			
		$db = new DB();
		return $db->query(sprintf("delete from releasecomment where ID = %d", $id));		
	}
	
	public function deleteCommentsForRelease($id)
	{			
		$db = new DB();
		return $db->query(sprintf("delete from releasecomment where releaseID = %d", $id));		
	}

	public function addComment($id, $text, $userid, $host)
	{			
		$db = new DB();
		return $db->queryInsert(sprintf("INSERT INTO releasecomment (`releaseID`, 	`text`, 	`userID`, 	`createddate`, 	`host`	)	
						VALUES (%d, 	%s, 	%d, 	now(), 	%s	)", $id, $db->escapeString($text), $userid, $db->escapeString($host) ));		
	}
	
	public function getCommentsRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(" SELECT releasecomment.*, users.username FROM releasecomment LEFT OUTER JOIN users ON users.ID = releasecomment.userID order by releasecomment.createddate desc ".$limit);		
	}
}
?>
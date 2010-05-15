<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/category.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/tvrage.php");

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
		return $db->query("select releases.*, g.name as group_name, c.title as category_name  from releases left outer join category c on c.ID = releases.categoryID left outer join groups g on g.ID = releases.groupID");		
	}

	public function getRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID order by adddate desc".$limit);		
	}
	
	public function getBrowseCount($category)
	{
		$db = new DB();
		$cat = ($category != -1 ? sprintf(" where releases.categoryID = %d", $category) : "");
		$res = $db->queryOneRow(sprintf("select count(ID) as num from releases %s", $cat));		
		return $res["num"];	
	}
	
	public function getBrowseRange($category, $start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		$cat = ($category != -1 ? sprintf(" where releases.categoryID = %d", $category) : "");
		
		return $db->query(sprintf(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID %s order by adddate".$limit, $cat));		
	}
	
	public function getRss($category, $num)
	{		
		$db = new DB();
		
		$limit = " LIMIT 0,".($num > 100 ? 100 : $num);
		$cat = ($category != -1 ? sprintf(" where releases.categoryID = %d", $category) : "");
			
		return $db->query(sprintf(" SELECT releases.*, g.name as group_name, concat(cp.title, ' > ', c.title) as category_name, coalesce(cp.ID,0) as parentCategoryID from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID left outer join groups g on g.ID = releases.groupID %s order by adddate %s" ,$cat, $limit));
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

	public function update($id, $name, $searchname, $fromname, $category, $parts, $grabs, $size, $posteddate, $addeddate)
	{			
		$db = new DB();

		$db->query(sprintf("update releases set name=%s, searchname=%s, fromname=%s, categoryID=%d, totalpart=%d, grabs=%d, size=%d, postdate=%s, adddate=%s where id = %d", 
			$db->escapeString($name), $db->escapeString($searchname), $db->escapeString($fromname), $category, $parts, $grabs, $size, $db->escapeString($posteddate), $db->escapeString($addeddate), $id));		
	}	
	
	public function search($search, $limit=1000)
	{			
		$db = new DB();
		$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) order by MATCH (searchname) AGAINST (%s IN BOOLEAN MODE) desc, adddate desc limit %d ", $db->escapeString($search), $db->escapeString($search), $limit));		

		if (!$res)
			$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) order by MATCH (searchname) AGAINST (%s IN BOOLEAN MODE) desc, adddate desc limit %d ", $db->escapeString($search."*"), $db->escapeString($search."*"), $limit));		

		return $res;
	}	
	
	public function searchbyRageId($rageId, $series="", $episode="", $limit=1000)
	{			
		$db = new DB();
		
		if ($series != "")
			$series = sprintf(" and upper(releases.season) = upper(%s)", $db->escapeString($series));
		
		if ($episode != "")
			$episode = sprintf(" and upper(releases.episode) = upper(%s)", $db->escapeString($episode));

		$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where rageID = %d %s %s order by adddate desc limit %d ", $rageId, $series, $episode, $limit));		

		return $res;
	}		
	
	
	
	public function searchSimilar($name, $limit=6)
	{			
		$words = str_word_count(str_replace(".", " ", $name), 2);
		$firstwords = array_slice($words, 0, 2);
		$name = implode(' ', $firstwords);
		return $this->search($name, $limit);
	}	
	
	public function getByGuid($guid)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID  left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where guid = %s ", $db->escapeString($guid)));		
	}	

	public function getbyRageId($rageid, $series = "", $episode = "")
	{			
		$db = new DB();

		if ($series != "")
			$series = sprintf(" and upper(releases.season) = upper(%s)", $db->escapeString($series));
		
		if ($episode != "")
			$episode = sprintf(" and upper(releases.episode) = upper(%s)", $db->escapeString($episode));

		return $db->queryOneRow(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID  left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where rageID = %d %s %s", $rageid, $series, $episode));		
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
		$res = $db->query(sprintf("SELECT distinct relname, groupID, count(ID) as parts from binaries where procstat = %d and relname is not null group by relname, groupID", Releases::PROCSTAT_READYTORELEASE));
		foreach($res as $arr) 
		{
			$relsearchname = preg_replace (array ('/^\[[\d]{5,7}\](?:-?\[full\])?-?\[#[\w\.]+@[\w]+net\](-?\[full\])?/i', '/([^\w-]|_)/i', '/-/', '/\s[\s]+/', '/^([\W]|_)*/i', '/([\W]|_)*$/i', '/[\s]+/'), array ('', ' ','-',' ', '', '', '.'), $arr["relname"]);
			
			//
			// insert the header release with a clean name
			// 
			$relid = $db->queryInsert(sprintf("insert into releases (name, searchname, totalpart, groupID, adddate, guid, categoryID, rageID) values (%s, %s, %d, %d, now(), md5(%s), %d, -1)", 
										$db->escapeString($arr["relname"]), $db->escapeString($relsearchname), $arr["parts"], $arr["groupID"], $db->escapeString(uniqid()), $cat->determineCategory($arr["groupID"], $arr["relname"]) ));
			
			//
			// tag every binary for this release with its parent release id
			// remove the release name from the binary as its no longer required
			//
			$db->query(sprintf("update binaries set relname = null, procstat = %d, releaseID = %d where relname = %s and procstat = %d and releaseID is null and groupID = %d ", 
								Releases::PROCSTAT_RELEASED, $relid, $db->escapeString($arr["relname"]), Releases::PROCSTAT_READYTORELEASE, $arr["groupID"]));

			$retcount++;
		}

		//
		// calculate the total size of all releases
		// TODO: do something with this to make it a bit more scalable, 
		// its not really worthwhile updating the size of every release in the database :/
		//
		$db->query("UPDATE releases INNER JOIN
								(
								SELECT binaries.releaseID, SUM(parts.size) AS size
								FROM parts
								INNER JOIN binaries ON parts.binaryID = binaries.ID
								WHERE releaseID IS NOT NULL
								GROUP BY binaries.releaseID
								) p ON p.releaseID = releases.ID AND releases.size = 0
								SET releases.size = p.size");	

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
		) x ON x.binaryID = binaries.ID
		SET binaries.size = x.size", Releases::PROCSTAT_RELEASED ));

		//
		// Process all TV related releases which will assign their series/episode/rage data
		//
		$this->processTvSeriesData();

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

	public function processTvSeriesData()
	{
		$ret = 0;
		$db = new DB();
		$rage = new TvRage();
		$res = $db->query(sprintf("SELECT searchname, ID from releases where rageID = -1"));
		foreach($res as $arr) 
		{
			//
			// see if its in the existing rage list
			// only process releases which have a searchname like S01E01 or S01E02-E03
			// 
			if (preg_match('/^(.*?)((S([\d]+))((.?E([\d]+))+))(.*$)/i', $arr["searchname"], $matches))
			{
				//
				// Get a clean name version of the release (the text upto the S01E01 part) and the series and episode parts
				//
				$relcleanname = str_replace(".", " ", $matches[1]);
				$fullseries = $matches[2];
				$series = $matches[3];
				$episode = $matches[5];
				
				$db->query(sprintf("update releases set seriesfull = %s, season = %s, episode = %s where ID = %d", 
							$db->escapeString($fullseries), $db->escapeString($series), $db->escapeString($episode), $arr["ID"]));

				//
				// try and retrieve the entry from tvrage
				//
				$id = $rage->getRageId($relcleanname);
				if ($id != -1)
				{
					$db->query(sprintf("update releases set rageID = %d where ID = %d", $id, $arr["ID"]));
				}
				else
				{
					//
					// Cant find rageid, so set rageid to na
					// 
					$db->query(sprintf("update releases set rageID = -2 where ID = %d", $arr["ID"]));
				}
				
				$ret++;
			}
			else
			{
				//
				// Not a tv episode, so set rageid to na
				// 
				$db->query(sprintf("update releases set rageID = -2 where ID = %d", $arr["ID"]));
			}
		}
		
		return $ret;
	}

	public function getCommentById($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT * from releasecomment where ID = %d", $id));		
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
		$res = $this->getCommentById($id);
		if ($res)
		{
			$db->query(sprintf("delete from releasecomment where ID = %d", $id));		
			$this->updateReleaseCommentCount($res["ID"]);
		}
	}
	
	public function deleteCommentsForRelease($id)
	{			
		$db = new DB();
		$db->query(sprintf("delete from releasecomment where releaseID = %d", $id));		
		$this->updateReleaseCommentCount($id);
	}

	public function addComment($id, $text, $userid, $host)
	{			
		$db = new DB();
		$comid = $db->queryInsert(sprintf("INSERT INTO releasecomment (`releaseID`, 	`text`, 	`userID`, 	`createddate`, 	`host`	)	
						VALUES (%d, 	%s, 	%d, 	now(), 	%s	)", $id, $db->escapeString($text), $userid, $db->escapeString($host) ));		
		$this->updateReleaseCommentCount($id);					
		return $comid;
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
	
	public function updateReleaseCommentCount($relid)
	{			
		$db = new DB();
		return $db->queryInsert(sprintf("update releases
				set comments = (select count(ID) from releasecomment where releasecomment.releaseID = %d)
				where releases.ID = %d", $relid, $relid ));		
	}
	
	public function getCommentCountForUser($uid)
	{			
		$db = new DB();
		$res = $db->queryOneRow(sprintf("select count(ID) as num from releasecomment where userID = %d", $uid));		
		return $res["num"];
	}
	
	public function getCommentsForUserRange($uid, $start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(sprintf(" SELECT releasecomment.*, users.username FROM releasecomment LEFT OUTER JOIN users ON users.ID = releasecomment.userID where userID = %d order by releasecomment.createddate desc ".$limit, $uid));		
	}
}
?>
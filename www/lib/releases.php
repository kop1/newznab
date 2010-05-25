<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/tvrage.php");
require_once(WWW_DIR."/lib/nzb.php");

class Releases
{	
	const PROCSTAT_NEW = 0;
	const PROCSTAT_TITLEMATCHED = 5;
	const PROCSTAT_READYTORELEASE = 1;
	const PROCSTAT_WRONGPARTS = 2;
	const PROCSTAT_BADTITLEFORMAT = 3;
	const PROCSTAT_RELEASED = 4;

	//TODO: Move to site table
	const maxAttemptsToProcessBinaryIntoRelease = 3;
	const maxDaysToProcessWrongFormatBinaryIntoRelease = 7;
	const numOverrideBinariesToSelect = 0;
	const numReleasesToProcessPerTime = 7500;
	
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
		
		return $db->query(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID order by postdate desc".$limit);		
	}
	
	public function getBrowseCount($category)
	{
		$db = new DB();
		$cat = ($category != -1 ? sprintf(" where releases.categoryID = %d", $category) : "");
		$res = $db->queryOneRow(sprintf("select count(ID) as num from releases %s", $cat));		
		return $res["num"];	
	}
	
	public function getBrowseRange($category, $start, $num, $orderby)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		$cat = ($category != -1 ? sprintf(" where releases.categoryID = %d", $category) : "");
		
		$order = $this->getBrowseOrder($orderby);

		return $db->query(sprintf(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID %s order by %s %s".$limit, $cat, $order[0], $order[1]));		
	}
	
	public function getBrowseOrder($orderby)
	{
		$order = ($orderby == '') ? 'posted_desc' : $orderby;
		$orderArr = explode("_", $order);
		switch($orderArr[0]) {
			case 'cat':
				$orderfield = 'categoryID';
			break;
			case 'name':
				$orderfield = 'searchname';
			break;
			case 'size':
				$orderfield = 'size';
			break;
			case 'files':
				$orderfield = 'totalpart';
			break;
			case 'stats':
				$orderfield = 'grabs';
			break;
			case 'posted': 
			default:
				$orderfield = 'postdate';
			break;
		}
		$ordersort = (isset($orderArr[1]) && preg_match('/^asc|desc$/i', $orderArr[1])) ? $orderArr[1] : 'desc';
		return array($orderfield, $ordersort);
	}
	
	public function getBrowseOrdering()
	{
		return array('name_asc', 'name_desc', 'cat_asc', 'cat_desc', 'posted_asc', 'posted_desc', 'size_asc', 'size_desc', 'files_asc', 'files_desc', 'stats_asc', 'stats_desc');
	
	}
	
	public function getRss($category, $num, $uid=0)
	{		
		$db = new DB();
		
		$limit = " LIMIT 0,".($num > 100 ? 100 : $num);
		$cat = "";
		if ($category > 0)
			$cat = sprintf(" where releases.categoryID = %d", $category);
		elseif ($category == -2)
			$cat = sprintf(" where releases.ID in (select releaseID from usercart where userID = %d)", $uid);
			
		return $db->query(sprintf(" SELECT releases.*, g.name as group_name, concat(cp.title, ' > ', c.title) as category_name, coalesce(cp.ID,0) as parentCategoryID from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID left outer join groups g on g.ID = releases.groupID %s order by postdate desc %s" ,$cat, $limit));
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
		$users = new Users();
		$this->deleteCommentsForRelease($id);
		$this->deleteReleaseNzb($id);
		$users->delCartForRelease($id);
		$db->query(sprintf("delete from releases where id = %d", $id));		
	}

	public function update($id, $name, $searchname, $fromname, $category, $parts, $grabs, $size, $posteddate, $addeddate, $rageid, $seriesfull, $season, $episode)
	{			
		$db = new DB();

		$db->query(sprintf("update releases set name=%s, searchname=%s, fromname=%s, categoryID=%d, totalpart=%d, grabs=%d, size=%d, postdate=%s, adddate=%s, rageID=%d, seriesfull=%s, season=%s, episode=%s where id = %d", 
			$db->escapeString($name), $db->escapeString($searchname), $db->escapeString($fromname), $category, $parts, $grabs, $size, $db->escapeString($posteddate), $db->escapeString($addeddate), $rageid, $db->escapeString($seriesfull), $db->escapeString($season), $db->escapeString($episode), $id));		
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
		{
			if (is_numeric($series))
				$series = sprintf('S%02d', $series);

			$series = sprintf(" and upper(releases.season) = upper(%s)", $db->escapeString($series));
		}
		if ($episode != "")
		{
			if (is_numeric($episode))
				$episode = sprintf('E%02d', $episode);

			$episode = sprintf(" and upper(releases.episode) = upper(%s)", $db->escapeString($episode));
		}
		$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where rageID = %d %s %s order by adddate desc limit %d ", $rageId, $series, $episode, $limit));		

		return $res;
	}		
	
	public function searchSimilar($currentid, $name, $limit=6)
	{			
		$words = str_word_count(str_replace(".", " ", $name), 2);
		$firstwords = array_slice($words, 0, 2);
		$name = implode(' ', $firstwords);
		$results = $this->search($name, $limit);
		if (!$results)
			return $results;

		$ret = array();
		foreach ($results as $res)
			if ($res["ID"] != $currentid)
				$ret[] = $res;

		return $ret;
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
		{
			if (is_numeric($series))
				$series = sprintf('S%02d', $series);
			
			$series = sprintf(" and upper(releases.season) = upper(%s)", $db->escapeString($series));
		}
		
		if ($episode != "")
		{
			if (is_numeric($episode))
				$episode = sprintf('E%02d', $episode);

			$episode = sprintf(" and upper(releases.episode) = upper(%s)", $db->escapeString($episode));
		}
		
		return $db->queryOneRow(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID  left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where rageID = %d %s %s", $rageid, $series, $episode));		
	}	

	public function removeRageIdFromReleases($rageid)
	{
		$db = new DB();
		$res = $db->queryOneRow(sprintf("select count(ID) as num from releases where rageID = %d", $rageid));		
		$ret = $res["num"];
		$res = $db->query(sprintf("update releases set rageID = -1, seriesfull = null, season = null, episode = null where rageID = %d", $rageid));		
		return $ret;
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
	
	function processReleases($echooutput=false) 
	{
		$db = new DB;
		$cat = new Category;
		$retcount = 0;
		$page = new Page();
		$nzb = new Nzb();

		//
		// should match fairly typical releases in format "relname [1/12] filename yenc"
		// handles brackets or square
		//
		$pattern = '/^([\s]*(.*)(?=(?:\[|\()(?:[\s]*0*)([\d]+)[\s]*(?:[^\d\]]{1,5})(?:[\s]*0*)([\d]+)[\s]*(?:\)|\])(?:[\s][^\w\"\']*)(\'|"?)([\W]*)(.*?)(\5)[\s]*(yEnc)?[\s]*$))/';

		$result = $db->queryDirect(sprintf("SELECT ID, name from binaries where procstat = %d", Releases::PROCSTAT_NEW));
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) 
		{
				$retcount ++;
		    if (preg_match ($pattern, $row["name"], $matches) > 0) 
		    {
						$db->query(sprintf("update binaries set filename = %s, relname = %s, relpart = %d, reltotalpart = %d, procstat=%d where ID = %d", 
							$db->escapeString($matches[7]), $db->escapeString($matches[1]), $matches[3], $matches[4], Releases::PROCSTAT_TITLEMATCHED, $row["ID"] ));
		    }

		    if ($echooutput && ($retcount % 100 == 0))
		    	echo "processed ".$retcount." binaries stage one\n";
		}
		$retcount=0;

		$result = $db->queryDirect(sprintf("SELECT relname, reltotalpart, count(*) as num from binaries where procstat = %d group by relname, reltotalpart", Releases::PROCSTAT_TITLEMATCHED));
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) 
		{
			$retcount ++;
			if ($row["reltotalpart"] == $row["num"])
			{
				$db->query(sprintf("update binaries set procstat=%d where relname = %s and procstat = %d", Releases::PROCSTAT_READYTORELEASE, $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED));
			}
			else
			{
				$db->query(sprintf("update binaries set procattempts = procattempts + 1 where relname = %s and procstat = %d", $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED ));
			}
			if ($echooutput && ($retcount % 100 == 0))
	    	echo "processed ".$retcount." binaries stage two\n";
		}
		$retcount=0;

		$result = $db->queryDirect(sprintf("SELECT distinct relname, groupID, g.name as group_name, count(binaries.ID) as parts from binaries inner join groups g on g.ID = binaries.groupID where procstat = %d and relname is not null group by relname, g.name, groupID", Releases::PROCSTAT_READYTORELEASE));
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) 
		{
			$retcount ++;

			$relsearchname = preg_replace (array ('/^\[[x\d]{4,7}\](?:-?\[full\])?-?\[(#[\w\.]+@[\w]+net|[a-z][\w.]+[a-z])\](-?\[full|vwhores|u4all|teevee|lostwhores|goodwifewhores\])?/i', '/([^\w-]|_)/i', '/-/', '/\s[\s]+/', '/^([\W]|_)*/i', '/([\W]|_)*$/i', '/[\s]+/'), array ('', ' ','-',' ', '', '', '.'), $row["relname"]);

			//
			// insert the header release with a clean name
			// 
			$relid = $db->queryInsert(sprintf("insert into releases (name, searchname, totalpart, groupID, adddate, guid, categoryID, rageID) values (%s, %s, %d, %d, now(), md5(%s), %d, -1)", 
										$db->escapeString($row["relname"]), $db->escapeString($relsearchname), $row["parts"], $row["groupID"], $db->escapeString(uniqid()), $cat->determineCategory($row["group_name"], $row["relname"]) ));
			
			//
			// tag every binary for this release with its parent release id
			// remove the release name from the binary as its no longer required
			//
			$db->query(sprintf("update binaries set relname = null, procstat = %d, releaseID = %d where relname = %s and procstat = %d and releaseID is null and groupID = %d ", 
								Releases::PROCSTAT_RELEASED, $relid, $db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE, $row["groupID"]));

			//
			// create the nzbxml file for each release.
			//
			//$nzbdata = $nzb->getNZBforReleaseId($relid);
			//$page->smarty->assign('binaries',$nzbdata);
			//$this->addReleaseNzb($relid, $page->smarty->fetch(WWW_DIR.'/templates/nzb.tpl'));

	    if ($echooutput && ($retcount % 2 == 0))
	    	echo "processed ".$retcount." binaries stage three\n";
		}
		
    if ($echooutput)
    	echo "updating release size\n";		
		
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

	    if ($echooutput)
	    	echo "updating postdate\n";		
								
		//
		// update the postdate and poster name of all new releases
		// TODO: like the size above, this could probably be done somewhere better
		//
		$db->query("update releases inner join
							(
							SELECT binaries.releaseID, binaries.fromname, max(date) as pdate
							from binaries
							where releaseID is not null
							group by binaries.releaseID, binaries.fromname
							) p on p.releaseID = releases.ID
							set releases.postdate = p.pdate, releases.fromname = p.fromname
							where postdate is null");				
	    
		if ($echooutput)
	    	echo "updating binary size\n";		
		
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
		if ($echooutput)
			echo "processing tv rage data\n";		

		$this->processTvSeriesData($echooutput);

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
		
		if ($echooutput)
			echo "processed ". $retcount." releases\n";

		return $retcount;
	}	

	public function processTvSeriesData($echooutput=false)
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
				$id = $rage->getRageId($relcleanname, $echooutput);
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
	
	public function deleteCommentsForUser($id)
	{			
		$db = new DB();
		
		$numcomments = $this->getCommentCountForUser($id);
		if ($numcomments > 0)
		{
			$comments = $this->getCommentsForUserRange($id, 0, $numcomments);
			foreach ($comments as $comment)
			{
				$this->deleteComment($comment["ID"]);
				$this->updateReleaseCommentCount($comment["releaseID"]);
			}
		}
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
		$db->query(sprintf("update releases
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
	
	public function addReleaseNzb($relid, $nzb)
	{
		$db = new DB();
		return $db->queryInsert(sprintf("insert into releasenzb (releaseID, nzb) values (%d, compress(%s))", $relid, $db->escapeString($nzb) ));		
	}
	
	public function deleteReleaseNzb($relid)
	{
		$db = new DB();
		$db->query(sprintf("delete from releasenzb where releaseID = %d", $relid));		
	}

}
?>

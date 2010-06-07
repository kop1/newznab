<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/tvrage.php");
require_once(WWW_DIR."/lib/nzb.php");
require_once(WWW_DIR."/lib/zipfile.php");

class Releases
{	
	const PROCSTAT_NEW = 0;
	const PROCSTAT_TITLEMATCHED = 5;
	const PROCSTAT_READYTORELEASE = 1;
	const PROCSTAT_WRONGPARTS = 2;
	const PROCSTAT_BADTITLEFORMAT = 3;
	const PROCSTAT_RELEASED = 4;
	const PROCSTAT_DUPLICATE = 6;

	//TODO: Move to site table
	const maxAttemptsToProcessBinaryIntoRelease = 3;
	
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
		$cat = "";
		if ($category != -1)
		{
			$categ = new Category();
			if ($categ->isParent($category))
			{
				$children = $categ->getChildren($category);
				$chlist = "-99";
				foreach ($children as $child)
					$chlist.=", ".$child["ID"];

				if ($chlist != "-99")
						$cat = "where releases.categoryID in (".$chlist.")";
			}
			else
			{
				$cat =  sprintf(" where releases.categoryID = %d", $category);
			}
		}

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
		
		$cat = "";
		if ($category != -1)
		{
			$categ = new Category();
			if ($categ->isParent($category))
			{
				$children = $categ->getChildren($category);
				$chlist = "-99";
				foreach ($children as $child)
					$chlist.=", ".$child["ID"];

				if ($chlist != "-99")
						$cat = "where releases.categoryID in (".$chlist.")";
			}
			else
			{
				$cat =  sprintf(" where releases.categoryID = %d", $category);
			}
		}

		$order = $this->getBrowseOrder($orderby);

		return $db->query(sprintf(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name, rn.ID as nfoID from releases left outer join releasenfo rn on rn.releaseID = releases.ID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID %s order by %s %s".$limit, $cat, $order[0], $order[1]));		
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
		$s = new Sites();
		$site = $s->get();

		//
		// delete from disk.
		//
		$rel = $this->getById($id);
		if ($rel && file_exists($site->nzbpath.$rel["guid"].".nzb.gz")) 
			unlink($site->nzbpath.$rel["guid"].".nzb.gz");
		
		$this->deleteReleaseNfo($id);
		$this->deleteCommentsForRelease($id);
		$users->delCartForRelease($id);
		$db->query(sprintf("delete from releases where id = %d", $id));		
	}

	public function update($id, $name, $searchname, $fromname, $category, $parts, $grabs, $size, $posteddate, $addeddate, $rageid, $seriesfull, $season, $episode, $imdbid)
	{			
		$db = new DB();

		$db->query(sprintf("update releases set name=%s, searchname=%s, fromname=%s, categoryID=%d, totalpart=%d, grabs=%d, size=%d, postdate=%s, adddate=%s, rageID=%d, seriesfull=%s, season=%s, episode=%s, imdbID=%d where id = %d", 
			$db->escapeString($name), $db->escapeString($searchname), $db->escapeString($fromname), $category, $parts, $grabs, $size, $db->escapeString($posteddate), $db->escapeString($addeddate), $rageid, $db->escapeString($seriesfull), $db->escapeString($season), $db->escapeString($episode), $imdbid, $id));		
	}	
	
	public function search($search, $cat=-1, $limit=1000)
	{			
		$db = new DB();
		
		$catsrch = "";
		if ($cat != -1)
			$catsrch = sprintf(" and releases.categoryID = %d", $cat);
			
		$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, rn.ID as nfoID from releases left outer join releasenfo rn on rn.releaseID = releases.ID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) %s order by MATCH (searchname) AGAINST (%s IN BOOLEAN MODE) desc, adddate desc limit %d ", $db->escapeString($search), $catsrch, $db->escapeString($search), $limit));		

		if (!$res)
			$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, rn.ID as nfoID from releases left outer join releasenfo rn on rn.releaseID = releases.ID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) %s order by MATCH (searchname) AGAINST (%s IN BOOLEAN MODE) desc, adddate desc limit %d ", $db->escapeString($search."*"), $catsrch, $db->escapeString($search."*"), $limit));		

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
		$results = $this->search($name, -1, $limit);
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
		return $db->queryOneRow(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where guid = %s ", $db->escapeString($guid)));		
	}	

	//
	// writes a zip file of an array of release guids directly to the stream
	//
	public function getZipped($guids)
	{
		$s = new Sites();
		$site = $s->get();
		$zipfile = new zipfile();
		
		foreach ($guids as $guid)
		{
			if (file_exists($site->nzbpath.$guid.".nzb.gz")) 
			{
				ob_start();
				@readgzfile($site->nzbpath.$guid.".nzb.gz");
				$nzbfile = ob_get_contents();
				ob_end_clean();

				$filename = $guid;
				$r = $this->getByGuid($guid);
				if ($r)
					$filename = $r["searchname"];
				
				$zipfile->add_file($nzbfile, $filename.".nzb");
			}
		}
		
		return $zipfile->file();
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
		$nzb = new Nzb;
		$page = new Page;
		$retcount = 0;
		
		if (!file_exists($page->site->nzbpath))
		{
			echo "bad or missing nzb directory - ".$page->site->nzbpath;
			return;
		}
		
		//
		// Get all regexes for all groups which are to be applied to new binaries
		// in order of how they should be applied
		//
		$result = $db->queryDirect("SELECT ID, regex, coalesce(groupID,99999) as groupID from releaseregex order by coalesce(groupID,99999), ordinal");
		while ($regexrow = mysql_fetch_array($result, MYSQL_BOTH)) 
		{
			// Get out all binaries of STAGE0 for current group
			$resbin = $db->queryDirect(sprintf("SELECT ID, name from binaries where groupID = coalesce(%s, groupID) and procstat = %d", ($regexrow["groupID"]=="99999"?"null":$regexrow["groupID"]), Releases::PROCSTAT_NEW));
			while ($rowbin = mysql_fetch_array($resbin, MYSQL_BOTH)) 
			{
				if (preg_match ($regexrow["regex"], $rowbin["name"], $matches) > 0) 
				{
					if (count($matches) >= 3)
					{
						$parts = explode("/", $matches[3]);
							$db->query(sprintf("update binaries set relname = %s, relpart = %d, reltotalpart = %d, procstat=%d where ID = %d", 
								$db->escapeString($matches[2]), $parts[0], $parts[1], Releases::PROCSTAT_TITLEMATCHED, $rowbin["ID"] ));
					}
					else
					{
						if ($echooutput)
							echo "bad regex applied which didnt return right number of capture groups - ".$regexrow["regex"]."\n";
						break;
					}
				}
			}
			if ($echooutput)
				echo "applied regex ".$regexrow["ID"]." for group ".($regexrow["groupID"]=="99999"?"misc":$regexrow["groupID"])."\n";
		}

		//
		// move all binaries which have the correct number of parts on to the next stage.
		//
		$result = $db->queryDirect(sprintf("SELECT relname, reltotalpart, count(ID) as num from binaries where procstat = %d group by relname, reltotalpart", Releases::PROCSTAT_TITLEMATCHED));
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) 
		{
			$retcount ++;
			if ($row["num"] >= $row["reltotalpart"])
			{
				$db->query(sprintf("update binaries set procstat=%d where relname = %s and procstat = %d", 
					Releases::PROCSTAT_READYTORELEASE, $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED));
			}
			else
			{
				echo "Incorrect number of parts ".$row["relname"]."-".$row["num"]."-".$row["reltotalpart"]."\n";
				$db->query(sprintf("update binaries set procattempts = procattempts + 1 where relname = %s and procstat = %d", $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED ));
			}
			if ($echooutput && ($retcount % 100 == 0))
				echo "processed ".$retcount." binaries stage two\n";
		}
		$retcount=0;
   
		//
		// Get out all distinct relname, group from binaries of STAGE2 
		// 
		$result = $db->queryDirect(sprintf("SELECT distinct relname, groupID, g.name as group_name, count(binaries.ID) as parts from binaries inner join groups g on g.ID = binaries.groupID where procstat = %d and relname is not null group by relname, g.name, groupID", Releases::PROCSTAT_READYTORELEASE));
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) 
		{
			$retcount ++;

			//
			// get the last post date and the poster name from the binary
			//
			$bindata = $db->queryOneRow(sprintf("select fromname, MAX(date) as date from binaries where relname = %s and procstat = %d group by fromname", 
										$db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE ));

			//
			// get all releases with the same name with a usenet posted date in a +1-1 date range.
			//
			$relDupes = $db->query(sprintf("select ID from releases where searchname = %s and (%s - INTERVAL 1 DAY < postdate AND %s + INTERVAL 1 DAY > postdate)", 
									$db->escapeString($row["relname"]), $db->escapeString($bindata["date"]), $db->escapeString($bindata["date"])));
			if (count($relDupes) > 0)
			{
				if ($echooutput)
					echo "found duplicate of existing release - ".$row["relname"]."\n";
				
				$db->query(sprintf("update binaries set relname = null, procstat = %d where relname = %s and procstat = %d and groupID = %d ", 
									Releases::PROCSTAT_DUPLICATE, $db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE, $row["groupID"]));

				continue;
			}

			//
			// get total size of this release
			// done in a big OR statement, not an IN as the mysql binaryID index on parts table
			// was not being used.
			//
			$totalSize = "0";
			$binariesForSize = $db->query(sprintf("select ID from binaries use index (ix_binary_relname) where relname = %s and procstat = %d", 
									$db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE ));
			if (count($binariesForSize) > 0)
			{
				$sizeSql = "select sum(size) as totalSize from parts where (";
				foreach ($binariesForSize as $binSizeId)
					$sizeSql.= " binaryID = ".$binSizeId["ID"]." or ";
				$sizeSql.=" 1=2) ";
				$temp = $db->queryOneRow($sizeSql);
				$totalSize = $temp["totalSize"]."";
			}
			
			//
			// insert the release
			// 
			$relguid = md5(uniqid());
			$relid = $db->queryInsert(sprintf("insert into releases (name, searchname, totalpart, groupID, adddate, guid, categoryID, rageID, postdate, fromname, size) values (%s, %s, %d, %d, now(), %s, %d, -1, %s, %s, %s)", 
										$db->escapeString($row["relname"]), $db->escapeString($row["relname"]), $row["parts"], $row["groupID"], $db->escapeString($relguid), $cat->determineCategory($row["group_name"], $row["relname"]), $db->escapeString($bindata["date"]), $db->escapeString($bindata["fromname"]), $totalSize));
			
			//
			// tag every binary for this release with its parent release id
			// remove the release name from the binary as its no longer required
			//
			$db->query(sprintf("update binaries set relname = null, procstat = %d, releaseID = %d where relname = %s and procstat = %d and groupID = %d ", 
								Releases::PROCSTAT_RELEASED, $relid, $db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE, $row["groupID"]));

			//
			// create the zipped nzb file for each release.
			//
			$nzbdata = $nzb->getNZBforReleaseId($relid);
			$page->smarty->assign('binaries',$nzbdata);
			$nzbfile = $page->smarty->fetch(WWW_DIR.'/templates/nzb.tpl');
			$fp = gzopen($page->site->nzbpath.$relguid.".nzb.gz", "w"); 
			if ($fp)
			{
				gzwrite($fp, $nzbfile); 
				gzclose($fp); 
			}
			else
				if ($echooutput)
					echo "Unable to write nzb to file.";
			
			//
			// find an .nfo in the release
			//
			$relnfo = $this->determineReleaseNfo($nzbdata);
			if ($relnfo !== false) {
				$this->addReleaseNfo($relid, $relnfo['binary']['ID']);
			}

			if ($echooutput && ($retcount % 5 == 0))
				echo "processed ".$retcount." binaries stage three\n";
		}    
    
		//
		// Process all TV related releases which will assign their series/episode/rage data
		//
		if ($echooutput)
			echo "processing tv rage data\n";		

		$this->processTvSeriesData($echooutput);
		
		//
		// Process nfo files
		//
		if ($echooutput)
			echo "processing nfo files\n";		

		$this->processNfoFiles($echooutput);
		
		//
		// tidy away any binaries which have been attempted to be grouped into 
		// a release more than x times
		//
		$res = $db->query(sprintf("update binaries set procstat = %d where procstat = %d and procattempts > %d ", Releases::PROCSTAT_WRONGPARTS, Releases::PROCSTAT_NEW, Releases::maxAttemptsToProcessBinaryIntoRelease));

		//
		// delete any parts and binaries which are older than the site's retention days
		//
		$res = $db->query(sprintf("delete from parts where dateadded < now() - interval %d day", $page->site->binretentiondays));
		$res = $db->query(sprintf("delete from binaries where dateadded < now() - interval %d day", $page->site->binretentiondays));
		

		if ($echooutput)
			echo "processed ". $retcount." releases\n";
			
		return $retcount;	
	}

	public function processTvSeriesData($echooutput=false)
	{
		$ret = 0;
		$db = new DB();
		$rage = new TvRage();
		$res = $db->query("SELECT searchname, ID from releases where rageID = -1");
		foreach($res as $arr) 
		{
			$show = '';
			$season = '';
			$episode = '';

			//S01E01
			//S01.E01
			if (preg_match('/^(.*?)\.s(\d{1,2})\.?e(\d{1,3})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = intval($matches[2]);
				$episode = intval($matches[3]);
			//S01
			} elseif (preg_match('/^(.*?)\.s(\d{1,2})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = intval($matches[2]);
				$episode = 'all';
			//1x01
			} elseif (preg_match('/^(.*?)\.(\d{1,2})x(\d{1,3})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = intval($matches[2]);
				$episode = intval($matches[3]);
			//2009.01.01
			} elseif (preg_match('/^(.*?)\.(19|20)(\d{2})\.(\d{2}).(\d{2})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = $matches[2].$matches[3];
				$episode = $matches[4].'/'.$matches[5];
			//01.01.2009
			} elseif (preg_match('/^(.*?)\.(\d{2}).(\d{2})\.(19|20)(\d{2})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = $matches[4].$matches[5];
				$episode = $matches[2].'/'.$matches[3];
			//01.01.09
			} elseif (preg_match('/^(.*?)\.(\d{2}).(\d{2})\.(\d{2})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = ($matches[4] <= 99 && $matches[4] > 15) ? '19'.$matches[4] : '20'.$matches[4];
				$episode = $matches[2].'/'.$matches[3];
			//2009.E01
			} elseif (preg_match('/^(.*?)\.20(\d{2})\.e(\d{1,3})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = '20'.$matches[2];
				$episode = intval($matches[3]);
			//S01E01-E02
			//S01E01-02
			} elseif (preg_match('/^(.*?)\.s(\d{1,2})\.?e(\d{1,3})-e?(\d{1,3})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = intval($matches[2]);
				$episode = intval($matches[3]).'-'.intval($matches[4]);
			//2009.Part1
			} elseif (preg_match('/^(.*?)\.20(\d{2})\.Part(\d{1,2})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = '20'.$matches[2];
				$episode = intval($matches[3]);
			//Part1
			} elseif (preg_match('/^(.*?)\.Part\.?(\d{1,2})\./i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = 1;
				$episode = intval($matches[2]);
			//The.Pacific.Pt.VI.HDTV.XviD-XII
			} elseif (preg_match('/^(.*?)\.Pt\.([civx]+)/i', $arr['searchname'], $matches)) {
				$show = $matches[1];
				$season = 1;
				$epLow = strtolower($matches[2]);
				switch($epLow) {
					case 'i': $e = 1; break;
					case 'ii': $e = 2; break;
					case 'iii': $e = 3; break;
					case 'iv': $e = 4; break;
					case 'v': $e = 5; break;
					case 'vi': $e = 6; break;
					case 'vii': $e = 7; break;
					case 'viii': $e = 8; break;
					case 'ix': $e = 9; break;
					case 'x': $e = 10; break;
				}
				$episode = $e;
			} elseif (preg_match('/^(.*?)\.(ws|hdtv|pdtv|dsr|dvdrip|bdrip|bluray|dvdcsr|internal|proper|repack)/i', $arr['searchname'], $matches)) {
				$show = $matches[1];
			}			
			
			//
			// see if its in the existing rage list
			// only process releases which have a searchname like S01E01 or S01E02-E03
			// 
			if ($season != '' && $episode != '')
			{
				$seriesfull = "";
				if (strlen($season) == 4)
				{
					$seriesfull = $season."/".$episode;
				}
				else
				{
					$season = sprintf('S%02d', $season);
					$episode = sprintf('E%02d', $episode);
					$seriesfull = $season.$episode;
				}

				if ($echooutput)
					echo "tv series - ".$show."-".$seriesfull."\n";
				
				//
				// Get a clean name version of the release (the text upto the S01E01 part) and the series and episode parts
				//
				$relcleanname = str_replace(".", " ", $show);
				
				$db->query(sprintf("update releases set seriesfull = %s, season = %s, episode = %s where ID = %d", 
							$db->escapeString($seriesfull), $db->escapeString($season), $db->escapeString($episode), $arr["ID"]));

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
				if ($echooutput)
					echo "not tv - ".$show."\n";
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
	
	public function determineReleaseNfo($nzbdata)
	{
		$nfos = array();
		foreach ($nzbdata as $bin) {
			if (preg_match('/.*\.nfo[ "\)\]\-]/i', $bin['binary']['name'])) {
				$nfos[$bin['binary']['name']] = $bin;
			}
		}
		ksort($nfos);
		return (is_array($nfos) && !empty($nfos)) ? array_shift($nfos) : false;
	}
	
	public function determineReleaseExtras($nzbdata)
	{
		$allowedFileExt = array('jpg', 'gif', 'png');
		$fileExtPattern = implode('|', $allowedFileExt);
		$files = $typesFound = array();
		foreach ($nzbdata as $bin) {
			if (preg_match('/\.('.$fileExtPattern.')[ "\)\]\-]/i', $bin['binary']['name'], $matches)) {
				if (!in_array($matches[1], $typesFound)) { //optional, only get one of each file ext
					$bin['type'] = $matches[1];
					$files[$bin['binary']['name']] = $bin;
					$typesFound[] = $bin['type'];
				}
			}
		}
		return (is_array($files) && !empty($files)) ? $files : false;
	}

	public function addReleaseNfo($relid, $binid)
	{
		$db = new DB();
		return $db->queryInsert(sprintf("INSERT INTO releasenfo (releaseID, binaryID) VALUES (%d, %d)", $relid, $binid));		
	}
	
	public function deleteReleaseNfo($relid)
	{
		$db = new DB();
		return $db->query(sprintf("delete from releasenfo where releaseID = %d", $relid));		
	}
	
	public function getReleaseNfo($id, $incnfo=true)
	{			
		$db = new DB();
		$selnfo = ($incnfo) ? ', uncompress(nfo) as nfo' : '';
		return $db->queryOneRow(sprintf("SELECT ID, releaseID".$selnfo." FROM releasenfo where releaseID = %d AND nfo IS NOT NULL", $id));		
	}
	
	public function processNfoFiles($echooutput=false)
	{
		$ret = 0;
		$db = new DB();
		$nzb = new Nzb();
		$nntp = new Nntp();
		$res = $db->query(sprintf("SELECT * FROM releasenfo WHERE nfo IS NULL AND attempts < 5"));
		if ($res) {
			$nntp->doConnect();
			foreach($res as $arr) {
				$binaryToFetch = $nzb->getNZB(array($arr['binaryID']));
				$fetchedBinary = $nntp->getBinary($binaryToFetch[0]);
				if ($fetchedBinary !== false) {
					//parse nfo for metadata
					$imdbId = $this->parseImdb($fetchedBinary);
					if ($imdbId !== false) {
						$db->query(sprintf("UPDATE releases SET imdbID = %s WHERE ID = %d", $db->escapeString($imdbId), $arr["releaseID"]));
					}
					
					//insert nfo into database
					$db->query(sprintf("UPDATE releasenfo SET nfo = compress(%s) WHERE ID = %d", $db->escapeString($fetchedBinary), $arr["ID"]));
					$ret++;
				} else {
					//nfo download failed, increment attempts
					$db->query(sprintf("UPDATE releasenfo SET attempts = attempts+1 WHERE ID = %d", $arr["ID"]));
				}
			}
			$nntp->doQuit();
		}
		return $ret;
	}
	
	public function parseImdb($str) {
		preg_match('/imdb.*?tt(\d{7})/i', $str, $matches);
		if (isset($matches[1]) && !empty($matches[1])) {
			return trim($matches[1]);
		}
		return false;
	}
}
?>

<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/binaries.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releaseregex.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/tvrage.php");
require_once(WWW_DIR."/lib/nzb.php");
require_once(WWW_DIR."/lib/nfo.php");
require_once(WWW_DIR."/lib/zipfile.php");

class Releases
{	
	//
	// initial binary state after being added from usenet
	const PROCSTAT_NEW = 0;

	//
	// after a binary has matched a releaseregex
	const PROCSTAT_TITLEMATCHED = 5;

	//
	// after a binary has been confirmed as having the right number of parts
	const PROCSTAT_READYTORELEASE = 1;
	
	//
	// after a binary has has been attempted to be matched for x days and 
	// still has the wrong number of parts
	const PROCSTAT_WRONGPARTS = 2;
	
	//
	// binary that has finished and successfully made it into a release
	const PROCSTAT_RELEASED = 4;
	
	//
	// binary that is identified as already being part of another release 
	//(with the same name posted in a similar date range)
	const PROCSTAT_DUPLICATE = 6;

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
	
	public function getBrowseRange($cat, $start, $num, $orderby, $maxage=-1)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
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
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}	
		
		if ($maxage > 0)
			$maxage = sprintf(" and postdate < now() - interval %d day ", $maxage);
		else
			$maxage = "";
			
		$order = $this->getBrowseOrder($orderby);
		
		return $db->query(sprintf(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, rn.ID as nfoID from releases left outer join releasenfo rn on rn.releaseID = releases.ID and rn.nfo is not null left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where 1=1 %s %s order by %s %s".$limit, $catsrch, $maxage, $order[0], $order[1]));		
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

	public function getForExport($postfrom, $postto, $group)
	{
		$db = new DB();
		if ($postfrom != "")
		{
			$dateparts = explode("/", $postfrom);
			if (count($dateparts) == 3)
				$postfrom = sprintf(" and postdate > %s ", $db->escapeString($dateparts[2]."-".$dateparts[1]."-".$dateparts[0]." 00:00:00"));
			else
				$postfrom = "";
		}

		if ($postto != "")
		{
			$dateparts = explode("/", $postto);
			if (count($dateparts) == 3)
				$postto = sprintf(" and postdate < %s ", $db->escapeString($dateparts[2]."-".$dateparts[1]."-".$dateparts[0]." 23:59:59"));
			else
				$postto = "";
		}
		
		if ($group != "" && $group != "-1")
			$group = sprintf(" and groupID = %d ", $group);
		else
			$group = "";
		
		return $db->query(sprintf("SELECT searchname, guid from releases where 1 = 1 %s %s %s", $postfrom, $postto, $group));
	}
	
	public function getEarliestUsenetPostDate()
	{
		$db = new DB();
		$row = $db->queryOneRow("SELECT DATE_FORMAT(min(postdate), '%d/%m/%Y') as postdate from releases");
		return $row["postdate"];	
	}

	public function getLatestUsenetPostDate()
	{
		$db = new DB();
		$row = $db->queryOneRow("SELECT DATE_FORMAT(max(postdate), '%d/%m/%Y') as postdate from releases");
		return $row["postdate"];	
	}

	public function getReleasedGroupsForSelect($blnIncludeAll = true)
	{
		$db = new DB();
		$groups = $db->query("select distinct groups.ID, groups.name from releases inner join groups on groups.ID = releases.groupID");
		$temp_array = array();
		
		if ($blnIncludeAll)
			$temp_array[-1] = "--All Groups--";
		
		foreach($groups as $group)
			$temp_array[$group["ID"]] = $group["name"];

		return $temp_array;
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
		$nfo = new Nfo();
		$site = $s->get();
		//
		// delete from disk.
		//
		$rel = $this->getById($id);
		if ($rel && file_exists($site->nzbpath.$rel["guid"].".nzb.gz")) 
			unlink($site->nzbpath.$rel["guid"].".nzb.gz");
		
		$nfo->deleteReleaseNfo($id);
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
	
	public function search($search, $cat=array(-1), $limit=1000, $orderby='', $maxage=-1)
	{			
		$db = new DB();
		
		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
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
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}		
		
		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the fulltext match, but mandate that all items returned must start with the provided word
		//
		$startswith = "";
		if (strpos($search, "^") === 0)
		{
			$words = explode(" ", $search);
			if (count($words) > 0)
			{
				$firstword = substr($words[0], 1);
				$startswith = sprintf(" and releases.searchname like %s ", $db->escapeString($firstword."%"));
				
				//
				// put the query back together with the caret removed
				//
				unset($words[0]);
				$search = $firstword." ".implode(" ", $words);
			}
		}
		
		if ($maxage > 0)
			$maxage = sprintf(" and postdate < now() - interval %d day ", $maxage);
		else
			$maxage = "";
		
		if ($orderby == "")
		{
			$order[0] = sprintf(" MATCH (searchname) AGAINST (%s IN BOOLEAN MODE) ", $db->escapeString($search));
			$order[1] = " desc ";
		}	
		else
			$order = $this->getBrowseOrder($orderby);

		$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, rn.ID as nfoID from releases left outer join releasenfo rn on rn.releaseID = releases.ID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) %s %s %s order by %s %s limit %d ", $db->escapeString($search), $catsrch, $startswith, $maxage, $order[0], $order[1], $limit));				
		if (!$res)
			$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, rn.ID as nfoID from releases left outer join releasenfo rn on rn.releaseID = releases.ID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) %s %s %s order by %s %s limit %d ", $db->escapeString($search."*"), $catsrch, $startswith, $maxage, $db->escapeString($search."*"), $order[0], $order[1], $limit));		

		return $res;
	}	
	
	public function searchbyRageId($rageId, $series="", $episode="", $limit=100, $name="", $cat=array(-1), $maxage=-1)
	{			
		$db = new DB();
		
		if ($rageId != "-1")
			$rageId = sprintf(" and rageID = %d ", $rageId);
		else
			$rageId = "";

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

		if ($name != "")
		{
			$name = sprintf(" and MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) ", $db->escapeString($name));
		}

		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
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
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}		
		
		if ($maxage > 0)
			$maxage = sprintf(" and postdate < now() - interval %d day ", $maxage);
		else
			$maxage = "";		
		
		$res = $db->query(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, rn.ID as nfoID from releases left outer join category c on c.ID = releases.categoryID left outer join releasenfo rn on rn.releaseID = releases.ID and rn.nfo is not null left outer join category cp on cp.ID = c.parentID where 1=1 %s %s %s %s %s %s order by postdate desc limit %d ", $rageId, $series, $episode, $name, $catsrch, $maxage, $limit));		

		return $res;
	}		
	
	public function searchSimilar($currentid, $name, $limit=6)
	{			
		$name = $this->getSimilarName($name);
		$results = $this->search($name, array(-1), $limit);
		if (!$results)
			return $results;

		$ret = array();
		foreach ($results as $res)
			if ($res["ID"] != $currentid)
				$ret[] = $res;

		return $ret;
	}	
	
	public function getSimilarName($name)
	{
		$words = str_word_count(str_replace(array(".","_"), " ", $name), 2);
		$firstwords = array_slice($words, 0, 2);
		return implode(' ', $firstwords);
	}
	
	public function getByGuid($guid)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where guid = %s ", $db->escapeString($guid)));		
	}	

	//
	// writes a zip file of an array of release guids directly to the stream
	//
	public function getZipped($guids)
	{
		$s = new Sites();
		$nzb = new NZB;
		$site = $s->get();
		$zipfile = new zipfile();
		
		foreach ($guids as $guid)
		{
			$nzbpath = $nzb->getNZBPath($guid, $site->nzbpath);

			if (file_exists($nzbpath)) 
			{
				ob_start();
				@readgzfile($nzbpath);
				$nzbfile = ob_get_contents();
				ob_end_clean();

				$filename = $guid;
				$r = $this->getByGuid($guid);
				if ($r)
					$filename = $r["searchname"];
				
				$zipfile->addFile($nzbfile, $filename.".nzb");
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

	public function getReleaseNfo($id, $incnfo=true)
	{			
		$db = new DB();
		$selnfo = ($incnfo) ? ', uncompress(nfo) as nfo' : '';
		return $db->queryOneRow(sprintf("SELECT ID, releaseID".$selnfo." FROM releasenfo where releaseID = %d AND nfo IS NOT NULL", $id));		
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
		$bin = new Binaries;
		$nzb = new Nzb;
		$relreg = new ReleaseRegex;
		$page = new Page;
		$nfo = new Nfo($echooutput);
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
		$regexrows = $relreg->get();
		foreach ($regexrows as $regexrow)
		{
			$groupmatch = "";
			
			//
			// groups ending in * need to be like matched when getting out binaries for groups and children
			//
			if (preg_match("/\*$/i", $regexrow["groupname"]))
			{
				$groupname = substr($regexrow["groupname"], 0, -1);
				$groupmatch = sprintf(" groups.name like %s ", $db->escapeString($groupname."%"));
			}
			//
			// a group name which doesnt end in a * needs an exact match
			//
			elseif ($regexrow["groupname"] != "")
				$groupmatch = sprintf(" groups.name = %s ", $db->escapeString($regexrow["groupname"]));
			//
			// no groupname specified (these must be the misc regexes applied to all groups)
			//
			else
				$groupmatch = " 1 = 1 ";
				
			// Get out all binaries of STAGE0 for current group
			$resbin = $db->queryDirect(sprintf("SELECT binaries.ID, binaries.name, binaries.date from binaries inner join groups on groups.ID = binaries.groupID where %s and procstat = %d", $groupmatch, Releases::PROCSTAT_NEW));

			while ($rowbin = mysql_fetch_array($resbin, MYSQL_BOTH)) 
			{
				if (preg_match ($regexrow["regex"], $rowbin["name"], $matches)) 
				{
					$matches = array_map("trim", $matches);
					
					if (!isset($matches['name']) || empty($matches['name'])) {
						if ($echooutput) {
							echo "bad regex applied which didnt return right number of capture groups - ".$regexrow["regex"]."\n";
							print_r($matches);
							break; //end loop
						}
					}
					
					// if theres no parts data, put it into a release if it was posted to usenet longer than three hours ago.
					if (!isset($matches['parts']) && time() - strtotime($rowbin['date']) > 10800) {
						 $matches['parts'] = "01/01";
						 if ($echooutput) {
							//echo "adding post without parts - bin: ".date("Y-m-d H:i:s", strtotime($rowbin['date']))." now: ".date("Y-m-d H:i:s")."\n";
						}
					}
					
					if (isset($matches['name']) && isset($matches['parts'])) {
						$parts = explode("/", $matches['parts']);
						$db->query(sprintf("update binaries set relname = %s, relpart = %d, reltotalpart = %d, procstat=%d where ID = %d", 
						$db->escapeString($matches['name']), $parts[0], $parts[1], Releases::PROCSTAT_TITLEMATCHED, $rowbin["ID"] ));
					}
				}
			}
			if ($echooutput)
				echo "applied regex ".$regexrow["ID"]." for group ".($regexrow["groupname"]==""?"all":$regexrow["groupname"])."\n";
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
				if ($echooutput)
					echo "Incorrect number of parts ".$row["relname"]."-".$row["num"]."-".$row["reltotalpart"]."\n";
					
				$db->query(sprintf("update binaries set procattempts = procattempts + 1 where relname = %s and procstat = %d", $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED ));
			}
			if ($echooutput && ($retcount % 100 == 0))
				echo "processed ".$retcount." binaries stage two\n";
		}
		$retcount=$nfocount=0;
   		
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
			$catId = $cat->determineCategory($row["group_name"], $row["relname"]);
			$relid = $db->queryInsert(sprintf("insert into releases (name, searchname, totalpart, groupID, adddate, guid, categoryID, rageID, postdate, fromname, size) values (%s, %s, %d, %d, now(), %s, %d, -1, %s, %s, %s)", 
										$db->escapeString($row["relname"]), $db->escapeString($row["relname"]), $row["parts"], $row["groupID"], $db->escapeString($relguid), $catId, $db->escapeString($bindata["date"]), $db->escapeString($bindata["fromname"]), $totalSize));
			
			//
			// tag every binary for this release with its parent release id
			// remove the release name from the binary as its no longer required
			//
			$db->query(sprintf("update binaries set relname = null, procstat = %d, releaseID = %d where relname = %s and procstat = %d and groupID = %d ", 
								Releases::PROCSTAT_RELEASED, $relid, $db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE, $row["groupID"]));

			//
			// find an .nfo in the release
			//
			$relnfo = $nfo->determineReleaseNfo($relid);
			if ($relnfo !== false) 
			{
				$nfo->addReleaseNfo($relid, $relnfo['ID']);
				$nfocount++;
			}

			//
			// write the nzb to disk
			//
			$nzb->writeNZBforReleaseId($relid, $relguid, $row["relname"], $catId, $nzb->getNZBPath($relguid, $page->site->nzbpath, true));

			if ($echooutput && ($retcount % 5 == 0))
				echo "processed ".$retcount." binaries stage three\n";
		}    
    	
    	if ($echooutput)
			echo "found ".$nfocount." nfos in ".$retcount." releases\n";
		
		//
		// Process nfo files
		//
		if ($page->site->lookupnfo != "1")
		{
			if ($echooutput)
				echo "site config (site.lookupnfo) prevented retrieving nfos\n";		
		}
		else
		{
			if ($echooutput)
				echo "processing nfo files\n";		

			$nfo->processNfoFiles(($page->site->lookupimdb=="1"));
		}
		
		//
		// Process all TV related releases which will assign their series/episode/rage data
		//
		if ($echooutput)
			echo "processing tv rage data\n";		

		$this->processTvSeriesData($echooutput, ($page->site->lookuptvrage=="1"));
		
		//
		// tidy away any binaries which have been attempted to be grouped into 
		// a release more than x times
		//
		if ($echooutput)
			echo "tidying away binaries which cant be grouped after ".$page->site->attemptgroupbindays." days\n";			
		$db->query(sprintf("update binaries set procstat = %d where procstat = %d and dateadded < now() - interval %d day ", 
			Releases::PROCSTAT_WRONGPARTS, Releases::PROCSTAT_NEW, $page->site->attemptgroupbindays));
		
		//
		// delete any parts and binaries which are older than the site's retention days
		//
		if ($echooutput)
			echo "deleting binaries which are older than ".$page->site->rawretentiondays." days\n";			
		$db->query(sprintf("delete from parts where dateadded < now() - interval %d day", $page->site->rawretentiondays));
		$db->query(sprintf("delete from binaries where dateadded < now() - interval %d day", $page->site->rawretentiondays));
		

		if ($echooutput)
			echo "processed ". $retcount." releases\n";
			
		return $retcount;	
	}

	public function processTvSeriesData($echooutput=false, $lookupTvRage = true)
	{
		$ret = 0;
		$db = new DB();
		$rage = new TvRage();

		if ($echooutput)
			echo "lookup tv rage from the web (".($lookupTvRage?"true)\n":"false)\n");
		
		$result = $db->queryDirect("SELECT searchname, ID from releases where rageID = -1");
		while ($arr = mysql_fetch_array($result, MYSQL_BOTH)) 
		{
			$show = $this->parseNameEpSeason($arr['searchname']);			
			
			//
			// see if its in the existing rage list
			// only process releases which have a searchname like S01E01 or S01E02-E03
			// 
			if (is_array($show) && $show['name'] != '')
			{
				if ($echooutput)
					echo "tv series - ".$show['name']."-".$show['seriesfull']."\n";
				
				//
				// Get a clean name version of the release (the text upto the S01E01 part) and the series and episode parts
				//
				$relcleanname = str_replace(".", " ", $show['name']);
				$relcleanname = str_replace("_", " ", $relcleanname);
				
				$db->query(sprintf("update releases set seriesfull = %s, season = %s, episode = %s where ID = %d", 
							$db->escapeString($show['seriesfull']), $db->escapeString($show['season']), $db->escapeString($show['episode']), $arr["ID"]));

				//
				// try and retrieve the entry from tvrage
				//
				$id = $rage->getRageId($relcleanname, $echooutput, $lookupTvRage);
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
	
	public function parseNameEpSeason($relname)
	{
		$showInfo = array(
			'name' => '',
			'season' => '',
			'episode' => '',
			'seriesfull' => ''
		);
		
		//S01E01
		//S01.E01
		if (preg_match('/^(.*?)\.s(\d{1,2})\.?e(\d{1,3})\.?/i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = intval($matches[2]);
			$showInfo['episode'] = intval($matches[3]);
		//S01
		} elseif (preg_match('/^(.*?)\.s(\d{1,2})\./i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = intval($matches[2]);
			$showInfo['episode'] = 'all';
		//1x01
		} elseif (preg_match('/^(.*?)\.(\d{1,2})x(\d{1,3})\./i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = intval($matches[2]);
			$showInfo['episode'] = intval($matches[3]);
		//2009.01.01
		} elseif (preg_match('/^(.*?)\.(19|20)(\d{2})\.(\d{2}).(\d{2})\./i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = $matches[2].$matches[3];
			$showInfo['episode'] = $matches[4].'/'.$matches[5];
		//01.01.2009
		} elseif (preg_match('/^(.*?)\.(\d{2}).(\d{2})\.(19|20)(\d{2})\./i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = $matches[4].$matches[5];
			$showInfo['episode'] = $matches[2].'/'.$matches[3];
		//01.01.09
		} elseif (preg_match('/^(.*?)\.(\d{2}).(\d{2})\.(\d{2})\./i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = ($matches[4] <= 99 && $matches[4] > 15) ? '19'.$matches[4] : '20'.$matches[4];
			$showInfo['episode'] = $matches[2].'/'.$matches[3];
		//2009.E01
		} elseif (preg_match('/^(.*?)\.20(\d{2})\.e(\d{1,3})\./i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = '20'.$matches[2];
			$showInfo['episode'] = intval($matches[3]);
		//S01E01-E02
		//S01E01-02
		} elseif (preg_match('/^(.*?)\.s(\d{1,2})\.?e(\d{1,3})-e?(\d{1,3})\./i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = intval($matches[2]);
			$showInfo['episode'] = intval($matches[3]).''.intval($matches[4]);
		//2009.Part1
		} elseif (preg_match('/^(.*?)\.20(\d{2})\.Part(\d{1,2})\./i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = '20'.$matches[2];
			$showInfo['episode'] = intval($matches[3]);
		//Part1
		} elseif (preg_match('/^(.*?)\.Part\.?(\d{1,2})\./i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = 1;
			$showInfo['episode'] = intval($matches[2]);
		//The.Pacific.Pt.VI.HDTV.XviD-XII
		} elseif (preg_match('/^(.*?)\.Pt\.([ivx]+)/i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = 1;
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
			$showInfo['episode'] = $e;
		//Band.Of.Brothers.EP06.Bastogne.DVDRiP.XviD-DEiTY
		} elseif (preg_match('/^(.*?)\.EP\.?(\d{1,3})/i', $relname, $matches)) {
			$showInfo['name'] = $matches[1];
			$showInfo['season'] = 1;
			$showInfo['episode'] = intval($matches[2]);
		}
		
		if (!empty($showInfo['name'])) {
			if (strlen($showInfo['season']) == 4) {
				$showInfo['seriesfull'] = $showInfo['season']."/".$showInfo['episode'];
			} else {
				$showInfo['season'] = sprintf('S%02d', $showInfo['season']);
				$showInfo['episode'] = sprintf('E%02d', $showInfo['episode']);
				$showInfo['seriesfull'] = $showInfo['season'].$showInfo['episode'];
			}
			return $showInfo;
		}
		
		return false;
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
}
?>

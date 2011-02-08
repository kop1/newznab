<?php
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/binaries.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releaseregex.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/tvrage.php");
require_once(WWW_DIR."/lib/movie.php");
require_once(WWW_DIR."/lib/music.php");
require_once(WWW_DIR."/lib/console.php");
require_once(WWW_DIR."/lib/nzb.php");
require_once(WWW_DIR."/lib/nfo.php");
require_once(WWW_DIR."/lib/zipfile.php");
require_once(WWW_DIR."/lib/rarinfo.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/util.php");

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

	//
	// after a series of attempts to lookup the allfilled style reqid
	// to get a name, its given up
	const PROCSTAT_NOREQIDNAMELOOKUPFOUND = 7;

	//
	// passworded indicator
	//
	const PASSWD_NONE = 0;
	const PASSWD_RAR = 1;
	const PASSWD_POTENTIAL = 2;	
	
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
	
	public function getBrowseCount($cat, $maxage=-1, $excludedcats=array(), $grp)
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

		if ($maxage > 0)
			$maxage = sprintf(" and postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";		
		
		$grpsql = "";
		if ($grp != "")
			$grpsql = sprintf(" and groups.name = %s ", $db->escapeString($grp));
		
		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and categoryID not in (".implode(",", $excludedcats).")";
		
		$res = $db->queryOneRow(sprintf("select count(releases.ID) as num from releases left outer join groups on groups.ID = releases.groupID where releases.passwordstatus <= (select showpasswordedrelease from site) %s %s %s %s", $catsrch, $maxage, $exccatlist, $grpsql));		
		return $res["num"];	
	}	
	
	public function getBrowseRange($cat, $start, $num, $orderby, $maxage=-1, $excludedcats=array(), $grp="")
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
		
		$maxagesql = "";
		if ($maxage > 0)
			$maxagesql = sprintf(" and postdate > now() - interval %d day ", $maxage);

		$grpsql = "";
		if ($grp != "")
			$grpsql = sprintf(" and groups.name = %s ", $db->escapeString($grp));

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";
			
		$order = $this->getBrowseOrder($orderby);
		return $db->query(sprintf(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID from releases left outer join groups on groups.ID = releases.groupID left outer join releasenfo rn on rn.releaseID = releases.ID and rn.nfo is not null left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select showpasswordedrelease from site) %s %s %s %s order by %s %s".$limit, $catsrch, $maxagesql, $exccatlist, $grpsql, $order[0], $order[1]));		
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
	
	public function getRss($category, $num, $uid=0, $rageid)
	{		
		$db = new DB();
		
		$limit = " LIMIT 0,".($num > 100 ? 100 : $num);

		$catsrch = "";
		
		if ($category > 0)
		{
			$catsrch = " and (";
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
					$catsrch.= "1=2 )";

		}
		elseif ($category == -2)
			$catsrch = sprintf(" and releases.ID in (select releaseID from usercart where userID = %d)", $uid);


		$rage = "";
		if ($rageid > 0)
			$rage = sprintf(" and releases.rageID = %d", $rageid);
			
		return $db->query(sprintf(" SELECT releases.*, m.cover, m.imdbID, m.rating, m.plot, m.year, m.genre, m.director, m.actors, g.name as group_name, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, coalesce(cp.ID,0) as parentCategoryID, mu.title as mu_title, mu.url as mu_url, mu.artist as mu_artist, mu.publisher as mu_publisher, mu.releasedate as mu_releasedate, mu.review as mu_review, mu.tracks as mu_tracks, mu.cover as mu_cover, mug.title as mu_genre, co.title as co_title, co.url as co_url, co.publisher as co_publisher, co.releasedate as co_releasedate, co.review as co_review, co.cover as co_cover, cog.title as co_genre  from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID left outer join groups g on g.ID = releases.groupID left outer join movieinfo m on m.imdbID = releases.imdbID and m.title != '' left outer join musicinfo mu on mu.ID = releases.musicinfoID left outer join genres mug on mug.ID = mu.genreID left outer join consoleinfo co on co.ID = releases.consoleinfoID left outer join genres cog on cog.ID = co.genreID where releases.passwordstatus <= (select showpasswordedrelease from site) %s %s order by postdate desc %s" ,$catsrch, $rage, $limit));
	}
		
	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from releases");		
		return $res["num"];
	}

	public function rebuild($id)
	{
		$this->delete($id);
		
		$db = new DB();
		$db->query(sprintf("update binaries set procstat = 0,procattempts=0, categoryID=null, regexID=null,reqID=null,relpart=null,reltotalpart=null,relname=null,releaseID=null where releaseID = %d", $id));

	}
	
	public function rebuildmulti($guids)
	{
		if (!is_array($guids) || sizeof($guids) < 1)
			return false;
		
		$db = new DB();
		
		$updateGuids = array();
		foreach($guids as $guid) {
			$updateGuids[] = $db->escapeString($guid);
		}
		
		$rels = $db->query(sprintf('select ID from releases where guid IN (%s)', implode(', ', $updateGuids)));
		$relids = array();
		foreach($rels as $r) {
			$relids[] = $r['ID'];
		}
			
		$this->delete($relids);
		
		$db = new DB();
		$db->query(sprintf("update binaries set procstat = 0,procattempts=0, categoryID=null, regexID=null,reqID=null,relpart=null,reltotalpart=null,relname=null,releaseID=null where releaseID IN (%s)", implode(',',$relids)));

	}
	
	public function delete($id, $isGuid=false)
	{			
		$db = new DB();
		$users = new Users();
		$s = new Sites();
		$nfo = new Nfo();
		$site = $s->get();
		
		if (!is_array($id))
			$id = array($id);
				
		foreach($id as $identifier)
		{
			//
			// delete from disk.
			//
			$rel = ($isGuid) ? $this->getByGuid($identifier) : $this->getById($identifier);

			if ($rel && file_exists($site->nzbpath.$rel["guid"].".nzb.gz")) 
				unlink($site->nzbpath.$rel["guid"].".nzb.gz");
			
			$nfo->deleteReleaseNfo($rel['ID']);
			$this->deleteCommentsForRelease($rel['ID']);
			$users->delCartForRelease($rel['ID']);
			$db->query(sprintf("delete from releases where id = %d", $rel['ID']));
		}
	}

	public function update($id, $name, $searchname, $fromname, $category, $parts, $grabs, $size, $posteddate, $addeddate, $rageid, $seriesfull, $season, $episode, $imdbid)
	{			
		$db = new DB();

		$db->query(sprintf("update releases set name=%s, searchname=%s, fromname=%s, categoryID=%d, totalpart=%d, grabs=%d, size=%d, postdate=%s, adddate=%s, rageID=%d, seriesfull=%s, season=%s, episode=%s, imdbID=%d where id = %d", 
			$db->escapeString($name), $db->escapeString($searchname), $db->escapeString($fromname), $category, $parts, $grabs, $size, $db->escapeString($posteddate), $db->escapeString($addeddate), $rageid, $db->escapeString($seriesfull), $db->escapeString($season), $db->escapeString($episode), $imdbid, $id));		
	}
	
	public function updatemulti($guids, $category, $grabs, $rageid, $season, $imdbid)
	{			
		if (!is_array($guids) || sizeof($guids) < 1)
			return false;
		
		$update = array(
			'categoryID'=>(($category == '-1') ? '' : $category),
			'grabs'=>$grabs,
			'rageID'=>$rageid,
			'season'=>$season,
			'imdbID'=>$imdbid
		);
		
		$db = new DB();
		$updateSql = array();
		foreach($update as $updk=>$updv) {
			if ($updv != '') 
				$updateSql[] = sprintf($updk.'=%s', $db->escapeString($updv));
		}
		
		if (sizeof($updateSql) < 1) {
			//echo 'no field set to be changed';
			return -1;
		}
		
		$updateGuids = array();
		foreach($guids as $guid) {
			$updateGuids[] = $db->escapeString($guid);
		}
		
		$sql = sprintf('update releases set '.implode(', ', $updateSql).' where guid in (%s)', implode(', ', $updateGuids));
		return $db->query($sql);
	}	
	
	public function search($search, $cat=array(-1), $offset=0, $limit=1000, $orderby='', $maxage=-1, $excludedcats=array())
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
					$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString(substr($word, 1)."%"));
				else
					$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString("%".$word."%"));

				$intwordcount++;
			}
		}
		
		if ($maxage > 0)
			$maxage = sprintf(" and postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";
		
		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";


		if ($orderby == "")
		{
			$order[0] = " postdate ";
			$order[1] = " desc ";
		}	
		else
			$order = $this->getBrowseOrder($orderby);

		$res = $db->query(sprintf("select SQL_CALC_FOUND_ROWS releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID, cp.ID as categoryParentID from releases left outer join releasenfo rn on rn.releaseID = releases.ID left outer join groups on groups.ID = releases.groupID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select showpasswordedrelease from site) %s %s %s %s order by %s %s limit %d, %d ", $searchsql, $catsrch, $maxage, $exccatlist, $order[0], $order[1], $offset, $limit), true);

		return $res;
	}	
	
	public function searchbyRageId($rageId, $series="", $episode="", $offset=0, $limit=100, $name="", $cat=array(-1), $maxage=-1)
	{			
		$db = new DB();
		
		if ($rageId != "-1")
			$rageId = sprintf(" and rageID = %d ", $rageId);
		else
			$rageId = "";

		if ($series != "")
		{
			//
			// Exclude four digit series, which will be the year 2010 etc
			//
			if (is_numeric($series) && strlen($series) != 4)
				$series = sprintf('S%02d', $series);

			$series = sprintf(" and upper(releases.season) = upper(%s)", $db->escapeString($series));
		}
		if ($episode != "")
		{
			if (is_numeric($episode))
				$episode = sprintf('E%02d', $episode);

			$episode = sprintf(" and releases.episode like %s", $db->escapeString('%'.$episode.'%'));
		}

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the fulltext match, but mandate that all items returned must start with the provided word
		//
		$words = explode(" ", $name);
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
					$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString(substr($word, 1)."%"));
				else
					$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString("%".$word."%"));

				$intwordcount++;
			}
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
			$maxage = sprintf(" and postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";		
		
		$res = $db->query(sprintf("select SQL_CALC_FOUND_ROWS releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID from releases left outer join category c on c.ID = releases.categoryID left outer join groups on groups.ID = releases.groupID left outer join releasenfo rn on rn.releaseID = releases.ID and rn.nfo is not null left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select showpasswordedrelease from site) %s %s %s %s %s %s order by postdate desc limit %d, %d ", $rageId, $series, $episode, $searchsql, $catsrch, $maxage, $offset, $limit), true);		
		
		return $res;
	}
	
	public function searchbyImdbId($imdbId, $offset=0, $limit=100, $name="", $cat=array(-1), $maxage=-1)
	{			
		$db = new DB();
		
		if ($imdbId != "-1" && is_numeric($imdbId)) 
		{
			//pad id with zeros just in case
			$imdbId = str_pad($imdbId, 7, "0",STR_PAD_LEFT);
			$imdbId = sprintf(" and imdbID = %d ", $imdbId);
		} 
		else 
		{
			$imdbId = "";
		}

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the fulltext match, but mandate that all items returned must start with the provided word
		//
		$words = explode(" ", $name);
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
					$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString(substr($word, 1)."%"));
				else
					$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString("%".$word."%"));

				$intwordcount++;
			}
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
			$maxage = sprintf(" and postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";		
		
		$res = $db->query(sprintf("select SQL_CALC_FOUND_ROWS releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID from releases left outer join groups on groups.ID = releases.groupID left outer join category c on c.ID = releases.categoryID left outer join releasenfo rn on rn.releaseID = releases.ID and rn.nfo is not null left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select showpasswordedrelease from site) %s %s %s %s order by postdate desc limit %d, %d ", $searchsql, $imdbId, $catsrch, $maxage, $offset, $limit), true);		

		return $res;
	}			

	public function searchSimilar($currentid, $name, $limit=6, $excludedcats=array())
	{			
		$name = $this->getSimilarName($name);
		$results = $this->search($name, array(-1), 0, $limit, '', -1, $excludedcats);
		if (!$results)
			return $results;

		//
		// Get the category for the parent of this release
		//
		$currRow = $this->getById($currentid);
		$cat = new Category();
		$catrow = $cat->getById($currRow["categoryID"]);
		$parentCat = $catrow["parentID"];
		
		$ret = array();
		foreach ($results as $res)
			if ($res["ID"] != $currentid && $res["categoryParentID"] == $parentCat)
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
			//
			// Exclude four digit series, which will be the year 2010 etc
			//
			if (is_numeric($series) && strlen($series) != 4)
				$series = sprintf('S%02d', $series);
			
			$series = sprintf(" and upper(releases.season) = upper(%s)", $db->escapeString($series));
		}
		
		if ($episode != "")
		{
			if (is_numeric($episode))
				$episode = sprintf('E%02d', $episode);

			$episode = sprintf(" and upper(releases.episode) = upper(%s)", $db->escapeString($episode));
		}
		
		return $db->queryOneRow(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID  left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select showpasswordedrelease from site) and rageID = %d %s %s", $rageid, $series, $episode));		
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
	
	function processReleases() 
	{
		$db = new DB;
		$cat = new Category;
		$bin = new Binaries;
		$nzb = new Nzb;
		$s = new Sites;
		$relreg = new ReleaseRegex;
		$page = new Page;
		$nfo = new Nfo(true);
		$retcount = 0;
		
		echo $s->getLicense();

		echo "\n\nStarting release update process (".date("Y-m-d H:i:s").")\n";
		
		if (!file_exists($page->site->nzbpath))
		{
			echo "Bad or missing nzb directory - ".$page->site->nzbpath;
			return;
		}
		
		$this->checkRegexesUptoDate($page->site->latestregexurl, $page->site->latestregexrevision, true);
		
		//
		// Get all regexes for all groups which are to be applied to new binaries
		// in order of how they should be applied
		//
		$regexrows = $relreg->get();
		foreach ($regexrows as $regexrow)
		{
			echo "Applying regex ".$regexrow["ID"]." for group ".($regexrow["groupname"]==""?"all":$regexrow["groupname"])."\n";
		
			$groupmatch = "";
			
			//
			// Groups ending in * need to be like matched when getting out binaries for groups and children
			//
			if (preg_match("/\*$/i", $regexrow["groupname"]))
			{
				$groupname = substr($regexrow["groupname"], 0, -1);
				$resgrps = $db->query(sprintf("select ID from groups where name like %s ", $db->escapeString($groupname."%")));
				foreach ($resgrps as $resgrp)
					$groupmatch.=" groupID = ".$resgrp["ID"]." or ";

				$groupmatch.=" 1=2 ";
			}
			//
			// A group name which doesnt end in a * needs an exact match
			//
			elseif ($regexrow["groupname"] != "")
			{
				$resgrp = $db->queryOneRow(sprintf("select ID from groups where name = %s ", $db->escapeString($regexrow["groupname"])));
				
				//
				// if group not found, its a regex for a group we arent indexing.
				//
				if ($resgrp)
					$groupmatch = " groupID = ".$resgrp["ID"];
				else
					$groupmatch = " 1=2 " ;
			}
			//
			// No groupname specified (these must be the misc regexes applied to all groups)
			//
			else
				$groupmatch = " 1=1 ";
			
			// Get current mysql time for date comparison checks in case php is in a different time zone
			$currTime = $db->queryOneRow("SELECT NOW() as now");
			
			// Get out all binaries of STAGE0 for current group
			$arrNoPartBinaries = array();
			$resbin = $db->queryDirect(sprintf("SELECT binaries.ID, binaries.name, binaries.date, binaries.totalParts from binaries where (%s) and procstat = %d order by binaries.date asc", $groupmatch, Releases::PROCSTAT_NEW));

			while ($rowbin = mysql_fetch_assoc($resbin)) 
			{
				if (preg_match ($regexrow["regex"], $rowbin["name"], $matches)) 
				{
					$matches = array_map("trim", $matches);
					
					if ((isset($matches['reqid']) && ctype_digit($matches['reqid'])) && (!isset($matches['name']) || empty($matches['name']))) {
						$matches['name'] = $matches['reqid'];
					}
					
					// Check that the regex provided the correct parameters
					if (!isset($matches['name']) || empty($matches['name'])) 
					{
						echo "regex applied which didnt return right number of capture groups - ".$regexrow["regex"]."\n";
						print_r($matches);
						continue;
					}

					// If theres no number of files data in the subject, put it into a release if it was posted to usenet longer than five hours ago.
					if ((!isset($matches['parts']) && strtotime($currTime['now']) - strtotime($rowbin['date']) > 18000) || isset($arrNoPartBinaries[$matches['name']]))
					{
						//
						// Take a copy of the name of this no-part release found. This can be used
						// next time round the loop to find parts of this set, but which have not yet reached 3 hours.
						//
						$arrNoPartBinaries[$matches['name']] = "1";
						$matches['parts'] = "01/01";
					}

					
					if (isset($matches['name']) && isset($matches['parts'])) 
					{
						if (strpos($matches['parts'], '/') === false) 
						{
							$matches['parts'] = str_replace(array('-','~',' of '), '/', $matches['parts']);
						}

						$regcatid = "null ";
						if ($regexrow["categoryID"] != "")
							$regcatid = $regexrow["categoryID"];
							
						$reqid = " null ";
						if (isset($matches['reqid'])) 
							$reqid = $matches['reqid'];
						
						//check if post is repost
						if (preg_match('/(repost\d?|re\-?up)/i', $rowbin['name'], $repost) && !preg_match('/repost|re\-?up/i', $matches['name'])) {
							$matches['name'] .= ' '.$repost[1];
						}
						
						$relparts = explode("/", $matches['parts']);
						$db->query(sprintf("update binaries set relname = replace(%s, '_', ' '), relpart = %d, reltotalpart = %d, procstat=%d, categoryID=%s, regexID=%d, reqID=%s where ID = %d", 
							$db->escapeString($matches['name']), $relparts[0], $relparts[1], Releases::PROCSTAT_TITLEMATCHED, $regcatid, $regexrow["ID"], $reqid, $rowbin["ID"] ));
					}
				}
			}
			
		}

		//
		// Move all binaries from releases which have the correct number of files on to the next stage.
		//
		echo "Stage 2\n";
		$result = $db->queryDirect(sprintf("SELECT relname, SUM(reltotalpart) AS reltotalpart, groupID, reqID, fromname, SUM(num) AS num, coalesce(g.minfilestoformrelease, s.minfilestoformrelease) as minfilestoformrelease FROM   ( SELECT relname, reltotalpart, groupID, reqID, fromname, COUNT(ID) AS num FROM binaries     WHERE procstat = %s     GROUP BY relname, reltotalpart, groupID, reqID, fromname    ) x left outer join groups g on g.ID = x.groupID inner join ( select * from site limit 1 ) s GROUP BY relname, groupID, reqID, fromname", Releases::PROCSTAT_TITLEMATCHED));
		while ($row = mysql_fetch_assoc($result)) 
		{
			$retcount ++;
			
			//
			// Less than the site permitted number of files in a release. Dont discard it, as it may
			// be part of a set being uploaded.
			//
			if ($row["num"] < $row["minfilestoformrelease"])
			{
				echo "Number of files in release ".$row["relname"]." less than site/group setting (".$row['num']."/".$row["minfilestoformrelease"].")\n";
					
				$db->query(sprintf("update binaries set procattempts = procattempts + 1 where relname = %s and procstat = %d and groupID = %d and fromname = %s", $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED, $row["groupID"], $db->escapeString($row["fromname"]) ));
			}
			
			//
			// There are the same or more files in our release than the number of files specified
			// in the message subject so go ahead and make a release
			//
			elseif ($row["num"] >= $row["reltotalpart"])
			{
				
				// Check that the binary is complete
				$binlist = $db->query(sprintf("SELECT ID, totalParts, date from binaries where relname = %s and procstat = %d and groupID = %d and fromname = %s", $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED, $row["groupID"], $db->escapeString($row["fromname"]) ));

				$incomplete = false;
				foreach ($binlist as $rowbin) 
				{
					$binParts = $db->queryOneRow(sprintf("SELECT COUNT(ID) AS num FROM parts WHERE binaryID = %d", $rowbin['ID']));
					if ($binParts['num'] < $rowbin['totalParts']) 
					{
						echo "binary ".$rowbin['ID']." from ".$row['relname']." has missing parts - ".$binParts['num']."/".$rowbin['totalParts']." (".number_format(($binParts['num']/$rowbin['totalParts'])*100, 1)."% complete)\n";
						
						// Allow to binary to release if posted to usenet longer than four hours ago and we still don't have all the parts
						if (strtotime($currTime['now']) - strtotime($rowbin['date']) > 14400)
						{
							echo "allowing incomplete binary ".$rowbin['ID']."\n";
						} 
						else 
						{
							$incomplete = true;
						}
					}
				}
				
				if ($incomplete) 
				{
					echo "Incorrect number of parts ".$row["relname"]."-".$row["num"]."-".$row["reltotalpart"]."\n";
						
					$db->query(sprintf("update binaries set procattempts = procattempts + 1 where relname = %s and procstat = %d and groupID = %d and fromname = %s", $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED, $row["groupID"], $db->escapeString($row["fromname"]) ));
				}
				
				//
				// Right number of files, but see if the binary is a allfilled/reqid post, in which case it needs its name looked up
				// 
				elseif ($row['reqID'] !='' && $page->site->reqidurl != "")
				{
					//
					// Try and get the name using the group
					//
					$binGroup = $db->queryOneRow(sprintf("SELECT name FROM groups WHERE ID = %d", $row["groupID"]));
					echo "Looking up ".$row['reqID']." in ".$binGroup['name']."... ";	
					$newtitle = $this->getReleaseNameForReqId($page->site->reqidurl, $binGroup["name"], $row["reqID"], true);

					//
					// if the feed/group wasnt supported by the scraper, then just use the release name as the title.
					//					
					if ($newtitle == "no feed")
					{
						$newtitle = $row["relname"];
						echo "Group not supported\n";
					}
					
					//
					// Valid release with right number of files and title now, so move it on
					//
					if ($newtitle != "")						
					{
						$db->query(sprintf("update binaries set relname = %s, procstat=%d where relname = %s and procstat = %d and groupID = %d and fromname=%s", 
							$db->escapeString($newtitle), Releases::PROCSTAT_READYTORELEASE, $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED, $row["groupID"], $db->escapeString($row["fromname"])));
					}
					else
					{
						//
						// Item not found, if the binary was added to the index yages ago, then give up.
						//
						$maxaddeddate = $db->queryOneRow(sprintf("SELECT NOW() as now, MAX(dateadded) as dateadded FROM binaries WHERE relname = %s and procstat = %d and groupID = %d and fromname=%s", 
																				$db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED, $row["groupID"], $db->escapeString($row["fromname"])));		
						
						//
						// If added to the index over 48 hours ago, give up trying to determine the title
						//
						if ($maxaddeddate['now'] - strtotime($maxaddeddate['dateadded']) > (60*60*48))
						{
							$db->query(sprintf("update binaries set procstat=%d where relname = %s and procstat = %d and groupID = %d and fromname=%s", 
								Releases::PROCSTAT_NOREQIDNAMELOOKUPFOUND, $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED, $row["groupID"], $db->escapeString($row["fromname"])));
							echo "Not found in 48 hours\n";
						}
					}
				}
				else
				{
					$db->query(sprintf("update binaries set procstat=%d where relname = %s and procstat = %d and groupID = %d and fromname=%s", 
						Releases::PROCSTAT_READYTORELEASE, $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED, $row["groupID"], $db->escapeString($row["fromname"])));
				}
			}
			
			//
			// Theres less than the expected number of files, so update the attempts and move on.
			//
			else
			{
				echo "Incorrect number of files for ".$row["relname"]." (".$row["num"]."/".$row["reltotalpart"].")\n";
					
				$db->query(sprintf("update binaries set procattempts = procattempts + 1 where relname = %s and procstat = %d and groupID = %d and fromname=%s", $db->escapeString($row["relname"]), Releases::PROCSTAT_TITLEMATCHED, $row["groupID"], $db->escapeString($row["fromname"]) ));
			}
			if ($retcount % 10 == 0)
				echo "-processed ".$retcount." binaries stage two\n";
		}
		$retcount=$nfocount=0;

		echo "Stage 3\n";
		//
		// Get out all distinct relname, group from binaries of STAGE2 
		// 
		$result = $db->queryDirect(sprintf("SELECT relname, groupID, g.name as group_name, fromname, count(binaries.ID) as parts from binaries inner join groups g on g.ID = binaries.groupID where procstat = %d and relname is not null group by relname, g.name, groupID, fromname ORDER BY COUNT(binaries.ID) desc", Releases::PROCSTAT_READYTORELEASE));
		while ($row = mysql_fetch_assoc($result)) 
		{
			$retcount ++;

			//
			// Get the last post date and the poster name from the binary
			//
			$bindata = $db->queryOneRow(sprintf("select fromname, MAX(date) as date from binaries where relname = %s and procstat = %d and groupID = %d and fromname = %s group by fromname", 
										$db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE, $row["groupID"], $db->escapeString($row["fromname"]) ));

			//
			// Get all releases with the same name with a usenet posted date in a +1-1 date range.
			//
			$relDupes = $db->query(sprintf("select ID from releases where searchname = %s and (%s - INTERVAL 1 DAY < postdate AND %s + INTERVAL 1 DAY > postdate)", 
									$db->escapeString($row["relname"]), $db->escapeString($bindata["date"]), $db->escapeString($bindata["date"])));
			if (count($relDupes) > 0)
			{
				$db->query(sprintf("update binaries set procstat = %d where relname = %s and procstat = %d and groupID = %d and fromname=%s ", 
									Releases::PROCSTAT_DUPLICATE, $db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE, $row["groupID"], $db->escapeString($row["fromname"])));
				continue;
			}

			//
			// Get total size of this release
			// Done in a big OR statement, not an IN as the mysql binaryID index on parts table
			// was not being used.
			//
			$totalSize = "0";
			$regexAppliedCategoryID = "";
			$regexIDused = "";
			$reqIDused = "";
			$relTotalParts = 0;
			$relCompletion = 0;
			$binariesForSize = $db->query(sprintf("select ID, categoryID, regexID, reqID, totalParts from binaries use index (ix_binary_relname) where relname = %s and procstat = %d and groupID = %d and fromname=%s", 
									$db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE, $row["groupID"], $db->escapeString($row["fromname"]) ));
			if (count($binariesForSize) > 0)
			{
				$sizeSql = "select sum(size) as totalSize, count(ID) as relParts from parts where (";
				foreach ($binariesForSize as $binSizeId)
				{
					$sizeSql.= " binaryID = ".$binSizeId["ID"]." or ";
					
					//
					// Get categoryID if one has been allocated to this 
					//					
					if ($binSizeId["categoryID"] != "" && $regexAppliedCategoryID == "")
						$regexAppliedCategoryID = $binSizeId["categoryID"];
					//
					// Get RegexID if one has been allocated to this 
					//					
					if ($binSizeId["regexID"] != "" && $regexIDused == "")
						$regexIDused = $binSizeId["regexID"];
					//
					// Get requestID if one has been allocated to this 
					//					
					if ($binSizeId["reqID"] != "" && $reqIDused == "")
						$reqIDused = $binSizeId["reqID"];
						
					//
					// Get number of expected parts
					//
					$relTotalParts += $binSizeId["totalParts"];
				}
				$sizeSql.=" 1=2) ";
				$temp = $db->queryOneRow($sizeSql);
				$totalSize = ($temp["totalSize"]+0)."";
				$relCompletion = number_format($temp["relParts"]/$relTotalParts*100, 1);
			}

			//
			// Insert the release
			// 
			$relguid = md5(uniqid());
			if ($regexAppliedCategoryID == "")
				$catId = $cat->determineCategory($row["group_name"], $row["relname"]);
			else
				$catId = $regexAppliedCategoryID;
			
			if ($regexIDused == "")				
				$regexID = " null ";
			else
				$regexID = $regexIDused;
			
			if ($reqIDused == "")				
				$reqID = " null ";
			else
				$reqID = $reqIDused;

			//Clean release name
			$cleanArr = array('#', '@', '$', '%', '^', '§', '¨', '©', 'Ö');
			$cleanRelName = str_replace($cleanArr, '', $row['relname']);
			
			$relid = $db->queryInsert(sprintf("insert into releases (name, searchname, totalpart, groupID, adddate, guid, categoryID, regexID, rageID, postdate, fromname, size, reqID, passwordstatus, completion) values (%s, %s, %d, %d, now(), %s, %d, %d, -1, %s, %s, %s, %s, %d, %f)", 
										$db->escapeString($cleanRelName), $db->escapeString($cleanRelName), $row["parts"], $row["groupID"], $db->escapeString($relguid), $catId, $regexID, $db->escapeString($bindata["date"]), $db->escapeString($bindata["fromname"]), $totalSize, $reqID, ($page->site->checkpasswordedrar == "1" ? -1 : 0), $relCompletion ));
			echo "Added release ".$cleanRelName."\n";
			
			//
			// Tag every binary for this release with its parent release id
			// remove the release name from the binary as its no longer required
			//
			$db->query(sprintf("update binaries set procstat = %d, releaseID = %d where relname = %s and procstat = %d and groupID = %d and fromname=%s", 
								Releases::PROCSTAT_RELEASED, $relid, $db->escapeString($row["relname"]), Releases::PROCSTAT_READYTORELEASE, $row["groupID"], $db->escapeString($row["fromname"])));

			//
			// Find an .nfo in the release
			//
			$relnfo = $nfo->determineReleaseNfo($relid);
			if ($relnfo !== false) 
			{
				$nfo->addReleaseNfo($relid, $relnfo['ID']);
				$nfocount++;
			}

			//
			// Write the nzb to disk
			//
			$nzb->writeNZBforReleaseId($relid, $relguid, $cleanRelName, $catId, $nzb->getNZBPath($relguid, $page->site->nzbpath, true));

			if ($retcount % 5 == 0)
				echo "-processed ".$retcount." releases stage three\n";
		}    
    	
		echo "Found ".$nfocount." nfos in ".$retcount." releases\n";
		
		//
		// Process nfo files
		//
		if ($page->site->lookupnfo != "1")
		{
			echo "Site config (site.lookupnfo) prevented retrieving nfos\n";		
		}
		else
		{
			$nfo->processNfoFiles($page->site->lookupimdb, ($page->site->lookuptvrage=="1"));
		}
		
		//
		// Lookup imdb if enabled
		//
		if ($page->site->lookupimdb == 1) 
		{
			$movie = new Movie(true);
			$movie->processMovieReleases();
		}
		
		//
		// Lookup music if enabled
		//
		if ($page->site->lookupmusic == 1) 
		{
			$music = new Music(true);
			$music->processMusicReleases();
		}
		
		//
		// Lookup games if enabled
		//
		if ($page->site->lookupgames == 1) 
		{
			$console = new Console(true);
			$console->processConsoleReleases();
		}
			
		//
		// Check for passworded releases
		//
		if ($page->site->checkpasswordedrar != "1")
		{
			echo "Site config (site.checkpasswordedrar) prevented checking releases are passworded\n";		
		}
		else
		{
			$this->processPasswordedReleases(true);
		}

		//
		// Process all TV related releases which will assign their series/episode/rage data
		//
		$tvrage = new TVRage(true);
		$tvrage->processTvReleases(($page->site->lookuptvrage=="1"));
		
		//
		// Get the current datetime again, as using now() in the housekeeping queries prevents the index being used.
		//
		$currTime = $db->queryOneRow("SELECT NOW() as now");		
		
		//
		// Tidy away any binaries which have been attempted to be grouped into 
		// a release more than x times
		//
		echo "Tidying away binaries which cant be grouped after ".$page->site->attemptgroupbindays." days\n";			
		$db->query(sprintf("update binaries set procstat = %d where procstat = %d and dateadded < %s - interval %d day ", 
			Releases::PROCSTAT_WRONGPARTS, Releases::PROCSTAT_NEW, $db->escapeString($currTime["now"]), $page->site->attemptgroupbindays));
		
		//
		// Delete any parts and binaries which are older than the site's retention days
		//
		echo "Deleting parts which are older than ".$page->site->rawretentiondays." days\n";			
		$db->query(sprintf("delete from parts where dateadded < %s - interval %d day", $db->escapeString($currTime["now"]), $page->site->rawretentiondays));

		echo "Deleting binaries which are older than ".$page->site->rawretentiondays." days\n";			
		$db->query(sprintf("delete from binaries where dateadded < %s - interval %d day", $db->escapeString($currTime["now"]), $page->site->rawretentiondays));
		
		//
		// Delete any releases which are older than site's release retention days
		//
		if($page->site->releaseretentiondays != 0)
		{
			echo "Determining any releases past retention to be deleted.\n\n";

			$result = $db->query(sprintf("select ID from releases where postdate < %s - interval %d day", $db->escapeString($currTime["now"]), $page->site->releaseretentiondays)); 		
			foreach ($result as $row)
				$this->delete($row["ID"]);
		}
		
		echo "Processed ". $retcount." releases\n\n";
			
		return $retcount;	
	}

	public function processPasswordedReleases($echooutput=false)
	{
		$maxattemptstocheckpassworded = 5;
		$potentiallypasswordedfileregex = "/\.(ace|cab|tar|gz)$/i";
		$numfound = 0; $numpasswd = 0; $numpot = 0; $numnone = 0;
		$db = new DB;
		$nntp = new Nntp;
		$rar = new RarInfo;
		$rar->setMaxBytes(4000);
		
		if($echooutput)
			echo "Checking for passworded releases.\n\n";
		
		//
		// Get out all releases which have not been checked more than max attempts for password.
		//
		$result = $db->query(sprintf("select ID from releases where passwordstatus between %d and -1", ($maxattemptstocheckpassworded + 1) * -1));
	
		if (count($result) > 0)
		{
			$nntp->doConnect();
	
			foreach ($result as $row)
			{
				//
				// get out all files for this release, if it contains no rars, mark as Releases::PASSWD_NONE
				// if it contains rars, try and retrieve the message for the first rar and inspect its filename
				// if rar file is encrypted set as Releases::PASSWD_RAR, if it contains an ace/cab etc 
				// mark as Releases::PASSWD_POTENTIAL, otherwise set as Releases::PASSWD_NONE.
				//
				$numfound++;
				
				//
				// Go through the binaries for this release looking for a rar
				//
				$binresult = $db->query(sprintf("select binaries.ID, binaries.name, groups.name as groupname from binaries inner join groups on groups.ID = binaries.groupID where releaseID = %d order by relpart", $row["ID"]));
				$msgid = -1;
				$bingroup = "";
				foreach ($binresult as $binrow)
				{
					if (preg_match("/\W(?:part0*1|(?!part\d+)[^.]+)\.rar(?!\.)/i", $binrow["name"]))
					{
						$bingroup = $binrow["groupname"];
						echo "Checking ".$binrow["name"]." for password.\n";
						$part = $db->queryOneRow(sprintf("select messageID from parts where binaryID = %d order by partnumber", $binrow["ID"]));
						if (isset($part["messageID"]))
						{
							$msgid = $part["messageID"];
							break;
						}
					}
				}
			
				$passStatus = Releases::PASSWD_NONE;
				
				//
				// no part of binary found matching a rar, so it cant be progressed further
				//
				if ($msgid != -1)
				{
					$fetchedBinary = $nntp->getMessage($bingroup, $msgid);
					if ($fetchedBinary === false) 
					{			
						$db->query(sprintf("update releases set passwordstatus = passwordstatus - 1 where ID = %d", $row["ID"]));
						continue;
					}
					
					if ($rar->setData($fetchedBinary))
					{
	
						//
						// whole archive password protected
						//
						if ($rar->isEncrypted)
						{
							$passStatus = Releases::PASSWD_RAR;
						}
						else
						{
							$files = $rar->getFileList();			
							foreach ($files as $file) 
							{
								//
								// individual file rar passworded
								//
								if ($file['pass'] == true) 
								{
									$passStatus = Releases::PASSWD_RAR;
									break;
								}
								//
								// individual file looks suspect
								//
								else if (preg_match($potentiallypasswordedfileregex, $file["name"]))
								{
									$passStatus = Releases::PASSWD_POTENTIAL;
									break;
								}
							}
						}
					}
				}
				//
				// increment reporting stats
				//
				if ($passStatus == Releases::PASSWD_RAR)
					$numpasswd++;
				elseif ($passStatus == Releases::PASSWD_POTENTIAL)
					$numpot++;
				else
					$numnone++;
					
				$db->query(sprintf("update releases set passwordstatus = %d where ID = %d", $passStatus, $row["ID"]));
			}
			
			$nntp->doQuit();
		}
					
		if($echooutput)
			echo sprintf("Finished checking for passwords for %d releases (%d passworded, %d potential, %d none).\n\n", $numfound, $numpasswd, $numpot, $numnone);
	}

	public function getReleaseNameForReqId($url, $groupname, $reqid, $echooutput=false)
	{
		$url = str_ireplace("[GROUP]", urlencode($groupname), $url);
		$url = str_ireplace("[REQID]", urlencode($reqid), $url);

		$xml = "";
		$arrXml = "";
		$xml = getUrl($url);
		
		if ($xml === false || preg_match('/no feed/i', $xml)) 
			return "no feed";
		else
		{		
			if ($xml != "")
			{
				$xmlObj = @simplexml_load_string($xml);
				$arrXml = objectsIntoArray($xmlObj);
	
				if (isset($arrXml["item"]) && is_array($arrXml["item"]) && is_array($arrXml["item"]["@attributes"]))
				{
					if ($echooutput)
						echo "found title for reqid ".$reqid." - ".$arrXml["item"]["@attributes"]["title"]."\n";
						
					return $arrXml["item"]["@attributes"]["title"];
				}
			}
		}

		if ($echooutput)
			echo "no title found for reqid ".$reqid."\n";

		return "";		
	}

	public function checkRegexesUptoDate($url, $rev, $echooutput=false)
	{
		if ($url != "")
		{
			$regfile = getUrl($url);
			if ($regfile !== false && $regfile != "")
			{
				/*$Rev: 728 $*/
				if (preg_match('/\/\*\$Rev: (\d{3,4})/i', $regfile, $matches))
				{ 
					$serverrev = intval($matches[1]);
					if ($serverrev > $rev)
					{
						$db = new DB();
						$site = new Sites;
						
						$queries = explode(";", $regfile);
						$queries = array_map("trim", $queries);
						foreach($queries as $q) 
							$db->query($q);

						$site->updateLatestRegexRevision($serverrev);

						if ($echooutput)
							echo "updated regexes to revision ".$serverrev."\n";
					}
					else
					{
						if ($echooutput)
							echo "using latest regex revision ".$rev."\n";
					}
				}
				else
				{
						if ($echooutput)
							echo "Error Processing Regex File\n";
				}
			}
			else
			{
				echo "Error Regex File Does Not Exist or Unable to Connect\n";
			}
		}
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
		
		$site = new Sites();
		$s = $site->get();
		if ($s->storeuserips != "1")
			$host = "";		
		
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
		
		return $db->query(" SELECT releasecomment.*, users.username, releases.guid FROM releasecomment LEFT OUTER JOIN users ON users.ID = releasecomment.userID inner join releases on releases.ID = releasecomment.releaseID order by releasecomment.createddate desc ".$limit);		
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
	
	public function getTopDownloads()
	{
		$db = new DB();
		return $db->query("SELECT ID, searchname, guid, adddate, SUM(grabs) as grabs FROM releases
							GROUP BY ID, searchname, adddate
							HAVING SUM(grabs) > 0
							ORDER BY grabs DESC
							LIMIT 10");		
	}	

	public function getTopComments()
	{
		$db = new DB();
		return $db->query("SELECT ID, guid, searchname, adddate, SUM(comments) as comments FROM releases
							GROUP BY ID, searchname, adddate
							HAVING SUM(comments) > 0
							ORDER BY comments DESC
							LIMIT 10");		
	}	

	public function getRecentlyAdded()
	{
		$db = new DB();
		return $db->query("SELECT concat(cp.title, ' > ', category.title) as title, COUNT(*) AS count
FROM category
left outer join category cp on cp.ID = category.parentID
INNER JOIN releases ON releases.categoryID = category.ID
WHERE releases.adddate > NOW() - INTERVAL 1 WEEK
GROUP BY concat(cp.title, ' > ', category.title)
ORDER BY COUNT(*) DESC");	
	}

}
?>

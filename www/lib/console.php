<?php
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/amazon.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/nfo.php");
require_once(WWW_DIR."/lib/site.php");

class Console
{
	const NUMTOPROCESSPERTIME = 100;
	
	function Console($echooutput=false)
	{
		$this->echooutput = $echooutput;
		$this->genres = '';
		$s = new Sites();
		$site = $s->get();
		$this->pubkey = $site->amazonpubkey;
		$this->privkey = $site->amazonprivkey;
	}
	
	public function getConsoleInfo($id)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT consoleinfo.* FROM consoleinfo where consoleinfo.ID = %d ", $id));
	}

	public function getConsoleInfoByName($title, $platform)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT * FROM consoleinfo where title like %s and platform like %s", $db->escapeString("%".$title."%"),  $db->escapeString("%".$platform."%")));
	}

	public function getRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(" SELECT * FROM consoleinfo ORDER BY createddate DESC".$limit);		
	}
	
	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from consoleinfo");		
		return $res["num"];
	}
	
	public function getConsoleCount($cat, $maxage=-1, $excludedcats=array())
	{
		$db = new DB();
		
		$browseby = $this->getBrowseBy();
		
		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " (";
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
								$catsrch .= " r.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" r.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}			

		if ($maxage > 0)
			$maxage = sprintf(" and r.postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";		
		
		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and r.categoryID not in (".implode(",", $excludedcats).")";
		
		$sql = sprintf("select count(r.ID) as num from releases r inner join consoleinfo c on c.ID = r.consoleinfoID and c.title != '' where r.passwordstatus <= (select showpasswordedrelease from site) and %s %s %s %s", $browseby, $catsrch, $maxage, $exccatlist);
		$res = $db->queryOneRow($sql);		
		return $res["num"];	
	}	
	
	public function getConsoleRange($cat, $start, $num, $orderby, $maxage=-1, $excludedcats=array())
	{	
		$db = new DB();
		
		$browseby = $this->getBrowseBy();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " (";
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
								$catsrch .= " r.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" r.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}	
		
		$maxage = "";
		if ($maxage > 0)
			$maxage = sprintf(" and r.postdate > now() - interval %d day ", $maxage);

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and r.categoryID not in (".implode(",", $excludedcats).")";
			
		$order = $this->getConsoleOrder($orderby);
		$sql = sprintf(" SELECT r.*, con.*, groups.name as group_name, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, rn.ID as nfoID from releases r left outer join groups on groups.ID = r.groupID inner join consoleinfo con on con.ID = r.consoleinfoID left outer join releasenfo rn on rn.releaseID = r.ID and rn.nfo is not null left outer join category c on c.ID = r.categoryID left outer join category cp on cp.ID = c.parentID where r.passwordstatus <= (select showpasswordedrelease from site) and %s %s %s %s order by %s %s".$limit, $browseby, $catsrch, $maxage, $exccatlist, $order[0], $order[1]);
		return $db->query($sql);		
	}
	
	public function getConsoleOrder($orderby)
	{
		$order = ($orderby == '') ? 'r.postdate' : $orderby;
		$orderArr = explode("_", $order);
		switch($orderArr[0]) {
			case 'artist':
				$orderfield = 'm.artist';
			break;
			case 'size':
				$orderfield = 'r.size';
			break;
			case 'files':
				$orderfield = 'r.totalpart';
			break;
			case 'stats':
				$orderfield = 'r.grabs';
			break;
			case 'year':
				$orderfield = 'm.year';
			break;
			case 'genre':
				$orderfield = 'm.musicgenreID';
			break;
			case 'posted': 
			default:
				$orderfield = 'r.postdate';
			break;
		}
		$ordersort = (isset($orderArr[1]) && preg_match('/^asc|desc$/i', $orderArr[1])) ? $orderArr[1] : 'desc';
		return array($orderfield, $ordersort);
	}
	
	public function getConsoleOrdering()
	{
		return array('artist_asc', 'artist_desc', 'posted_asc', 'posted_desc', 'size_asc', 'size_desc', 'files_asc', 'files_desc', 'stats_asc', 'stats_desc', 'year_asc', 'year_desc', 'genre_asc', 'genre_desc');
	}
	
	public function getBrowseByOptions()
	{
		return array('artist'=>'artist', 'title'=>'title', 'genre'=>'musicgenreID', 'year'=>'year');
	}
	
	public function getBrowseBy()
	{
		$db = new Db;
		
		$browseby = ' ';
		$browsebyArr = $this->getBrowseByOptions();
		foreach ($browsebyArr as $bbk=>$bbv) {
			if (isset($_REQUEST[$bbk]) && !empty($_REQUEST[$bbk])) {
				$bbs = stripslashes($_REQUEST[$bbk]);
				if (preg_match('/id/i', $bbv)) {
					$browseby .= "m.{$bbv} = $bbs AND ";
				} else {
					$browseby .= "m.$bbv LIKE(".$db->escapeString('%'.$bbs.'%').") AND ";
				}
			}
		}
		return $browseby;
	}
	
	public function makeFieldLinks($data, $field)
	{
		$tmpArr = explode(', ',$data[$field]);
		$newArr = array();
		$i = 0;
		foreach($tmpArr as $ta) {
			if ($i > 5) { break; } //only use first 6
			$newArr[] = '<a href="'.WWW_TOP.'/console?'.$field.'='.urlencode($ta).'" title="'.$ta.'">'.$ta.'</a>';
			$i++;
		}
		return implode(', ', $newArr);
	}
	
	public function update($id, $title, $tagline, $plot, $year, $rating, $genre, $director, $actors, $language, $cover, $backdrop)
	{			
		$db = new DB();
		
		//$db->query(sprintf("UPDATE musicinfo SET title=%s, tagline=%s, plot=%s, year=%s, rating=%s, genre=%s, director=%s, actors=%s, language=%s, cover=%d, backdrop=%d, updateddate=NOW() WHERE imdbID = %d", 
		//	$db->escapeString($title), $db->escapeString($tagline), $db->escapeString($plot), $db->escapeString($year), $db->escapeString($rating), $db->escapeString($genre), $db->escapeString($director), $db->escapeString($actors), $db->escapeString($language), $cover, $backdrop, $id));		
	}
	
	public function updateConsoleInfo($gameInfo)
	{
		$db = new DB();

		if ($this->echooutput)
			echo "Looking up: ".$gameInfo['title']." ".$gameInfo['platform']." [".$gameInfo['release']."]\n";
		
		$con = array();
		$amaz = $this->fetchAmazonProperties($gameInfo['title'], $gameInfo['node']);
		if (!$amaz) 
			return false;	
		
		//
		// get game properties
		//

		$con['coverurl'] = (string) $amaz->Items->Item->MediumImage->URL;
		if ($con['coverurl'] != "")
			$con['cover'] = 1;
		else
			$con['cover'] = 0;

		$con['title'] = (string) $amaz->Items->Item->ItemAttributes->Title;
		if (empty($con['title']))
			$con['title'] = $title;
			
		$con['asin'] = (string) $amaz->Items->Item->ASIN;
		
		$con['url'] = (string) $amaz->Items->Item->DetailPageURL;
		$con['url'] = str_replace("%26tag%3Dws", "%26tag%3Dopensourceins%2D21", $con['url']);
		
		$con['salesrank'] = (string) $amaz->Items->Item->SalesRank;
		if ($con['salesrank'] == "")
			$con['salesrank'] = 'null';
		
		$con['platform'] = (string) $amaz->Items->Item->ItemAttributes->Platform;
		if (empty($con['platform']))
			$con['platform'] = $platform;
		
		$con['publisher'] = (string) $amaz->Items->Item->ItemAttributes->Publisher;
		
		$con['releasedate'] = $db->escapeString((string) $amaz->Items->Item->ItemAttributes->ReleaseDate);
		if ($con['releasedate'] == "''")
			$con['releasedate'] = 'null';
		
		$con['review'] = "";
		if (isset($amaz->Items->Item->EditorialReviews))
			$con['review'] = trim(strip_tags((string) $amaz->Items->Item->EditorialReviews->EditorialReview->Content));
		
		$query = sprintf("
		INSERT INTO consoleinfo  (`title`, `asin`, `url`, `salesrank`,  `platform`, `publisher`, `releasedate`, `review`, `cover`, `createddate`, `updateddate`)
		VALUES (%s,        %s,        %s,        %s,        %s,        %s,        %s,        %s,        %d,        now(),        now())
			ON DUPLICATE KEY UPDATE  `title` = %s,  `asin` = %s,  `url` = %s,  `salesrank` = %s,  `platform` = %s,  `publisher` = %s,  `releasedate` = %s,  `review` = %s, `cover` = %d,  createddate = now(),  updateddate = now()", 
		$db->escapeString($con['title']), $db->escapeString($con['asin']), $db->escapeString($con['url']), 
		$con['salesrank'], $db->escapeString($con['platform']), $db->escapeString($con['publisher']), 
		$con['releasedate'], $db->escapeString($con['review']), $con['cover'], 
		$db->escapeString($con['title']), $db->escapeString($con['asin']), $db->escapeString($con['url']), 
		$con['salesrank'], $db->escapeString($con['platform']), $db->escapeString($con['publisher']), 
		$con['releasedate'], $db->escapeString($con['review']), $con['cover'] );
		
		$consoleId = $db->queryInsert($query);

		if ($consoleId) 
		{
			if ($this->echooutput)
				echo "added/updated game: ".$con['title']." ".$con['platform']."\n";

			$con['cover'] = $this->saveCoverImage($con['coverurl'], $consoleId);
		} 
		else 
		{
			if ($this->echooutput)
				echo "nothing to update: ".$con['title']." (".$con['platform'].")\n";
		}

		return $consoleId;
	}
	
	public function fetchCoverImage($imgUrl)
	{		
		$img = @file_get_contents($imgUrl);
		if ($img !== false)
		{
			$im = @imagecreatefromstring($img);
			if ($im !== false)
			{
				return $img;
			}
		}
		return false;	
	}
	
	public function saveCoverImage($imgUrl, $id)
	{
		$cover = $this->fetchCoverImage($imgUrl);
		if ($cover !== false) 
		{
			$coverSave = @file_put_contents(WWW_DIR.'covers/console/'.$id.'.jpg', $cover);
			return ($coverSave !== false) ? 1 : 0;
		}
		return 0;
	}
	
	public function fetchAmazonProperties($title, $node)
	{
    $obj = new AmazonProductAPI($this->pubkey, $this->privkey);
    try
    {
         $result = $obj->searchProducts($title, AmazonProductAPI::GAMES, "NODE", $node);
    }
    catch(Exception $e)
    {
		$result = false;
    }

		return $result;
	}
    
  public function processConsoleReleases()
	{
		$ret = 0;
		$db = new DB();
		$nfo = new Nfo;
		
		$res = $db->queryDirect(sprintf("SELECT searchname, ID from releases where consoleinfoID IS NULL and categoryID in ( select ID from category where parentID = %d ) ORDER BY id DESC LIMIT %d", Category::CAT_PARENT_GAME, Console::NUMTOPROCESSPERTIME));
		if (mysql_num_rows($res) > 0)
		{	
			if ($this->echooutput)
				echo "Processing ".mysql_num_rows($res)." console releases\n";
						
			while ($arr = mysql_fetch_assoc($res)) 
			{				
				$gameInfo = $this->parseTitle($arr['searchname']);
				if ($gameInfo !== false)
				{
					
					//check for existing console entry
					$gameCheck = $this->getConsoleInfoByName($gameInfo["title"], $gameInfo["platform"]);
					
					if ($gameCheck === false)
					{
						$gameId = $this->updateConsoleInfo($gameInfo);
						if ($gameId === false)
						{
							$gameId = -2;
						}
					}
					else 
					{
						$gameId = $gameCheck["ID"];
					}

					//update release
					$db->query(sprintf("UPDATE releases SET consoleinfoID = %d WHERE ID = %d", $gameId, $arr["ID"]));

				} 
				else {
					//could not parse release title
					$db->query(sprintf("UPDATE releases SET consoleinfoID = %d WHERE ID = %d", -2, $arr["ID"]));
				}
			}
		}
	}
	
	function parseTitle($releasename)
	{
		$result = array();
		
		//get name of the game from name of release
		preg_match('/^(?P<title>.*?)[\.\-_ ](v\.?\d\.\d|PAL|NTSC|EUR|USA|JP|ASIA|JAP|JPN|AUS|MULTI\.?5|MULTI\.?4|MULTI\.?3|PATCHED|FULLDVD|DVD5|DVD9|DVDRIP|PROPER|REPACK|RETAIL|DEMO|DISTRIBUTION|REGIONFREE|READ\.?NFO|NFOFIX|PS2|PS3|PSP|WII|X\-?BOX|XBLA|X360|NDS|N64|NGC)/i', $releasename, $matches);
		if (isset($matches['title'])) {
			$title = $matches['title'];
			//replace dots or underscores with spaces
			$result['title'] = preg_replace('/(\.|_|\%20)/', ' ', $title);
		}
		//get the platform of the release
		preg_match('/[\.\-_ ](?P<platform>N64|SNES|NES|PS2|PS3|PSP|WII|XBOX360|X\-?BOX|X360|NDS|NGC)/i', $releasename, $matches);
		if (isset($matches['platform'])) {
			$platform = $matches['platform'];
			$browseNode = $this->getBrowseNode($platform);
			$result['platform'] = $platform;
			$result['node'] = $browseNode;
		}
		$result['release'] = $releasename;
		array_map("trim", $result);
		//make sure we got a title and platform otherwise the resulting lookup will probably be shit
		//other option is to pass the $release->categoryID here if we dont find a platform but that would require an extra lookup to determine the name
		//in either case we should have a title at the minimum
		return (isset($result['title']) && !empty($result['title']) && isset($result['platform'])) ? $result : false;
	}
	
	function getBrowseNode($platform)
	{
		switch($platform)
		{
			case 'PS2':
				$nodeId = '301712';
			break;
			case 'PS3':
				$nodeId = '14210751';
			break;
			case 'PSP':
				$nodeId = '11075221';
			break;
			case 'WII':
				$nodeId = '14218901';
			break;
			case 'XBOX360':
			case 'X360':
				$nodeId = '14220161';
			break;
			case 'XBOX':
			case 'X-BOX':
				$nodeId = '537504';
			break;
			case 'NDS':
				$nodeId = '11075831';
			break;
			case 'N64':
				$nodeId = '229763';
			break;
			case 'SNES':
				$nodeId = '294945';
			break;
			case 'NES':
				$nodeId = '566458';
			break;
			case 'NGC':
				$nodeId = '541022';
			break;
			default:
				$nodeId = '468642'; 
			break;
		}
	
		return $nodeId;
	}

}


?>
<?php
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/amazon.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/nfo.php");

class Music
{
	function Music($echooutput=false)
	{
		$this->echooutput = $echooutput;
	}
	
	public function getMusicInfo($id)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT * FROM musicinfo where ID = %d", $id));
	}

	public function getMusicInfoByName($artist, $album)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT * FROM musicinfo where title like %s and artist like %s", $db->escapeString("%".$artist."%"),  $db->escapeString("%".$album."%")));
	}

	public function getRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(" SELECT * FROM musicinfo ORDER BY createddate DESC".$limit);		
	}
	
	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from movieinfo");		
		return $res["num"];
	}
	
	public function getMusicCount($cat, $maxage=-1, $excludedcats=array())
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
		
		$sql = sprintf("select count(r.ID) as num from releases r inner join musicinfo m on m.ID = r.musicID and m.title != '' where r.passwordstatus <= (select showpasswordedrelease from site) and %s %s %s %s", $browseby, $catsrch, $maxage, $exccatlist);
		$res = $db->queryOneRow($sql);		
		return $res["num"];	
	}	
	
	public function getMusicRange($cat, $start, $num, $orderby, $maxage=-1, $excludedcats=array())
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
			
		$order = $this->getMusicOrder($orderby);
		$sql = sprintf(" SELECT r.*, m.*, groups.name as group_name, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, rn.ID as nfoID from releases r left outer join groups on groups.ID = r.groupID inner join musicinfo m on m.ID = r.musicID and m.title != '' left outer join releasenfo rn on rn.releaseID = r.ID and rn.nfo is not null left outer join category c on c.ID = r.categoryID left outer join category cp on cp.ID = c.parentID where r.passwordstatus <= (select showpasswordedrelease from site) and %s %s %s %s order by %s %s".$limit, $browseby, $catsrch, $maxage, $exccatlist, $order[0], $order[1]);
		return $db->query($sql);		
	}
	
	public function getMusicOrder($orderby)
	{
		$order = ($orderby == '') ? 'r.postdate' : $orderby;
		$orderArr = explode("_", $order);
		switch($orderArr[0]) {
			case 'title':
				$orderfield = 'm.title';
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
			case 'rating':
				$orderfield = 'm.rating';
			break;
			case 'posted': 
			default:
				$orderfield = 'r.postdate';
			break;
		}
		$ordersort = (isset($orderArr[1]) && preg_match('/^asc|desc$/i', $orderArr[1])) ? $orderArr[1] : 'desc';
		return array($orderfield, $ordersort);
	}
	
	public function getMusicOrdering()
	{
		return array('title_asc', 'title_desc', 'posted_asc', 'posted_desc', 'size_asc', 'size_desc', 'files_asc', 'files_desc', 'stats_asc', 'stats_desc', 'year_asc', 'year_desc', 'rating_asc', 'rating_desc');
	}
	
	public function getBrowseByOptions()
	{
		return array('title', 'director', 'actors', 'genre', 'rating', 'year', 'imdb');
	}
	
	public function getBrowseBy()
	{
		$db = new Db;
		
		$browseby = ' ';
		$browsebyArr = $this->getBrowseByOptions();
		foreach ($browsebyArr as $bb) {
			if (isset($_REQUEST[$bb]) && !empty($_REQUEST[$bb])) {
				$bbv = stripslashes($_REQUEST[$bb]);
				if ($bb == 'id') {
					$browseby .= "m.{$bb}ID = $bbv AND ";
				} else {
					$browseby .= "m.$bb LIKE(".$db->escapeString('%'.$bbv.'%').") AND ";
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
			$newArr[] = '<a href="'.WWW_TOP.'/music?'.$field.'='.urlencode($ta).'" title="'.$ta.'">'.$ta.'</a>';
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
	
	public function updateMusicInfo($artist, $album, $year)
	{
		$db = new DB();

		if ($this->echooutput)
			echo "fetching music info from amazon: ".$artist." - ".$album." (".$year.")\n";
		
		$mus = array();
		$amaz = $this->fetchAmazonProperties($artist." - ".$album);
		if (!$amaz) 
		{
			if ($this->echooutput)
				echo "- not found in amazon\n";
			
			return false;
		}
		
		//
		// get album properties
		//

		$mus['coverurl'] = (string) $amaz->Items->Item->MediumImage->URL;
		if ($mus['coverurl'] != "")
			$mus['cover'] = 1;
		else
			$mus['cover'] = 0;

		$mus['title'] = (string) $amaz->Items->Item->ItemAttributes->Title;
		if (empty($mus['title']))
			$mus['title'] = $album;
			
		$mus['asin'] = (string) $amaz->Items->Item->ASIN;
		
		$mus['url'] = (string) $amaz->Items->Item->DetailPageURL;
		
		$mus['salesrank'] = (string) $amaz->Items->Item->SalesRank;
		if ($mus['salesrank'] == "")
			$mus['salesrank'] = 'null';
		
		$mus['artist'] = (string) $amaz->Items->Item->ItemAttributes->Artist;
		if (empty($mus['artist']))
			$mus['artist'] = $artist;
		
		$mus['publisher'] = (string) $amaz->Items->Item->ItemAttributes->Publisher;
		
		$mus['releasedate'] = $db->escapeString((string) $amaz->Items->Item->ItemAttributes->ReleaseDate);
		if ($mus['releasedate'] == "''")
			$mus['releasedate'] = 'null';
		
		$mus['review'] = "";
		if (isset($amaz->Items->Item->EditorialReviews))
			$mus['review'] = trim(strip_tags((string) $amaz->Items->Item->EditorialReviews->EditorialReview->Content));
		
		$mus['year'] = $year;
		
		$mus['tracks'] = "";
		if (isset($amaz->Items->Item->Tracks))
		{
			$tmpTracks = (array) $amaz->Items->Item->Tracks->Disc;
			$tracks = $tmpTracks['Track'];
			$mus['tracks'] = (is_array($tracks) && !empty($tracks)) ? implode('|', $tracks) : '';
		}
				
		/*
		`musicgenreID` int(10) unsigned NULL,
		*/
		$mus["musicgenreID"] = -1;
		
		$query = sprintf("
		INSERT INTO musicinfo  (`title`, `asin`, `url`, `salesrank`,  `artist`, `publisher`, `releasedate`, `review`,`year`, `musicgenreID`, `tracks`, `cover`, `createddate`, `updateddate`)
		VALUES (%s,        %s,        %s,        %s,        %s,        %s,        %s,        %s,        %s,        %d,        %s,        %d,        now(),        now())
			ON DUPLICATE KEY UPDATE  `title` = %s,  `asin` = %s,  `url` = %s,  `salesrank` = %s,  `artist` = %s,  `publisher` = %s,  `releasedate` = %s,  `review` = %s,  `year` = %s,  `musicgenreID` = %d,  `tracks` = %s,  `cover` = %d,  createddate = now(),  updateddate = now()", 
		$db->escapeString($mus['title']), $db->escapeString($mus['asin']), $db->escapeString($mus['url']), 
		$mus['salesrank'], $db->escapeString($mus['artist']), $db->escapeString($mus['publisher']), 
		$mus['releasedate'], $db->escapeString($mus['review']), $db->escapeString($mus['year']), 
		$mus['musicgenreID'], $db->escapeString($mus['tracks']), $mus['cover'], 
		$db->escapeString($mus['title']), $db->escapeString($mus['asin']), $db->escapeString($mus['url']), 
		$mus['salesrank'], $db->escapeString($mus['artist']), $db->escapeString($mus['publisher']), 
		$mus['releasedate'], $db->escapeString($mus['review']), $db->escapeString($mus['year']), 
		$mus['musicgenreID'], $db->escapeString($mus['tracks']), $mus['cover'] );
		
		$musicId = $db->queryInsert($query);

		if ($musicId) 
		{
			if ($this->echooutput)
				echo "added/updated album: ".$mus['title']." (".$mus['year'].")\n";

			$mus['cover'] = $this->saveCoverImage($mus['coverurl'], $musicId);
		} 
		else 
		{
			if ($this->echooutput)
				echo "nothing to update: ".$mus['title']." (".$mus['year'].")\n";
		}
		
		return $musicId;
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
			$coverSave = @file_put_contents(WWW_DIR.'covers/music/'.$id.'.jpg', $cover);
			return ($coverSave !== false) ? 1 : 0;
		}
		return 0;
	}
	
	public function fetchAmazonProperties($title)
	{
    $obj = new AmazonProductAPI();
    try
    {
         $result = $obj->searchProducts($title, AmazonProductAPI::MUSIC, "TITLE");
    }
    catch(Exception $e)
    {
    	//if first search failed try the mp3downloads section
    	try
    	{
    		$result = $obj->searchProducts($title, AmazonProductAPI::MP3, "TITLE");
    	}
    	catch(Exception $e2)
    	{
				if ($this->echooutput)
					echo "Error fetching amazon properties - ".$e2->getMessage();
					
				$result = false;
			}
    }

		return $result;
	}
    
  public function processMusicReleases()
	{
		$ret = 0;
		$db = new DB();
		$nfo = new Nfo;
		
		$res = $db->queryDirect(sprintf("SELECT searchname, ID from releases where musicinfoID IS NULL and categoryID in ( select ID from category where parentID = %d )", Category::CAT_PARENT_MUSIC));
		if (mysql_num_rows($res) > 0)
		{	
			if ($this->echooutput)
				echo "Processing ".mysql_num_rows($res)." music releases\n";
		
			while ($arr = mysql_fetch_assoc($res)) 
			{				
				$album = $this->parseArtist($arr['searchname']);
				if ($album !== false)
				{
					if ($this->echooutput)
						echo 'Looking up: '.$album["artist"].' - '.$album["album"].' ('.$album['year'].') ['.$arr['searchname'].']'."\n";
					
					//check for existing movie entry
					$albumCheck = $this->getMusicInfoByName($album["artist"], $album["album"]);
					
					if ($albumCheck === false)
					{
						$albumId = $this->updateMusicInfo($album["artist"], $album["album"], $album['year']);
						if ($albumId === false)
						{
							$albumId = -2;
						}
					}
					else 
					{
						$albumId = $albumCheck["ID"];
					}

					//update release
					$db->query(sprintf("UPDATE releases SET musicinfoID = %d WHERE ID = %d", $albumId, $arr["ID"]));

				} 
				else {
					//no album found
					$db->query(sprintf("UPDATE releases SET musicinfoID = %d WHERE ID = %d", -2, $arr["ID"]));
				}
			}
		}
	}
	
	function parseArtist($releasename)
	{
		$result = array();
		/*TODO: FIX VA lookups
		if (substr($releasename, 0, 3) == 'VA-') {
				$releasename = trim(str_replace('VA-', '', $releasename));
		} elseif (substr($name, 0, 3) == 'VA ') {
				$releasename = trim(str_replace('VA ', '', $releasename));
		}
		*/
		//remove years, vbr etc
		$newName = preg_replace('/\(.*?\)/i', '', $releasename);
		//remove double dashes
		$newName = str_replace('--', '-', $newName);
		
		$name = explode("-", $newName);
		$name = array_map("trim", $name);
		
		if (preg_match('/^the /i', $name[0])) {
				$name[0] = preg_replace('/^the /i', '', $name[0]).', The';     
		}
		if (preg_match('/deluxe edition|single|nmrVBR|READ NFO/i', $name[1], $albumType)) {
				$name[1] = preg_replace('/'.$albumType[0].'/i', '', $name[1]);
		}
		$result['artist'] = trim($name[0]);
		$result['album'] = trim($name[1]);
		
		//make sure we've actually matched an album name
		if (preg_match('/^(nmrVBR|WEB|SAT|20\d{2}|19\d{2}|CDM|EP)$/i', $result['album'])) {
			$result['album'] = '';
		}
		
		preg_match('/((?:19|20)\d{2})/i', $releasename, $year);
		$result['year'] = (isset($year[1]) && !empty($year[1])) ? $year[1] : '';
		
		$result['releasename'] = $releasename;
		
		return (!empty($result['artist']) && !empty($result['album'])) ? $result : false;
	}

    public function getGenres()
    {
			$db = new DB();
			return $db->query("select * from musicgenre");		
		}	

}


?>
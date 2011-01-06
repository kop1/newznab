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
			
		$order = $this->getMovieOrder($orderby);
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
	
	public function updateMusicInfo($artist, $album)
	{
		if ($this->echooutput)
			echo "fetching music info from amazon - ".$artist." - ".$album."\n";
		
		$amaz = $this->fetchAmazonProperties($artist." - ".$album);
		if (!$amaz) 
		{
			if ($this->echooutput)
				echo "not found in amazon\n";
			
			return false;
		}
		
		$mus['cover'] = 0;
		if ($amaz->Items->Item->MediumImage) 
		{
			$mus['cover'] = $this->saveCoverImage($amaz->Items->Item->MediumImage, $imdbId);
		}

		//
		// get all other props 
		//



		$db = new DB();
		$query = sprintf("
			INSERT INTO musicinfo 
				(imdbID, tmdbID, title, rating, tagline, plot, year, genre, director, actors, language, cover, backdrop, createddate, updateddate)
			VALUES 
				(%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, NOW(), NOW())
			ON DUPLICATE KEY UPDATE
				imdbID=%d, tmdbID=%s, title=%s, rating=%s, tagline=%s, plot=%s, year=%s, genre=%s, director=%s, actors=%s, language=%s, cover=%d, backdrop=%d, updateddate=NOW()", 
		$mov['imdb_id'], $mov['tmdb_id'], $db->escapeString($mov['title']), $db->escapeString($mov['rating']), $db->escapeString($mov['tagline']), $db->escapeString($mov['plot']), $db->escapeString($mov['year']), $db->escapeString($mov['genre']), $db->escapeString($mov['director']), $db->escapeString($mov['actors']), $db->escapeString($mov['language']), $mov['cover'], $mov['backdrop'],
		$mov['imdb_id'], $mov['tmdb_id'], $db->escapeString($mov['title']), $db->escapeString($mov['rating']), $db->escapeString($mov['tagline']), $db->escapeString($mov['plot']), $db->escapeString($mov['year']), $db->escapeString($mov['genre']), $db->escapeString($mov['director']), $db->escapeString($mov['actors']), $db->escapeString($mov['language']), $mov['cover'], $mov['backdrop']);
		
		$musicId = $db->queryInsert($query);

		if ($musicId) {
			if ($this->echooutput)
				echo "added/updated album: ".$mov['title']." (".$mov['year'].") - ".$mov['imdb_id']."\n";
		} else {
			if ($this->echooutput)
				echo "nothing to update for album: ".$mov['title']." (".$mov['year'].") - ".$mov['imdb_id']."\n";
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
				/*
				$max_width = 1024; 
				$max_height = 768; 
				$width = imagesx($im);
				$height = imagesy($im); 
				$ratioh = $max_height/$height; 
				$ratiow = $max_width/$width; 
				$ratio = min($ratioh, $ratiow); 
				// New dimensions 
				$new_width = intval($ratio*$width); 
				$new_height = intval($ratio*$height); 
				if ($new_width < $width) {
					$new_image = imagecreatetruecolor($new_width, $new_height);
      				imagecopyresampled($new_image, $im, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
      				return $new_image;
				}
				*/
				return $img;
			}
		}
		return false;	
	}
	
	public function saveCoverImage($imgUrl, $id, $type='cover')
	{
		$cover = $this->fetchCoverImage($imgUrl);
		if ($cover !== false) 
		{
			$coverSave = @file_put_contents(WWW_DIR.'covers/music/'.$id.'-'.$type.'.jpg', $cover);
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
			if ($this->echooutput)
				echo "Error fetching amazon properties - ".$e->getMessage();
				
				$result = false;
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
						echo 'Looking up: '.$album["album"].' ['.$arr['searchname'].']'."\n";
					
					//check for existing movie entry
					$albumCheck = $this->getMusicInfoByName($album["artist"], $album["album"]);
					
					if ($albumCheck === false)
					{
						$albumId = $this->updateMusicInfo($album["artist"], $album["album"]);
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
			$name = explode("-", $newName);
			$name = array_map("trim", $name);
			if (preg_match('/^the /i', $name[0])) {
					$name[0] = preg_replace('/^the /i', '', $name[0]).', The';     
			}
			if (preg_match('/deluxe edition|single/i', $name[1], $albumType)) {
					$name[1] = preg_replace('/'.$albumType[0].'/i', '', $name[1]);
			}
			$result['artist'] = trim($name[0]);
			$result['album'] = trim($name[1]);
			$result['releasename'] = $releasename;
			//echo $artist.' - '.$album.' - '.$releasename."\n";
			return $result;
	}
}
?>
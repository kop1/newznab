<?php
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/amazon.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/nfo.php");
require_once(WWW_DIR."/lib/site.php");

class Music
{
	const NUMTOPROCESSPERTIME = 250;
	
	function Music($echooutput=false)
	{
		$this->echooutput = $echooutput;
		$this->genres = '';
		$s = new Sites();
		$site = $s->get();
		$this->pubkey = $site->amazonpubkey;
		$this->privkey = $site->amazonprivkey;
	}
	
	public function getMusicInfo($id)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT musicinfo.*, musicgenre.title as genre FROM musicinfo left outer join musicgenre on musicgenre.ID = musicinfo.musicgenreID where musicinfo.ID = %d ", $id));
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
		$res = $db->queryOneRow("select count(ID) as num from musicinfo");		
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
		
		$sql = sprintf("select count(r.ID) as num from releases r inner join musicinfo m on m.ID = r.musicinfoID and m.title != '' where r.passwordstatus <= (select showpasswordedrelease from site) and %s %s %s %s", $browseby, $catsrch, $maxage, $exccatlist);
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
		$sql = sprintf(" SELECT r.*, r.ID as releaseID, m.*, mg.title as genre, groups.name as group_name, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, rn.ID as nfoID from releases r left outer join groups on groups.ID = r.groupID inner join musicinfo m on m.ID = r.musicinfoID and m.title != '' left outer join releasenfo rn on rn.releaseID = r.ID and rn.nfo is not null left outer join category c on c.ID = r.categoryID left outer join category cp on cp.ID = c.parentID left outer join musicgenre mg on mg.ID = m.musicgenreID where r.passwordstatus <= (select showpasswordedrelease from site) and %s %s %s %s order by %s %s".$limit, $browseby, $catsrch, $maxage, $exccatlist, $order[0], $order[1]);
		return $db->query($sql);		
	}
	
	public function getMusicOrder($orderby)
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
	
	public function getMusicOrdering()
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
			$newArr[] = '<a href="'.WWW_TOP.'/music?'.$field.'='.urlencode($ta).'" title="'.$ta.'">'.$ta.'</a>';
			$i++;
		}
		return implode(', ', $newArr);
	}
	
	public function update($id, $title, $asin, $url, $salesrank, $artist, $publisher, $releasedate, $year, $tracks, $cover, $musicgenreID)
	{			
		$db = new DB();
		
		$db->query(sprintf("UPDATE musicinfo SET title=%s, asin=%s, url=%s, salesrank=%s, artist=%s, publisher=%s, releasedate='%s', year=%s, tracks=%s, cover=%d, musicgenreID=%d, updateddate=NOW() WHERE ID = %d", 
		$db->escapeString($title), $db->escapeString($asin), $db->escapeString($url), $salesrank, $db->escapeString($artist), $db->escapeString($publisher), $releasedate, $db->escapeString($year), $db->escapeString($tracks), $cover, $musicgenreID, $id));		
	}
	
	public function updateMusicInfo($artist, $album, $year)
	{
		$db = new DB();

		if ($this->echooutput)
			echo "Looking up: ".$artist." - ".$album." (".$year.")\n";
		
		$mus = array();
		$amaz = $this->fetchAmazonProperties($artist." - ".$album);
		if (!$amaz) 
			return false;
		
		//load genres
		$defaultGenres = (is_array($this->genres)) ? $this->genres : $this->getGenres();
		$genres = array();
		foreach($defaultGenres as $dg) {
			$genres[$dg['ID']] = strtolower($dg['title']);
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
		$mus['url'] = str_replace("%26tag%3Dws", "%26tag%3Dopensourceins%2D21", $mus['url']);
		
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
		if ($mus['year'] == "" && $mus['releasedate'] != 'null')
			$mus['year'] = substr($mus['releasedate'], 1, 4);
		
		$mus['tracks'] = "";
		if (isset($amaz->Items->Item->Tracks))
		{
			$tmpTracks = (array) $amaz->Items->Item->Tracks->Disc;
			$tracks = $tmpTracks['Track'];
			$mus['tracks'] = (is_array($tracks) && !empty($tracks)) ? implode('|', $tracks) : '';
		}
		
		$genreKey = -1;
		$genreName = '';
		$amazGenres = (array) $amaz->Items->Item->BrowseNodes;
		foreach($amazGenres as $amazGenre) {
			foreach($amazGenre as $ag) {
				$tmpGenre = strtolower( (string) $ag->Name );
				if (!empty($tmpGenre)) {
					if (in_array($tmpGenre, $genres)) {
						$genreKey = array_search($tmpGenre, $genres);
						$genreName = $tmpGenre;
						break;
					} else {
						//we got a genre but its not stored in our musicgenre table
						$genreName = (string) $ag->Name;
						$genreKey = $db->queryInsert(sprintf("INSERT INTO musicgenre (`title`) VALUES (%s)", $db->escapeString($genreName)));
						$nextId = sizeof($this->genres)+1;
						$this->genres[$nextId]['ID'] = $genreKey;
						$this->genres[$nextId]['title'] = $genreName;
						break;
					}
				}
			}
		}
		$mus['musicgenre'] = $genreName;
		$mus['musicgenreID'] = $genreKey;
				
		$query = sprintf("
		INSERT INTO musicinfo  (`title`, `asin`, `url`, `salesrank`,  `artist`, `publisher`, `releasedate`, `review`, `year`, `musicgenreID`, `tracks`, `cover`, `createddate`, `updateddate`)
		VALUES (%s,        %s,        %s,        %s,        %s,        %s,        %s,        %s,        %s,        %s,        %s,        %d,        now(),        now())
			ON DUPLICATE KEY UPDATE  `title` = %s,  `asin` = %s,  `url` = %s,  `salesrank` = %s,  `artist` = %s,  `publisher` = %s,  `releasedate` = %s,  `review` = %s,  `year` = %s,  `musicgenreID` = %s,  `tracks` = %s,  `cover` = %d,  createddate = now(),  updateddate = now()", 
		$db->escapeString($mus['title']), $db->escapeString($mus['asin']), $db->escapeString($mus['url']), 
		$mus['salesrank'], $db->escapeString($mus['artist']), $db->escapeString($mus['publisher']), 
		$mus['releasedate'], $db->escapeString($mus['review']), $db->escapeString($mus['year']), 
		($mus['musicgenreID']==-1?"null":$mus['musicgenreID']), $db->escapeString($mus['tracks']), $mus['cover'], 
		$db->escapeString($mus['title']), $db->escapeString($mus['asin']), $db->escapeString($mus['url']), 
		$mus['salesrank'], $db->escapeString($mus['artist']), $db->escapeString($mus['publisher']), 
		$mus['releasedate'], $db->escapeString($mus['review']), $db->escapeString($mus['year']), 
		($mus['musicgenreID']==-1?"null":$mus['musicgenreID']), $db->escapeString($mus['tracks']), $mus['cover'] );
		
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
    $obj = new AmazonProductAPI($this->pubkey, $this->privkey);
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
		
		$res = $db->queryDirect(sprintf("SELECT searchname, ID from releases where musicinfoID IS NULL and categoryID in ( select ID from category where parentID = %d ) ORDER BY id DESC LIMIT %d", Category::CAT_PARENT_MUSIC, Music::NUMTOPROCESSPERTIME));
		if (mysql_num_rows($res) > 0)
		{	
			if ($this->echooutput)
				echo "Processing ".mysql_num_rows($res)." music releases\n";
			
			$this->genres = $this->getGenres();
			
			while ($arr = mysql_fetch_assoc($res)) 
			{				
				$album = $this->parseArtist($arr['searchname']);
				if ($album !== false)
				{
					if ($this->echooutput)
						echo 'Looking up: '.$album["artist"].' - '.$album["album"].' ('.$album['year'].') ['.$arr['searchname'].']'."\n";
					
					//check for existing music entry
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

	public function getGenres($activeOnly=false)
	{
		$db = new DB();
		if ($activeOnly)
			return $db->query("SELECT musicgenre.* FROM musicgenre INNER JOIN (SELECT DISTINCT musicgenreID FROM musicinfo) X ON X.musicgenreID = musicgenre.ID ORDER BY title");		
		else
			return $db->query("select * from musicgenre order by title");		
	}	

}


?>
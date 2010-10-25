<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/TMDb.php");

class Movie
{
	const TMDBAPIKEY = '9a4e16adddcd1e86da19bcaf5ff3c2a3';
	
	function Movie($echooutput=false)
	{
		$this->echooutput = $echooutput;
	}
	
	public function getMovieInfo($imdbId)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT * FROM movieinfo where imdbID = %d", $imdbId));
	}
	
	public function getRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(" SELECT * FROM movieinfo ORDER BY createddate DESC".$limit);		
	}
	
	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from movieinfo");		
		return $res["num"];
	}
	
	public function getMovieCount($cat, $maxage=-1, $excludedcats=array())
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
		
		$sql = sprintf("select count(r.ID) as num from releases r inner join movieinfo m on m.imdbID = r.imdbID and m.title != '' where r.passwordstatus <= (select showpasswordedrelease from site) and %s %s %s %s", $browseby, $catsrch, $maxage, $exccatlist);
		$res = $db->queryOneRow($sql);		
		return $res["num"];	
	}	
	
	public function getMovieRange($cat, $start, $num, $orderby, $maxage=-1, $excludedcats=array())
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
		$sql = sprintf(" SELECT r.*, m.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, rn.ID as nfoID from releases r inner join movieinfo m on m.imdbID = r.imdbID and m.title != '' left outer join releasenfo rn on rn.releaseID = r.ID and rn.nfo is not null left outer join category c on c.ID = r.categoryID left outer join category cp on cp.ID = c.parentID where r.passwordstatus <= (select showpasswordedrelease from site) and %s %s %s %s order by %s %s".$limit, $browseby, $catsrch, $maxage, $exccatlist, $order[0], $order[1]);
		return $db->query($sql);		
	}
	
	public function getMovieOrder($orderby)
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
	
	public function getMovieOrdering()
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
				if ($bb == 'rating') { $bbv .= '.'; }
				if ($bb == 'imdb') {
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
			$newArr[] = '<a href="'.WWW_TOP.'/movies?'.$field.'='.urlencode($ta).'" title="'.$ta.'">'.$ta.'</a>';
			$i++;
		}
		return implode(', ', $newArr);
	}
	
	public function update($id, $title, $tagline, $plot, $year, $rating, $genre, $director, $actors, $language, $cover, $backdrop)
	{			
		$db = new DB();
		
		$db->query(sprintf("UPDATE movieinfo SET title=%s, tagline=%s, plot=%s, year=%s, rating=%s, genre=%s, director=%s, actors=%s, language=%s, cover=%d, backdrop=%d WHERE imdbID = %d", 
			$db->escapeString($title), $db->escapeString($tagline), $db->escapeString($plot), $db->escapeString($year), $db->escapeString($rating), $db->escapeString($genre), $db->escapeString($director), $db->escapeString($actors), $db->escapeString($language), $cover, $backdrop, $id));		
	}
	
	public function updateMovieInfo($imdbId)
	{
		if ($this->echooutput)
			echo "fetching imdb info from tmdb - ".$imdbId."\n";
		
		//check themoviedb for imdb info
		$tmdb = $this->fetchTmdbProperties($imdbId);
		if (!$tmdb) 
		{
			if ($this->echooutput)
				echo "not found in tmdb\n";
		}
		
		//check imdb for movie info
		$imdb = $this->fetchImdbProperties($imdbId);
		if (!$imdb) 
		{
			if ($this->echooutput)
				echo "unable to get movie info from imdb - ".$imdbId."\n";
		}
										
		if (!$imdb && !$tmdb) {
			return false;
		}
		
		$mov = array();
		$mov['imdb_id'] = $imdbId;
		$mov['tmdb_id'] = (!isset($tmdb['tmdb_id']) || $tmdb['tmdb_id'] == '') ? "NULL" : $tmdb['tmdb_id'];
		
		//prefer tmdb cover over imdb cover
		$mov['cover'] = 0;
		if (isset($tmdb['cover']) && $tmdb['cover'] != '') {
			$mov['cover'] = $this->saveCoverImage($tmdb['cover'], $imdbId, 'cover');
		} elseif (isset($imdb['cover']) && $imdb['cover'] != '') {
			$mov['cover'] = $this->saveCoverImage($imdb['cover'], $imdbId, 'cover');
		}
		
		$mov['backdrop'] = 0;
		if (isset($tmdb['backdrop']) && $tmdb['backdrop'] != '') {
			$mov['backdrop'] = $this->saveCoverImage($tmdb['backdrop'], $imdbId, 'backdrop');
		}
		
		$mov['title'] = '';
		if (isset($imdb['title']) && $imdb['title'] != '') {
			$mov['title'] = $imdb['title'];
		} elseif (isset($tmdb['title']) && $tmdb['title'] != '') { 
			$mov['title'] = $tmdb['title'];
		}
		
		$mov['rating'] = '';
		if (isset($imdb['rating']) && $imdb['rating'] != '') {
			$mov['rating'] = $imdb['rating'];
		} elseif (isset($tmdb['rating']) && $tmdb['rating'] != '') { 
			$mov['rating'] = $tmdb['rating'];
		}
		
		$mov['tagline'] = '';
		if (isset($imdb['tagline']) && $imdb['tagline'] != '') { 
			$mov['tagline'] = $imdb['tagline'];
		}

		$mov['plot'] = '';
		if (isset($imdb['plot']) && $imdb['plot'] != '') {
			$mov['plot'] = $imdb['plot'];
		} elseif (isset($tmdb['plot']) && $tmdb['plot'] != '') { 
			$mov['plot'] = $tmdb['plot'];
		}
		
		$mov['year'] = '';
		if (isset($imdb['year']) && $imdb['year'] != '') {
			$mov['year'] = $imdb['year'];
		} elseif (isset($tmdb['year']) && $tmdb['year'] != '') { 
			$mov['year'] = $tmdb['year'];
		}

		$mov['genre'] = '';
		if (isset($tmdb['genre']) && $tmdb['genre'] != '') {
			$mov['genre'] = $tmdb['genre'];
		} elseif (isset($imdb['genre']) && $imdb['genre'] != '') { 
			$mov['genre'] = $imdb['genre'];
		}
		if (is_array($mov['genre'])) {
			$mov['genre'] = implode(', ', array_unique($mov['genre']));
		}
		
		$mov['director'] = '';
		if (isset($imdb['director']) && $imdb['director'] != '') { 
			$mov['director'] = (is_array($imdb['director'])) ? implode(', ', array_unique($imdb['director'])) : $imdb['director'];
		}
		
		$mov['actors'] = '';
		if (isset($imdb['actors']) && $imdb['actors'] != '') { 
			$mov['actors'] = (is_array($imdb['actors'])) ? implode(', ', array_unique($imdb['actors'])) : $imdb['actors'];
		}
		
		$mov['language'] = '';
		if (isset($imdb['language']) && $imdb['language'] != '') { 
			$mov['language'] = (is_array($imdb['language'])) ? implode(', ', array_unique($imdb['language'])) : $imdb['language'];
		}

		$db = new DB();
		$query = sprintf("
			INSERT INTO movieinfo 
				(imdbID, tmdbID, title, rating, tagline, plot, year, genre, director, actors, language, cover, backdrop, createddate)
			VALUES 
				(%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, NOW())
			ON DUPLICATE KEY UPDATE
				imdbID=%d, tmdbID=%s, title=%s, rating=%s, tagline=%s, plot=%s, year=%s, genre=%s, director=%s, actors=%s, language=%s, cover=%d, backdrop=%d", 
		$mov['imdb_id'], $mov['tmdb_id'], $db->escapeString($mov['title']), $db->escapeString($mov['rating']), $db->escapeString($mov['tagline']), $db->escapeString($mov['plot']), $db->escapeString($mov['year']), $db->escapeString($mov['genre']), $db->escapeString($mov['director']), $db->escapeString($mov['actors']), $db->escapeString($mov['language']), $mov['cover'], $mov['backdrop'],
		$mov['imdb_id'], $mov['tmdb_id'], $db->escapeString($mov['title']), $db->escapeString($mov['rating']), $db->escapeString($mov['tagline']), $db->escapeString($mov['plot']), $db->escapeString($mov['year']), $db->escapeString($mov['genre']), $db->escapeString($mov['director']), $db->escapeString($mov['actors']), $db->escapeString($mov['language']), $mov['cover'], $mov['backdrop']);
		
		$movieId = $db->queryInsert($query);

		if ($movieId) {
			if ($this->echooutput)
				echo "added/updated movie: ".$mov['title']." (".$mov['year'].") - ".$mov['imdb_id']."\n";
		} else {
			if ($this->echooutput)
				echo "nothing to update for movie: ".$mov['title']." (".$mov['year'].") - ".$mov['imdb_id']."\n";
		}
		
		return $movieId;
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
	
	public function saveCoverImage($imgUrl, $imdbId, $type='cover')
	{
		$cover = $this->fetchCoverImage($imgUrl);
		if ($cover !== false) 
		{
			$coverSave = @file_put_contents(WWW_DIR.'views/images/covers/'.$imdbId.'-'.$type.'.jpg', $cover);
			return ($coverSave !== false) ? 1 : 0;
		}
		return 0;
	}
	
	public function fetchTmdbProperties($imdbId)
	{
		$tmdb = new TMDb(Movie::TMDBAPIKEY);
		$lookupId = 'tt'.$imdbId;
		$tmdbLookup = json_decode($tmdb->getMovie($lookupId, TMDb::IMDB));
		if (!$tmdbLookup) { return false; }
		$movie = array_shift($tmdbLookup);
		if ($movie == 'Nothing found.') { return false; }

		$ret = array();
		$ret['title'] = $movie->name;
		$ret['tmdb_id'] = $movie->id;
		$ret['imdb_id'] = $imdbId;
		$ret['rating'] = ($movie->rating == 0) ? '' : $movie->rating;
		$ret['plot'] = $movie->overview;
		$ret['year'] = date("Y", strtotime($movie->released));
		if (isset($movie->genres) && sizeof($movie->genres) > 0) 
		{
			$genres = array();
			foreach($movie->genres as $genre) 
			{
				$genres[] = $genre->name;
			}
			$ret['genre'] = $genres;
		}
		if (isset($movie->posters) && sizeof($movie->posters) > 0) 
		{
			foreach($movie->posters as $poster) 
			{
				if ($poster->image->size == 'cover') 
				{
					$ret['cover'] = $poster->image->url;
				}
			}
		}
		if (isset($movie->backdrops) && sizeof($movie->backdrops) > 0) 
		{
			foreach($movie->backdrops as $backdrop) 
			{
				if ($backdrop->image->size == 'original') 
				{
					$ret['backdrop'] = $backdrop->image->url;
				}
			}
		}
		return $ret;
	}
	
    public function fetchImdbProperties($imdbId)
    {
        $imdb_regex = array(
            'title'    => '/<title>(.*?)\(.*?<\/title>/i',
			'tagline'  => '/taglines:<\/h4>\s([^<]+)/i',
			'plot'     => '/<p>\s<p>(.*?)\s<\/p>\s<\/p>/i',
            'rating'   => '/<span class="rating\-rating">([0-9]{1,2}\.[0-9]{1,2})<span>/i',
			'year'     => '/<title>.*?\(.*?(\d{4}).*?<\/title>/i',
			'cover'    => '/<a.*?href="\/media\/.*?><img src="(.*?)"/i'
        );
        
        $imdb_regex_multi = array(
        	'genre'    => '/href="\/genre\/(.*?)"/i',
        	'language' => '/<a href="\/language\/[a-z]+">(.*?)<\/a>/i'
        );

        $buffer = file_get_contents("http://akas.imdb.com/title/tt$imdbId/");

        // make sure we got some data
        if (strlen($buffer))
        {
        	$ret = array();
            foreach ($imdb_regex as $field => $regex)
            {
                if (preg_match($regex, $buffer, $matches))
                {
                    $match = $matches[1];
                    $match = strip_tags(trim(rtrim(addslashes($match))));
                    $ret[$field] = $match;
                }
            }
            
            foreach ($imdb_regex_multi as $field => $regex)
            {
                if (preg_match_all($regex, $buffer, $matches))
                {
                    $match = $matches[1];
                    $match = array_map("trim", $match);
                    $ret[$field] = $match;
                }
            }
            
            //actors
            if (preg_match('/<table class="cast_list">(.+)<\/table>/s', $buffer, $hit))
            {
				if (preg_match_all('/<a.*?href="\/name\/(nm\d{1,8})\/">([^<]+)<\/a>/', $hit[0], $results, PREG_PATTERN_ORDER))
				{
					$ret['actors'] = $results[2];
				} 
            }
            
            //directors
			if (preg_match('/Directors?:[\s]+?<\/h4>(.+)<\/div>/sU', $buffer, $hit))
			{
				if (preg_match_all('/<a.*?>(.*?)<\/a>/is', $hit[0], $results, PREG_PATTERN_ORDER))
				{
					$ret['director'] = $results[1];
				} 
			}
            
            return $ret;
        }
        return false;
    }
    
    public function getGenres()
    {
    	return array(
    		'Action',
    		'Adventure',
    		'Animation',
    		'Biography',
			'Comedy',
			'Crime',
			'Documentary',
			'Drama',
			'Family',
			'Fantasy',
			'Film-Noir',
			'Game-Show',
			'History',
			'Horror',
			'Music',
			'Musical',
			'Mystery',
			'News',
			'Reality-TV',
			'Romance',
			'Sci-Fi',
			'Sport',
			'Talk-Show',
			'Thriller',
			'War',
			'Western'
		);
    }
}
?>
<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/TMDb.php");

class Movie
{
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
	
	public function update($id, $title, $plot, $year, $rating, $genre, $cover, $backdrop)
	{			
		$db = new DB();
		
		$db->query(sprintf("update movieinfo set title = %s, plot = %s, year = %s, rating = %s, genre = %s, cover = %d, backdrop = %d where imdbID = %d", 
			$db->escapeString($title), $db->escapeString($plot), $db->escapeString($year), $db->escapeString($rating), $db->escapeString($genre), $cover, $backdrop, $id));		
	}
	
	public function updateMovieInfo($imdbId)
	{
		if ($this->echooutput)
			echo "fetching imdb info from tmdb - ".$imdbId."\n";
		
		//check themoviedb for imdb info
		$imdb = $this->fetchTmdbProperties($imdbId);
		if (!$imdb) {
			if ($this->echooutput)
				echo "not found in tmdb... trying from imdb - ".$imdbId."\n";
			
			//check imdb for movie info
			$imdb = $this->fetchImdbProperties($imdbId);
			if (!$imdb) {
				if ($this->echooutput)
					echo "unable to get movie info from imdb - ".$imdbId."\n";
			}
		}
										
		if (!$imdb) {
			return false;
		}
		
		$imdb['tmdb_id'] = (!isset($imdb['tmdb_id']) || $imdb['tmdb_id'] == '') ? "NULL" : $imdb['tmdb_id'];
		
		if (isset($imdb['cover']) && $imdb['cover'] != '') {
			$imdb['cover'] = $this->saveCoverImage($imdb['cover'], $imdbId, 'cover');
		}
			
		if (isset($imdb['backdrop']) && $imdb['backdrop'] != '') {
			$imdb['backdrop'] = $this->saveCoverImage($imdb['backdrop'], $imdbId, 'backdrop');
		}
		
		$db = new DB();
		$query = sprintf("
			INSERT INTO movieinfo 
				(imdbID, tmdbID, title, rating, plot, year, genre, cover, backdrop, createddate)
			VALUES 
				(%d, %s, %s, %s, %s, %s, %s, %d, %d, NOW())
			ON DUPLICATE KEY UPDATE
				imdbID=%d, tmdbID=%s, title=%s, rating=%s, plot=%s, year=%s, genre=%s, cover=%d, backdrop=%d", 
		$imdb['imdb_id'], $imdb['tmdb_id'], $db->escapeString($imdb['title']), $db->escapeString($imdb['rating']), $db->escapeString($imdb['plot']), $db->escapeString($imdb['year']), $db->escapeString($imdb['genre']), $imdb['cover'], $imdb['backdrop'],
		$imdb['imdb_id'], $imdb['tmdb_id'], $db->escapeString($imdb['title']), $db->escapeString($imdb['rating']), $db->escapeString($imdb['plot']), $db->escapeString($imdb['year']), $db->escapeString($imdb['genre']), $imdb['cover'], $imdb['backdrop']);
		
		$movieId = $db->queryInsert($query);
		
		if ($movieId) {
			if ($this->echooutput)
				echo "added movie: ".$imdb['title']." (".$imdb['year'].") - ".$imdb['imdb_id']."\n";
		} else {
			if ($this->echooutput)
				echo "error adding movie: ".$imdb['title']." (".$imdb['year'].") - ".$imdb['imdb_id']."\n";
		}
		
		return $movieId;
	}
	
	private function fetchCoverImage($imgUrl)
	{		
		$img = @file_get_contents($imgUrl);
		if ($img !== false) {
			$im = @imagecreatefromstring($img);
			if ($im !== false) {
				return $img;
			}
		}
		return false;	
	}
	
	private function saveCoverImage($imgUrl, $imdbId, $type='cover')
	{
		$cover = $this->fetchCoverImage($imgUrl);
		if ($cover !== false) {
			$coverSave = file_put_contents(WWW_DIR.'images/covers/'.$imdbId.'-'.$type.'.jpg', $cover);
			return ($coverSave !== false) ? 1 : 0;
		}
		return 0;
	}
	
	private function fetchTmdbProperties($imdbId)
	{
		$tmdb = new TMDb('9a4e16adddcd1e86da19bcaf5ff3c2a3');
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
		$ret['genre'] = '';
		if (isset($movie->genres) && sizeof($movie->genres) > 0) {
			$genres = array();
			foreach($movie->genres as $genre) {
				$genres[] = $genre->name;
			}
			$ret['genre'] = implode(', ', $genres);
		}
		$ret['cover'] = '';
		if (isset($movie->posters) && sizeof($movie->posters) > 0) {
			foreach($movie->posters as $poster) {
				if ($poster->image->size == 'cover') {
					$ret['cover'] = $poster->image->url;
				}
			}
		}
		$ret['backdrop'] = '';
		if (isset($movie->backdrops) && sizeof($movie->backdrops) > 0) {
			foreach($movie->backdrops as $backdrop) {
				if ($backdrop->image->size == 'original') {
					$ret['backdrop'] = $backdrop->image->url;
				}
			}
		}
		return $ret;
	}
	
	/**
     * fetchImdbProperties()
     *
     * @link http://code.google.com/p/moving-pictures/source/browse/trunk/MovingPictures/DataProviders/ScraperScripts/IMDb.xml?spec=svn920&r=920
     * @param int $imdb_id  imdb id
     * @return array
     */
    private function fetchImdbProperties($imdbId)
    {
        $imdb_regex = array(
            'title'    => '/<title>(.*?)\(.*?<\/title>/i',
			'plot'     => '/plot\s?(?:outline|summary)?:<\/h5>\s<div.*?>([^<]*)/i',
            'rating'   => '/<b>([0-9]{1,2}\.[0-9]{1,2})\/10<\/b>/i',
			'year'     => '/<title>.*?\((\d+).*?<\/title>/i',
			'genre'    => '/\/Sections\/Genres\/(.+?)\//i',
			'cover'    => '/<a name="poster".+title=".+">.*?src="([^"]*)"/i'
        );

        $buffer = file_get_contents("http://www.imdb.com/title/tt$imdbId/");

        // make sure we got some data
        if (strlen($buffer))
        {
        	$ret = array();
            $ret['tmdb_id'] = '';
			$ret['imdb_id'] = $imdbId;
			$ret['backdrop'] = '';
			$error = true;
            foreach ($imdb_regex as $field => $regex)
            {
                if (!preg_match($regex, $buffer, $matches))
                {
                    //print "Error fetching '$field' for imdb id: $imdbId\n";
                    $ret[$field] = '';
                }
                else 
                {
                    $match = $matches[1];
                    $match = strip_tags(trim(rtrim(addslashes($match))));
                    $ret[$field] = $match;
                    $error = false;
                }
            }
            return ($error === true) ? false : $ret;
        }
        return false;
    }
}
?>
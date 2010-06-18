<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/nzb.php");
require_once(WWW_DIR."/lib/nntp.php");

class Nfo 
{
	function Nfo() 
	{
		//TODO
	}
	
	/**
     * determineReleaseNfo()
     *
     * @param array $nzbdata  array of nzb binary data provided by $nzb->getNZBforReleaseId($relid);
     * @return array
     */
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
	
	public function processNfoFiles($echooutput=false, $processImdb = true)
	{
		$ret = 0;
		$db = new DB();
		$nzb = new Nzb();
		$nntp = new Nntp();
	
		$res = $db->queryDirect(sprintf("SELECT * FROM releasenfo WHERE nfo IS NULL AND attempts < 5"));

		if ($res) 
		{
			$nntp->doConnect();
			while ($arr = mysql_fetch_array($res, MYSQL_BOTH)) 
			{
				//if ($ret > 0) { continue; } //only process one nfo per run for testing
				$binaryToFetch = $nzb->getNZB(array($arr['binaryID']));
				$fetchedBinary = $nntp->getBinary($binaryToFetch[0]);
				if ($fetchedBinary !== false) 
				{
					//insert nfo into database
					$db->query(sprintf("UPDATE releasenfo SET nfo = compress(%s) WHERE ID = %d", $db->escapeString($fetchedBinary), $arr["ID"]));
					$ret++;
					
					//scan for imdb info if set
					if ($processImdb)
					{
						$imdbId = $this->parseImdb($fetchedBinary);
						if ($imdbId !== false) 
						{
							//update release with imdb id
							$db->query(sprintf("UPDATE releases SET imdbID = %s WHERE ID = %d", $db->escapeString($imdbId), $arr["releaseID"]));
							
							//check for existing movie entry
							$movCheck = $db->queryOneRow(sprintf("SELECT ID FROM movieinfo where imdbID = %d", $imdbId));
							if ($movCheck === false)
							{
								if ($echooutput)
									echo "fetching imdb info from tmdb - ".$imdbId."\n";
								
								//check themoviedb for imdb info
								$imdb = $this->fetchTvdbProperties($imdbId);
								if ($imdb === false) {
									if ($echooutput)
										echo "not found in tmdb... trying from imdb - ".$imdbId."\n";
									
									//check imdb for movie info
									$imdb = $this->fetchImdbProperties($imdbId);
									if ($imdb === false) {
										if ($echooutput)
											echo "unable to get movie info from imdb - ".$imdbId."\n";
									}

								}
																
								if ($imdb !== false) {
									$imdb['tmdb_id'] = (!isset($imdb['tmdb_id']) || $imdb['tmdb_id'] == '') ? "NULL" : $imdb['tmdb_id'];
									if (isset($imdb['cover']) && $imdb['cover'] != '') {
										$cover = $this->fetchCoverImage($imdb['cover']);
										if ($cover !== false) {
											$coverSave = $this->saveCoverImage($cover, $imdb['imdb_id'], 'cover');
											if ($coverSave !== false) {
												$imdb['cover'] = 1;
											} else {
												if ($echooutput)
													echo "unable to save cover image - ".$imdb['cover']."\n";
												$imdb['cover'] = 0;
											}
										} else {
											if ($echooutput)
												echo "cover download failed - ".$imdb['cover']."\n";
										}
									}
									if (isset($imdb['backdrop']) && $imdb['backdrop'] != '') {
										$backdrop = $this->fetchCoverImage($imdb['backdrop']);
										if ($backdrop !== false) {
											$backdropSave = $this->saveCoverImage($backdrop, $imdb['imdb_id'], 'backdrop');
											if ($backdropSave !== false) {
												$imdb['backdrop'] = 1;
											} else {
												if ($echooutput)
													echo "unable to save cover image - ".$imdb['backdrop']."\n";
												$imdb['backdrop'] = 0;
											}
										} else {
											if ($echooutput)
												echo "backdrop download failed - ".$imdb['backdrop']."\n";
										}
									}
								
									$query = sprintf("INSERT INTO movieinfo (imdbID, tmdbID, title, rating, plot, year, genre, cover, backdrop, createddate) VALUES (%d, %s, %s, %s, %s, %s, %s, %d, %d, NOW())", $imdb['imdb_id'], $imdb['tmdb_id'], $db->escapeString($imdb['title']), $db->escapeString($imdb['rating']), $db->escapeString($imdb['plot']), $db->escapeString($imdb['year']), $db->escapeString($imdb['genre']), $imdb['cover'], $imdb['backdrop']);
									$movieId = $db->queryInsert($query);
								
									if ($echooutput && $movieId)
										echo "added movie: ".$imdb['title']." (".$imdb['year'].") - ".$imdb['imdb_id']."\n";
									
								} //no data fetched
							} //no local version of movie
						} //no imdb id found
					} //no imdb processing
				} 
				else 
				{
					if ($echooutput)
						echo "nfo download failed - release ".$arr['releaseID']." on attempt ".($arr["attempts"]++)."\n";
						
					//nfo download failed, increment attempts
					$db->query(sprintf("UPDATE releasenfo SET attempts = attempts+1 WHERE ID = %d", $arr["ID"]));
				}
			}
			$nntp->doQuit();
		}
		
		if ($echooutput)
			echo $ret." nfo files processed\n";
		
		return $ret;
	}
	
	private function fetchCoverImage($imgUrl)
	{		
		$img = @file_get_contents($imgUrl);
		if ($img !== false) {
			$im = @imagecreatefromstring($img);
			if($im !== false) {
				return $img;
			}
		}
		return false;	
	}
	
	private function saveCoverImage($image, $id, $type='cover')
	{
		return file_put_contents(WWW_DIR.'images/covers/'.$id.'-'.$type.'.jpg', $image);
	}
	
	private function parseImdb($str) {
		preg_match('/imdb.*?(tt|Title\?)(\d{7})/i', $str, $matches);
		if (isset($matches[2]) && !empty($matches[2])) {
			return trim($matches[2]);
		}
		return false;
	}
	
	private function fetchTvdbProperties($imdbId)
	{
		require_once(WWW_DIR."/lib/TMDb.php");
		$tmdb = new TMDb('9a4e16adddcd1e86da19bcaf5ff3c2a3');
		$lookupId = 'tt'.$imdbId;
		$movie = array_shift(json_decode($tmdb->getMovie($lookupId, TMDb::IMDB)));
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
        	$ret    = array();
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
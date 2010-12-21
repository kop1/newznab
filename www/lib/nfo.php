<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/nntp.php");
require_once(WWW_DIR."/lib/movie.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/tvrage.php");

class Nfo 
{
	function Nfo($echooutput=false) 
	{
		$this->echooutput = $echooutput;
	}
	
	public function determineReleaseNfo($relid)
	{
		$nfos = array();
		$db = new DB();
		$result = $db->queryDirect(sprintf("select binaries.* from binaries where releaseID = %d order by relpart", $relid));		
		while ($row = mysql_fetch_assoc($result)) 
			if (preg_match('/.*\.nfo[ "\)\]\-]/i', $row['name'])) 
				$nfos[$row['name']] = $row;

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
	
	public function parseImdb($str) 
	{
		preg_match('/imdb.*?(tt|Title\?)(\d{7})/i', $str, $matches);
		if (isset($matches[2]) && !empty($matches[2])) 
		{
			return trim($matches[2]);
		}
		return false;
	}
	
	public function parseRageId($str) 
	{
		preg_match('/tvrage\.com\/shows\/id-(\d{1,6})/i', $str, $matches);
		if (isset($matches[1])) 
		{
			return trim($matches[1]);
		}
		return false;
	}
	
	public function processNfoFiles($processImdb=true)
	{
		$ret = 0;
		$db = new DB();
		$nntp = new Nntp();
		$tvr = new Tvrage();
		
		$res = $db->queryDirect(sprintf("SELECT rn.*, r.searchname FROM releasenfo rn left outer join releases r ON r.ID = rn.releaseID WHERE rn.nfo IS NULL AND rn.attempts < 5"));
		if (mysql_num_rows($res) > 0)
		{	
			if ($this->echooutput)
				echo "Processing ".mysql_num_rows($res)." nfos\n";
		
			$nntp->doConnect();
			while ($arr = mysql_fetch_assoc($res)) 
			{
				$fetchedBinary = $nntp->getBinary($arr['binaryID'], true);
				if ($fetchedBinary !== false) 
				{
					//insert nfo into database
					$db->query(sprintf("UPDATE releasenfo SET nfo = compress(%s) WHERE ID = %d", $db->escapeString($fetchedBinary), $arr["ID"]));
					$ret++;
					
					$imdbId = $this->parseImdb($fetchedBinary);
					if ($imdbId !== false) 
					{
						//update release with imdb id
						$db->query(sprintf("UPDATE releases SET imdbID = %s WHERE ID = %d", $db->escapeString($imdbId), $arr["releaseID"]));
						
						//if set scan for imdb info
						if ($processImdb)
						{
							$movie = new Movie($this->echooutput);
							//check for existing movie entry
							$movCheck = $movie->getMovieInfo($imdbId);
							if ($movCheck === false || (isset($movCheck['updateddate']) && (time() - strtotime($movCheck['updateddate'])) > 2592000))
							{
								$movieId = $movie->updateMovieInfo($imdbId);
							}
						}
					}
					
					$rageId = $this->parseRageId($fetchedBinary);
					if ($rageId !== false)
					{	
						//update release with rage id
						$rel = new Releases();
						$show = $rel->parseNameEpSeason($arr['searchname']);			
						$db->query(sprintf("update releases set rageID = %d, seriesfull = %s, season = %s, episode = %s WHERE ID = %d", 
							$rageId, $db->escapeString($show['seriesfull']), $db->escapeString($show['season']), $db->escapeString($show['episode']), $arr["releaseID"]));
						
						$rid = $tvr->getByRageID($rageId);
						if (!$rid)
							$tvr->add($rageId, $show['name'], '', '');
						
					}
				} 
				else 
				{
					if ($this->echooutput)
						echo "NFO download failed - release ".$arr['releaseID']." on attempt ".($arr["attempts"]++)."\n";
						
					//nfo download failed, increment attempts
					$db->query(sprintf("UPDATE releasenfo SET attempts = attempts+1 WHERE ID = %d", $arr["ID"]));
				}
				
				if ($ret != 0 && $this->echooutput && ($ret % 5 == 0))
					echo "-processed ".$ret." nfos\n";
				
			}
			$nntp->doQuit();
		}
		
		//remove nfo that we cant fetch after 5 attempts
		$db->query("DELETE FROM releasenfo WHERE nfo IS NULL AND attempts >= 5");
		
		if ($this->echooutput)
			echo $ret." nfo files processed\n";
		
		return $ret;
	}
}
?>
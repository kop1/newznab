<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/nzb.php");
require_once(WWW_DIR."/lib/nntp.php");
require_once(WWW_DIR."/lib/movie.php");

class Nfo 
{
	function Nfo($echooutput=false) 
	{
		$this->echooutput = $echooutput;
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
	
	public function parseImdb($str) {
		preg_match('/imdb.*?(tt|Title\?)(\d{7})/i', $str, $matches);
		if (isset($matches[2]) && !empty($matches[2])) {
			return trim($matches[2]);
		}
		return false;
	}
	
	public function processNfoFiles($processImdb=true)
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
							if ($movCheck === false)
							{
								$movieId = $movie->updateMovieInfo($imdbId);
							}
						}
					}
				} 
				else 
				{
					if ($this->echooutput)
						echo "nfo download failed - release ".$arr['releaseID']." on attempt ".($arr["attempts"]++)."\n";
						
					//nfo download failed, increment attempts
					$db->query(sprintf("UPDATE releasenfo SET attempts = attempts+1 WHERE ID = %d", $arr["ID"]));
				}
			}
			$nntp->doQuit();
		}
		
		if ($this->$echooutput)
			echo $ret." nfo files processed\n";
		
		return $ret;
	}
}
?>
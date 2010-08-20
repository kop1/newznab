<?php

require_once("config.php");
require_once(WWW_DIR."/lib/util.php");
require_once(WWW_DIR."/lib/framework/db.php");

class TvRage
{	
	const APIKEY = '7FwjZ8loweFcOhHfnU3E';

	function TvRage()
	{
		$this->searchUrl = "http://services.tvrage.com/feeds/search.php?show=";
		$this->showInfoUrl = "http://services.tvrage.com/feeds/full_show_info.php?sid="; 	
		$this->episodeInfoUrl = "http://services.tvrage.com/myfeeds/episodeinfo.php?key=".TvRage::APIKEY;
	}
	
	public function getByID($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from tvrage where ID = %d", $id ));		
	}
	
	public function getByRageID($id)
	{			
		$db = new DB();
		return $db->query(sprintf("select * from tvrage where rageID = %d", $id ));		
	}
	
	public function add($rageid, $releasename, $desc, $imgbytes)
	{			
		if ($imgbytes == '' && $rageid > 0) {
			$tmpimg = $this->getRageImage($rageid);
			if ($tmpimg !== false) {
				$imgbytes = $tmpimg;
			}
		}
		
		$releasename = str_replace(array('.','_'), array(' ',' '), $releasename);
		
		$db = new DB();
		return $db->queryInsert(sprintf("insert into tvrage (rageID, releasetitle, description, createddate, imgdata) values (%d, %s, %s, now(), %s)", 
			$rageid, $db->escapeString($releasename), $db->escapeString($desc), $db->escapeString($imgbytes)));		
	}

	public function update($id, $rageid, $releasename, $desc, $imgbytes)
	{			
		$db = new DB();
		
		if ($imgbytes != "")
			$imgbytes = sprintf(", imgdata = %s", $db->escapeString($imgbytes));
		
		$db->query(sprintf("update tvrage set rageID = %d, releasetitle = %s, description = %s %s where ID = %d", 
			$rageid, $db->escapeString($releasename), $db->escapeString($desc), $imgbytes, $id ));		
	}

	public function delete($id)
	{			
		$db = new DB();
		return $db->query(sprintf("delete from tvrage where ID = %d",$id));		
	}

	public function getRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(" SELECT ID, rageID, releasetitle, description, createddate from tvrage order by rageID asc".$limit);		
	}
	
	public function getEpisodeInfo($rageid, $series, $episode)
	{
		$series = str_ireplace("s", "", $series);
		$episode = str_ireplace("e", "", $episode);
		$xml = file_get_contents($this->episodeInfoUrl."&sid=".$rageid."&ep=".$series."x".$episode);
		if (preg_match('/no show found/i', $xml))
			return "";
			
		return $xml;
	}
	
	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from tvrage");		
		return $res["num"];
	}
	
	function getRageImage($showId)
	{
		$xml = file_get_contents($this->showInfoUrl.$showId);
		$xmlObj = simplexml_load_string($xml);
		$arrXml = objectsIntoArray($xmlObj);

		if (isset($arrXml['image']) && $arrXml['image'] != '')
		{
			$img = file_get_contents($arrXml['image']);
			$im = @imagecreatefromstring($img);
			if($im !== false) {
				return $img;
			}
		}
		return false;	
	}
	
	function getRageId($title, $echooutput=false, $lookupTvRage = true)
	{
		$db = new DB();
		$res = $db->queryOneRow(sprintf("SELECT rageID from tvrage where lower(releasetitle) = lower(%s)", $db->escapeString($title)));
		if ($res)
			return $res["rageID"];
		
		$title2 = str_replace(' and ', ' & ', $title);
		$res = $db->queryOneRow(sprintf("SELECT rageID from tvrage where lower(releasetitle) = lower(%s)", $db->escapeString($title2)));
		if ($res)
			return $res["rageID"];
		
		if ($lookupTvRage)
		{
			if ($echooutput)
				echo "didnt find rageid for ".$title." in local db, checking web\n";

			$xml = file_get_contents($this->searchUrl.urlencode($title));
			$xmlObj = simplexml_load_string($xml);
			$arrXml = objectsIntoArray($xmlObj);

			if (isset($arrXml["show"]))
			{
				if (isset($arrXml["show"]["showid"]))
				{
					return $arrXml["show"]["showid"];
				}
				else
				{
					$first = "";
					$best = "";
					
					foreach ($arrXml["show"] as $arr)
					{
						if ($first == "")
							$first = $arr["showid"];

						if (isset($arr["name"]) && $arr["name"] == $title)
						{
							$best = $arr["showid"];
							break;
						}
					}
					if ($best != "")
					{
						$this->add($best, $title, "", "");
						return $best;
					}
					elseif ($first != "")
					{
						$this->add($first, $title, "", "");
						return $first;
					}		
				}				
			}
			else
			{
				//
				// Nothing returned form rage, so insert a dummy row in database to prevent going to rage again
				//
				$this->add(-2, $title, "", "");
			}
		}
		return -1;
	}	
}

?>

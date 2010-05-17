<?php

require_once("config.php");
require_once(WWW_DIR."/lib/util.php");
require_once(WWW_DIR."/lib/framework/db.php");

class TvRage
{	
	function TvRage()
	{
		$this->searchUrl = "http://services.tvrage.com/feeds/search.php?show="; 	
		
		//
		// TODO: move this to site table.
		//
		$this->doWebLookup = true; 	
	}

	public function get()
	{			
		$db = new DB();
		return $db->query("select * from tvrage");		
	}
	
	public function getByID($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from tvrage where ID = %d", $id ));		
	}
	
	public function add($rageid, $releasename, $desc)
	{			
		$db = new DB();
		return $db->queryInsert(sprintf("insert into tvrage (rageID, releasetitle, description, createddate) values (%d, %s, %s, now())", 
			$rageid, $db->escapeString($releasename), $db->escapeString($desc) ));		
	}

	public function update($id, $rageid, $releasename, $desc)
	{			
		$db = new DB();
		$db->query(sprintf("update tvrage set rageID = %d, releasetitle = %s, description = %s where ID = %d", 
			$rageid, $db->escapeString($releasename), $db->escapeString($desc), $id ));		
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
		
		return $db->query(" SELECT * from tvrage order by rageID asc".$limit);		
	}
	
	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from tvrage");		
		return $res["num"];
	}

	function getRageId($title)
	{
		$db = new DB();
		$res = $db->queryOneRow(sprintf("SELECT rageID from tvrage where lower(releasetitle) = lower(%s)", $db->escapeString($title)));
		if ($res)
			return $res["rageID"];
		
		if ($this->doWebLookup)
		{
			$xml = file_get_contents($this->searchUrl.urlencode($title));
			$xmlObj = simplexml_load_string($xml);
			$arrXml = objectsIntoArray($xmlObj);
		
			if (isset($arrXml["show"]))
			{
				$first = "";
				$best = "";
				foreach ($arrXml["show"] as $arr)
				{
						if ($first == "")
							$first = $arr["showid"];
						if ($arr["name"] == $title)
						{
							$best = $arr["showid"];
							exit;
						}
				}
				if ($best != "")
				{
					$this->add($best, $title, "");
					return $best;
				}
				elseif ($first != "")
				{
					$this->add($first, $title, "");
					return $first;
				}				
			}
			else
			{
					//
					// Nothing returned form rage, so insert a dummy row in database to prevent going to rage again
					//
					$this->add(-2, $title, "");
			}
		}
		return -1;
	}	
}

?>

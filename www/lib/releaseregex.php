<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");

class ReleaseRegex
{	

	public function get($activeonly=true)
	{			
		$db = new DB();
		
		$where = "";
		if ($activeonly)
			$where = " where releaseregex.status = 1";
			
		return $db->query("SELECT releaseregex.ID, releaseregex.status, releaseregex.description, releaseregex.groupname AS groupname, releaseregex.regex, 
												groups.ID AS groupID, releaseregex.ordinal FROM releaseregex 
												left outer JOIN groups ON groups.name = releaseregex.groupname ".$where."
												ORDER BY coalesce(groupname,'zzz'), ordinal");		
	}

	public function getByID($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from releaseregex where ID = %d ", $id));		
	}

	public function update($regex)
	{			
		$db = new DB();
		
		$groupname = $regex["groupname"];
		if ($groupname == "")
			$groupname = "null";
		else
			$groupname = sprintf("%s", $db->escapeString($regex["groupname"]));
			
		$db->query(sprintf("update releaseregex set groupname=%s, regex=%s, ordinal=%d, status=%d, description=%s where ID = %d ", 
			$groupname, $db->escapeString($regex["regex"]), $regex["ordinal"], $regex["status"], $db->escapeString($regex["description"]), $regex["id"]));	
	}
	
	public function add($regex)
	{			
		$db = new DB();
		
		$groupname = $regex["groupname"];
		if ($groupname == "")
			$groupname = "null";
		else
			$groupname = sprintf("%s", $db->escapeString($regex["groupname"]));
			
		return $db->queryInsert(sprintf("insert into releaseregex (groupname, regex, ordinal, status, description) values (%s, %s, %d, %d, %s) ", 
			$groupname, $db->escapeString($regex["regex"]), $regex["ordinal"], $regex["status"], $db->escapeString($regex["description"])));	
		
	}	

}
?>

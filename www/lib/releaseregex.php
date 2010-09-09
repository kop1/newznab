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
			
		return $db->query("SELECT releaseregex.ID, releaseregex.categoryID, category.title as categoryTitle, releaseregex.status, releaseregex.description, releaseregex.groupname AS groupname, releaseregex.regex, 
												groups.ID AS groupID, releaseregex.ordinal FROM releaseregex 
												left outer JOIN groups ON groups.name = releaseregex.groupname 
												left outer join category on category.ID = releaseregex.categoryID
												".$where."
												ORDER BY coalesce(groupname,'zzz'), ordinal");		
	}

	public function getByID($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from releaseregex where ID = %d ", $id));		
	}

	public function delete($id)
	{			
		$db = new DB();
		return $db->query(sprintf("delete from releaseregex where ID = %d", $id));		
	}		
	
	public function update($regex)
	{			
		$db = new DB();
		
		$groupname = $regex["groupname"];
		if ($groupname == "")
			$groupname = "null";
		else
			$groupname = sprintf("%s", $db->escapeString($regex["groupname"]));
			
		$catid = $regex["category"];
		if ($catid == "-1")
			$catid = "null";
		else
			$catid = sprintf("%d", $regex["category"]);

		$db->query(sprintf("update releaseregex set groupname=%s, regex=%s, ordinal=%d, status=%d, description=%s, categoryID=%s where ID = %d ", 
			$groupname, $db->escapeString($regex["regex"]), $regex["ordinal"], $regex["status"], $db->escapeString($regex["description"]), $catid, $regex["id"]));	
	}
	
	public function add($regex)
	{			
		$db = new DB();
		
		$groupname = $regex["groupname"];
		if ($groupname == "")
			$groupname = "null";
		else
			$groupname = sprintf("%s", $db->escapeString($regex["groupname"]));

		$catid = $regex["category"];
		if ($catid == "-1")
			$catid = "null";
		else
			$catid = sprintf("%d", $regex["category"]);
			
		return $db->queryInsert(sprintf("insert into releaseregex (groupname, regex, ordinal, status, description, categoryID) values (%s, %s, %d, %d, %s, %s) ", 
			$groupname, $db->escapeString($regex["regex"]), $regex["ordinal"], $regex["status"], $db->escapeString($regex["description"]), $catid));	
		
	}	
}
?>

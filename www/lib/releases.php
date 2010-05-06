<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

class Releases
{	
	public function get()
	{			
		$db = new DB();
		return $db->query("select * from releases ");		
	}	
	
	public function getByGuid($guid)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select releases.*, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID where guid = %s ", $db->escapeString($guid)));		
	}	
}
?>
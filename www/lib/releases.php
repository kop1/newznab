<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

class Releases
{	
	public function get()
	{			
		$db = new DB();
		return $db->query("select releases.* from releases");		
	}
	
	public function search($search)
	{			
		$db = new DB();
		$res = $db->query(sprintf("select releases.*, 'cat' as categoryname from releases where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) order by MATCH (searchname) AGAINST (%s IN BOOLEAN MODE) desc, adddate desc ", $db->escapeString($search), $db->escapeString($search)));		

		if (!$res)
				$res = $db->query(sprintf("select releases.*, 'cat' as categoryname from releases where MATCH(searchname) AGAINST (%s IN BOOLEAN MODE) order by MATCH (searchname) AGAINST (%s IN BOOLEAN MODE) desc, adddate desc ", $db->escapeString($search."*"), $db->escapeString($search."*")));		

		return $res;
	}	
	
	public function getByGuid($guid)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select releases.*, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID where guid = %s ", $db->escapeString($guid)));		
	}	
}
?>
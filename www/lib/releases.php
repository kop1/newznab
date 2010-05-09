<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

class Releases
{	
	public function get()
	{			
		$db = new DB();
		return $db->query("select releases.* from releases");		
	}

	public function getRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(" SELECT * from releases order by adddate".$limit);		
	}

	
	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from releases");		
		return $res["num"];
	}
	
	public function delete($id)
	{			
		$db = new DB();
		$db->query(sprintf("delete from releases where id = %d", $id));		
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
	
	public function getById($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select releases.*, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID where releases.ID = %d ", $id));		
	}	

	public function updateGrab($guid)
	{			
		$db = new DB();
		$db->queryOneRow(sprintf("update releases set grabs = grabs + 1 where guid = %s", $db->escapeString($guid)));		
	}
}
?>
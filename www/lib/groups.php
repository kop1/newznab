<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

class Groups
{	

	public function getAll()
	{			
		$db = new DB();
		return $db->query("SELECT groups.*, COALESCE(rel.num, 0) AS num_releases
											FROM groups
											LEFT OUTER JOIN 
											(
												SELECT COUNT(ID) AS num, groupID FROM releases
											) rel ON rel.groupID = groups.ID ");		
	}	
	
	public function getByID($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from groups where ID = %d ", $id));		
	}	

	public function update($group)
	{			
		$db = new DB();
		return $db->query(sprintf("update groups set description = %s, active=%d where ID = %d ",$db->escapeString($group["description"]), $group["active"] , $group["id"] ));		
	}	


}
?>
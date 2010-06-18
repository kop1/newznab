<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");

class ReleaseRegex
{	

	public function get()
	{			
		$db = new DB();
		return $db->query("SELECT releaseregex.ID, releaseregex.groupname AS groupname, releaseregex.regex, 
												groups.ID AS groupID, releaseregex.ordinal FROM releaseregex 
												left outer JOIN groups ON groups.name = releaseregex.groupname 
												ORDER BY coalesce(groupname,'zzz'), ordinal");		
	}

	public function getByID($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from releaseregex where ID = %d ", $id));		
	}

	public function update()
	{			
		$db = new DB();
		
	}

}
?>

<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");

class ReleaseRegex
{	

	public function get()
	{			
		$db = new DB();
		return $db->query("select * from releaseregex");		
	}

	public function getByID($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from releaseregex where ID = %d", $id));		
	}

	public function update()
	{			
		$db = new DB();
		
	}

}
?>

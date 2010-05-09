<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

class Binaries
{	
	public function get()
	{			
		$db = new DB();
		return $db->query("select releases.* from releases");		
	}

	public function getForReleaseGuid($guid)
	{			
		$db = new DB();
		return $db->query(sprintf("select binaries.* from binaries inner join releases on releases.ID = binaries.releaseID where releases.guid = %s", $db->escapeString($guid)));		
	}

}
?>
<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

class Binaries
{	

	const RETENTION = 20; // number of days afterwhich binaries are deleted.

	public function get()
	{			
		$db = new DB();
		return $db->query("select releases.* from releases");		
	}

	public function getForReleaseGuid($guid)
	{			
		$db = new DB();
		return $db->query(sprintf("select binaries.* from binaries inner join releases on releases.ID = binaries.releaseID where releases.guid = %s order by relpart", $db->escapeString($guid)));		
	}

	
	function delOldBinaries($groupID='') 
	{
		$db = new DB();

		$count = 0;
		$res = $db->query(sprintf("SELECT ID FROM binaries WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date)) / 3600 / 24 > %d %s ", Binaries::RETENTION, (is_numeric($groupID) ? " AND groupID = {$groupID} " : "")));
		foreach($res as $arr) 
		{
			$db->query(sprintf("DELETE FROM parts WHERE binaryID = %d", $arr['ID']));
			$db->query(sprintf("DELETE FROM binaries WHERE ID = %d", $arr['ID']));
			$count++;
		}
		return "Deleted {$count} binaries\n";
	}
	
}
?>
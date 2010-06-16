<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/releases.php");

class Binaries
{	

	const RETENTION = 20; // number of days afterwhich binaries are deleted.

	public function get()
	{			
		$db = new DB();
		return $db->query("select releases.* from releases");		
	}
	
	public function search($search, $limit=1000)
	{			
		$db = new DB();
		$res = $db->query(sprintf("
					SELECT b.*, 
					g.name AS group_name,
					r.guid
					FROM binaries b
					INNER JOIN groups g ON g.ID = b.groupID
					LEFT OUTER JOIN releases r ON r.ID = b.releaseID
					WHERE MATCH(b.name) AGAINST (%s IN BOOLEAN MODE) ORDER BY MATCH (b.name) AGAINST (%s IN BOOLEAN MODE) DESC, DATE DESC LIMIT %d ", 
					$db->escapeString($search), $db->escapeString($search), $limit));		

		if (!$res)
			$res = $db->query(sprintf("
					SELECT b.*, 
					g.name AS group_name,
					r.guid
					FROM binaries b
					INNER JOIN groups g ON g.ID = b.groupID
					LEFT OUTER JOIN releases r ON r.ID = b.releaseID
					WHERE MATCH(b.name) AGAINST (%s IN BOOLEAN MODE) ORDER BY MATCH (b.name) AGAINST (%s IN BOOLEAN MODE) DESC, DATE DESC LIMIT %d ", 
					$db->escapeString($search."*"), $db->escapeString($search."*"), $limit));		

		return $res;
	}	

	public function getForReleaseId($id)
	{			
		$db = new DB();
		return $db->query(sprintf("select binaries.* from binaries where releaseID = %d order by relpart", $id));		
	}

}
?>

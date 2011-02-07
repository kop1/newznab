<?php
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/nntp.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/binaries.php");

class Groups
{	
	public function getAll()
	{			
		$db = new DB();
		
		return $db->query("SELECT groups.*, COALESCE(rel.num, 0) AS num_releases
							FROM groups
							LEFT OUTER JOIN
							(
							SELECT groupID, COUNT(ID) AS num FROM releases group by groupID
							) rel ON rel.groupID = groups.ID ORDER BY groups.name");
	}	
	
	public function getByID($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from groups where ID = %d ", $id));		
	}	
	
	public function getActive()
	{			
		$db = new DB();
		return $db->query("SELECT * FROM groups WHERE active = 1 ORDER BY name");		
	}
	
	public function getByName($grp)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from groups where name = '%s' ", $grp));		
	}	

	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from groups");		
		return $res["num"];
	}	
	
	public function getRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query("SELECT groups.*, COALESCE(rel.num, 0) AS num_releases
							FROM groups
							LEFT OUTER JOIN
							(
							SELECT groupID, COUNT(ID) AS num FROM releases group by groupID
							) rel ON rel.groupID = groups.ID ORDER BY groups.name ".$limit);		
	}	
	
	public function add($group)
	{			
		$db = new DB();
		
		if ($group["minfilestoformrelease"] == "" || $group["minfilestoformrelease"] == "0")
			$minfiles = 'null';
		else
			$minfiles = $group["minfilestoformrelease"] + 0;
		
		return $db->queryInsert(sprintf("insert into groups (name, description, first_record, last_record, last_updated, active, minfilestoformrelease) values (%s, %s, %s, %s, null, %d, %s) ",$db->escapeString($group["name"]), $db->escapeString($group["description"]), $db->escapeString($group["first_record"]), $db->escapeString($group["last_record"]), $group["active"], $minfiles));		
	}	
	
	public function delete($id)
	{			
		$db = new DB();
		return $db->query(sprintf("delete from groups where ID = %d", $id));		
	}	
	
	public function reset($id)
	{			
		$db = new DB();
		return $db->query(sprintf("update groups set backfill_target=0, first_record=0, first_record_postdate=null, last_record=0, last_record_postdate=null, last_updated=null where ID = %d", $id));		
	}		
	
	public function purge($id)
	{	
		$db = new DB();
		$releases = new Releases();		
		$binaries = new Binaries();
		
		$this->reset($id);

		$rels = $db->query(sprintf("select ID from releases where groupID = %d", $id));
		foreach ($rels as $rel)
			$releases->delete($rel["ID"]);

		$bins = $db->query(sprintf("select ID from binaries where groupID = %d", $id));
		foreach ($bins as $bin)
			$binaries->delete($bin["ID"]);
	}		
	
	public function update($group)
	{			
		$db = new DB();

		if ($group["minfilestoformrelease"] == "" || $group["minfilestoformrelease"] == "0")
			$minfiles = 'null';
		else
			$minfiles = $group["minfilestoformrelease"] + 0;
		
		return $db->query(sprintf("update groups set name=%s, description = %s, backfill_target = %s , active=%d, minfilestoformrelease=%s where ID = %d ",$db->escapeString($group["name"]), $db->escapeString($group["description"]), $db->escapeString($group["backfill_target"]),$group["active"] , $minfiles, $group["id"] ));		
	}	

	//
	// update the list of newsgroups and return an array of messages.
	//
	function addBulk($groupList, $active = 1) 
	{
		$ret = array();
	
		if ($groupList == "")
		{
			$ret[] = "No group list provided.";
		}
		else
		{
			$db = new DB();
			$nntp = new Nntp;
			$nntp->doConnect();
			$groups = $nntp->getGroups();
			$nntp->doQuit();
				
			$regfilter = "/(" . str_replace (array ('.','*'), array ('\.','.*?'), $groupList) . ")$/";

			foreach($groups AS $group) 
			{
				if (preg_match ($regfilter, $group['group']) > 0)
				{
					$res = $db->queryOneRow(sprintf("SELECT ID FROM groups WHERE name = %s ", $db->escapeString($group['group'])));
					if($res) 
					{
						
						$db->query(sprintf("UPDATE groups SET active = %d where ID = %d", $active, $res["ID"]));
						$ret[] = array ('group' => $group['group'], 'msg' => 'Updated');
					} 
					else 
					{
						$desc = "";
						$db->queryInsert(sprintf("INSERT INTO groups (name, description, active) VALUES (%s, %s, %d)", $db->escapeString($group['group']), $db->escapeString($desc), $active));
						$ret[] = array ('group' => $group['group'], 'msg' => 'Created');
					}
				}
			}
		}
		return $ret;
	}

    public function updateGroupStatus($id, $status = 0)
    {
        $db = new DB();

        // don't need to escape anything when typecasting
        $id     = (int)$id;
        $status = (int)$status;

        $db->query("UPDATE groups SET active = '". $status ."' WHERE id = '". $id ."'");
        $status = ($status == 0) ? 'deactivated' : 'activated';
        return "Group $id has been $status.";
    }
}
?>

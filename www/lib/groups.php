<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/nntp.php");
require_once(WWW_DIR."/lib/site.php");

class Groups
{	
	/*
	Recommended list of binary newsgroups
	
	.alt.binaries.audio.warez
	.alt.binaries.dvd
	.alt.binaries.dvdr
	.alt.binaries.erotica.divx
	.alt.binaries.games.nintendods
	.alt.binaries.games.wii
	.alt.binaries.games.xbox360
	.alt.binaries.ipod.videos.tvshows
	.alt.binaries.mac
	.alt.binaries.movies.divx
	.alt.binaries.mpeg.video.music
	.alt.binaries.nintendo.ds
	.alt.binaries.sony.psp
	.alt.binaries.sounds.mp3.complete_cd
	.alt.binaries.sounds.mp3.dance
	.alt.binaries.sounds.mp3.goa-trance
	.alt.binaries.sounds.mp3.rap-hiphop.mixtapes
	.alt.binaries.tv.swedish
	.alt.binaries.tvseries
	.alt.binaries.warez.ibm-pc.0-day
	.alt.binaries.e-book
	
	alt.binaries.erotica
	alt.binaries.b4e
	alt.binaries.boneless
	alt.binaries.cd.image
	alt.binaries.hdtv.x264
	alt.binaries.multimedia
	alt.binaries.warez
	alt.binaries.x264
	alt.binaries.teevee
	alt.binaries.moovee
	alt.binaries.inner-sanctum	
	*/	

	public function getAll()
	{			
		$db = new DB();
		
		return $db->query("SELECT groups.*, COALESCE(rel.num, 0) AS num_releases
							FROM groups
							LEFT OUTER JOIN
							(
							SELECT groupID, COUNT(ID) AS num FROM releases group by groupID
							) rel ON rel.groupID = groups.ID");
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

	public function add($group)
	{			
		$db = new DB();
		
		return $db->queryInsert(sprintf("insert into groups (name, description, first_record, last_record, last_updated, active) values (%s, %s, %s, %s, null, %d) ",$db->escapeString($group["name"]), $db->escapeString($group["description"]), $db->escapeString($group["first_record"]), $db->escapeString($group["last_record"]), $group["active"]));		
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
	
	public function update($group)
	{			
		$db = new DB();
		
		return $db->query(sprintf("update groups set name=%s, description = %s, backfill_target = %s , active=%d where ID = %d ",$db->escapeString($group["name"]), $db->escapeString($group["description"]), $db->escapeString($group["backfill_target"]),$group["active"] , $group["id"] ));		
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


    /**
     * updateGroupStatus();
     *
     * @param id        group id
     * @param status    0 = deactive, 1 = activate
     * return string
     */
    public function updateGroupStatus($id, $status = 0)
    {
        $db = new DB();

        // don't need to escape anything when typecasting
        $id     = (int)$id;
        $status = (int)$status;

        $sql = "
            UPDATE
                `groups`
            SET
                active = '". $status ."'
            WHERE
                id = '". $id ."' LIMIT 1
        ";

        $db->query($sql);
        $status = ($status == 0) ? 'deactivated' : 'activated';
        return "Group $id has been $status.";
    }

}
?>

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
		
		return $db->query("SELECT groups.*, concat(cp.title,' > ',c.title) as category_name, COALESCE(rel.num, 0) AS num_releases
							FROM groups
							LEFT OUTER JOIN
							(
							SELECT groupID, COUNT(ID) AS num FROM releases group by groupID
							) rel ON rel.groupID = groups.ID
							left outer join category c on c.ID = groups.categoryID
							left outer join category cp on c.parentID = cp.ID");
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

	public function update($group)
	{			
		$db = new DB();
		return $db->query(sprintf("update groups set description = %s, active=%d where ID = %d ",$db->escapeString($group["description"]), $group["active"] , $group["id"] ));		
	}	

	//
	// update the list of newsgroups and return an array of messages.
	//
	function updateGroupList($blnUpdateCategory = true, $blnExpandGroupName = false) 
	{
		$ret = array();
		$s = new Sites();
		$site = $s->get(); // $site->groupfilter is regex of newsgroups to match on
	
		if ($site->groupfilter == "")
		{
			$ret[] = "No groupfilter specified in site table.";
		}
		else
		{
			$db = new DB();
			$category = new Category();
			$nntp = new Nntp;
			$nntp->doConnect();
			$groups = $nntp->getGroups();
			$nntp->doQuit();
				
			$regfilter = "/(" . str_replace (array ('.','*'), array ('\.','.*?'), $site->groupfilter) . ")".(!$blnExpandGroupName ? "$" : "")."/";

			foreach($groups AS $group) 
			{
				if (preg_match ($regfilter, $group['group']) > 0)
				{
					$res = $db->queryOneRow(sprintf("SELECT ID FROM groups WHERE name = %s ", $db->escapeString($group['group'])));
					if($res) 
					{
						$cat = "";
						if($blnUpdateCategory)
						{
							$cat = $category->determineCategory($group['group']);
							if ($cat == -1)
								$cat = " categoryID = null, ";
							else
								$cat = " categoryID = ".$cat.", ";
						}
						
						$db->query(sprintf("UPDATE groups SET %s description = %s where ID = %d", $cat, $db->escapeString((isset($group['desc']) ? $group['desc'] : "description")), $res["ID"]));
						$ret[] = array ('group' => $group['group'], 'msg' => 'Updated');
					} 
					else 
					{
						$desc = "";
						if (isset($group['desc']))
						{
							$desc = $group['desc'];
						}
						
						$cat = $category->determineCategory($group['group']);
						if ($cat == -1)
							$cat = "null";
							
						$db->queryInsert(sprintf("INSERT INTO groups (name, description, active, categoryID) VALUES (%s, %s, 1, %s)", $db->escapeString($group['group']), $db->escapeString($desc), $cat));
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

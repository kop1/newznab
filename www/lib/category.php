<?php

require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");

class Category
{	
	const CAT_GAME_NDS = 1010;
	const CAT_GAME_PSP = 1020;
	const CAT_GAME_WII = 1030;
	const CAT_GAME_XBOX = 1040;
	const CAT_GAME_XBOX360 = 1050;
	const CAT_GAME_WIIWARE = 1060;
	const CAT_GAME_XBOX360DLC = 1070;
	const CAT_GAME_PS3 = 1080;
	const CAT_MOVIE_DVD = 2010;
	const CAT_MOVIE_WMV_HD = 2020;
	const CAT_MOVIE_XVID = 2030;
	const CAT_MOVIE_X264 = 2040;
	const CAT_MOVIE_HDOTHER = 2050;
	const CAT_MUSIC_MP3 = 3010;
	const CAT_MUSIC_VIDEO = 3020;
	const CAT_MUSIC_AUDIOBOOK = 3030;
	const CAT_MUSIC_LOSSLESS = 3040;
	const CAT_PC_0DAY = 4010;
	const CAT_PC_ISO = 4020;
	const CAT_PC_MAC = 4030;
	const CAT_PC_PHONE = 4040;
	const CAT_PC_GAMES = 4050;
	const CAT_TV_DVD = 5010;
	const CAT_TV_FOREIGN = 5020;
	const CAT_TV_XVID = 5030;
	const CAT_TV_X264 = 5040;
	const CAT_TV_MOBILE = 5050;
	const CAT_TV_SPORT = 5060;
	const CAT_XXX_DVD = 6010;
	const CAT_XXX_WMV = 6020;
	const CAT_XXX_XVID = 6030;
	const CAT_XXX_X264 = 6040;
	const CAT_MISC = 7010;
	const CAT_MISC_EBOOK = 7020;
	
	const CAT_PARENT_GAME = 1000;
	const CAT_PARENT_MOVIE = 2000;
	const CAT_PARENT_MUSIC = 3000;
	const CAT_PARENT_PC = 4000;
	const CAT_PARENT_TV = 5000;
	const CAT_PARENT_XXX = 6000;
	const CAT_PARENT_MISC = 7000;

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;

	public function get($activeonly=false)
	{			
		$db = new DB();
		$act = "";
		if ($activeonly)
			$act = sprintf(" where c.status = %d ", Category::STATUS_ACTIVE ) ;
			
		return $db->query("select c.ID, concat(cp.title, ' > ',c.title) as title, cp.ID as parentID, c.status from category c inner join category cp on cp.ID = c.parentID ".$act);		
	}	
	
	public function isParent($cid)
	{			
		$db = new DB();
		$ret = $db->queryOneRow(sprintf("select * from category where ID = %d and parentID is null", $cid));
		if ($ret)
			return true;
		else
			return false;
	}		
	
	public function getFlat($activeonly=false)
	{			
		$db = new DB();
		$act = "";
		if ($activeonly)
			$act = sprintf(" where c.status = %d ", Category::STATUS_ACTIVE ) ;

		return $db->query("select c.*, (SELECT title FROM category WHERE ID=c.parentID) AS parentName from category c ".$act);		
	}		

	public function getChildren($cid)
	{			
		$db = new DB();
		return $db->query(sprintf("select c.* from category c where parentID = %d", $cid));		
	}		
	
	public function getById($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT c.ID, CONCAT(COALESCE(cp.title,'') , CASE WHEN cp.title IS NULL THEN '' ELSE ' > ' END , c.title) as title, c.status from category c left outer join category cp on cp.ID = c.parentID where c.ID = %d", $id));
	}	
	
	public function getByIds($ids)
	{			
		$db = new DB();
		return $db->query(sprintf("SELECT concat(cp.title, ' > ',c.title) as title from category c inner join category cp on cp.ID = c.parentID where c.ID in (%s)", implode(',', $ids)));
	}	

	public function update($id, $status, $desc)
	{			
		$db = new DB();
		return $db->query(sprintf("update category set status = %d, description = %s where ID = %d", $status, $db->escapeString($desc), $id));
	}	
	
	public function getForMenu()
	{			
		$db = new DB();
		$ret = array();
		$arr = $db->query(sprintf("select * from category where status = %d", Category::STATUS_ACTIVE));	
		foreach ($arr as $a)
			if ($a["parentID"] == "")
				$ret[] = $a;

		foreach ($ret as $key => $parent)
		{
			$subcatlist = array();
			$subcatnames = array();
			foreach ($arr as $a)
			{
				if ($a["parentID"] == $parent["ID"])
				{
					$subcatlist[] = $a;
					$subcatnames[] = $a["title"];
				}
			}
			
			if (count($subcatlist > 0))
			{
				array_multisort($subcatnames, SORT_ASC, $subcatlist);
				$ret[$key]["subcatlist"] = $subcatlist;
			}
		}
		return $ret;
	}	
	
	public function getForSelect($blnIncludeNoneSelected = true)
	{
		$categories = $this->get();
		$temp_array = array();
		
		if ($blnIncludeNoneSelected)
		{
			$temp_array[-1] = "--Please Select--";
		}
		
		foreach($categories as $category)
			$temp_array[$category["ID"]] = $category["title"];

		return $temp_array;
	}
	
	//
	// Work out which category is applicable for either a group or a binary.
	// returns -1 if no category is appropriate from the group name.
	// 
	function determineCategory($group, $releasename = "")
	{
		$ret = Category::CAT_MISC;

		//
		// Try and determine based on group
		//
		if (preg_match('/alt\.binaries\..*?audiobook.*?/i', $group)) 
			return Category::CAT_MUSIC_AUDIOBOOK;

		if (preg_match('/alt\.binaries\.sounds.*?|alt\.binaries\.mp3.*?/i', $group)) 
			return Category::CAT_MUSIC_MP3;
			
		if (preg_match('/alt\.binaries\.console.ps3/i', $group)) 
			return Category::CAT_GAME_PS3;
		
		if (preg_match('/alt\.binaries\.games\.xbox/i', $group)) {
			if (preg_match('/DLC.*?xbox360|xbox360.*?DLC/i', $releasename)) { return Category::CAT_GAME_XBOX360DLC; }
			if (preg_match('/xbox360/i', $releasename)) { return Category::CAT_GAME_XBOX360; }
			if (preg_match('/wmv/i', $releasename)) { return Category::CAT_MOVIE_WMV_HD; }
			return Category::CAT_GAME_XBOX;
		}

		if (preg_match('/alt\.binaries\.dvd.*?/i', $group)) {
			if ($this->isTv($releasename)) { return Category::CAT_TV_DVD; }
			return Category::CAT_MOVIE_DVD;	
		}
		
		if (preg_match('/alt\.binaries\.hdtv\.x264|alt\.binaries\.x264/i', $group)) {
			if ($this->isTv($releasename)) {
				if ($this->isForeign($releasename)) { return Category::CAT_TV_FOREIGN; }
				return Category::CAT_TV_X264;
			}
			if (preg_match('/x264/i', $releasename)) { return Category::CAT_MOVIE_X264; }
			return Category::CAT_MOVIE_HDOTHER;
		}
			
		if (preg_match('/alt\.binaries\.movies\.xvid|alt\.binaries\.movies\.divx/i', $group)) 
			return Category::CAT_MOVIE_XVID;	
			
		if (preg_match('/alt\.binaries\.e-book.*?/i', $group)) 
			return Category::CAT_MISC_EBOOK;

		if (preg_match('/alt\.binaries\.warez\.ibm\-pc\.0\-day|alt\.binaries\.warez/i', $group)) {
			if ($this->isMac($releasename)) { return Category::CAT_PC_MAC; }
			if (preg_match('/[\.\-_](IPHONE|ITOUCH|ANDROID|COREPDA|symbian|xscale|wm5|wm6)[\.\-_]/i', $releasename)) { return Category::CAT_PC_PHONE; }
			return Category::CAT_PC_0DAY;
		}
		
		if (preg_match('/alt\.binaries\.cd\.image|alt\.binaries\.audio\.warez/i', $group)) {
			if ($this->isMac($releasename)) { return Category::CAT_PC_MAC; }
			return Category::CAT_PC_ISO;		
		}
		
		if (preg_match('/alt\.binaries\.sony\.psp/i', $group)) 
			return Category::CAT_GAME_PSP;		
			
		if (preg_match('/alt\.binaries\.nintendo\.ds|alt\.binaries\.games\.nintendods/i', $group)) 
			return Category::CAT_GAME_NDS;		
			
		if (preg_match('/alt\.binaries\.mpeg\.video\.music/i', $group)) 
			return Category::CAT_MUSIC_VIDEO;				

		if (preg_match('/alt\.binaries\.mac/i', $group)) 
			return Category::CAT_PC_MAC;

		if (preg_match('/linux/i', $group)) 
			return Category::CAT_PC_ISO;

		if (preg_match('/alt\.binaries\.ipod\.videos\.tvshows/i', $group)) 
			return Category::CAT_TV_MOBILE;	

		if (preg_match('/alt\.binaries\.tv\.swedish/i', $group)) 
			return Category::CAT_TV_FOREIGN;									
			
		if (preg_match('/alt\.binaries\.erotica\.divx/i', $group)) 
			return Category::CAT_XXX_XVID;				

		if (preg_match('/alt\.binaries\.mma|alt\.binaries\.multimedia\.sports.*?/i', $group)) 
			return Category::CAT_TV_SPORT;		

		//
		// If nothing can be done, try on releasename
		//

		//
		// Tv 
		//
		if (preg_match('/alt\.binaries\.(teevee|multimedia|tv|tvseries)/i', $group)) {
			// Sports
			if (preg_match('/ESPN/', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/WWE\./', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/MMA\./', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/UFC\./', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/FIA\./', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/PGA\./', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/NFL\./', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/Rugby\./', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/TNA\./', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/EPL\.(\d{4})/', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/NASCAR\.(\d{4})/', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/NBA\.(\d{4})/', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/NHL\.(\d{4})/', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/NRL\.(\d{4})/', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/Superleague\.Formula/', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/FIFA\./', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/netball\.anz/', $releasename)) { return Category::CAT_TV_SPORT; }
			if (preg_match('/motogp/i', $releasename)) { return Category::CAT_TV_SPORT; }
			// Foreign
			if ($this->isForeign($releasename)) { return Category::CAT_TV_FOREIGN; }
			// x264
			if ($this->isHd($releasename)) { 
				if (preg_match('/x264/i', $releasename)) { return Category::CAT_TV_X264; }
				return Category::CAT_TV_X264; //HD-Other?
			}
			// DVDR
			if (preg_match('/dvdr[^ip]|dvd5|dvd9/i', $releasename)) { return Category::CAT_TV_DVD; }
			// Mobile
			if (preg_match('/itouch\-/i', $releasename)) { return Category::CAT_TV_MOBILE; }
			return Category::CAT_TV_XVID;
		}
		
		//S01E01
		//S01.E01
		//1x01
		//S1.D1
		if ($this->isTv($releasename)) {
			if ($this->isForeign($releasename)) { return Category::CAT_TV_FOREIGN; }
			if ($this->isHd($releasename)) { 
				if (preg_match('/x264/i', $releasename)) { return Category::CAT_TV_X264; }
				return Category::CAT_TV_X264; //HD-Other?
			}
			if (preg_match('/dvdr[^ip]|dvd5|dvd9/i', $releasename)) { return Category::CAT_TV_DVD; }
			return Category::CAT_TV_XVID;
		}
		
		if (preg_match('/\.S\d{2}\./i', $releasename)) {
			if ($this->isForeign($releasename)) { return Category::CAT_TV_FOREIGN; }
			if ($this->isHd($releasename)) { return Category::CAT_TV_X264; }
			return Category::CAT_TV_XVID;
		}
		
		//
		// XXX 
		//
		if (preg_match('/erotica/i', $group)) { 
			if (preg_match('/wmv|pack\-|mp4|f4v|flv|mov|h264|mpeg|isom|mkv|realmedia|multiformat|divx|(e\d{2,})|(\d{2}\.\d{2}\.\d{2})|uhq|(issue\.\d{2,})/i', $releasename)) { return Category::CAT_XXX_WMV; }
			if (preg_match('/x264/i', $releasename)) { return Category::CAT_XXX_X264; }
			if (preg_match('/xvid|dvdrip|bdrip|brrip|pornolation|swe6|nympho|detoxication|tesoro/i', $releasename)) { return Category::CAT_XXX_XVID; }
			if (preg_match('/dvdr[^ip]|dvd5|dvd9/i', $releasename)) { return Category::CAT_XXX_DVD; }
			return Category::CAT_XXX_XVID;
		}

		//
		// Movie 
		//		
		if (preg_match('/xvid|dvdscr|extrascene|dvdrip|r5/i', $releasename)) 
			return Category::CAT_MOVIE_XVID;

		if (preg_match('/dvdr|dvd9|dvd5/i', $releasename) && !preg_match('/dvdrip/i', $releasename)) 
			return Category::CAT_MOVIE_DVD;
		
		if ($this->isHd($releasename)) {
			if (preg_match('/x264/i', $releasename)) { return Category::CAT_MOVIE_X264; }
			return Category::CAT_MOVIE_HDOTHER;
		}
		
		if (preg_match('/bluray\-/i', $releasename)) 
			return Category::CAT_MOVIE_HDOTHER;
		
		if (preg_match('/wmv/i', $releasename)) 
			return Category::CAT_MOVIE_WMV_HD;
		
		//
		// Console 
		//	
		if (preg_match('/PSP\-/', $releasename)) 
			return Category::CAT_GAME_PSP;
		
		if (preg_match('/PS3\-/', $releasename)) 
			return Category::CAT_GAME_PS3;
		
		if (preg_match('/(WIIWARE|VC|CONSOLE)/i', $releasename)) 
			return Category::CAT_GAME_WIIWARE;
			
		if (preg_match('/WII/i', $releasename)) 
			return Category::CAT_GAME_WII;
		
		if (preg_match('/(DLC.*?xbox360|xbox360.*?DLC)/i', $releasename)) 
			return Category::CAT_GAME_XBOX360DLC;
			
		if (preg_match('/xbox360/i', $releasename)) 
			return Category::CAT_GAME_XBOX360;
		
		if (preg_match('/xbox/i', $releasename)) 
			return Category::CAT_GAME_XBOX;
		
		//
		// PC
		//
		if ($this->is0day($releasename))
			return Category::CAT_PC_0DAY;
		
		//
		// Phone
		//
		if (preg_match('/iPhone\.iPod\.Touch/i', $releasename)) 
			return Category::CAT_PC_PHONE;
		
		//
		// If no release name provided and the group wasnt determined, then return -1
		//
		if (($releasename == "") && ($ret == Category::CAT_MISC))
			$ret = -1;

		return $ret;
	}
	
	private function isTv($releasename) {
		return preg_match('/(S?(\d{1,2})\.?(?!x264)(E|X|D)(\d{1,3}))|(dsr|pdtv)[\.\-_]/i', $releasename);
		//alternative (S(\d{1,2})\.?(E|D)(\d{1,3}))|([\._ ]((?!x264)\d{1,2}X\d{1,2})[\._ ])|(dsr|pdtv)[\.\-_]
	}
	
	private function isForeign($releasename) {
		return preg_match('/(danish|flemish|dutch|nl\.?subbed|swedish|swesub|french|german|spanish)[\.\-]/i', $releasename);
	}
	
	private function is0day($releasename) {
		return preg_match('/[\.\-_ ](winnt|win9x|win2k|winxp|winnt2k2003serv|win9xnt|win9xme|winnt2kxp|win2kxp|win2kxp2k3|keygen|regged|keymaker|winall|win32|template|Patch|GAMEGUiDE|unix|irix|solaris|freebsd|hpux|linux|windows|multilingual)[\.\-_ ]/i', $releasename);
	}
	
	private function isMac($releasename) {
		return preg_match('/osx|os\.x|\.mac\./i', $releasename);
	}
	
	private function isHd($releasename) {
		return preg_match('/720p|1080p|1080i/i', $releasename);
	}
}
?>
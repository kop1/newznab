<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

class Category
{	
	const CAT_GAME_NDS = 8;
	const CAT_GAME_PSP = 9;
	const CAT_GAME_WII = 10;
	const CAT_GAME_XBOX = 11;
	const CAT_GAME_XBOX360 = 12;
	const CAT_MOVIE_DVD = 13;
	const CAT_MOVIE_WMV_HD = 14;
	const CAT_MOVIE_XVID = 15;
	const CAT_MOVIE_X264 = 16;
	const CAT_MUSIC_MP3 = 17;
	const CAT_MUSIC_VIDEO = 18;
	const CAT_PC_0DAY = 19;
	const CAT_PC_ISO = 20;
	const CAT_PC_MAC = 21;
	const CAT_TV_DVD = 22;
	const CAT_TV_H264 = 23;
	const CAT_TV_SWE = 24;
	const CAT_TV_XVID = 25;
	const CAT_TV_X264 = 26;
	const CAT_XXX_DVD = 27;
	const CAT_XXX_WMV = 28;
	const CAT_XXX_XVID = 29;
	const CAT_XXX_X264 = 30;
	const CAT_MISC = 31;
	const CAT_MUSIC_AUDIOBOOK = 32;
	const CAT_MISC_EBOOK = 33;
	const CAT_TV_IPOD = 34;
	const CAT_TV_SPORT = 35;

	public function get()
	{			
		$db = new DB();
		return $db->query("select c.ID, concat(cp.title, ' > ',c.title) as title from category c inner join category cp on cp.ID = c.parentID");		
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
	function determineCategory($group, $binaryname = "")
	{
		$ret = Category::CAT_MISC;

		//
		// Try and determine based on group
		//
		if (preg_match('/alt\.binaries\.mp3\.audiobooks/i', $group)) 
			return Category::CAT_MUSIC_AUDIOBOOK;

		if (preg_match('/alt\.binaries\.sounds*|alt\.binaries\.mp3*|alt\.binaries\.audio\.warez/i', $group)) 
			return Category::CAT_MUSIC_MP3;

		if (preg_match('/alt\.binaries\.games\.xbox360/i', $group)) 
			return Category::CAT_GAME_XBOX360;

		if (preg_match('/alt\.binaries\.dvd*/i', $group)) 
			return Category::CAT_MOVIE_DVD;	
			
		if (preg_match('/alt\.binaries\.movies\.xvid|alt\.binaries\.movies\.divx/i', $group)) 
			return Category::CAT_MOVIE_XVID;	
			
		if (preg_match('/alt\.binaries\.e-book*/i', $group)) 
			return Category::CAT_MISC_EBOOK;

		if (preg_match('/alt\.binaries\.warez\.ibm\-pc\.0\-day/i', $group)) 
			return Category::CAT_PC_0DAY;		
		
		if (preg_match('/alt\.binaries\.sony\.psp/i', $group)) 
			return Category::CAT_GAME_PSP;		
			
		if (preg_match('/alt\.binaries\.nintendo\.ds|alt\.binaries\.games\.nintendods/i', $group)) 
			return Category::CAT_GAME_NDS;		
			
		if (preg_match('/alt\.binaries\.mpeg\.video\.music/i', $group)) 
			return Category::CAT_MUSIC_VIDEO;				

		if (preg_match('/alt\.binaries\.mac/i', $group)) 
			return Category::CAT_PC_MAC;

		if (preg_match('/alt\.binaries\.ipod\.videos\.tvshows/i', $group)) 
			return Category::CAT_TV_IPOD;	

		if (preg_match('/alt\.binaries\.tv\.swedish/i', $group)) 
			return Category::CAT_TV_SWE;		

		if (preg_match('/alt\.binaries\.tvseries/i', $group)) 
			return Category::CAT_TV_XVID;				

		if (preg_match('/alt\.binaries\.tvseries/i', $group)) 
			return Category::CAT_TV_XVID;	

		if (preg_match('/alt\.binaries\.games\.wii/i', $group)) 
			return Category::CAT_GAME_WII;				
			
		if (preg_match('/alt\.binaries\.erotica\.divx/i', $group)) 
			return Category::CAT_XXX_XVID;				

		if (preg_match('/alt\.binaries\.mma/i', $group)) 
			return Category::CAT_TV_SPORT;		

		//
		// If nothing can be done, try on binaryname
		//

		//
		// Tv 
		//
		if (preg_match('/S([\d]+)E([\d]+)(.*?(720p).*?)/i', $binaryname))
			return Category::CAT_TV_X264;				

		if (preg_match('/S([\d]+)E([\d]+)(.*?(Xvid).*?)/i', $binaryname)) 
			return Category::CAT_TV_XVID;				

		if (preg_match('/S([\d]+)E([\d]+)/i', $binaryname)) 
			return Category::CAT_TV_XVID;				

		//
		// If no more tv scenarios found, and the group was teevee, then throw it in tvxvid
		//
		if (preg_match('/alt\.binaries\.teevee/i', $group)) 
			return Category::CAT_TV_XVID;				


		if (preg_match('/xvid|dvdscr|extrascene/i', $binaryname)) 
			return Category::CAT_MOVIE_XVID;

		if (preg_match('/dvdr|dvd9|dvd5/i', $binaryname) && !preg_match('/dvdrip/i', $binaryname)) 
			return Category::CAT_MOVIE_DVD;

		if (preg_match('/wmv/i', $binaryname)) 
			return Category::CAT_MOVIE_WMV_HD;

		if (preg_match('/PSP-/', $binaryname)) 
			return Category::CAT_GAME_PSP;

		if (preg_match('/WII-/i', $binaryname)) 
			return Category::CAT_GAME_WII;

		if (preg_match('/xbox/i', $binaryname)) 
			return Category::CAT_GAME_XBOX;
		
		if (preg_match('/xbox360/i', $binaryname)) 
			return Category::CAT_GAME_XBOX360;
		
		//
		// If no binary name provided and the group wasnt determined, then return -1
		//
		if (($binaryname == "") && ($ret == Category::CAT_MISC))
			$ret = -1;

		return $ret;
	}
}
?>
<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");

class Site 
{
	public $id = '';
	public $code = '';
	public $title = '';
	public $strapline = '';
	public $meta_title = '';
	public $meta_description = '';
	public $meta_keywords = '';
	public $footer = '';
	public $email = '';
	public $last_update = '';	
	public $google_analytics_acc = '';	
	public $google_adsense_menu = '';	
	public $google_adsense_sidepanel = '';	
	public $google_adsense_search = '';	
	public $groupfilter = '';	
	public $siteseed = '';
	public $tandc = '';
	public $registerstatus = '';
	public $style = '';
	public $dereferrer_link = '';
	public $nzbpath = '';
	public $binretentiondays = '';
	public $attemptgroupbindays = '';
	public $lookuptvrage = '';
	public $lookupimdb = '';
	public $lookupnfo = '';
}

class Sites
{	
	const REGISTER_STATUS_OPEN = 0;
	const REGISTER_STATUS_INVITE = 1;
	const REGISTER_STATUS_CLOSED = 2;

	public function version()
	{
		return "0.1.360";
	}
	
	public function update($form)
	{		
		$site = $this->row2Object($form);
		
		$this->data_update($site);
		
		return $site;
	}	

	public function get()
	{			
		$row = $this->data_get();
		if ($row === false)
			return false;
		
		return $this->row2Object($row);
	}	
	
	public function row2Object($row)
	{
		$obj = new Site();
		if (isset($row["id"]))
			$obj->id = $row["id"];
			
		$obj->code = $row["code"];
		$obj->groupfilter = $row["groupfilter"];
		$obj->title = $row["title"];
		$obj->strapline = $row["strapline"];
		$obj->meta_title = $row["metatitle"];
		$obj->meta_description = $row["metadescription"];
		$obj->meta_keywords = $row["metakeywords"];
		$obj->footer = $row["footer"];
		$obj->email = $row["email"];
		if (isset($row["lastupdate"]))
			$obj->last_update = $row["lastupdate"];
		$obj->google_analytics_acc = $row["google_analytics_acc"];
		$obj->google_adsense_menu = $row["google_adsense_menu"];
		$obj->google_adsense_sidepanel = $row["google_adsense_sidepanel"];
		$obj->google_adsense_search = $row["google_adsense_search"];
		if (isset($row["siteseed"]))
			$obj->siteseed = $row["siteseed"];
		$obj->tandc = $row["tandc"];
		$obj->registerstatus = $row["registerstatus"];
		$obj->style = $row["style"];
		$obj->dereferrer_link = $row["dereferrer_link"];
		$obj->version = $this->version();
		$obj->nzbpath = $row["nzbpath"];
		$obj->binretentiondays = $row["binretentiondays"];
		$obj->attemptgroupbindays = $row["attemptgroupbindays"];
		$obj->lookuptvrage = $row["lookuptvrage"];
		$obj->lookupimdb = $row["lookupimdb"];
		$obj->lookupnfo = $row["lookupnfo"];

		return $obj;
	}

	public function data_update($site)
	{		
		$db = new DB();
		
		if (substr($site->nzbpath, strlen($site->nzbpath) - 1) != '/')
		{
			$site->nzbpath = $site->nzbpath."/";
		}
		
		return $db->query(sprintf("update site set	code = %s , 	title = %s , 	strapline = %s , 	metatitle = %s , 	metadescription = %s , 	metakeywords = %s , 	footer = %s ,	email = %s , 	lastupdate = now(), google_adsense_menu = %s, google_adsense_search = %s, google_adsense_sidepanel = %s, google_analytics_acc = %s, groupfilter = %s, tandc=%s, registerstatus=%d, style=%s, dereferrer_link=%s, nzbpath=%s, binretentiondays=%d, attemptgroupbindays=%d, lookuptvrage=%d, lookupimdb=%d, lookupnfo=%d", $db->escapeString($site->code), $db->escapeString($site->title), $db->escapeString($site->strapline), $db->escapeString($site->meta_title), $db->escapeString($site->meta_description), $db->escapeString($site->meta_keywords), $db->escapeString($site->footer), $db->escapeString($site->email), $db->escapeString($site->google_adsense_menu), $db->escapeString($site->google_adsense_search), $db->escapeString($site->google_adsense_sidepanel), $db->escapeString($site->google_analytics_acc), $db->escapeString($site->groupfilter), $db->escapeString($site->tandc), $site->registerstatus, $db->escapeString($site->style), $db->escapeString($site->dereferrer_link), $db->escapeString($site->nzbpath), $site->binretentiondays, $site->attemptgroupbindays, $site->lookuptvrage, $site->lookupimdb, $site->lookupnfo  ));
	}

	public function data_get()
	{			
		$db = new DB();
		return $db->queryOneRow("select * from site limit 1");		
	}	
}
?>

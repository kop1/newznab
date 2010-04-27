<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

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
	public $root = '';
	public $last_update = '';	
	public $google_analytics_acc = '';	
	public $google_adsense_menu = '';	
	public $google_adsense_sidepanel = '';	
	public $google_adsense_search = '';	
}

class Sites
{	
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
		if (isset($row["ID"]))
			$obj->id = $row["ID"];
			
		$obj->code = $row["CODE"];
		$obj->title = $row["TITLE"];
		$obj->strapline = $row["STRAPLINE"];
		$obj->meta_title = $row["METATITLE"];
		$obj->meta_description = $row["METADESCRIPTION"];
		$obj->meta_keywords = $row["METAKEYWORDS"];
		$obj->footer = $row["FOOTER"];
		$obj->email = $row["EMAIL"];
		$obj->root = $row["ROOT"];
		if (isset($row["LASTUPDATE"]))
			$obj->last_update = $row["LASTUPDATE"];
		$obj->google_analytics_acc = $row["GOOGLE_ANALYTICS_ACC"];
		$obj->google_adsense_menu = $row["GOOGLE_ADSENSE_MENU"];
		$obj->google_adsense_sidepanel = $row["GOOGLE_ADSENSE_SIDEPANEL"];
		$obj->google_adsense_search = $row["GOOGLE_ADSENSE_SEARCH"];
			
		return $obj;
	}

	public function data_update()
	{		
		$db = new DB();
		return $db->query(sprintf("UPDATE sites SET	CODE = %s , 	TITLE = %s , 	STRAPLINE = %s , 	METATITLE = %s , 	METADESCRIPTION = %s , 	METAKEYWORDS = %s , 	FOOTER = %s ,	EMAIL = %s , 	ROOT = %s , 	LASTUPDATE = now(), GOOGLE_ADSENSE_MENU = %s, GOOGLE_ADSENSE_SEARCH = %s, GOOGLE_ADSENSE_SIDEPANEL = %s, GOOGLE_ANALYTICS_ACC = %s	WHERE	ID = %d", $db->escapeString($site->code), $db->escapeString($site->title), $db->escapeString($site->strapline), $db->escapeString($site->meta_title), $db->escapeString($site->meta_description), $db->escapeString($site->meta_keywords), $db->escapeString($site->footer), $db->escapeString($site->term_singular), $db->escapeString($site->term_plural), $db->escapeString($site->email), $db->escapeString($site->root), $db->escapeString($site->google_adsense_menu), $db->escapeString($site->google_adsense_search), $db->escapeString($site->google_adsense_sidepanel), $db->escapeString($site->google_analytics_acc), $site->id ));
	}

	public function data_get()
	{			
		$db = new DB();
		return $db->queryOneRow("SELECT * FROM SITE ");		
	}	
}
?>
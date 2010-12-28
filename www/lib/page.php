<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/basepage.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/content.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/menu.php");

class Page extends BasePage
{    
	public $site = "";
	
	function Page()
	{	
		parent::BasePage();

		// set site variable
		$s = new Sites();
		$this->site = $s->get();
		$this->smarty->assign('site',$this->site);

		$role=Users::ROLE_GUEST;
		if ($this->userdata != null)
			$role = $this->userdata["role"];

		$content = new Contents();
		$menu = new Menu();
		$this->smarty->assign('menulist',$menu->get($role, $this->serverurl));
		$this->smarty->assign('usefulcontentlist',$content->getForMenuByTypeAndRole(Contents::TYPEUSEFUL, $role));
		$this->smarty->assign('articlecontentlist',$content->getForMenuByTypeAndRole(Contents::TYPEARTICLE, $role));
		
		$this->smarty->assign('main_menu',$this->smarty->fetch('mainmenu.tpl'));		
		$this->smarty->assign('useful_menu',$this->smarty->fetch('usefullinksmenu.tpl'));		
		$this->smarty->assign('article_menu',$this->smarty->fetch('articlesmenu.tpl'));	

		$category = new Category();
		if ($this->userdata != null)
			$parentcatlist = $category->getForMenu($this->userdata["categoryexclusions"]);
		else
			$parentcatlist = $category->getForMenu();

		$this->smarty->assign('parentcatlist',$parentcatlist);
		if (isset($_REQUEST["search"]))
			$this->smarty->assign('header_menu_search',$_REQUEST["search"]);
		if (isset($_REQUEST["t"]))
			$this->smarty->assign('header_menu_cat',$_REQUEST["t"]);
		$header_menu = $this->smarty->fetch('headermenu.tpl');
		$this->smarty->assign('header_menu',$header_menu);	
	
	}	
	
	public function render() 
	{			
		$this->smarty->assign('page',$this);
		$this->page_template = "basepage.tpl";				
		
		parent::render();
	}
}

?>

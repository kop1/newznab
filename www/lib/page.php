<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/basepage.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/content.php");
require_once(WWW_DIR."/lib/category.php");

class Page extends BasePage
{    
	function Page()
	{	
		parent::BasePage();
		
		// set ad variables
		$this->smarty->assign('google_adsense_acc',GOOGLE_ADSENSE_ACC);

		// set site variable
		$s = new Sites();
		$this->site = $s->get();
		$this->smarty->assign('site',$this->site);

		$content = new Contents();
		$this->smarty->assign('usefulcontentlist',$content->getForMenuByType(Contents::TYPEUSEFUL));
		$usefullinks_menu = $this->smarty->fetch('usefullinksmenu.tpl');
		$this->smarty->assign('useful_menu',$usefullinks_menu);		

		$this->smarty->assign('articlecontentlist',$content->getForMenuByType(Contents::TYPEARTICLE));
		$article_menu = $this->smarty->fetch('articlesmenu.tpl');
		$this->smarty->assign('article_menu',$article_menu);	

		$category = new Category();
		$parentcatlist = $category->getForMenu();
		$this->smarty->assign('parentcatlist',$parentcatlist);
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

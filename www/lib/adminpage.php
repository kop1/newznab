<?php

require_once("config.php");
require_once(WWW_DIR."/lib/framework/basepage.php");
require_once(WWW_DIR."/lib/users.php");

class AdminPage extends BasePage
{    
	function AdminPage()
	{	
		$this->template_dir = 'admin';
		parent::BasePage();
		
		$users = new Users();
		if (!$users->isLoggedIn() || !isset($this->userdata["role"]) || $this->userdata["role"] != Users::ROLE_ADMIN)
			$this->show403(true);

		// set site variable
		$s = new Sites();
		$this->site = $s->get();
		$this->smarty->assign('site',$this->site);
			
	}	
	
	public function render() 
	{			
		$this->smarty->assign('page',$this);
		
		$admin_menu = $this->smarty->fetch('adminmenu.tpl');
		$this->smarty->assign('admin_menu',$admin_menu);
		
		$this->page_template = "baseadminpage.tpl";				
		
		parent::render();
	}
}

?>

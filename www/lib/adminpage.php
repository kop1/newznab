<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/basepage.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");

class AdminPage extends BasePage
{    
	function AdminPage()
	{	
		parent::BasePage();
		
		$users = new Users();
		if (!$users->isLoggedIn() || !isset($this->userdata["role"]) || $this->userdata["role"] != Users::ROLE_ADMIN)
			$this->show403();
	}	
	
	public function render() 
	{			
		$this->smarty->assign('page',$this);
		
		$admin_menu = $this->smarty->fetch('admin/adminmenu.tpl');
		$this->smarty->assign('admin_menu',$admin_menu);
		
		$this->page_template = "admin/baseadminpage.tpl";				
		
		parent::render();
	}
}

?>

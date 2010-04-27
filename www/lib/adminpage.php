<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/basepage.php");

class AdminPage extends BasePage
{    
	function AdminPage()
	{	
		parent::BasePage();
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

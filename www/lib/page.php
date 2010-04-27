<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/basepage.php");

class Page extends BasePage
{    
	function Page()
	{	
		parent::BasePage();
		
		// set ad variables
		$this->smarty->assign('google_adsense_acc',GOOGLE_ADSENSE_ACC);
	}	
	
    public function render() 
	{			
		$this->smarty->assign('page',$this);
		$this->page_template = "basepage.tpl";				
		
		parent::render();
	}
}

?>

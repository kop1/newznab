<?php

require_once($_SERVER['DOCUMENT_ROOT']."/config.php");
require_once(SMARTY_DIR.'Smarty.class.php');

class BasePage 
{
	public $title = '';
	public $content = '';
	public $head = '';
	public $meta_keywords = '';
	public $meta_title = '';
	public $meta_description = '';    
	public $page_template = ''; 
	public $smarty = '';
		
	function BasePage()
	{			
		if((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || ini_get('magic_quotes_sybase'))
		{
            foreach($_GET as $k => $v) $_GET[$k] = stripslashes($v);
            foreach($_POST as $k => $v) $_POST[$k] = stripslashes($v);
            foreach($_COOKIE as $k => $v) $_COOKIE[$k] = stripslashes($v);
        }	
		
		$this->smarty = new Smarty();
		
        $this->smarty->template_dir = WWW_DIR.'templates/';
        $this->smarty->compile_dir  = SMARTY_DIR.'templates_c/';
        $this->smarty->config_dir   = SMARTY_DIR.'configs/';
        $this->smarty->cache_dir    = SMARTY_DIR.'cache/';				
		
		$this->smarty->assign('page',$this);
	}    
	
	public function addToHead($headcontent) 
	{			
		$this->head = $this->head."\n".$headcontent;
	}	
	
	public function render() 
	{
		$this->smarty->display($this->page_template);
	}
	
	public function isPostBack()
	{
		return (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST");	
	}
	
	public function buttonWasPressed($buttonName)
	{
		return isset($_POST[$buttonName]);
	}
}
?>

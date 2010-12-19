<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/menu.php");

$page = new AdminPage();
$menu = new Menu();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
		
	    if ($_POST["id"] == "")
    	{
				$menu->add($_POST);
			}
			else
			{
				$ret = $menu->update($_POST);
			}	
		
			header("Location:".WWW_TOP."/menu-list.php");
	        break;
    case 'view':
    default:

		if (isset($_GET["id"]))
		{
			$page->title = "Menu Edit";
			$id = $_GET["id"];
			
			$menurow = $menu->getByID($id);

			$page->smarty->assign('menu', $menurow);	
		}

      break;   
}

$page->content = $page->smarty->fetch('menu-edit.tpl');
$page->render();

?>

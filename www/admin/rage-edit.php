<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/tvrage.php");

$page = new AdminPage();
$tvrage = new TvRage();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
		if ($_POST["id"] == "")
			$tvrage->add($_POST["rageID"], $_POST["releasetitle"], $_POST["description"]);
		else
			$tvrage->update($_POST["id"], $_POST["rageID"], $_POST["releasetitle"], $_POST["description"]);
			
		header("Location:".WWW_TOP."/rage-list.php");
        break;
    case 'view':
    default:

			if (isset($_GET["id"]))
			{
				$page->title = "Tv Rage Edit";
				$id = $_GET["id"];
				
				$rage = $tvrage->getByID($id);
				$page->smarty->assign('rage', $rage);	
			}
	
	   	break;   
}

$page->content = $page->smarty->fetch('admin/rage-edit.tpl');
$page->render();

?>

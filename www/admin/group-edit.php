<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/groups.php");

$page = new AdminPage();
$groups = new Groups();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
				$groups->update($_POST);
				header("Location:".WWW_TOP."/group-list.php");

        break;
    case 'view':
    default:

			if (isset($_GET["id"]))
			{
				$page->title = "Group Edit";
				$id = $_GET["id"];
				
				$group = $groups->getByID($id);
				$page->smarty->assign('group', $group);	
			}

      break;   
}

$page->smarty->assign('yesno_ids', array(1,0));
$page->smarty->assign('yesno_names', array( 'Yes', 'No'));

$page->content = $page->smarty->fetch('admin/group-edit.tpl');
$page->render();

?>

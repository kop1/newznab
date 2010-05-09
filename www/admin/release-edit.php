<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");

$page = new AdminPage();
$releases = new Releases();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
				$releases->update($_POST);
				header("Location:release-list.php");

        break;
    case 'view':
    default:

			if (isset($_GET["id"]))
			{
				$page->title = "Release Edit";
				$id = $_GET["id"];
				
				$release = $releases->getByID($id);

				$page->smarty->assign('release', $release);	
			}

      break;   
}

$page->smarty->assign('yesno_ids', array(1,0));
$page->smarty->assign('yesno_names', array( 'Yes', 'No'));

$page->content = $page->smarty->fetch('admin/release-edit.tpl');
$page->render();

?>

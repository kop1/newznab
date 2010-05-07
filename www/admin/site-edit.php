<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/site.php");

$page = new AdminPage();
$sites = new Sites();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
		$site = $sites->update($_POST);
		$returnid = $site->id;
		header("Location:site-edit.php?id=".$returnid);

        break;
    case 'view':
    default:

			$page->title = "Site Edit";
			$site = $sites->get();
			$page->smarty->assign('fsite', $site);	

      break;   
}

$page->content = $page->smarty->fetch('admin/site-edit.tpl');
$page->render();

?>

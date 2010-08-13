<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/site.php");

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
		header("Location:".WWW_TOP."/site-edit.php?id=".$returnid);

        break;
    case 'view':
    default:

			$page->title = "Site Edit";
			$site = $sites->get();
			$page->smarty->assign('fsite', $site);	

      break;   
}

$page->smarty->assign('yesno_ids', array(1,0));
$page->smarty->assign('yesno_names', array( 'Yes', 'No'));
$page->smarty->assign('newgroupscan_names', array('Days','Posts'));
$page->smarty->assign('registerstatus_ids', array(Sites::REGISTER_STATUS_OPEN, Sites::REGISTER_STATUS_INVITE, Sites::REGISTER_STATUS_CLOSED));
$page->smarty->assign('registerstatus_names', array( 'Open', 'Invite', 'Closed'));

$themelist = array();
$themelist[] = "/";
$themes = scandir(WWW_DIR."/theme");
foreach ($themes as $theme)
	if (strpos($theme, ".") === false && is_dir(WWW_DIR."/theme/".$theme))
		$themelist[] = $theme;

$page->smarty->assign('themelist', $themelist);

$page->content = $page->smarty->fetch('admin/site-edit.tpl');
$page->render();

?>

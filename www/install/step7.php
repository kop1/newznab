<?php
require_once('../config.php');
require_once('../lib/installpage.php');
require_once('../lib/install.php');

$page = new Installpage();
$page->title = "Install Sample Data";

$cfg = new Install();

if (!$cfg->isInitialized()) {
	header("Location: index.php");
	die();
}

$cfg = $cfg->getSession();

if  ($page->isPostBack()) {
	$cfg->doCheck = true;
	
	require_once($cfg->WWW_DIR.'/lib/groups.php');
	require_once($cfg->WWW_DIR.'/lib/nzb.php');
	require_once($cfg->WWW_DIR.'/lib/releases.php');
	
	$groups = new Groups();
	$nzb = new NZB();
	$releases = new Releases();

	$group = array();
	$group['name'] = 'alt.binaries.teevee';
	$group['description'] = '';
	$group['first_record'] = 0;
	$group['last_record'] = 0;
	$group['active'] = 1;
	$group['maxmsgs'] = 20000;
	$groups->add($group);

	ob_start();
	$nzb->updateAllGroups();
	$proccount = $releases->processReleases(true);
	$results = ob_get_contents();
	ob_end_clean();

	$page->smarty->assign('proccount', $proccount);
	//$page->smarty->assign('output', $output);
	
	if (!$cfg->error) {
		$cfg->setSession();
	}
}

$page->smarty->assign('cfg', $cfg);

$page->smarty->assign('page', $page);

$page->content = $page->smarty->fetch('step7.tpl');
$page->render();

?>
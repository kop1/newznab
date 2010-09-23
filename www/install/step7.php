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
	
	require_once($cfg->WWW_DIR.'/lib/binaries.php');
	require_once($cfg->WWW_DIR.'/lib/releases.php');
	
	$binaries = new Binaries();
	$releases = new Releases();
	
	//activate teevee
	$db = new DB();
	$db->query("INSERT INTO groups (name, description, active) VALUES ('alt.binaries.teevee', '', 1) ON DUPLICATE KEY UPDATE active = 1");
	
	ob_start();
	$binaries->updateAllGroups();
	$proccount = $releases->processReleases(true);
	$output = ob_get_contents();
	ob_end_clean();

	$page->smarty->assign('proccount', $proccount);
	$page->smarty->assign('output', str_replace('<BR>', '', $output));
	
	if (!$cfg->error) {
		$cfg->setSession();
	}
}

$page->smarty->assign('cfg', $cfg);

$page->smarty->assign('page', $page);

$page->content = $page->smarty->fetch('step7.tpl');
$page->render();

?>

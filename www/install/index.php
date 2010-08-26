<?php
require_once('../lib/installpage.php');
require_once('../lib/install.php');

$page = new Installpage();
$page->title = "Welcome";

$cfg = new Install();
if ($cfg->isLocked()) {
	$cfg->error = true;
}

if (!$cfg->error) {
	$cfg->setSession();
}

$page->smarty->assign('cfg', $cfg);
$page->smarty->assign('page', $page);
$page->content = $page->smarty->fetch('welcome.tpl');
$page->render();
?>
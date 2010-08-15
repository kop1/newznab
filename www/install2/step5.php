<?php
require_once './lib/installpage.php';
require_once('./lib/config.php');

$page = new Installpage();
$page->title = "Save settings";

$cfg = new Config();

if (!$cfg->isInitialized()) {
	header("Location: index.php");
	die();
}

$cfg = $cfg->getSession();

if ($cfg->saveConfig() === false) {
	$cfg->error = true;
}

$page->smarty->assign('cfg', $cfg);

$page->smarty->assign('page', $page);

$page->content = $page->smarty->fetch('step5.tpl');
$page->render();


?>
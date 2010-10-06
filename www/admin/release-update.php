<?php

require_once("config.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/adminpage.php");

$page = new AdminPage();

$releases = new Releases;
ob_start();
$proccount = $releases->processReleases(true);
$output = ob_get_contents();
ob_end_clean();

$page->title = "Updating Releases";
$page->smarty->assign('proccount', $proccount);
$page->smarty->assign('output', $output);
$page->content = $page->smarty->fetch('release-update.tpl');
$page->render();

?>

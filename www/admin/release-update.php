<?php

require_once("config.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/adminpage.php");

$page = new AdminPage();

$releases = new Releases;
$proccount = $releases->processReleases();

$page->title = "Updating Releases";
$page->smarty->assign('proccount', $proccount);
$page->content = $page->smarty->fetch('admin/release-update.tpl');
$page->render();

?>

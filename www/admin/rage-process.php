<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/releases.php");

$page = new AdminPage();

$releases = new Releases();
$num = $releases->processTvSeriesData();

$page->smarty->assign('numtv',$num);	

$page->title = "Process Tv Manually";
$page->content = $page->smarty->fetch('admin/rage-process.tpl');
$page->render();

?>

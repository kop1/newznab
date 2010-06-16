<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/releases.php");

$page = new AdminPage();

$releases = new Releases();
$num = $releases->processTvSeriesData(false, ($page->site->lookuptvrage=="1"));

$page->smarty->assign('numtv',$num);	

$page->title = "Process Tv Manually";
$page->content = $page->smarty->fetch('admin/rage-process.tpl');
$page->render();

?>

<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/releases.php");

$page = new AdminPage();
$basepg = new Page();

$releases = new Releases();
$num = $releases->processTvSeriesData(false, ($basepg->site->lookuptvrage=="1"));

$page->smarty->assign('numtv',$num);	

$page->title = "Process Tv Manually";
$page->content = $page->smarty->fetch('rage-process.tpl');
$page->render();

?>

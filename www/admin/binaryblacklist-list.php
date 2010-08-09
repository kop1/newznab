<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/binaries.php");

$page = new AdminPage();

$bin = new Binaries();

$page->title = "Binary Blacklist List";

$binlist = $bin->getBlacklist(false);
$page->smarty->assign('binlist', $binlist);	

$page->content = $page->smarty->fetch('admin/binaryblacklist-list.tpl');
$page->render();

?>

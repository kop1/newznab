<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/releaseregex.php");

$page = new AdminPage();

$reg = new ReleaseRegex();

$page->title = "Release Regex List";

$regexlist = $reg->get(false);
$page->smarty->assign('regexlist', $regexlist);	

$page->content = $page->smarty->fetch('regex-list.tpl');
$page->render();

?>

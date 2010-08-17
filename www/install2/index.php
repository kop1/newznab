<?php
require_once('../lib/installpage.php');

$page = new Installpage();
$page->title = "Welcome";

$page->smarty->assign('page', $page);
$page->content = $page->smarty->fetch('welcome.tpl');
$page->render();
?>
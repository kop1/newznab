<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");

$page = new AdminPage();

$page->title = "Admin Hangout";
$page->content = $page->smarty->fetch('admin/index.tpl');
$page->render();

?>

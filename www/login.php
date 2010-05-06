<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");

$page = new Page();

$page->meta_title = "Login";
$page->meta_keywords = "Login";
$page->meta_description = "Login";

$page->content = $page->smarty->fetch('login.tpl');
$page->render();

?>
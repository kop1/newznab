<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/content.php");

$page = new Page();

$contentid = $_GET["id"];

$contents = new Contents();

if ($contentid == 0)
{
$content = $contents->getIndex();
}
else
{
$content = $contents->getByID($contentid);
}

$page->smarty->assign('content',$content);	
$page->meta_title = $content->title;
$page->meta_keywords = $content->metakeywords;
$page->meta_description = $content->metadescription;

$page->content = $page->smarty->fetch('content.tpl');
$page->render();

?>

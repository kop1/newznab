<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/releaseregex.php");

$page           = new AdminPage();
$page->title    = "Submit your regex expressions to the Official Database";

// Logic
$regex          = new ReleaseRegex();
$regexList      = $regex->get(false, -1, true);
$regexSerialize = serialize($regexList);
$regexFilename  = 'releaseregex-' . time();

// Assigns
$page->smarty->assign('regex_filename', $regexFilename);
$page->smarty->assign('regex_contents', $regexList);

// Render
$page->content  = $page->smarty->fetch('regex-submit.tpl');
$page->render();

# vim:ft=php ts=2


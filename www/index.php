<?php
if(is_file("config.php")) {
	require_once("config.php");
} else {
	if(is_dir("install")) {
		header("location: install");
		exit();
	} 
}

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");

$page = new Page;
$users = new Users;

switch($page->page) {
	case 'content':
	case 'sendtosab':
	case 'browse':
	case 'browsegroup':
	case 'getnzb':
	case 'search':
	case 'searchraw':
	case 'rss':
	case 'api':
	case 'apihelp':
	case 'movies':
	case 'movie':
	case 'series':
	case 'music':
	case 'musicmodal':
	case 'console':
	case 'nfo':
	case 'details':
	case 'forum':
	case 'forumpost':
	case 'filelist':
	case 'getimage':
	case 'cart':
	case 'queue':
	case 'queuedata':
	case 'profile':
	case 'profileedit':
	case 'login':
	case 'logout':
	case 'register':
	case 'forgottenpassword':
	case 'sitemap':
	case 'contact-us':
	case 'terms-and-conditions':
	case 'ajax_profile':
	case 'ajax_release-admin':
		include(WWW_DIR.'pages/'.$page->page.'.php');
	break;
	default:
		$page->show404();
	break;
}
?>
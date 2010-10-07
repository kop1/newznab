<?php
require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");

$users = new Users;
$page = new Page;

if (!$users->isLoggedIn())
	$page->show403();

if (isset($_GET['action']) && $_GET['action'] == "1" && isset($_GET['emailto']))
{
	$emailto = $_GET['emailto'];
	$ret = $users->sendInvite($page->site->title, $page->site->email, $page->serverurl, $users->currentUserId(), $emailto);
	if (!$ret)
		print "Invite not sent.";
	else
		print "Invite sent. Alternatively paste them following link to register - ".$ret;
}
//
// Send an nzb to sab.
//
else if (isset($_GET['action']) && $_GET['action'] == "2" && isset($_GET['sab']))
{
	$rh = fopen($_GET['sab'], 'r');
}else
{
	print "Invite not sent.";
}

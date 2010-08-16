<?php
require_once './lib/installpage.php';
require_once('./lib/config.php');

$page = new Installpage();
$page->title = "News server setup";

$cfg = new Config();

if (!$cfg->isInitialized()) {
	header("Location: index.php");
	die();
}

$cfg = $cfg->getSession();

if  ($page->isPostBack()) {
	$cfg->doCheck = true;
	
	$cfg->NNTP_SERVER = trim($_POST['server']);
	$cfg->NNTP_USERNAME = trim($_POST['user']);
	$cfg->NNTP_PASSWORD = trim($_POST['pass']);
	$cfg->NNTP_PORT = (trim($_POST['port']) == '') ? 119 : trim($_POST['port']);

	include('Net/NNTP/Client.php');
	$test = new Net_NNTP_Client();
	$cfg->nntpCheck = $test->connect($cfg->NNTP_SERVER, false, $cfg->NNTP_PORT);
	if(PEAR::isError($cfg->nntpCheck)){
		$cfg->error = true;	
	} else {
		$cfg->nntpCheck = $test->authenticate($cfg->NNTP_USERNAME, $cfg->NNTP_PASSWORD);
		if(PEAR::isError($cfg->nntpCheck)){
			$cfg->error = true;	
		}
	}
	
	if (!$cfg->error) {
		$cfg->setSession();
	}
}

$page->smarty->assign('cfg', $cfg);

$page->smarty->assign('page', $page);

$page->content = $page->smarty->fetch('step3.tpl');
$page->render();

?>
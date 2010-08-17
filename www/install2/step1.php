<?php
require_once('../lib/installpage.php');
require_once('../lib/install.php');

$page = new Installpage();
$page->title = "Preflight Checklist";

$cfg = new Install();

if (!$cfg->isInitialized()) {
	header("Location: index.php");
	die();
}

$cfg = $cfg->getSession();

// Start checks
$cfg->sha1Check = function_exists('sha1');
if ($cfg->sha1Check === false) { $cfg->error = true; }

$cfg->mysqlCheck = function_exists('mysql_connect');
if ($cfg->mysqlCheck === false) { $cfg->error = true; }

$cfg->cacheCheck = is_writable($cfg->SMARTY_DIR.'/templates_c');
if ($cfg->cacheCheck === false) { $cfg->error = true; }

$cfg->coversCheck = is_writable($cfg->WWW_DIR.'/images/covers');
if ($cfg->coversCheck === false) { $cfg->error = true; }

$cfg->configCheck = is_writable($cfg->WWW_DIR.'/config.php');
if($cfg->configCheck === false) {
	$cfg->configCheck = is_file($cfg->WWW_DIR.'/config.php');
	if($cfg->configCheck === true) {
		$cfg->configCheck = false;
		$cfg->error = true;
	} else {
		$cfg->configCheck = is_writable($cfg->WWW_DIR);
		if($cfg->configCheck === false) {
			$cfg->error = true;
		}
	}
}

$cfg->lockCheck = is_writable($cfg->INSTALL_DIR.'/install.lock');
if ($cfg->lockCheck === false) { 
	$cfg->lockCheck = is_file($cfg->INSTALL_DIR.'/install.lock');
	if($cfg->lockCheck === true) {
		$cfg->lockCheck = false;
		$cfg->error = true;
	} else {
		$cfg->lockCheck = is_writable($cfg->INSTALL_DIR);
		if($cfg->lockCheck === false) {
			$cfg->error = true;
		}
	}
}

$cfg->pearCheck = @include('Net/NNTP/client.php');
if ($cfg->pearCheck === false) { $cfg->error = true; }

$cfg->schemaCheck = is_readable($cfg->DB_DIR.'/schema.sql');
if ($cfg->schemaCheck === false) { $cfg->error = true; }

if (file_exists($cfg->WWW_DIR.'/config.php') && is_readable($cfg->WWW_DIR.'/config.php')) {
	$tmpCfg = file_get_contents($cfg->WWW_DIR.'/config.php');
	$cfg->setConfig($tmpCfg);
}

if (!$cfg->error)
	$cfg->setSession();

$page->smarty->assign('cfg', $cfg);

$page->smarty->assign('page', $page);

$page->content = $page->smarty->fetch('step1.tpl');
$page->render();

?>
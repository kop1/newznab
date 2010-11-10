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

$cfg->gdCheck = function_exists('imagecreatetruecolor');

$cfg->cacheCheck = is_writable($cfg->SMARTY_DIR.'/templates_c');
if ($cfg->cacheCheck === false) { $cfg->error = true; }

$cfg->coversCheck = is_writable($cfg->WWW_DIR.'/views/images/covers');
if ($cfg->coversCheck === false) { $cfg->error = true; }

$cfg->tempCheck = is_writable($cfg->WWW_DIR.'/temp');
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

$cfg->pearCheck = @include('System.php');
$cfg->pearCheck = class_exists('System');
if (!$cfg->pearCheck) { $cfg->error = true; }

$cfg->schemaCheck = is_readable($cfg->DB_DIR.'/schema.sql');
if ($cfg->schemaCheck === false) { $cfg->error = true; }

// Dont set error = true for these as we only want to display a warning
$cfg->timelimitCheck = (ini_get('max_execution_time') >= 60) ? true : false;
$cfg->memlimitCheck = (ini_get('memory_limit') >= 256) ? true : false;
$cfg->opensslCheck = !extension_loaded("opensssl");

$cfg->rewriteCheck = in_array("mod_rewrite", apache_get_modules());
if (!$cfg->rewriteCheck) { $cfg->error = true; }

//Load previous config.php
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

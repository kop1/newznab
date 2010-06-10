<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/framework/db.php");
$db = new DB();

if (empty($argc))
	$page = new AdminPage();

$rel = new Releases();

if (!empty($argc) || $page->isPostBack() )
{
	$retval = "";	
	$strTerminator = "<br />";
	
	if (!empty($argc))
	{
		$strTerminator = "\n";
		$path = $argv[1];
	}
	else		
	{
		$strTerminator = "<br />";
		$path = $_POST["folder"];
	}

	if ($path != "")
	{

		if (substr($path, strlen($path) - 1) != '/')
			$path = $path."/";
		
		$releases = $rel->getForExport();
		$s = new Sites();
		$site = $s->get();
		$nzbCount = 0;
		
		foreach ($releases as $release)
		{
			ob_start();
			@readgzfile($site->nzbpath.$release["guid"].".nzb.gz");
			$nzbfile = ob_get_contents();
			ob_end_clean();
			$fh = fopen($path.$release["searchname"].".nzb", 'w');
			fwrite($fh, $nzbfile);
			fclose($fh);
			$nzbCount++;
		}
		
		$retval.= 'Processed '.$nzbCount.' nzbs';
	
		if (!empty($argc))
		{
			echo 'Processed '.$nzbCount.' nzbs';
			die();
		}
	}
	
	$page->smarty->assign('folder', $path);	
	$page->smarty->assign('output', $retval);	
}
else
{
	$page->smarty->assign('fromdate', $rel->getLatestUsenetPostDate());	
	$page->smarty->assign('todate', $rel->getLatestUsenetPostDate());	
}

$page->title = "Export Nzbs";

$grouplist = array("-1" => "--All Groups--");
$page->smarty->assign('grouplist', $grouplist);


$page->content = $page->smarty->fetch('admin/nzb-export.tpl');
$page->render();

?>

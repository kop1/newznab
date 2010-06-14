<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/framework/db.php");
$db = new DB();

if (empty($argc))
	$page = new AdminPage();

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

	if (substr($path, strlen($path) - 1) != '/')
		$path = $path."/";

	$groups = $db->query("SELECT ID, name FROM groups");
	foreach ($groups as $group)
		$siteGroups[$group["name"]] = $group["ID"];

	if (!isset($groups) || count($groups) == 0)
	{
		if (!empty($argc))
		{
			echo "no groups specified\n";
		}
		else
		{
			$retval.= "no groups specified"."<br />";
		}		
	}
	else
	{	
		$nzbCount = 0;
		foreach(glob($path."*.nzb") as $nzbFile) 
		{
	
			$nzb = file_get_contents($nzbFile);
			
			$xml = simplexml_load_string($nzb);
			if (!$xml || strtolower($xml->getName()) != 'nzb') 
			{
				continue;
			}
die();
			$i=0;
			foreach($xml->file as $file) 
			{
				//file info
				$groupID = -1;
				$name = (string)$file->attributes()->subject;
				$fromname = (string)$file->attributes()->poster;
				$unixdate = (string)$file->attributes()->date;
				$date = date("Y-m-d H:i:s", (string)$file->attributes()->date);
				
				//groups
				$groupArr = array();
				foreach($file->groups->group as $group) 
				{
					$group = (string)$group;
					if (array_key_exists($group, $siteGroups)) 
					{
						$groupID = $siteGroups[$group];
					}
					$groupArr[] = $group;
				}
				
				
				
				if ($groupID != -1)
				{
					
					$xref = 'Xref: '.implode(' ', $groupArr);
							
					$totalParts = sizeof($file->segments->segment);
					
					//insert binary
					$binarySql = sprintf("INSERT INTO binaries (name, fromname, date, xref, totalParts, groupID) values (%s, %s, %s, %s, %s, %s)", 
							$db->escapeString($name), $db->escapeString($fromname), $db->escapeString($date),
							$db->escapeString($xref), $db->escapeString($totalParts), $db->escapeString($groupID) );
					
					$binaryId = $db->queryInsert($binarySql);
					
					//segments (i.e. parts)
					foreach($file->segments->segment as $segment) 
					{
						$messageId = (string)$segment;
						$partnumber = $segment->attributes()->number;
						$size = $segment->attributes()->bytes;
						$partsSql = sprintf("INSERT INTO parts (binaryID, messageID, number, partnumber, size, dateadded) values (%s, %s, 0, %s, %s, %s)", 
								$db->escapeString($binaryId), $db->escapeString($messageId), $db->escapeString($partnumber), 
								$db->escapeString($size), $db->escapeString($date));
						$partsQuery = $db->queryInsert($partsSql);
					}
				}
				else
				{
					if (!empty($argc))
					{
						echo ("no group found for ".$name."\n");
						flush();
					}
					else
					{
						$retval.= "no group found for ".$name."<br />";
					}
				}
			}
			$nzbCount++;
			unlink($nzbFile);
	
			if (!empty($argc))
			{
				echo ("imported ".$nzbFile."\n");
				flush();
			}
			else
			{
				$retval.= "imported ".$nzbFile."<br />";
			}
		}
	}
	
	$retval.= 'Processed '.$nzbCount.' nzbs';

	if (!empty($argc))
	{
		echo 'Processed '.$nzbCount.' nzbs';
		die();
	}
	
	$page->smarty->assign('output', $retval);	
	
}

$page->title = "Import Nzbs";
$page->content = $page->smarty->fetch('admin/nzb-import.tpl');
$page->render();

?>

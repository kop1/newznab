<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/framework/db.php");
$db = new DB();

if (empty($argc))
	$page = new AdminPage();

$filestoprocess = Array();
$browserpostednames = Array();
$viabrowser = false;

if (!empty($argc) || $page->isPostBack() )
{
	$retval = "";	

	//
	// Via browser, build an array of all the nzb files uploaded into php /tmp location
	//	
	if (isset($_FILES["uploadedfiles"]))
	{
    foreach ($_FILES["uploadedfiles"]["error"] as $key => $error)
    {
      if ($error == UPLOAD_ERR_OK)
      {
          $tmp_name = $_FILES["uploadedfiles"]["tmp_name"][$key];
          $name = $_FILES["uploadedfiles"]["name"][$key];
          $filestoprocess[] = $tmp_name;
          $browserpostednames[$tmp_name] = $name;
          $viabrowser = true;
      }
    }
	}

	if (!empty($argc))
	{
		$strTerminator = "\n";
		$path = $argv[1];
		$usenzbname = (isset($argv[2]) && $argv[2] == 'true') ? true : false;
	}
	else		
	{
		$strTerminator = "<br />";
		$path = (isset($_POST["folder"]) ? $_POST["folder"] : "");
		$usenzbname = (isset($_POST['usefilename']) && $_POST["usefilename"] == 'on') ? true : false;
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
	
		//
		// read from the path, if no files submitted via the browser
		//		
		if (count($filestoprocess) == 0)
			$filestoprocess = glob($path."*.nzb"); 
		
		foreach($filestoprocess as $nzbFile) 
		{
			$importfailed = false;
			$nzb = file_get_contents($nzbFile);
			
			$xml = @simplexml_load_string($nzb);
			if (!$xml || strtolower($xml->getName()) != 'nzb') 
			{
				continue;
			}

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
					
					$xref = implode(': ', $groupArr).':';
							
					$totalParts = sizeof($file->segments->segment);
					
					//insert binary
					$binarySql = sprintf("INSERT INTO binaries (name, fromname, date, xref, totalParts, groupID, dateadded, importname) values (%s, %s, %s, %s, %s, %s, NOW(), %s)", 
							$db->escapeString($name), $db->escapeString($fromname), $db->escapeString($date),
							$db->escapeString($xref), $db->escapeString($totalParts), $db->escapeString($groupID), $db->escapeString($nzbFile) );
					
					$binaryId = $db->queryInsert($binarySql);
					
					if ($usenzbname) 
					{
						$usename = str_replace('.nzb', '', ($viabrowser ? $browserpostednames[$nzbFile] : basename($nzbFile)));
						
						$db->query(sprintf("update binaries set relname = replace(%s, '_', ' '), relpart = %d, reltotalpart = %d, procstat=%d, categoryID=%s, regexID=%d, reqID=%s where ID = %d", 
							$db->escapeString($usename), 1, 1, 5, "null", "null", "null", $binaryId));
					}
					
					//segments (i.e. parts)
					foreach($file->segments->segment as $segment) 
					{
						$messageId = (string)$segment;
						$partnumber = $segment->attributes()->number;
						$size = $segment->attributes()->bytes;
						$partsSql = sprintf("INSERT INTO parts (binaryID, messageID, number, partnumber, size, dateadded) values (%s, %s, 0, %s, %s, NOW())", 
								$db->escapeString($binaryId), $db->escapeString($messageId), $db->escapeString($partnumber), 
								$db->escapeString($size));
						$partsQuery = $db->queryInsert($partsSql);
					}

				}
				else
				{
					$importfailed = true;
					if (!empty($argc))
					{
						echo ("no group found for ".$name." (one of ".implode(', ', $groupArr)." are missing)\n");
						flush();
					}
					else
					{
						$retval.= "no group found for ".$name." (one of ".implode(', ', $groupArr)." are missing)<br />";
					}
					break;
				}
			}
			
			if (!$importfailed)
			{
				$nzbCount++;
				@unlink($nzbFile);

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
$page->content = $page->smarty->fetch('nzb-import.tpl');
$page->render();

?>

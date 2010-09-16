<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/tvrage.php");

$page = new AdminPage();
$tvrage = new TvRage();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
		if ($_POST["id"] == "")
		{
			$imgbytes = "";
			if($_FILES['imagedata']['size'] > 0)
			{
				$fileName = $_FILES['imagedata']['name'];
				$tmpName  = $_FILES['imagedata']['tmp_name'];
				$fileSize = $_FILES['imagedata']['size'];
				$fileType = $_FILES['imagedata']['type'];

				//
				// check the uploaded file is actually an image.
				//
				$file_info = getimagesize($tmpName);
				if(!empty($file_info))
				{
					$fp = fopen($tmpName, 'r');
					$imgbytes = fread($fp, filesize($tmpName));
					fclose($fp);		
				}				
			}		

			$tvrage->add($_POST["rageID"], $_POST["releasetitle"], $_POST["description"], $imgbytes);
		}
		else
		{
			$imgbytes = "";
			if($_FILES['imagedata']['size'] > 0)
			{
				$fileName = $_FILES['imagedata']['name'];
				$tmpName  = $_FILES['imagedata']['tmp_name'];
				$fileSize = $_FILES['imagedata']['size'];
				$fileType = $_FILES['imagedata']['type'];

				//
				// check the uploaded file is actually an image.
				//
				$file_info = getimagesize($tmpName);
				if(!empty($file_info))
				{
					$fp = fopen($tmpName, 'r');
					$imgbytes = fread($fp, filesize($tmpName));
					fclose($fp);		
				}			
			}	

			$tvrage->update($_POST["id"], $_POST["rageID"], $_POST["releasetitle"], $_POST["description"], $imgbytes);
		}
		
		header("Location:".WWW_TOP."/rage-list.php");
        break;
    case 'view':
    default:

			if (isset($_GET["id"]))
			{
				$page->title = "Tv Rage Edit";
				$id = $_GET["id"];
				
				$rage = $tvrage->getByID($id);
				$page->smarty->assign('rage', $rage);	
			}
	
	   	break;   
}

$page->title="Add/Edit TV Rage Show Data";
$page->content = $page->smarty->fetch('admin/rage-edit.tpl');
$page->render();

?>

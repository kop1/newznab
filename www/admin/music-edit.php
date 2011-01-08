<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/music.php");

$page = new AdminPage();
$music = new Music();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

if (isset($_REQUEST["id"]))
{
	$id = $_REQUEST["id"];
	$mus = $music->getMusicInfo($id);
	
	if (!$mus) {
		$page->show404();
	}
	
	switch($action) 
	{
	    case 'submit':
	    	$coverLoc = WWW_DIR."covers/music/".$id.'.jpg';
	    	
			if($_FILES['cover']['size'] > 0)
			{
				$tmpName = $_FILES['cover']['tmp_name'];
				$file_info = getimagesize($tmpName);
				if(!empty($file_info))
				{
					move_uploaded_file($_FILES['cover']['tmp_name'], $coverLoc);
				}
			}
			
			$_POST['cover'] = (file_exists($coverLoc)) ? 1 : 0;
			
			$music->update($id, $_POST["title"], $_POST['tagline'], $_POST["plot"], $_POST["year"], $_POST["rating"], $_POST["genre"], $_POST["director"], $_POST["actors"], $_POST["language"], $_POST["cover"], $_POST['backdrop']);
			
			header("Location:".WWW_TOP."/music-list.php");
	        die();
	    break;
	    case 'view':
	    default:				
			$page->title = "Music Edit";
			$page->smarty->assign('music', $mus);
		break;   
	}
}

$page->content = $page->smarty->fetch('music-edit.tpl');
$page->render();

?>

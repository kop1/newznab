<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/category.php");

$page = new AdminPage();
$releases = new Releases();
$category = new Category();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
		
		$releases->update($_POST["id"], $_POST["name"], $_POST["searchname"], $_POST["fromname"], $_POST["category"], $_POST["totalpart"], $_POST["grabs"], $_POST["size"], $_POST["postdate"], $_POST["adddate"], $_POST["rageID"], $_POST["seriesfull"], $_POST["season"], $_POST["episode"]);
		header("Location:".WWW_TOP."/release-list.php");
        break;
    case 'view':
    default:

		if (isset($_GET["id"]))
		{
			$page->title = "Release Edit";
			$id = $_GET["id"];
			
			$release = $releases->getByID($id);

			$page->smarty->assign('release', $release);	
		}

      break;   
}

$page->smarty->assign('yesno_ids', array(1,0));
$page->smarty->assign('yesno_names', array( 'Yes', 'No'));
$page->smarty->assign('catlist',$category->getForSelect(false));

$page->content = $page->smarty->fetch('admin/release-edit.tpl');
$page->render();

?>

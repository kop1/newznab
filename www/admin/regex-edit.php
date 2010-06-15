<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/releaseregex.php");

$page = new AdminPage();
$reg = new ReleaseRegex();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
		$ret = $reg->update($_POST["id"], $_POST["status"], $_POST["description"]);
		header("Location:".WWW_TOP."/regex-list.php");
		break;
    case 'view':
    default:

			if (isset($_GET["id"]))
			{
				$page->title = "Release Regex Edit";
				$id = $_GET["id"];
				
				$r = $reg->getByID($id);

				$page->smarty->assign('regex', $r);	
			}

      break;   
}

$page->smarty->assign('status_ids', array(Category::STATUS_ACTIVE,Category::STATUS_INACTIVE));
$page->smarty->assign('status_names', array( 'Yes', 'No'));

$page->content = $page->smarty->fetch('admin/regex-edit.tpl');
$page->render();

?>

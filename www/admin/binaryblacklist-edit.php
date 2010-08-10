<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/binaries.php");

$page = new AdminPage();
$bin = new Binaries();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
	    if ($_POST["id"] == "")
    	{
			$bin->addBlacklist($_POST);
		}
		else
		{
			$ret = $bin->updateBlacklist($_POST);
		}	
		header("Location:".WWW_TOP."/binaryblacklist-list.php");
		break;
    case 'addtest':
    	if (isset($_GET['regex']) && isset($_GET['groupname'])) {
    		$r = array('groupname'=>$_GET['groupname'], 'regex'=>$_GET['regex'], 'ordinal'=>'1', 'status'=>'1');
    		$page->smarty->assign('regex', $r);	
    	}
    	break;
    case 'view':
    default:

			if (isset($_GET["id"]))
			{
				$page->title = "Binary Black/Whitelist Edit";
				$id = $_GET["id"];
				
				$r = $bin->getBlacklistByID($id);

				$page->smarty->assign('regex', $r);	
			}
			else
			{
				$page->title = "Binary Black/Whitelist Add";
			}

      break;   
}

$page->smarty->assign('status_ids', array(Category::STATUS_ACTIVE,Category::STATUS_INACTIVE));
$page->smarty->assign('status_names', array( 'Yes', 'No'));

$page->smarty->assign('optype_ids', array(1,2));
$page->smarty->assign('optype_names', array( 'Black', 'White'));

$page->content = $page->smarty->fetch('admin/binaryblacklist-edit.tpl');
$page->render();

?>

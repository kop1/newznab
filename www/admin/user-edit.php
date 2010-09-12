<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/users.php");

$page = new AdminPage();
$users = new Users();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'add':
				$user = array();
				$user["role"] = Users::ROLE_USER;
				$page->smarty->assign('user', $user);	

			break;
    case 'submit':
    
    	if ($_POST["id"] == "")
    	{
				$ret = $users->signup($_POST["username"], $_POST["password"], $_POST["email"], '', $_POST["role"]);
    	}
    	else
    	{
				$ret = $users->update($_POST["id"], $_POST["username"], $_POST["email"], $_POST["grabs"], $_POST["role"]);
			}

				if ($ret >= 0)
					header("Location:".WWW_TOP."/user-list.php");
				else
				{
					switch ($ret)
					{
						case Users::ERR_SIGNUP_BADUNAME:
							$page->smarty->assign('error', "Bad username. Try a better one.");
							break;
						case Users::ERR_SIGNUP_BADPASS:
							$page->smarty->assign('error', "Bad password. Try a longer one.");
							break;
						case Users::ERR_SIGNUP_BADEMAIL:
							$page->smarty->assign('error', "Bad email.");
							break;
						case Users::ERR_SIGNUP_UNAMEINUSE:
							$page->smarty->assign('error', "Username in use.");
							break;
						case Users::ERR_SIGNUP_EMAILINUSE:
							$page->smarty->assign('error', "Email in use.");
							break;
						default:
							$page->smarty->assign('error', "Unknown save error.");
							break;
					}
					$user = array();
					$user["ID"] = $_POST["id"];
					$user["username"] = $_POST["username"];
					$user["email"] = $_POST["email"];
					$user["grabs"] = $_POST["grabs"];
					$user["role"] = $_POST["role"];
					$page->smarty->assign('user', $user);	
				}
        break;
    case 'view':
    default:

			if (isset($_GET["id"]))
			{
				$page->title = "User Edit";
				$id = $_GET["id"];
				$user = $users->getByID($id);

				$page->smarty->assign('user', $user);	
			}

      break;   
}

$page->smarty->assign('yesno_ids', array(1,0));
$page->smarty->assign('yesno_names', array( 'Yes', 'No'));

$page->smarty->assign('role_ids', array(Users::ROLE_ADMIN, Users::ROLE_USER, Users::ROLE_DISABLED));
$page->smarty->assign('role_names', array( 'Admin', 'User', 'Disabled'));

$page->content = $page->smarty->fetch('admin/user-edit.tpl');
$page->render();

?>

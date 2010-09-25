<?php
require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/category.php");

$page = new Page;
$users = new Users;
$category = new Category;

if (!$users->isLoggedIn())
	$page->show403();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

$userid = $users->currentUserId();
$data = $users->getById($userid);
if (!$data)
	$page->show404();
	
switch($action) 
{
	case 'newapikey':
		$users->updateRssKey($userid);
		header("Location: profileedit" );
		break;

	case 'submit':
		
		$data["email"] = $_POST['email'];
		
		if ($_POST['password']!= "" && $_POST['password'] != $_POST['confirmpassword'])
		{
			$page->smarty->assign('error', "Password Mismatch");
		}
		else
		{
			if ($_POST['password']!= "" && !$users->isValidPassword($_POST['password']))
			{
				$page->smarty->assign('error', "Your password must be longer than five characters.");
			}
			else
			{
				if (!$users->isValidEmail($_POST['email']))
					$page->smarty->assign('error', "Your email is not a valid format.");	
				else
				{
					$res = $users->getByEmail($_POST['email']);
					if ($res && $res["ID"] != $userid)
						$page->smarty->assign('error', "Sorry, the email is already in use.");	
					else
					{
						$users->update($userid, $data["username"], $_POST['email'], $data["grabs"], $data["role"]);
						
						$users->addCategoryExclusions($userid, $_POST['exccat']);

						if ($_POST['password'] != "")
							$users->updatePassword($userid, $_POST['password']);
						
						header("Location:".WWW_TOP."/profile");
						die();
					}
				}
			}
		}
		break;
		
	break;
	case 'view':
	default:				
	break;   
}

$page->smarty->assign('user', $data);
$page->smarty->assign('userexccat', $users->getCategoryExclusion($userid));

$page->meta_title = "Edit User Profile";
$page->meta_keywords = "edit,profile,user,details";
$page->meta_description = "Edit User Profile for ".$data["username"] ;


$page->smarty->assign('catlist',$category->getForSelect(false));

$page->content = $page->smarty->fetch('profileedit.tpl');
$page->render();


?>

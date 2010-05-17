<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");

$page = new Page();
$users = new Users();

$page->addToBody("onload=\"setFocus('username');\"");
$page->meta_title = "Register";
$page->meta_keywords = "register,signup,registration";
$page->meta_description = "Register";

if ($users->isLoggedIn())
	$page->show404();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

switch($action) 
{
    case 'submit':
		
		$page->smarty->assign('username', $_POST['username']);
		$page->smarty->assign('password', $_POST['password']);
		$page->smarty->assign('confirmpassword', $_POST['confirmpassword']);
		$page->smarty->assign('email', $_POST['email']);
		
		//
		// check uname/email isnt in use, password valid.
		// if all good create new user account and redirect back to home page
		//
		if ($_POST['password'] != $_POST['confirmpassword'])
		{
			$page->smarty->assign('error', "Password Mismatch");
		}
		else
		{
			$ret = $users->signup($_POST['username'], $_POST['password'], $_POST['email'], $_SERVER['REMOTE_ADDR']);
			if ($ret > 0)
			{
				$users->login($ret);
				header("Location:/");
			}
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
						$page->smarty->assign('error', "Failed to register.");
						break;
				}
			}
		}
		break;
	
}

$page->content = $page->smarty->fetch('register.tpl');
$page->render();

?>

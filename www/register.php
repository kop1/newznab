<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/site.php");

$page = new Page();
$users = new Users();

$page->meta_title = "Register";
$page->meta_keywords = "register,signup,registration";
$page->meta_description = "Register";

if ($users->isLoggedIn())
	$page->show404();

if ($page->site->registerstatus != Sites::REGISTER_STATUS_OPEN)
{
	$page->smarty->assign('error', "Registrations are currently disabled.");
	$page->smarty->assign('showregister', "0");
}
else
{	
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
					header("Location: ".WWW_TOP."/");
				}
				else
				{
					switch ($ret)
					{
						case Users::ERR_SIGNUP_BADUNAME:
							$page->smarty->assign('error', "Your username must be longer than three characters.");
							break;
						case Users::ERR_SIGNUP_BADPASS:
							$page->smarty->assign('error', "Your password must be longer than five characters.");
							break;
						case Users::ERR_SIGNUP_BADEMAIL:
							$page->smarty->assign('error', "Your email is not a valid format.");
							break;
						case Users::ERR_SIGNUP_UNAMEINUSE:
							$page->smarty->assign('error', "Sorry, the username is already taken.");
							break;
						case Users::ERR_SIGNUP_EMAILINUSE:
							$page->smarty->assign('error', "Sorry, the email is already in use.");
							break;
						default:
							$page->smarty->assign('error', "Failed to register.");
							break;
					}
				}
			}
			break;
		
	}
}
$page->content = $page->smarty->fetch('register.tpl');
$page->render();

?>

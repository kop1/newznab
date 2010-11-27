<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/util.php");

class Users
{	
	const ERR_SIGNUP_BADUNAME = -1;
	const ERR_SIGNUP_BADPASS = -2;
	const ERR_SIGNUP_BADEMAIL = -3;
	const ERR_SIGNUP_UNAMEINUSE = -4;
	const ERR_SIGNUP_EMAILINUSE = -5;
	const ERR_SIGNUP_BADINVITECODE = -6;
	const SUCCESS = 1;
	
	const ROLE_GUEST = 0;
	const ROLE_USER = 1;
	const ROLE_ADMIN = 2;
	const ROLE_DISABLED = 3;
	
	const DEFAULT_INVITES = 1;
	const DEFAULT_INVITE_EXPIRY_DAYS = 7;

	const SALTLEN = 4;
	const SHA1LEN = 40;
	const HASHLEN = 44;
	const TOKENLEN = 12;

	public function get()
	{			
		$db = new DB();
		return $db->query("select * from users");		
	}	
	
	public function delete($id)
	{			
		$db = new DB();
		$this->delCartForUser($id);
		$this->delUserCategoryExclusions($id);
		
		$releases = new Releases();
		$releases->deleteCommentsForUser($id);
		
		$db->query(sprintf("delete from users where ID = %d", $id));		
	}	
	
	public function getRange($start, $num, $orderby)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		$order = $this->getBrowseOrder($orderby);
		
		return $db->query(sprintf(" SELECT * from users order by %s %s".$limit, $order[0], $order[1]));		
	}	
	
	public function getBrowseOrder($orderby)
	{
		$order = ($orderby == '') ? 'username_desc' : $orderby;
		$orderArr = explode("_", $order);
		switch($orderArr[0]) {
			case 'username':
				$orderfield = 'username';
			break;
			case 'email':
				$orderfield = 'email';
			break;
			case 'host':
				$orderfield = 'host';
			break;
			case 'createddate':
				$orderfield = 'createddate';
			break;
			case 'lastlogin':
				$orderfield = 'lastlogin';
			break;
			case 'grabs':
				$orderfield = 'grabs';
			break;		
			case 'role':
				$orderfield = 'role';
			break;				
			default:
				$orderfield = 'username';
			break;
		}
		$ordersort = (isset($orderArr[1]) && preg_match('/^asc|desc$/i', $orderArr[1])) ? $orderArr[1] : 'desc';
		return array($orderfield, $ordersort);
	}	
	
	public function getCount()
	{			
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from users");
		return $res["num"];		
	}	

	public function add($uname, $pass, $email, $role, $host, $invites=Users::DEFAULT_INVITES, $invitedby=0)
	{			
		$db = new DB();
		
		$site = new Sites();
		$s = $site->get();
		if ($s->storeuserips != "1")
			$host = "";
		
		if ($invitedby == 0)
			$invitedby = "null";
			
		return $db->queryInsert(sprintf("insert into users (username, password, email, role, createddate, host, rsstoken, invites, invitedby) values (%s, %s, lower(%s), %d, now(), %s, md5(%s), %d, %s)", 
			$db->escapeString($uname), $db->escapeString($this->hashPassword($pass)), $db->escapeString($email), $role, $db->escapeString($host), $db->escapeString(uniqid()), $invites, $invitedby));		
	}	
	
	public function update($id, $uname, $email, $grabs, $role, $invites, $movieview)
	{			
		$db = new DB();
		
		$uname = trim($uname);
		$email = trim($email);

		if (!$this->isValidUsername($uname))
			return Users::ERR_SIGNUP_BADUNAME;
			
		if (!$this->isValidEmail($email))
			return Users::ERR_SIGNUP_BADEMAIL;			

		$res = $this->getByUsername($uname);
		if ($res)
			if ($res["ID"] != $id)
				return Users::ERR_SIGNUP_UNAMEINUSE;
		
		$res = $this->getByEmail($email);
		if ($res)
			if ($res["ID"] != $id)
				return Users::ERR_SIGNUP_EMAILINUSE;		
		
		$db->query(sprintf("update users set username = %s, email = %s, grabs = %d, role = %d, invites=%d, movieview=%d where id = %d", 
			$db->escapeString($uname), $db->escapeString($email), $grabs, $role, $invites, $movieview, $id));		
			
		return Users::SUCCESS;
	}	
	
	public function updateRssKey($uid)
	{
		$db = new DB();
		return $db->query(sprintf("update users set rsstoken = md5(%s) where id = %d", 
			$db->escapeString(uniqid()), $uid));		
	}
	
	public function updatePassResetGuid($id, $guid)
	{			
		$db = new DB();
		$db->query(sprintf("update users set resetguid = %s where id = %d", $db->escapeString($guid), $id));		
		return Users::SUCCESS;
	}	
	
	public function updatePassword($id, $password)
	{			
		$db = new DB();
		$db->query(sprintf("update users set password = %s where id = %d", $db->escapeString($this->hashPassword($password)), $id));		
		return Users::SUCCESS;
	}		
	
	public function getByEmail($email)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from users where lower(email) = lower(%s) ", $db->escapeString($email)));		
	}	
	
	public function getByPassResetGuid($guid)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from users where lower(resetguid) = lower(%s) ", $db->escapeString($guid)));		
	}	
	
	public function getByUsername($uname)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from users where lower(username) = lower(%s) ", $db->escapeString($uname)));		
	}	
	
	public function incrementGrabs($id, $num=1)
	{			
		$db = new DB();
		$db->query(sprintf("update users set grabs = grabs + %d where id = %d ", $num, $id));		
	}	

	public function getById($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select *, NOW() as now from users where id = %d ", $id));		
	}	
	
	public function getByIdAndRssToken($id, $rsstoken)
	{			
		$db = new DB();
		$res = $this->getById($id);
		return ($res && $res["rsstoken"] == $rsstoken ? $res : null);
	}	
	
	public function getByRssToken($rsstoken)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from users where lower(rsstoken) = lower(%s) ", $db->escapeString($rsstoken)));		
	}	
	
	public function getBrowseOrdering()
	{
		return array('username_asc', 'username_desc', 'email_asc', 'email_desc', 'host_asc', 'host_desc', 'createddate_asc', 'createddate_desc', 'lastlogin_asc', 'lastlogin_desc', 'grabs_asc', 'grabs_desc', 'role_asc', 'role_desc');
	}	
	
	public function isValidUsername($uname)
	{
		return preg_match("/^[a-z][a-z0-9]{2,}$/i", $uname);
	}
	
	public function isValidPassword($pass)
	{
		return (strlen($pass) > 5);
	}
	
	public function isDisabled($username)
	{
	  $db = new DB();
 		$role = $db->queryOneRow(sprintf("select role as role from users where username = %s ", $db->escapeString($username)));
 		return ($role["role"] == Users::ROLE_DISABLED);
	}
	
	public function isValidEmail($email)
	{
		return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i", $email);
	}
	
	public function isValidUrl($url)
	{
		return (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $url)) ? false : true;
	}

	public function generateUsername($email)
	{
		//TODO: Make this generate a more friendly username based on the email address.
		return "u".substr(md5(uniqid()), 0, 7);
	}
	
	public function generatePassword()
	{
		return substr(md5(uniqid()), 0, 8);
	}
	
	public function signup($uname, $pass, $email, $host, $role = Users::ROLE_USER, $invites=Users::DEFAULT_INVITES, $invitecode="")
	{
		$uname = trim($uname);
		$pass = trim($pass);
		$email = trim($email);

		if (!$this->isValidUsername($uname))
			return Users::ERR_SIGNUP_BADUNAME;
		
		if (!$this->isValidPassword($pass))
			return Users::ERR_SIGNUP_BADPASS;
			
		if (!$this->isValidEmail($email))
			return Users::ERR_SIGNUP_BADEMAIL;			

		$res = $this->getByUsername($uname);
		if ($res)
			return Users::ERR_SIGNUP_UNAMEINUSE;
		
		$res = $this->getByEmail($email);
		if ($res)
			return Users::ERR_SIGNUP_EMAILINUSE;

		//
		// make sure this is the last check, as if a further validation check failed, 
		// the invite would still have been used up
		//
		$invitedby = 0;
		if ($invitecode!="")
		{	
			$invitedby = $this->checkAndUseInvite($invitecode);
			if ($invitedby < 0)
				return Users::ERR_SIGNUP_BADINVITECODE;
		}
		
		return $this->add($uname, $pass, $email, $role, $host, $invites, $invitedby);
	}
	
	function randomKey($amount)
	{
		$keyset  = "abcdefghijklmABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$randkey = "";
		for ($i=0; $i<$amount; $i++)
			$randkey .= substr($keyset, rand(0, strlen($keyset)-1), 1);
		return $randkey;	
	}
	
	public static function hashPassword($password)
	{
		$salt = self::randomKey(self::SALTLEN);
		$site = new Sites();
		$s = $site->get();
		return self::hashSHA1($s->siteseed.$password.$salt.$s->siteseed).$salt; 
	}

	public static function hashSHA1($string)
	{
		return sha1($string);
	}

	public static function checkPassword($password, $hash)
	{
		$salt = substr($hash, -self::SALTLEN);
		$site = new Sites();
		$s = $site->get();
		return self::hashSHA1($s->siteseed.$password.$salt.$s->siteseed) === substr($hash, 0, self::SHA1LEN);
	}	
		
	public function isLoggedIn()
	{
		if (isset($_SESSION['uid'])) {
			return true;
		} elseif (isset($_COOKIE['uid']) && isset($_COOKIE['idh'])) {
		 	$site = new Sites();
			$s = $site->get();
		 	if ($_COOKIE['idh'] == $this->hashSHA1($s->siteseed.$_COOKIE['uid'])) {
				$this->login($_COOKIE['uid'], $_SERVER['REMOTE_ADDR']);
			}
		}
		return isset($_SESSION['uid']);
	}
	
	public function currentUserId()
	{
		return (isset($_SESSION['uid']) ? $_SESSION['uid'] : -1);
	}
	
	public function logout()
	{
		session_start();
		session_unset();
		session_destroy();
		setcookie('uid', '', (time()-2592000));
		setcookie('idh', '', (time()-2592000));
	}
	
	public function login($uid, $host="", $remember="")
	{
		$_SESSION['uid'] = $uid;
		
		$site = new Sites();
		$db = new DB();
		$s = $site->get();
		
		if ($s->storeuserips != "1")
			$host = '';
			
		$this->updateSiteAccessed($uid, $host);
		
		if ($remember == 1) 
			$this->setCookies($uid, $s->siteseed);
	}
	
	public function updateSiteAccessed($uid, $host="")
	{			
		$db = new DB();
		$hostSql = '';
		if ($host != '')
			$hostSql = sprintf(', host = %s', $db->escapeString($host));
			
		$db->query(sprintf("update users set lastlogin = now() %s where ID = %d ", $hostSql, $uid));		
	}
	
	public function updateApiAccessed($uid)
	{			
		$db = new DB();
		$db->query(sprintf("update users set apiaccess = now() where id = %d ", $uid));		
	}		
	
	public function setCookies($uid, $seed)
	{			
		$idh = $this->hashSHA1($seed.$uid);
		setcookie('uid', $uid, (time()+2592000));
		setcookie('idh', $idh, (time()+2592000));
	}
	
	public function addCart($uid, $releaseid)
	{			
		$db = new DB();
		return $db->queryInsert(sprintf("insert into usercart (userID, releaseID, createddate) values (%d, %d, now())", $uid, $releaseid));		
	}	

	public function getCart($uid)
	{			
		$db = new DB();
		return $db->query(sprintf("select usercart.*, releases.searchname,releases.guid from usercart inner join releases on releases.ID = usercart.releaseID where userID = %d", $uid));		
	}	

	public function delCart($id, $uid)
	{			
		$db = new DB();
		$db->query(sprintf("delete from usercart where ID = %d and userID = %d", $id, $uid));		
	}	
	
	public function delCartForUser($uid)
	{			
		$db = new DB();
		$db->query(sprintf("delete from usercart where userID = %d", $uid));		
	}	

	public function delCartForRelease($rid)
	{			
		$db = new DB();
		$db->query(sprintf("delete from usercart where releaseID = %d", $rid));		
	}	
	
	public function addCategoryExclusions($uid, $catids)
	{			
		$db = new DB();
		$this->delUserCategoryExclusions($uid);
		if (count($catids) > 0)
		{
			foreach ($catids as $catid)
			{
				$db->queryInsert(sprintf("insert into userexcat (userID, categoryID, createddate) values (%d, %d, now())", $uid, $catid));		
			}
		}
	}

	public function getCategoryExclusion($uid)
	{			
		$db = new DB();
		$ret = array();
		$data = $db->query(sprintf("select categoryID from userexcat where userID = %d", $uid));		
		foreach ($data as $d)
			$ret[] = $d["categoryID"];
		
		return $ret;
	}

	public function getCategoryExclusionNames($uid)
	{			
		$data = $this->getCategoryExclusion($uid);
		$db = new DB();
		$category = new Category();
		$data = $category->getByIds($data);
		$ret = array();
		foreach ($data as $d)
			$ret[] = $d["title"];
		
		return $ret;
	}

	public function delCategoryExclusion($uid, $catid)
	{			
		$db = new DB();
		$db->query(sprintf("delete from userexcat where userID = %d and categoryID = %d", $uid, $catid));		
	}
	
	public function delUserCategoryExclusions($uid)
	{
		$db = new DB();
		$db->query(sprintf("delete from userexcat where userID = %d", $uid));		
	}
	
	public function sendInvite($sitetitle, $siteemail, $serverurl, $uid, $emailto)
	{	
		$sender = $this->getById($uid);
		$token = $this->hashSHA1(uniqid());
		$subject = $sitetitle." Invitation";
		$url = $serverurl."register.php?invite=".$token;
		$contents = $sender["username"]." has sent an invite to join ".$sitetitle." to this email address. To accept the invition click the following link.\n\n ".$url;

		sendEmail($emailto, $subject, $contents, $siteemail);
		$this->addInvite($uid, $token);
		return $url;
	}
	
	public function getInvite($inviteToken)
	{
		$db = new DB();

		//
		// Tidy any old invites sent greater than DEFAULT_INVITE_EXPIRY_DAYS days ago.
		//
		$db->query(sprintf("delete from userinvite where createddate < now() - interval %d day", Users::DEFAULT_INVITE_EXPIRY_DAYS));
		
		return $db->queryOneRow(sprintf("select * from userinvite where guid = %s", $db->escapeString($inviteToken)));
	}
	
	public function addInvite($uid, $inviteToken)
	{
		$db = new DB();
		$db->queryInsert(sprintf("insert into userinvite (guid, userID, createddate) values (%s, %d, now())", $db->escapeString($inviteToken), $uid));		
	}
	
	public function deleteInvite($inviteToken)
	{
		$db = new DB();
		$db->query(sprintf("delete from userinvite where guid = %s ", $db->escapeString($inviteToken)));		
	}	
	
	public function checkAndUseInvite($invitecode)
	{
		$invite = $this->getInvite($invitecode);
		if (!$invite)
			return -1;
		
		$db = new DB();
		$db->query(sprintf("update users set invites = invites-1 where id = %d ", $invite["userID"]));		
		$this->deleteInvite($invitecode);
		return $invite["userID"];
	}
	
	public function getTopGrabbers()
	{
		$db = new DB();
		return $db->query("SELECT ID, username, SUM(grabs) as grabs FROM users
							GROUP BY ID, username
							HAVING SUM(grabs) > 0
							ORDER BY grabs DESC
							LIMIT 10");		
	}
	
}
?>

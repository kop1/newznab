<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/releases.php");

class Users
{	
	const ERR_SIGNUP_BADUNAME = -1;
	const ERR_SIGNUP_BADPASS = -2;
	const ERR_SIGNUP_BADEMAIL = -3;
	const ERR_SIGNUP_UNAMEINUSE = -4;
	const ERR_SIGNUP_EMAILINUSE = -5;
	const SUCCESS = 1;
	
	const ROLE_GUEST = 0;
	const ROLE_USER = 1;
	const ROLE_ADMIN = 2;
	const ROLE_DISABLED = 3;
	
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

	public function add($uname, $pass, $email, $role, $host)
	{			
		$db = new DB();
		
		$site = new Sites();
		$s = $site->get();
		if ($s->storeuserips != "1")
			$host = "";
				
		return $db->queryInsert(sprintf("insert into users (username, password, email, role, createddate, host, rsstoken) values (%s, %s, lower(%s), %d, now(), %s, md5(%s))", 
			$db->escapeString($uname), $db->escapeString($this->hashPassword($pass)), $db->escapeString($email), $role, $db->escapeString($host), $db->escapeString(uniqid())));		
	}	
	
	public function update($id, $uname, $email, $grabs, $role)
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
		
		$db->query(sprintf("update users set username = %s, email = %s, grabs = %d, role = %d where id = %d", 
			$db->escapeString($uname), $db->escapeString($email), $grabs, $role, $id));		
			
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
		return $db->queryOneRow(sprintf("select * from users where id = %d ", $id));		
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
	
	public function signup($uname, $pass, $email, $host, $role = Users::ROLE_USER)
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

		return $this->add($uname, $pass, $email, $role, $host);
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
		if ($s->storeuserips == "1")
		{
			$db->query(sprintf("update users set lastlogin=now(), host = %s where ID = %d ", $db->escapeString($host), $uid));		
		}
		else
		{
			$db->query(sprintf("update users set lastlogin=now() where ID = %d ", $uid));		
		}
		
		if ($remember == 1) 
		{
			$this->setCookies($uid, $s->siteseed);
		}	
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
		foreach ($catids as $catid)
		{
			$db->queryInsert(sprintf("insert into userexcat (userID, categoryID, createddate) values (%d, %d, now())", $uid, $catid));		
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

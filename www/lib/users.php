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
	
	const ROLE_USER = 1;
	const ROLE_ADMIN = 2;
	
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
		
		$releases = new Releases();
		$releases->deleteCommentsForUser($id);
		
		$db->query(sprintf("delete from users where ID = %d", $id));		
	}	
	
	public function getRange($start, $num)
	{		
		$db = new DB();
		
		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;
		
		return $db->query(" SELECT * from users".$limit);		
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
	
	public function update($id, $uname, $email, $grabs)
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
		
		$db->queryInsert(sprintf("update users set username = %s, email = %s, grabs = %d where id = %d", 
			$db->escapeString($uname), $db->escapeString($email), $grabs, $id));		
			
		return Users::SUCCESS;
	}	
	
	public function getByEmail($email)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from users where lower(email) = lower(%s) ", $db->escapeString($email)));		
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
	
	public function isValidUsername($uname)
	{
		return preg_match("/^[a-z][a-z0-9]{2,}$/i", $uname);
	}
	
	public function isValidPassword($pass)
	{
		return (strlen($pass) > 5);
	}
	
	public function isValidEmail($email)
	{
		return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i", $email);
	}
	
	public function isValidUrl($url)
	{
		return (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $url)) ? false : true;
	}

	public function signup($uname, $pass, $email, $host)
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

		return $this->add($uname, $pass, $email, Users::ROLE_USER, $host);
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
	}
	
	public function login($uid, $host="")
	{
		$_SESSION['uid'] = $uid;
		
		$site = new Sites();
		$s = $site->get();
		if ($s->storeuserips == "1")
		{
			$db = new DB();
			$db->query(sprintf("update users set host = %s where ID = %d ", $db->escapeString($host), $uid));		
		}		
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

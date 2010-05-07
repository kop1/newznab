<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

class Users
{	
	const ERR_SIGNUP_BADUNAME = -1;
	const ERR_SIGNUP_BADPASS = -2;
	const ERR_SIGNUP_BADEMAIL = -3;
	const ERR_SIGNUP_UNAMEINUSE = -4;
	const ERR_SIGNUP_EMAILINUSE = -5;
	
	const ROLE_USER = 1;
	const ROLE_ADMIN = 2;
	
	const SALTLEN = 4;
	const SHA1LEN = 40;
	const HASHLEN = 44;
	const TOKENLEN = 12;
	const SECRET_SALT = "NEW&NAB_SECR3T_SaLT"; //TODO:store in site table to allow change per site.

	public function get()
	{			
		$db = new DB();
		return $db->query("select * from users");		
	}	
	
	public function add($uname, $pass, $email, $role, $host)
	{			
		$db = new DB();
		return $db->queryInsert(sprintf("insert into users (username, password, email, role, createddate, host) values (%s, %s, %s, %d, now(), %s)", 
			$db->escapeString($uname), $db->escapeString($this->hashPassword($pass)), $db->escapeString($email), $role, $db->escapeString($host)));		
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
	
	public function getById($id)
	{			
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from users where id = %d ", $id));		
	}	
	
	public function isValidUsername($uname)
	{
		return eregi("^[a-z][a-z0-9]{2,}$", $uname);
	}
	
	public function isValidPassword($pass)
	{
		return eregi("^[^\s]{6,}$", $pass);
	}
	
	public function isValidEmail($email)
	{
		return eregi("^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$", $email);
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
		return self::hashSHA1(self::SECRET_SALT.$password.$salt.self::SECRET_SALT).$salt; 
	}

	public static function hashSHA1($string)
	{
		return bin2hex(mhash(MHASH_SHA1, $string));
	}

	public static function checkPassword($password, $hash)
	{
		$salt = substr($hash, -self::SALTLEN);
		return self::hashSHA1(self::SECRET_SALT.$password.$salt.self::SECRET_SALT) === substr($hash, 0, self::SHA1LEN);
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
	
	public function login($uid)
	{
		$_SESSION['uid'] = $uid;
	}
}
?>
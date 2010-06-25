<?php
require_once("config.php");
require_once(WWW_DIR."/lib/yenc.php");
require_once(WWW_DIR."/lib/binaries.php");
require_once(WWW_DIR."/lib/framework/db.php");

if(!include('Net/NNTP/Client.php')) 
{
	exit("Error: <b>You must install the pear package 'Net_NNTP'.</b>");	
}

class Nntp extends Net_NNTP_Client
{    
	function doConnect() 
	{
		$ret = $this->connect(NNTP_SERVER);
		if(PEAR::isError($ret))
		{
			echo "Cannot connect to server ".NNTP_SERVER." $ret";
			die();
		}
		if(!defined(NNTP_USERNAME) && NNTP_USERNAME!="" )
		{
			$ret2 = $this->authenticate(NNTP_USERNAME, NNTP_PASSWORD);
			if(PEAR::isError($ret) || PEAR::isError($ret2)) 
			{
				echo "Cannot authenticate to server ".NNTP_SERVER." - ".NNTP_USERNAME." ($ret $ret2)";
				die();
			}
		}
	}
	
	function doQuit() 
	{
		$this->quit();
	}
	
	function getBinary($binaryId)
	{
		$db = new DB();
		$yenc = new yenc();
		$bin = new Binaries();
		
		$binary = $bin->getById($binaryId);
		if (!$binary)
			return false;
		
		$summary = $this->selectGroup($binary['groupname']);
		$message = $dec = '';

		if (PEAR::isError($summary)) 
		{
			echo $summary->getMessage();
			return false;
		}

		$resparts = $db->queryDirect(sprintf("SELECT size, partnumber, messageID FROM parts WHERE binaryID = %d ORDER BY partnumber", $binaryId));
		while ($part = mysql_fetch_array($resparts, MYSQL_BOTH)) 
		{
			$messageID = '<'.$part['messageID'].'>';
			$body = $this->getBody($messageID, true);
			if (PEAR::isError($body)) 
			{
			   echo 'Error fetching part number '.$part['messageID'].' in '.$binary['groupname'].' (Server response: '. $body->getMessage().')';
			   return false;
			}
			
			$dec = $yenc->decode($body);
			if ($yenc->error) 
			{
				echo $yenc->error;
				return false;
			}

			$message .= $dec;
		}
		return $message;
	}
	
}
?>

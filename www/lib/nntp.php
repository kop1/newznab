<?php
require_once($_SERVER['DOCUMENT_ROOT']."/config.php");
require_once "Net/NNTP/Client.php";

class Nntp extends Net_NNTP_Client
{    
	function doConnect() 
	{
		$ret = $this->connect(NNTP_SERVER);
		$ret2 = $this->authenticate(NNTP_USERNAME, NNTP_PASSWORD);
		if(PEAR::isError($ret) || PEAR::isError($ret2)) 
		{
			echo "Cannot connect to server - ".NNTP_SERVER." - ".NNTP_USERNAME." ($ret $ret2)";
			exit;
		}
	}
	
	function doQuit() 
	{
		$this->quit();
	}
}
?>

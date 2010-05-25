<?php
require_once("config.php");
if(!include('Net/NNTP/Client.php')) {
	exit("Error: <b>You must install the pear package 'Net_NNTP'.</b>");	
}

class Nntp extends Net_NNTP_Client
{    
	function doConnect() 
	{
		$ret = $this->connect(NNTP_SERVER);
		if(!is_null(NNTP_USERNAME) && !is_null (NNTP_PASSWORD))
			$ret2 = $this->authenticate(NNTP_USERNAME, NNTP_PASSWORD);
		if(PEAR::isError($ret) || PEAR::isError($ret2)) 
		{
			echo "Cannot connect to server - ".NNTP_SERVER." - ".NNTP_USERNAME." ($ret $ret2)";
			die();
		}
	}
	
	function doQuit() 
	{
		$this->quit();
	}
}
?>

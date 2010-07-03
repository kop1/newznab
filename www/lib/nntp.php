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
	
	function getXOverview($range, $_names = true, $_forceNames = true)
    {
    	// Fetch overview from server
    	$overview = $this->cmdXZver($range);
    	if (PEAR::isError($overview)) {
    	    return $overview;
    	}

    	// Use field names from overview format as keys?
    	if ($_names) 
    	{
    	    // Already cached?
    	    if (is_null($this->_overviewFormatCache)) {
    	    	// Fetch overview format
    	        $format = $this->getOverviewFormat($_forceNames, true);
    	        if (PEAR::isError($format)){
    	            return $format;
    	        }
				
    	    	// Prepend 'Number' field
    	    	$format = array_merge(array('Number' => false), $format);
				
    	    	// Cache format
    	        $this->_overviewFormatCache = $format;
    	    } 
    	    else 
    	    {
    	        $format = $this->_overviewFormatCache;
    	    }
			
    	    // Loop through all articles
            foreach ($overview as $key => $article) 
            {	
            	if (sizeof($format) == sizeof($article))
            	{
            		//Replace overview using $format as keys, $article as values
					$overview[$key] = array_combine(array_keys($format), $article);
					
					// If article prefixed by field name, remove it
					foreach($format as $fkey=>$fval) 
					{
						if ($fval === true) 
						{
							$overview[$key][$fkey] = trim(str_replace($fkey.':', '', $overview[$key][$fkey]));
						}
					}
				}
    	    }
    	}

    	switch (true)
    	{
    	    // Expect one article
    	    case is_null($range);
    	    case is_int($range);
            case is_string($range) && ctype_digit($range):
    	    case is_string($range) && substr($range, 0, 1) == '<' && substr($range, -1, 1) == '>':
    	        if (count($overview) == 0) {
    	    	    return false;
    	    	} else {
    	    	    return reset($overview);
    	    	}
    	    	break;

    	    // Expect multiple articles
    	    default:
    	    	return $overview;
    	}
    }
	
	function cmdXZver($range = NULL)
	{
	    if (is_null($range)) 
	        $command = 'XZVER';
	    else 
	        $command = 'XZVER ' . $range;
	
	    $response = $this->_sendCommand($command);

	    switch ($response) {
	    case 224: // 224, RFC2980: 'Overview information follows'
	        $data = $this->_getTextResponse();

			//de-yenc
			$yenc = new yenc();
			$dec = $yenc->decode(implode("\r\n", $data));
			if ($yenc->error) 
			{
				$this->throwError($yenc->error);
			}
			
			//inflate deflated string
			$data = explode("\r\n", gzinflate($dec));

	        foreach ($data as $key => $value) 
	            $data[$key] = explode("\t", ltrim($value));
	
	        return $data;
	        break;
	    case 412: // 412, RFC2980: 'No news group current selected'
	        $this->throwError("No news group current selected ({$this->_currentStatusResponse()})", $response);
	        break;
	    case 420: // 420, RFC2980: 'No article(s) selected'
	        $this->throwError("No article(s) selected ({$this->_currentStatusResponse()})", $response);
	        break;
	    case 502: // 502 RFC2980: 'no permission'
	        $this->throwError("No permission ({$this->_currentStatusResponse()})", $response);
	        break;
	    case 500: // 500  RFC2980: 'unknown command'
	        $this->throwError("XZver not supported ({$this->_currentStatusResponse()})", $response);
	        break;
	    default:
	        return $this->_handleUnexpectedResponse($response);
	    }
	}
	
}
?>

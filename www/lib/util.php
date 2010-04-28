<?php


	function readablesize($bytes, $dec=1) 
	{
		if($bytes >= 1099511627776) 
		{
			$return = round($bytes / 1024 / 1024 / 1024 / 1024, $dec);
			$suffix = "TB";
		} 
		elseif($bytes >= 1073741824) 
		{
			$return = round($bytes / 1024 / 1024 / 1024, $dec);
			$suffix = "GB";
		} 
		elseif($bytes >= 1048576) 
		{
			$return = round($bytes / 1024 / 1024, $dec);
			$suffix = "MB";
		} 
		elseif($bytes >= 1024) 
		{
			$return = round($bytes / 1024, $dec);
			$suffix = "KB";
		} 
		else 
		{
			$return = $bytes;
			$suffix = "Byte";
		}
		$return = number_format($return,$dec,',','');
		$return .= " ".$suffix;
		return $return;
	}

	function getmicrotime() 
	{
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

	function sec2min($diff) 
	{
		$minsDiff = 0; $secsDiff = 0;
		$sec_in_a_min = 60;
		while($diff >= 60) 
		{
			$minsDiff++;
			$diff -= 60;
		}
		$secsDiff = $diff;
		return ($minsDiff.'m '.$secsDiff.'s)');
	}
	
	
?>
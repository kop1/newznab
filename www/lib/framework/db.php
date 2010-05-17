<?php

require_once($_SERVER['DOCUMENT_ROOT']."/config.php");

class DB
{
	private static $initialized = false;

	function DB()
	{
		if (DB::$initialized === false)
		{
			// initialize db connection
			mysql_pconnect(DB_HOST, DB_USER, DB_PASSWORD)
			or die("[".CODENAME."] fatal error: could not connect to database!");
			
			mysql_select_db(DB_NAME)
			or die("[".CODENAME."] fatal error: could not select database!");
			
			DB::$initialized = true;
		}			
	}	
				
	public function escapeString($str, $allowEmptyString=false)
	{
		if (!$str)
		{
			if($str != 0 && $str != 0.0 && $str != "0")
			{
				return "'".str_replace("'", "''", $str)."'";
			}
			else
			{
				if ($str == "")
				{
					if ($allowEmptyString)
						return "'".str_replace("'", "''", $str)."'";
				}			
			}
			return "NULL";			
		}
		else
		{
			return "'".str_replace("'", "''", $str)."'";
		}
	}		
	
	public function now()
	{
		return "now()";
	}		
	
	public function makeLookupTable($rows, $keycol)
	{
		$arr = array();
		foreach($rows as $row)
			$arr[$row[$keycol]] = $row;			
		return $arr;
	}	
	
	public function queryInsert($query)
	{
		$result = mysql_query($query);
		return mysql_insert_id();
	}
	
	public function queryOneRow($query)
	{
		$rows = $this->query($query);
		
		if (!$rows)
			return false;
		
		if ($rows)
			return $rows[0];
		else
			return $rows;		
	}	
		
	public function query($query)
	{
		$result = mysql_query($query);
		
		if ($result === false || $result === true)
			return $result;
		
		$rows = array();
		
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
			$rows[] = $row;	
		}
		mysql_free_result($result);
		
		return $rows;
	}	
	
	function optimize() 
	{
		$db = new DB();
		echo "Optimizing table: binaries...\n";
		$db->query("OPTIMIZE TABLE binaries");
		echo "Optimizing table: parts...\n";
		$db->query("OPTIMIZE TABLE parts");
		echo "Done\n\n";
	}	
}
?>

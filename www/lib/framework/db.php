<?php

require_once("config.php");

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
		return "'".mysql_real_escape_string($str)."'";
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
	
	public function queryDirect($query)
	{
		$result = mysql_query($query);
		return $result;
	}	

	public function optimise() 
	{
		$ret = array();
		$alltables = $this->query("SHOW TABLES"); 

		foreach ($alltables as $tablename) 
		{
			$ret[] = $tablename[0];
			$this->query("OPTIMIZE TABLE '".$tablename[0]."'"); 
		}
			
		return $ret;
	}	
}
?>

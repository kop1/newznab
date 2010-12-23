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
			or die("fatal error: could not connect to database! Check your config.");
			
			mysql_select_db(DB_NAME)
			or die("fatal error: could not select database! Check your config.");
			
			DB::$initialized = true;
		}			
	}	
				
	public function escapeString($str)
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
	
	public function queryInsert($query, $returnlastid=true)
	{
		$result = mysql_query($query);
		return ($returnlastid) ? mysql_insert_id() : $result;
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
		
	public function query($query, $addtotalcount=false)
	{
		$result = mysql_query($query);
		
		if ($addtotalcount)
		{
			$totalResult = mysql_query("Select FOUND_ROWS()");
			$totalRow = mysql_fetch_array($totalResult);
			$totalRow = $totalRow[0];
		}
		
		if ($result === false || $result === true)
			return array();
		
		$rows = array();

		while ($row = mysql_fetch_assoc($result)) 
		{
			$rows[] = $row;	
			if ($addtotalcount)
				$rows[count($rows)-1]["_totalrows"] = $totalRow;
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
			$ret[] = $tablename['Tables_in_'.DB_NAME];
			$this->queryDirect("REPAIR TABLE `".$tablename['Tables_in_'.DB_NAME]."`"); 
			$this->queryDirect("OPTIMIZE TABLE `".$tablename['Tables_in_'.DB_NAME]."`"); 
		}
			
		return $ret;
	}	
}
?>

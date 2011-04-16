<?php
require_once(WWW_DIR."/lib/framework/db.php");


class Sites
{	
	const REGISTER_STATUS_OPEN = 0;
	const REGISTER_STATUS_INVITE = 1;
	const REGISTER_STATUS_CLOSED = 2;

	public function version()
	{
		return "0.2.3";
	}
	
	public function update($form)
	{		
		$db = new DB();
		$site = $this->row2Object($form);

		if (substr($site->nzbpath, strlen($site->nzbpath) - 1) != '/')
			$site->nzbpath = $site->nzbpath."/";
		
		$sql = $sqlKeys = array();
		foreach($form as $settingK=>$settingV)
		{
			$sql[] = sprintf("WHEN %s THEN %s", $db->escapeString($settingK), $db->escapeString($settingV));
			$sqlKeys[] = $db->escapeString($settingK);
		}
		
		$db->query(sprintf("UPDATE site SET value = CASE setting %s END WHERE setting IN (%s)", implode(' ', $sql), implode(', ', $sqlKeys)));	
		
		return $site;
	}	

	public function get()
	{			
		$db = new DB();
		$rows = $db->query("select * from site");			

		if ($rows === false)
			return false;
		
		return $this->rows2Object($rows);
	}
		
	public function rows2Object($rows)
	{
		$obj = new stdClass;
		foreach($rows as $row)
			$obj->{$row['setting']} = $row['value'];
		
		return $obj;
	}
	
	public function row2Object($row)
	{
		$obj = new stdClass;
		$rowKeys = array_keys($row);
		foreach($rowKeys as $key)
			$obj->{$key} = $row[$key];
		
		return $obj;
	}
	
	public function updateLatestRegexRevision($rev)
	{
		$db = new DB();
		return $db->query(sprintf("update site set value = %d where setting = 'latestregexrevision'", $rev));
	}
	
	public function getLicense($html=false)
	{
		$n = "\r\n";
		if ($html)
			$n = "<br/>";
	
		return $n."newznab ".$this->version()." Copyright (C) ".date("Y")." newznab.com".$n."

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.".$n."

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
".$n;
	}
}
?>

<?php

require_once('config.php');

//
// find the item for a reqid/type
//

$type = "";
$reqid = "";

if (isset($GET["t"])
	$type = $GET["t"];

if (isset($GET["reqid"])
	$reqid = $GET["reqid"];

$result = mysql_query("select * from release where reqid = '".mysql_real_escape_string($str)."' and type = '".mysql_real_escape_string($str)."'");	

$ret = "";
while ($row = mysql_fetch_array($result, MYSQL_BOTH)) 
{
	//
	// build metadata about the item(s)
	//
	$ret.="item";
}

echo $ret;

?>
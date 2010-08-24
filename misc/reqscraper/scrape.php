<?php

require_once('magpierss/rss_fetch.inc');
require_once('config.php');

//
// retrieve a list of feeds to be scraped
//
$result = mysql_query("select * from feed where status=1");	
while ($row = mysql_fetch_array($result, MYSQL_BOTH)) 
{
	$rss = fetch_rss($row["url"]);

	//
	// scrape every item into a database table
	//
	foreach ($rss->items as $item) 
	{
		$link = mysql_real_escape_string($item['link']);
		$description = mysql_real_escape_string($item['description']);	
		$feedID = $row["ID"];
		$pubdate = date("Y-m-d H:i:s", strtotime($item['pubdate']));
		$guid = mysql_real_escape_string($item['guid']);	

		//
		// store 'specific stuff' like parsed reqids by regexing
		//
		$reqid = 0;
		$matches = "";
		if (preg_match($row["reqidregex"], $row[$row["reqidtitle"]], $matches))
			$reqid = mysql_real_escape_string($matches["reqid"]);	

		$title = "";
		if (preg_match($row["titleregex"], $row[$row["titlecol"]], $matches))
			$title = mysql_real_escape_string($matches["title"]);	

		$res = mysql_query("insert into item (feedID, reqid, title, link, description, pubdate, guid, adddate) values ($feedID, '$reqid', '$title', '$link', '$description', '$pubdate', '$guid', now())");	
	}
}

?>
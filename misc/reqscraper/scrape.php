<?php

require_once('magpierss/rss_fetch.inc');
require_once('config.php');

//
// retrieve a list of feeds to be scraped
//
$result = mysql_query("select * from feed where status=1");	
while ($row = mysql_fetch_assoc($result)) 
{
	$rss = fetch_rss($row["url"]);

	//
	// scrape every item into a database table
	//
	foreach ($rss->items as $item) 
	{
		$link = mysql_real_escape_string($item['link']);
		
		if (isset($item['description']))
			$description = mysql_real_escape_string($item['description']);	
		elseif (isset($item['summary']))
			$description = mysql_real_escape_string($item['description']);	
		else
			$description = "";
			
		$feedID = $row["ID"];
		
		if (isset($item['pubdate']))
			$pubdate = date("Y-m-d H:i:s", strtotime($item['pubdate']));
		elseif (isset($item["dc"]) && isset($item["dc"]["date"]))
			$pubdate = date("Y-m-d H:i:s", strtotime($item["dc"]["date"]));
		else
			$pubdate = date("Y-m-d H:i:s");
		
		//
		// store 'specific stuff' like parsed reqids by regexing
		//
		$reqid = 0;
		$matches = "";
		if (preg_match($row["reqidregex"], $item[$row["reqidcol"]], $matches))
			$reqid = mysql_real_escape_string($matches["reqid"]);	

		$title = "";
		if (preg_match($row["titleregex"], $item[$row["titlecol"]], $matches))
			$title = mysql_real_escape_string($matches["title"]);	

		if (isset($item['guid']))
			$guid = mysql_real_escape_string($item['guid']);	
		else
		{
			if ($title != "" && $reqid != 0)
				$guid = md5($reqid.$title);
			else
				$guid = md5(uniqid());	
		}	
			
		$res = mysql_query("insert into item (feedID, reqid, title, link, description, pubdate, guid, adddate) values ($feedID, '$reqid', '$title', '$link', '$description', '$pubdate', '$guid', now())");	
	}
}

?>
<?php

require_once('magpierss/rss_fetch.inc');
require_once('config.php');

//
// retrieve a list of feeds to be scraped
//
$rss = fetch_rss("http://abteevee.allfilled.com/rss.php");

//
// scrape every item into a database table
//

//
// store 'specific stuff' like parsed reqids by regexing
//


?>
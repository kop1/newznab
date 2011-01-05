<?php

if (!$users->isLoggedIn())
	$page->show403();

if (!isset($_COOKIE['sabnzbd_'.$users->currentUserId().'__host']))
	$page->show403();

if (!isset($_COOKIE['sabnzbd_'.$users->currentUserId().'__apikey']))
	$page->show403();
	
$server = $_COOKIE['sabnzbd_'.$users->currentUserId().'__host'];
$key = $_COOKIE['sabnzbd_'.$users->currentUserId().'__apikey'];

$json = file_get_contents($server."api/?mode=qstatus&output=json&apikey=".$key);
$obj = json_decode($json);

$queue = $obj->{'jobs'};

$count = 1;

if (count($queue) > 0)
{
	print "<table class=\"data\">";
	print "<tr><th></th><th></th><th style='width:80px;text-align:right;'>size (mb)</th><th style='width:80px;text-align:right;'>left (mb)</th><th style='width:50px;text-align:right;'></th></tr>";
	foreach ($queue as $item)
	{
		if (strpos($item->{'filename'}, "fetch NZB") > 0)
		{
		}
		else
		{
			print "<tr>";
			print "<td style='text-align:right;padding-right:10px;'>".$count.")</td>";
			print "<td>".$item->{'filename'}."</td>";
			print "<td style='text-align:right;'>".number_format(round($item->{'mb'}))."</td>";
			if ($count ==1)
			{
				print "<td class='right'>".number_format(round($item->{'mbleft'}))."</td>";
				print "<td class='right'>".round($item->{'mbleft'}/$item->{'mb'}*100)."%</td>";
			}
			else
			{
				print "<td></td><td></td>";
			}
			print "</tr>";
			$count++;
		}
	}
	print "</table>";
}
else
{
	print "<p>The queue is currently empty.</p>";
}

?>
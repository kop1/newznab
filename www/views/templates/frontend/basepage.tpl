<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="{$page->meta_keywords},{$site->meta_keywords}" />
	<meta name="description" content="{$page->meta_description} - {$site->meta_description}" />	
	<meta name="newznab_version" content="{$site->version}" />
	<title>{$page->meta_title} - {$site->meta_title}</title>
	<link href="{$smarty.const.WWW_TOP}/views/styles/style.css" rel="stylesheet" type="text/css" media="screen" />
	{if $site->google_adsense_acc == ''}
<link href="{$smarty.const.WWW_TOP}/views/styles/style_noadsense.css" rel="stylesheet" type="text/css" media="screen" />
{else}
<link href="http://www.google.com/cse/api/branding.css" rel="stylesheet" type="text/css" media="screen" />
	{/if}
	<link rel="shortcut icon" type="image/ico" href="{$smarty.const.WWW_TOP}/views/images/favicon.ico"/>
	<script type="text/javascript" src="{$smarty.const.WWW_TOP}/views/scripts/jquery.js"></script>
	<script type="text/javascript" src="{$smarty.const.WWW_TOP}/views/scripts/utils.js"></script>
	<script type="text/javascript" src="{$smarty.const.WWW_TOP}/views/scripts/functions_sorttable.js"></script>
	{if $site->style != "" && $site->style != "/"}<link href="{$smarty.const.WWW_TOP}/views/themes/{$site->style}/style.css" rel="stylesheet" type="text/css" media="screen" />
	{/if}

	<script type="text/javascript">
		var WWW_TOP = "{$smarty.const.WWW_TOP}";
		var SERVERROOT = "{$serverroot}";
		var UID = "{$userdata.ID}";
		var RSSTOKEN = "{$userdata.rsstoken}";
	</script>
	{$page->head}
</head>
<body {$page->body}>

	<div id="statusbar">
		{if $loggedin=="true"}
			Welcome back <a href="{$smarty.const.WWW_TOP}/profile">{$userdata.username}</a>. <a href="{$smarty.const.WWW_TOP}/logout">Logout</a>
		{else}
			<a href="{$smarty.const.WWW_TOP}/login">Login</a> or <a href="{$smarty.const.WWW_TOP}/register">Register</a>
		{/if}
	</div>

	<div id="logo">
		<a title="{$site->title} Logo" href="{$smarty.const.WWW_TOP}/"><img alt="{$site->title} Logo" src="{$smarty.const.WWW_TOP}/views/images/clearlogo.png" /></a>
		<h1><a href="{$smarty.const.WWW_TOP}/">{$site->title}</a></h1>
		<p><em>{$site->strapline}</em></p>
	</div>
	<hr />
	
	<div id="header">

		<div id="menu"> 

			{if $loggedin=="true"}
				{$header_menu}
			{/if}
				
			{if 1==2 && $site->google_adsense_acc != '' && $site->google_adsense_menu != ''}
			{literal}
				<script type="text/javascript">
						<!--
						google_ad_client = "{/literal}{$site->google_adsense_acc}{literal}";
						google_ad_slot = "{/literal}{$site->google_adsense_menu}{literal}";
						google_ad_width = 728;
						google_ad_height = 15;
						//-->
						</script>
						<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
				</script>
			{/literal}
			{/if}
						
		</div> 
		<!-- end #menu --> 
	</div>
	
	
	<div id="page">

	<div id="adpanel">
			&nbsp;
			{if $site->google_adsense_acc != '' && $site->google_adsense_sidepanel != ''}
			{literal}
		
				<script type="text/javascript"><!--
				google_ad_client = "{/literal}{$site->google_adsense_acc}{literal}";
				google_ad_slot = "{/literal}{$site->google_adsense_sidepanel}{literal}";
				google_ad_width = 160;
				google_ad_height = 600;
				//-->
				</script>
				<script type="text/javascript"
				src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
				</script>
		
			{/literal}
			{/if}
		
		</div>

		<div id="content">
			{$page->content}
		</div>
		<!-- end #content -->

		<div id="sidebar">
		<ul>		
		<li>
			<h2>Menu</h2> 
			
			<ul>
			{if $loggedin=="true"}
				<li><a title="Search for Nzbs" href="{$smarty.const.WWW_TOP}/search">Search</a></li>
				<li><a title="Browse for Nzbs" href="{$smarty.const.WWW_TOP}/browse">Browse</a></li>
				<li><a title="Search for individual files" href="{$smarty.const.WWW_TOP}/searchraw">Raw Search</a></li>
				<li><a title="Browse for Movies" href="{$smarty.const.WWW_TOP}/movies">Movies</a></li>
				{if $isadmin=="true"}
					<li><a title="Admin" href="{$smarty.const.WWW_TOP}/admin/">Admin</a></li>
				{/if}
				<li><a title="Your Nzb Cart" href="{$smarty.const.WWW_TOP}/cart">My Cart</a></li>
				<li><a title="View your Profile " href="{$smarty.const.WWW_TOP}/profile">Profile</a></li>
				{if $sabintegrated=="true"}
					<li><a title="Your Sabnzbd Queue" href="{$smarty.const.WWW_TOP}/queue">Download Queue</a></li>
				{/if}
				<li><a title="Logout" href="{$smarty.const.WWW_TOP}/logout">Logout</a></li>
			{else}
				<li><a title="Login" href="{$smarty.const.WWW_TOP}/login">Login</a></li>
				<li><a title="Register" href="{$smarty.const.WWW_TOP}/register">Register</a></li>
			{/if}
			</ul>
		</li>

		{$article_menu}

		{$useful_menu}
		
		{if $site->google_adsense_acc != '' && $site->google_adsense_search != ''}
		{literal}
			<li>
			<h2>Search for {/literal}{$site->term_plural}{literal}</h2> 
			<div style="padding-left:20px;">
				<div class="cse-branding-bottom" style="background-color:#FFFFFF;color:#000000">
				  <div class="cse-branding-form">
				    <form action="http://www.google.co.uk/cse" id="cse-search-box" target="_blank">
				      <div>
				        <input type="hidden" name="cx" value="partner-{/literal}{$site->google_adsense_acc}{literal}:{/literal}{$site->google_adsense_search}{literal}" />
				        <input type="hidden" name="ie" value="ISO-8859-1" />
				        <input type="text" name="q" size="10" />
				        <input type="submit" name="sa" value="Search" />
				      </div>
				    </form>
				  </div>
				  <div class="cse-branding-logo">
				    <img src="http://www.google.com/images/poweredby_transparent/poweredby_FFFFFF.gif" alt="Google" />
				  </div>
				  <div class="cse-branding-text">
				    Custom Search
				  </div>
				</div>
			</div>
			</li>		
		{/literal}
		{/if}
		
		<li>
			<a title="Sickbeard - The ultimate usenet PVR" href="http://www.sickbeard.com/"><img class="menupic" alt="Sickbeard - The ultimate usenet PVR" src="{$smarty.const.WWW_TOP}/views/images/sickbeard.png" /></a>
		</li>
		<li>
			<a title="CouchPotato - Automatic movie downloader" href="http://www.couchpotatoapp.com/"><img style="padding-left:30px;"  class="menupic" alt="CouchPotato - Automatic Movie Downloader" src="{$smarty.const.WWW_TOP}/views/images/couchpotato.png" /></a>
		</li>
		<li>
			<a title="Sabznbd - A great usenet binary downloader" href="http://www.sabnzbd.org/"><img class="menupic" alt="Sabznbd - A great usenet binary downloader" src="{$smarty.const.WWW_TOP}/views/images/sabnzbd.png" /></a>
		</li>
		
		</ul>
		</div>
		<!-- end #sidebar -->
	
		<div  style="clear: both;text-align:right;padding-bottom:10px;">
			<a class="w3validator" href="http://validator.w3.org/check?uri=referer">
			<img src="{$smarty.const.WWW_TOP}/views/images/valid-xhtml10.png" alt="Valid XHTML 1.0 Transitional" height="31" width="88" />
			</a> 
		</div>

		 
		
	</div>
	<!-- end #page -->

	<div class="footer">
	<p>
		{$site->footer}
		<br /><br /><br /><a href="http://www.newznab.com/">Newznab</a> is released under GPL. All rights reserved {$smarty.now|date_format:"%Y"}. <br/><a href="{$smarty.const.WWW_TOP}/terms-and-conditions.php">{$site->title} Terms and Conditions</a>
	</p>
	</div>
	<!-- end #footer -->
	
	{if $site->google_analytics_acc != ''}
	{literal}
	<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	try {	
	var pageTracker = _gat._getTracker("{/literal}{$site->google_analytics_acc}{literal}");	
	pageTracker._trackPageview();
	} catch(err) {}</script>
	{/literal}
	{/if}
	
</body>
</html>

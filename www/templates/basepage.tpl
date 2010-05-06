<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<meta name="keywords" content="{$page->meta_keywords},{$site->meta_keywords}" />
	<meta name="description" content="{$page->meta_description} - {$site->meta_description}" />	
	<title>{$page->meta_title} - {$site->meta_title}</title>
	<link href="/style.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="/includes/utils.js"></script>
	<link href="http://www.google.com/cse/api/branding.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$scheme}{$smarty.server.SERVER_NAME}{$port}/rss" rel="alternate" type="application/rss+xml" title="{$site->title} RSS Feed" />
	{$page->head}
</head>
<body>

	<div id="logo">
		<a title="{$site->title} Logo" href="{$scheme}{$smarty.server.SERVER_NAME}{$port}/"><img alt="{$site->title} Logo" src="/images/banner.jpg" /></a>
		<h1><a href="/">{$site->title}</a></h1>
		<p><em>{$site->strapline}</em></p>
	</div>
	<hr />
	
	<div id="header">
		<div id="menu"> 

	{if $google_adsense_acc != '' && $site->google_adsense_menu != ''}
	{literal}
		<script type="text/javascript">
				<!--
				google_ad_client = "{/literal}{$google_adsense_acc}{literal}";
				/* 728x15, created 2/13/10 */
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
			{if $google_adsense_acc != '' && $site->google_adsense_sidepanel != ''}
			{literal}
		
				<script type="text/javascript"><!--
				google_ad_client = "{/literal}{$google_adsense_acc}{literal}";
				/* pbp_sidepanel */
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
				<li><a href="/login">Login</a></li>
				<li><a href="/search">Search</a></li>
				<li><a href="/browse">Browse</a></li>
			</ul>
		</li>
		<li>
		{$article_menu}
		</li>		
		<li>
		{$useful_menu}
		</li>		

		{if $google_adsense_acc != '' && $site->google_adsense_search != ''}
		{literal}
			<li>
			<h2>Search for {/literal}{$site->term_plural}{literal}</h2> 
			<div style="padding-left:20px;">
				<div class="cse-branding-bottom" style="background-color:#FFFFFF;color:#000000">
				  <div class="cse-branding-form">
				    <form action="http://www.google.co.uk/cse" id="cse-search-box" target="_blank">
				      <div>
				        <input type="hidden" name="cx" value="partner-{/literal}{$google_adsense_acc}{literal}:{/literal}{$site->google_adsense_search}{literal}" />
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
		
		</ul>
		</div>
		<!-- end #sidebar -->
	
		<div style="clear: both;text-align:right;padding-bottom:10px;">
			<a href="http://validator.w3.org/check?uri=referer">
			<img src="/images/valid-xhtml10.png" alt="Valid XHTML 1.0 Transitional" height="31" width="88" />
			</a>
		</div>

		
		
	</div>
	<!-- end #page -->

	<div id="footer">
	<p>
		{$site->footer}
		<br /><br /><br />Copyright &copy; {$smarty.now|date_format:"%Y"} {$site->title}. All rights reserved. <br/><a href="{$scheme}{$smarty.server.SERVER_NAME}{$port}/terms-and-conditions.php">Terms and Conditions</a>
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

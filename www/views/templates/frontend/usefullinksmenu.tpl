<li>
		<h2>Useful Links</h2> 
		<ul>
		<li><a title="Contact Us" href="{$smarty.const.WWW_TOP}/contact-us.php">Contact Us</a></li>
		<li><a title="Site Map" href="{$smarty.const.WWW_TOP}/sitemap.php">Site Map</a></li>
		{if $loggedin=="true"}
		<li><a title="{$site->title} Rss Feeds" href="{$smarty.const.WWW_TOP}/rss">Rss Feeds</a></li>
		<li><a title="{$site->title} Api" href="{$smarty.const.WWW_TOP}/apihelp">Api</a></li>
		{/if}
		{foreach from=$usefulcontentlist item=content}
			<li><a title="{$content->title}" href="{$smarty.const.WWW_TOP}{$content->url}c{$content->id}">{$content->title}</a></li>
		{/foreach}
		</ul>
</li>
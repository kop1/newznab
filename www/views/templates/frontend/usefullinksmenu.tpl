<li class="menu_useful"> 
		<h2>Useful Links</h2> 
		<ul>
		<li onclick=""><a title="Contact Us" href="{$smarty.const.WWW_TOP}/contact-us">Contact Us</a></li>
		<li onclick="document.location='{$smarty.const.WWW_TOP}/sitemap'"><a title="Site Map" href="{$smarty.const.WWW_TOP}/sitemap">Site Map</a></li>
		{if $loggedin=="true"}
		<li onclick="document.location='{$smarty.const.WWW_TOP}/rss'"><a title="{$site->title} Rss Feeds" href="{$smarty.const.WWW_TOP}/rss">Rss Feeds</a></li>
		<li onclick="document.location='{$smarty.const.WWW_TOP}/apihelp'"><a title="{$site->title} Api" href="{$smarty.const.WWW_TOP}/apihelp">Api</a></li>
		{/if}
		{foreach from=$usefulcontentlist item=content}
			<li onclick="document.location='{$smarty.const.WWW_TOP}/content/{$content->id}{$content->url}'"><a title="{$content->title}" href="{$smarty.const.WWW_TOP}/content/{$content->id}{$content->url}">{$content->title}</a></li>
		{/foreach}
		</ul>
</li>
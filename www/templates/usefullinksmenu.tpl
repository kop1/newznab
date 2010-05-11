		<h2>Useful Links</h2> 
		<ul>
		<li><a title="Contact Us" href="{$scheme}{$smarty.server.SERVER_NAME}{$port}/contact-us.php">Contact Us</a></li>
		<li><a title="Site Map" href="{$scheme}{$smarty.server.SERVER_NAME}{$port}/sitemap.php">Site Map</a></li>
		{if $loggedin=="true"}
		<li><a title="{$site->title} Rss Feeds" href="{$scheme}{$smarty.server.SERVER_NAME}{$port}/rss">Rss Feeds</a></li>
		{/if}
		{foreach from=$usefulcontentlist item=content}
			<li><a title="{$content->title}" href="{$scheme}{$smarty.server.SERVER_NAME}{$port}{$content->url}c{$content->id}">{$content->title}</a></li>
		{/foreach}
		</ul>
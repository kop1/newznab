		<h2>Useful Links</h2> 
		<ul>
		<li><a title="Contact Us" href="http://{$smarty.server.SERVER_NAME}/contact-us.php">Contact Us</a></li>
		<li><a title="Site Map" href="http://{$smarty.server.SERVER_NAME}/sitemap.php">Site Map</a></li>
		<li><a target="_blank" title="{$site->title} Rss Feed" href="http://{$smarty.server.SERVER_NAME}/rss">Rss Feed</a></li>
		{foreach from=$usefulcontentlist item=content}
			<li><a title="{$content->title}" href="http://{$smarty.server.SERVER_NAME}{$content->url}c{$content->id}">{$content->title}</a></li>
		{/foreach}
		</ul>
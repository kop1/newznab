{if $articlecontentlist|@count > 0}

		<h2>Articles</h2> 
		<ul>
		{foreach from=$articlecontentlist item=content}
			<li><a title="{$content->title}" href="http://{$smarty.server.SERVER_NAME}{$content->url}c{$content->id}">{$content->title}</a></li>
		{/foreach}
		</ul>

{/if}
{if $articlecontentlist|@count > 0}

		<h2>Articles</h2> 
		<ul>
		{foreach from=$articlecontentlist item=content}
			<li><a title="{$content->title}" href="{$scheme}{$smarty.server.SERVER_NAME}{$port}{$content->url}c{$content->id}">{$content->title}</a></li>
		{/foreach}
		</ul>

{/if}
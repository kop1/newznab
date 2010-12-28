{if $articlecontentlist|@count > 0}
<li class="menu_articles">
		<h2>Articles</h2> 
		<ul>
		{foreach from=$articlecontentlist item=content}
			<li onclick="document.location='{$smarty.const.WWW_TOP}{$content->url}c{$content->id}'"><a title="{$content->title}" href="{$smarty.const.WWW_TOP}{$content->url}c{$content->id}">{$content->title}</a></li>
		{/foreach}
		</ul>
</li>
{/if}

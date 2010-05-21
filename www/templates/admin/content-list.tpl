
<h1>{$page->title}</h1>

<table class="data Sortable highlight">

	<tr>
		<th width="60">ordinal</th>
		<th width="30">id</th>
		<th>title</th>
		<th>url</th>
		<th>type</th>
		<th>status</th>
		<th>in menu</th>
		<th>body</th>
		<th>options</th>
	</tr>

	
	{foreach from=$contentlist item=content}
	<tr>
		<td>{$content->ordinal}</td>
		<td>{$content->id}</td>
		<td><a href="content-add.php?id={$content->id}" title="Edit {$content->title}">{$content->title}</a></td>
		<td><a title="Preview in new window" href="{$content->url}c{$content->id}" target="null">{$content->url}c{$content->id}</a></td>
		<td>
			{if $content->contenttype == "1"}
				Useful Link
			{elseif $content->contenttype == "2"}
				Article
			{elseif $content->contenttype == "3"}
				Homepage
			{/if}
		</td>
		<td>
			{if $content->status == "1"}
				Enabled
			{else}
				Disabled
			{/if}
		</td>
		<td style="width:50px;">
			{if $content->showinmenu == "1"}
				Yes
			{else}
				No
			{/if}
		</td>
		<td title="{$content->body|escape:'htmlall'}">{$content->body|truncate:100|escape:'htmlall'}</td>
		<td><a onclick="return confirm('Are you sure?');" href="content-delete.php?id={$content->id}">delete</a></td>
	</tr>
	{/foreach}


</table>

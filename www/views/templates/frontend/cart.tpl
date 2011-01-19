
<h1>My Cart</h1>

<p>
Your cart can be downloaded as an <a href="{$smarty.const.WWW_TOP}/rss?t=-2&amp;dl=1&amp;i={$userdata.ID}&amp;r={$userdata.rsstoken}&amp;del=1">Rss Feed</a>.
</p>

{if $results|@count > 0}

<table style="width:100%;" class="data Sortable highlight">
	<tr>
		<th>name</th>
		<th>added</th>
		<th>options</th>
	</tr>

	{foreach from=$results item=result}
		<tr class="{cycle values=",alt"}">
			<td>
				<a title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/{$result.searchname|escape:"htmlall"}">{$result.searchname|escape:"htmlall"|wordwrap:75:"\n":true}</a>
			</td>
			<td class="less" title="Added on {$result.createddate}">{$result.createddate|date_format}</td>
			<td><a title="Delete from your cart" href="?delete={$result.ID}">delete</a></td>
		</tr>
	{/foreach}
	
</table>
{/if}
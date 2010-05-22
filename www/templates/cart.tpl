
<h1>My Cart</h1>

<p>
Your cart can be downloaded as an <a href="{$smarty.const.WWW_TOP}/rss">rss feed</a>
</p>

{if $results|@count > 0}

<table style="width:100%;margin-top:40px;" class="data Sortable highlight">
	<tr>
		<th>name</th>
		<th>added</th>
		<th>options</th>
	</tr>

	{foreach from=$results item=result}
		<tr>
			<td>
				<a title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}">{$result.searchname|escape:"htmlall"|wordwrap:75:"\n":true}</a>
			</td>
			<td class="less" title="{$result.createddate}">{$result.createddate|date_format}</td>
			<td><a href="?delete={$result.ID}">delete</a></td>
		</tr>
	{/foreach}
	
</table>
{/if}
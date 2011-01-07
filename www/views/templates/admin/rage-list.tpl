<h1>{$page->title}</h1> 

{if $tvragelist}
{$pager}

<table style="margin-top:10px;" class="data Sortable highlight">

	<tr>
		<th>rageid</th>
		<th>title</th>
		<th>date</th>
		<th>options</th>
	</tr>
	
	{foreach from=$tvragelist item=tvrage}
	<tr class="{cycle values=",alt"}">
		<td class="less"><a href="http://www.tvrage.com/shows/id-{$tvrage.rageID}" title="View in TvRage">{$tvrage.rageID}</a></td>
		<td><a title="Edit" href="{$smarty.const.WWW_TOP}/rage-edit.php?id={$tvrage.ID}">{$tvrage.releasetitle|escape:"htmlall"}</a></td>
		<td class="less">{$tvrage.createddate|date_format}</td>
		<td><a href="{$smarty.const.WWW_TOP}/rage-delete.php?id={$tvrage.ID}">delete</a> | <a title="remove this rageid from all releases" href="{$smarty.const.WWW_TOP}/rage-remove.php?id={$tvrage.rageID}">remove</a></td>
	</tr>
	{/foreach}

</table>
{else}
<p>No TVRage episodes available.</p>
{/if}

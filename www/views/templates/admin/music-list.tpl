<h1>{$page->title}</h1> 

{if $musiclist}
{$pager}

<table style="margin-top:10px;" class="data Sortable highlight">

	<tr>
		<th>ID</th>
		<th>Title</th>
		<th>Artist</th>
		<th>Created</th>
		<th></th>
	</tr>
	
	{foreach from=$musiclist item=music}
	<tr class="{cycle values=",alt"}">
		<td class="less">{$music.ID}</td>
		<td><a title="Edit" href="{$smarty.const.WWW_TOP}/music-edit.php?id={$music.ID}">{$music.title} ({$music.year})</a></td>
		<td class="less">{$music.artist}</td>
		<td class="less">{$movie.createddate|date_format}</td>
		<td class="less"><a title="Update" href="{$smarty.const.WWW_TOP}/music-add.php?id={$music.ID}">Update</a></td>
	</tr>
	{/foreach}

</table>
{else}
<p>No Music available.</p>
{/if}

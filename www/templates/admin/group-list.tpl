
<h1>{$page->title}</h1>

<table class="data Sortable">

	<tr>
		<th>group</th>
		<th>category</th>
		<th>last record</th>
		<th>last updated</th>
		<th>active</th>
		<th>releases</th>
	</tr>
	
	{foreach from=$grouplist item=group}
	<tr>
		<td><a href="group-edit.php?id={$group.ID}">{$group.name|replace:"alt.binaries":"a.b"}</a></td>
		<td class="less">{$group.category_name}</td>
		<td class="less">{$group.last_record}</td>
		<td class="less">{$group.last_updated}</td>
		<td class="less">{if $group.active=="1"}Yes{else}No{/if}</td>
		<td class="less">{$group.num_releases}</td>
	</tr>
	{/foreach}

</table>
		
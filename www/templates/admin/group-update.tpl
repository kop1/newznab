
<h1>{$page->title}</h1>

<table class="data Sortable">

	<tr>
		<th>group</th>
		<th>msg</th>
	</tr>
	
	{foreach from=$groupmsglist item=group}
	<tr>
		<td>{$group.group}</td>
		<td>{$group.msg}</td>
	</tr>
	{/foreach}

</table>
		
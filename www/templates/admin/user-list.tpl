
<h1>{$page->title}</h1>

{$pager}

<table style="margin-top:10px;" class="data Sortable highlight">

	<tr>
		<th>name</th>
		<th>email</th>
		<th>host</th>
		<th>join date</th>
		<th>grabs</th>
		<th>options</th>
	</tr>

	
	{foreach from=$userlist item=user}
	<tr>
		<td><a href="user-edit.php?id={$user.ID}">{$user.username}</a></td>
		<td>{$user.email}</td>
		<td>{$user.host}</td>
		<td>{$user.createddate|date_format}</td>
		<td>{$user.grabs}</td>
		<td>{if $user.role=="1"}<a onclick="return confirm('Are you sure?');" href="user-delete.php?id={$user.ID}">delete</a>{/if}</td>
	</tr>
	{/foreach}


</table>
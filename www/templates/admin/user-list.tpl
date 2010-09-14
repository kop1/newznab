
<h1>{$page->title}</h1>

{$pager}

<table style="margin-top:10px;" class="data Sortable highlight">

	<tr>
		<th>name</th>
		<th>email</th>
		<th>host</th>
		<th>join date</th>
		<th>last login</th>
		<th>grabs</th>
		<th>role</th>
		<th>options</th>
	</tr>

	
	{foreach from=$userlist item=user}
	<tr class="{cycle values=",alt"}">
		<td><a href="{$smarty.const.WWW_TOP}/user-edit.php?id={$user.ID}">{$user.username}</a></td>
		<td>{$user.email}</td>
		<td>{$user.host}</td>
		<td>{$user.createddate|date_format}</td>
		<td>{$user.lastlogin|date_format}</td>
		<td>{$user.grabs}</td>
		<td>{if $user.role=="1"}User{elseif $user.role=="2"}Admin{elseif $user.role=="3"}Disabled{else}Unknown{/if}</td>
		<td>{if $user.role!="2"}<a class="confirm_action" href="{$smarty.const.WWW_TOP}/user-delete.php?id={$user.ID}">delete</a>{/if}</td>
	</tr>
	{/foreach}


</table>
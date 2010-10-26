
<h1>{$page->title}</h1>

{$pager}

<table style="margin-top:10px;" class="data highlight">

	<tr>
		<th>name<br/><a title="Sort Descending" href="{$orderbyusername_desc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbyusername_asc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>email<br/><a title="Sort Descending" href="{$orderbyemail_desc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbyemail_asc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>host<br/><a title="Sort Descending" href="{$orderbyhost_desc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbyhost_asc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>join date<br/><a title="Sort Descending" href="{$orderbycreateddate_desc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbycreateddate_asc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>last login<br/><a title="Sort Descending" href="{$orderbylastlogin_desc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbylastlogin_asc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>grabs<br/><a title="Sort Descending" href="{$orderbygrabs_desc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbygrabs_asc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>invites</th>
		<th>role<br/><a title="Sort Descending" href="{$orderbyrole_desc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbyrole_asc}"><img src="{$smarty.const.WWW_TOP}/../views/images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>options</th>
	</tr>

	
	{foreach from=$userlist item=user}
	<tr class="{cycle values=",alt"}">
		<td><a href="{$smarty.const.WWW_TOP}/user-edit.php?id={$user.ID}">{$user.username}</a></td>
		<td>{$user.email}</td>
		<td>{$user.host}</td>
		<td title="{$user.createddate}">{$user.createddate|date_format}</td>
		<td title="{$user.lastlogin}">{$user.lastlogin|date_format}</td>
		<td>{$user.grabs}</td>
		<td>{$user.invites}</td>
		<td>{if $user.role=="1"}User{elseif $user.role=="2"}Admin{elseif $user.role=="3"}Disabled{else}Unknown{/if}</td>
		<td>{if $user.role!="2"}<a class="confirm_action" href="{$smarty.const.WWW_TOP}/user-delete.php?id={$user.ID}">delete</a>{/if}</td>
	</tr>
	{/foreach}


</table>
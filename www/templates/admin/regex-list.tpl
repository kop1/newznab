
<h1>{$page->title}</h1>

<p>
	Regexs are applied to group message subjects into releases. The capture groups are named to hold the release name and number of parts.
	They are applied to messages from that group in order, then any general regexs are applied in order afterwards.
</p>
<p>
	If you want to apply a regex to a group and all its children then append an asterix a.b.blah* to the end. 
</p>

<table style="margin-top:10px;" class="data Sortable highlight">

	<tr>
		<th style="width:20px;">id</th>
		<th>group</th>
		<th>regex</th>
		<th>status</th>
		<th>ordinal</th>
		<th style="width:60px;">Order</th>
		<th style="width:75px;">Options</th>
	</tr>
	
	{foreach from=$regexlist item=regex}
	<tr class="{cycle values=",alt"}">
		<td>{$regex.ID}</td>
		<td title="{$regex.description}">{if $regex.groupname==""}all{else}{$regex.groupname|replace:"alt.binaries":"a.b"}{/if}</td>
		<td title="Edit regex"><a href="{$smarty.const.WWW_TOP}/regex-edit.php?id={$regex.ID}">{$regex.regex|escape:html}</a></td>
		<td>{if $regex.status==1}active{else}disabled{/if}</td>
		<td style="text-align:center;">{$regex.ordinal}</td>
		<td><a title="Move up" href="#">up</a> | <a title="Move down" href="#">down</a></td>
		<td><a onclick="return confirm('Are you sure?');" href="#">delete</a>{if $regex.groupname != ""} | <a href="{$smarty.const.WWW_TOP}/regex-test.php?action=submit&groupname={$regex.groupID}&regex={$regex.regex|urlencode}">test</a>{/if}</td>
	</tr>
	{/foreach}


</table>

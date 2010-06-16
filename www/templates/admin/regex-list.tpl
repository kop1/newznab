
<h1>{$page->title}</h1>

<p>
	Regexs are applied to group message subjects into releases. The second capture group must always be the name of the release, and the third should be the number of parts portion.
	They are applied to messages from that group in order, then any general regexs are applied in order afterwards.
</p>


<table style="margin-top:10px;" class="data Sortable highlight">

	<tr>
		<th style="width:20px;">id</th>
		<th>group</th>
		<th>regex</th>
		<th>status</th>
		<th>ordinal</th>
		<th style="width:60px;"></th>
		<th></th>
	</tr>
	
	{foreach from=$regexlist item=regex}
	<tr class="{cycle values=",alt"}">
		<td>{$regex.ID}</td>
		<td>{if $regex.groupname=="zzzz_misc"}all{else}{$regex.groupname|replace:"alt.binaries":"a.b"}{/if}</td>
		<td><a href="{$smarty.const.WWW_TOP}/regex-edit.php?id={$regex.ID}">{$regex.regex}</a></td>
		<td>active</td>
		<td style="text-align:center;">{$regex.ordinal}</td>
		<td><a href="#">up</a> | <a href="#">down</a></td>
		<td><a href="#">delete</a></td>
	</tr>
	{/foreach}


</table>

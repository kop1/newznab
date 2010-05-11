
<h1>{$page->title}</h1>

{$pager}

<table style="margin-top:10px;" class="data Sortable">

	<tr>
		<th>user</th>
		<th>date</th>
		<th>comment</th>
		<th>host</th>
		<th>options</th>
	</tr>

	
	{foreach from=$commentslist item=comment}
	<tr>
		<td><a href="user-edit.php?id={$comment.userID}">{$comment.username}</a></td>
		<td title="{$comment.createddate}">{$comment.createddate|date_format}</td>
		<td>{$comment.text|escape:"htmlall"|nl2br}</td>
		<td>{$comment.host}</td>
		<td><a href="comments-delete.php?id={$comment.ID}">delete</a></td>
	</tr>
	{/foreach}


</table>
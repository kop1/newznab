
<h1>{$release.searchname}</h1>

<table class="data">
	<tr><th>Subject:</th><td>{$release.name}</td></tr>
	<tr><th>Group:</th><td>{$release.group_name|replace:"alt.binaries":"a.b"}</td></tr>
	<tr><th>Category:</th><td>{$release.category_name}</td></tr>
	<tr><th>Size:</th><td>{$release.size|fsize_format:"MB"}</td></tr>
	<tr><th>Grabs:</th><td>{$release.grabs}</td></tr>
	<tr><th>Files:</th><td><a title="View file list" href="/filelist/{$release.guid}">{$release.totalpart}</a></td></tr>
	<tr><th>Poster:</th><td>{$release.fromname}</td></tr>
	<tr><th>Posted:</th><td title="{$release.postdate}">{$release.postdate|date_format}</td></tr>
	<tr><th>Added:</th><td title="{$release.adddate}">{$release.adddate|date_format}</td></tr>
</table>

<div style="padding-top:20px;">
<a title="Download Nzb for {$release.searchname}" href="/download/{$release.searchname}/nzb/{$release.guid}">Download Nzb for {$release.searchname}</a>
</div>

<div class="comments">
	<a id="comments"></a>
	<h2>Comments</h2>
	
	{if $comments|@count > 0}
	
		<table style="margin-bottom:20px;" class="data Sortable">
			<tr>
			<th width="80">User</th>
			<th>Comment</th>
			</tr>
		{foreach from=$comments item=comment}
			<tr>
				<td title="{$comment.createddate}">{$comment.username}<br/>{$comment.createddate|date_format}</td>
				<td>{$comment.text|escape:"htmlall"}</td>
			</tr>
		{/foreach}
		</table>
	
	{/if}
	
	<form method="post">
		<label for="txtAddComment">Add Comment</label>:<br/>
		<textarea name="txtAddComment"></textarea>
		<br/>
		<input type="submit" value="add comment"/>
	</form>

</div>

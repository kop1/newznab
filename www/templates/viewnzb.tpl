
<h1>{$release.searchname|escape:"htmlall"}</h1>

<table class="data">
	<tr><th>Original Subject:</th><td>{$release.name|escape:"htmlall"}</td></tr>
	<tr><th>Group:</th><td title="{$release.group_name}">{$release.group_name|replace:"alt.binaries":"a.b"}</td></tr>
	<tr><th>Category:</th><td><a title="Browse by {$release.category_name}" href="/browse?t={$release.categoryID}">{$release.category_name}</a></td></tr>
	<tr><th>Size:</th><td>{$release.size|fsize_format:"MB"}</td></tr>
	<tr><th>Grabs:</th><td>{$release.grabs} time{if $release.grabs==1}{else}s{/if}</td></tr>
	<tr><th>Files:</th><td><a title="View file list" href="/filelist/{$release.guid}">{$release.totalpart} file{if $release.totalpart==1}{else}s{/if}</a></td></tr>
	{if $release.rageID > 0}
	<tr><th>Tv Info:</th><td><a href="http://www.tvrage.com/shows/id-{$release.rageID}" title="View in TvRage">Rage Id {$release.rageID}</a> ({$release.seriesfull})</td></tr>
	{/if}
	<tr><th>Poster:</th><td>{$release.fromname|escape:"htmlall"}</td></tr>
	<tr><th>Posted:</th><td title="{$release.postdate}">{$release.postdate|date_format}</td></tr>
	<tr><th>Added:</th><td title="{$release.adddate}">{$release.adddate|date_format}</td></tr>
	<tr><th>Download:</th><td><a title="Download Nzb for {$release.searchname|escape:"htmlall"}" href="/download/{$release.searchname|escape:"htmlall"}/nzb/{$release.guid}">Download Nzb for {$release.searchname|escape:"htmlall"}</a></td></tr>
	{if $similars|@count > 1}
	<tr>
		<th>Similar:</th>
		<td>
			{foreach from=$similars item=similar}
				{if $similar.ID != $release.ID}
					<a title="View similar Nzb details" href="/details/{$similar.searchname|escape:"htmlall"}/viewnzb/{$similar.guid}">{$similar.searchname|escape:"htmlall"}</a><br/>
				{/if}
			{/foreach}
		</td>
	</tr>
	{/if}
</table>

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
				<td class="less" title="{$comment.createddate}"><a title="View {$comment.username}'s profile" href="/profile?name={$comment.username}">{$comment.username}</a><br/>{$comment.createddate|date_format}</td>
				<td>{$comment.text|escape:"htmlall"|nl2br}</td>
			</tr>
		{/foreach}
		</table>
	
	{/if}
	
	<form method="post">
		<label for="txtAddComment">Add Comment</label>:<br/>
		<textarea id="txtAddComment" name="txtAddComment"></textarea>
		<br/>
		<input type="submit" value="submit"/>
	</form>

</div>

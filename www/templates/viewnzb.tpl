
<h1>{$release.searchname|escape:"htmlall"}</h1>

<table class="data">
	{if $isadmin}
	<tr><th>Admin Functions:</th><td><a href="{$smarty.const.WWW_TOP}/admin/release-edit.php?id={$release.ID}&amp;from={$smarty.server.REQUEST_URI}" title="Edit Release">[Edit]</a> [Delete]</td></tr>
	{/if}
	<tr><th>Name:</th><td>{$release.name|escape:"htmlall"}</td></tr>
	<tr><th>Group:</th><td title="{$release.group_name}">{$release.group_name|replace:"alt.binaries":"a.b"}</td></tr>
	<tr><th>Category:</th><td><a title="Browse by {$release.category_name}" href="{$smarty.const.WWW_TOP}/browse?t={$release.categoryID}">{$release.category_name}</a></td></tr>
	<tr><th>Size:</th><td>{$release.size|fsize_format:"MB"}</td></tr>
	<tr><th>Grabs:</th><td>{$release.grabs} time{if $release.grabs==1}{else}s{/if}</td></tr>
	<tr><th>Files:</th><td><a title="View file list" href="{$smarty.const.WWW_TOP}/filelist/{$release.guid}">{$release.totalpart} file{if $release.totalpart==1}{else}s{/if}</a></td></tr>
	{if $nfo.ID|@count > 0}
	<tr><th>Nfo:</th><td><a href="{$smarty.const.WWW_TOP}/nfo/{$release.guid}" title="View Nfo">View Nfo</a></td></tr>
	{/if}
	{if $release.rageID > 0}
	<tr><th>Tv Info:</th><td>[<a target="_blank" href="{$site->dereferrer_link}http://www.tvrage.com/shows/id-{$release.rageID}" title="View in TvRage"><div style="padding-right:5px;" class="icon icon_tvrage"></div>Rage Id {$release.rageID}</a>] [<a title="View series info" href="{$smarty.const.WWW_TOP}/series/{$release.rageID}">Series Info</a>] [{$release.seriesfull}]</td></tr>
	{/if}
	
	{if $movie.imdbID > 0}
	<tr><th>Movie Info:</th><td>
		{if $movie.cover == 1}<img src="{$smarty.const.WWW_TOP}/images/covers/{$movie.imdbID}-cover.jpg" alt="{$movie.title}" height="140" align="left" hspace="10" />{/if}
		<strong>{$movie.title} ({$movie.year})</strong>
		{if $movie.plot != ''}<br />{$movie.plot}{/if}
		<br /><br /><strong>Rating:</strong> {if $movie.rating == ''}N/A{/if}{$movie.rating}/10
		<br /><strong>Genre:</strong>{$movie.genre}
		<br /><strong>More:</strong> [<a target="_blank" href="{$site->dereferrer_link}http://www.imdb.com/title/tt{$release.imdbID}/" title="View IMDB">IMDB</a>]{if $movie.tmdbID != ''}&nbsp;&nbsp;[<a target="_blank" href="{$site->dereferrer_link}http://www.themoviedb.org/movie/{$movie.tmdbID}" title="View TMDb">TMDb</a>]{/if}
	</td></tr>
	{/if}
	
	<tr><th>Poster:</th><td>{$release.fromname|escape:"htmlall"}</td></tr>
	<tr><th>Posted:</th><td title="{$release.postdate}">{$release.postdate|date_format}</td></tr>
	<tr><th>Added:</th><td title="{$release.adddate}">{$release.adddate|date_format}</td></tr>
	<tr id="guid{$release.guid}"><th>Download:</th><td>
		<div class="icon icon_nzb"><a title="Download Nzb" href="{$smarty.const.WWW_TOP}/download/{$result.searchname|escape:"htmlall"}/nzb/{$release.guid}">&nbsp;</a></div>
		<div class="icon icon_cart" title="Add to Cart"></div>
		<div class="icon icon_sab" title="Send to my Sabnzbd"></div>
	</td></tr>
	{if $similars|@count > 1}
	<tr>
		<th>Similar:</th>
		<td>
			{foreach from=$similars item=similar}
				<a title="View similar Nzb details" href="{$smarty.const.WWW_TOP}/details/{$similar.searchname|escape:"htmlall"}/viewnzb/{$similar.guid}">{$similar.searchname|escape:"htmlall"}</a><br/>
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
			<tr class="{cycle values=",alt"}">
			<th width="80">User</th>
			<th>Comment</th>
			</tr>
		{foreach from=$comments item=comment}
			<tr>
				<td class="less" title="{$comment.createddate}"><a title="View {$comment.username}'s profile" href="{$smarty.const.WWW_TOP}/profile?name={$comment.username}">{$comment.username}</a><br/>{$comment.createddate|date_format}</td>
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

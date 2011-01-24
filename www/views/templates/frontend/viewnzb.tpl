
<h1>{$release.searchname|escape:"htmlall"}</h1>

<table class="data">
	{if $isadmin}
	<tr><th>Admin Functions:</th><td><a class="rndbtn" href="{$smarty.const.WWW_TOP}/admin/release-edit.php?id={$release.ID}&amp;from={$smarty.server.REQUEST_URI}" title="Edit Release">Edit</a> <a class="rndbtn confirm_action" href="{$smarty.const.WWW_TOP}/admin/release-delete.php?id={$release.ID}&amp;from={$smarty.server.HTTP_REFERER}" title="Delete Release">Delete</a> <a class="rndbtn confirm_action" href="{$smarty.const.WWW_TOP}/admin/release-rebuild.php?id={$release.ID}&amp;from={$smarty.server.HTTP_REFERER}" title="Rebuild Release - Delete and reset for reprocessing if binaries still exist.">Rebuild</a></td></tr>
	{/if}
	<tr><th>Name:</th><td>{$release.name|escape:"htmlall"}</td></tr>
	{if $release.rageID > 0}
		<tr><th>Episode:</th><td>{$release.seriesfull|replace:"S":"Series "|replace:"E":" Episode "} <a title="View series info" href="{$smarty.const.WWW_TOP}/series/{$release.rageID}">View All Episodes</a></td></tr>
		<tr><th>Tv Info:</th><td>
		{if $rage.imgdata != ""}
			<img src="{$smarty.const.WWW_TOP}/getimage?type=tvrage&id={$rage.ID}" width="180" align="left" style="padding-right:10px;"  />
		{/if}

		{if $release.tvtitle != ""}
			<strong>{$release.tvtitle}</strong>
			<br/>
		{/if}
		{if $release.tvairdate != ""}
			<strong>Aired:</strong> {$release.tvairdate|date_format}
			<br/>
		{/if}
		<strong>More:</strong> <a target="_blank" href="{$site->dereferrer_link}http://www.tvrage.com/shows/id-{$release.rageID}" title="View in TvRage">[TV Rage]</a> 
		</td></tr>
	{/if}
	<tr><th>Group:</th><td title="{$release.group_name}"><a title="Browse {$release.group_name}" href="{$smarty.const.WWW_TOP}/browse?g={$release.group_name}">{$release.group_name|replace:"alt.binaries":"a.b"}</a></td></tr>
	<tr><th>Category:</th><td><a title="Browse by {$release.category_name}" href="{$smarty.const.WWW_TOP}/browse?t={$release.categoryID}">{$release.category_name}</a></td></tr>
	{if $nfo.ID|@count > 0}
	<tr><th>Nfo:</th><td><a href="{$smarty.const.WWW_TOP}/nfo/{$release.guid}" title="View Nfo">View Nfo</a></td></tr>
	{/if}
	<tr><th>Size:</th><td>{$release.size|fsize_format:"MB"}</td></tr>
	<tr><th>Grabs:</th><td>{$release.grabs} time{if $release.grabs==1}{else}s{/if}</td></tr>
	<tr><th>Files:</th><td><a title="View file list" href="{$smarty.const.WWW_TOP}/filelist/{$release.guid}">{$release.totalpart} file{if $release.totalpart==1}{else}s{/if}</a></td></tr>

	{if $site->checkpasswordedrar == 1}
	<tr><th>Password:</th>
		<td>
			{if $release.passwordstatus == 0}None{elseif $release.passwordstatus == 1}Passworded Rar Archive{elseif $release.passwordstatus == 2}Contains Cab/Ace Archive{else}Unknown{/if}
		</td>
	</tr>
	{/if}
	
	{if $movie.imdbID > 0}
	<tr><th>Movie Info:</th><td>
		{if $movie.cover == 1}<img src="{$smarty.const.WWW_TOP}/covers/movies/{$movie.imdbID}-cover.jpg" alt="{$movie.title}" height="140" align="left" hspace="10" />{/if}
		<strong>{$movie.title} ({$movie.year}) {if $movie.rating == ''}N/A{/if}{$movie.rating}/10</strong>
		{if $movie.tagline != ''}<br />{$movie.tagline}{/if}
		{if $movie.plot != ''}{if $movie.tagline != ''} - {else}<br />{/if}{$movie.plot}{/if}
		<br /><br /><strong>Director:</strong> {$movie.director}
		<br /><strong>Genre:</strong> {$movie.genre}
		<br /><strong>Starring:</strong> {$movie.actors}
		<br /><strong>More:</strong> [<a target="_blank" href="{$site->dereferrer_link}http://www.imdb.com/title/tt{$release.imdbID}/" title="View IMDB">IMDB</a>]{if $movie.tmdbID != ''}&nbsp;&nbsp;[<a target="_blank" href="{$site->dereferrer_link}http://www.themoviedb.org/movie/{$movie.tmdbID}" title="View TMDb">TMDb</a>]{/if}
	</td></tr>
	{/if}
	
	{if $con.ID > 0}
	<tr><th>Console Info:</th><td>
		{if $con.cover == 1}<a target="_blank" href="{$site->dereferrer_link}{$con.url}/" ><img src="{$smarty.const.WWW_TOP}/covers/console/{$con.ID}.jpg" alt="{$con.title}" height="140" align="left" hspace="10" /></a>{/if}
		<strong>{$con.title} ({$con.releasedate|date_format:"%Y"})</strong><br />
		{if $con.esrb != ""}<strong>ESRB:</strong> {$con.esrb}<br />{/if}
		{if $con.publisher != ""}<strong>Publisher:</strong> {$con.publisher}<br />{/if}
		{if $con.platform != ""}<strong>Platform:</strong> {$con.platform}<br />{/if}
		{if $con.releasedate != ""}<strong>Released:</strong> {$con.releasedate|date_format}<br />{/if}
		<strong>More:</strong> <a class="rndbtn" target="_blank" href="{$site->dereferrer_link}{$con.url}/" title="View Game">Amazon</a>
	</td></tr>
	{/if}	
	{if $con.review != ""}<tr><th>Console Review:</th><td>{$con.review|nl2br|magicurl}</td></tr>{/if}	
	
	{if $music.ID > 0}
	<tr><th>Music Info:</th><td>
		{if $music.cover == 1}<a target="_blank" href="{$site->dereferrer_link}{$music.url}/" ><img src="{$smarty.const.WWW_TOP}/covers/music/{$music.ID}.jpg" alt="{$music.title}" height="120" align="left" hspace="10" /></a>{/if}
		<strong>{$music.title} {if $music.year != ""}({$music.year}){/if}</strong><br />
		{if $music.genre != ""}<strong>Genre:</strong> {$music.genre}<br />{/if}
		{if $music.publisher != ""}<strong>Publisher:</strong> {$music.publisher}<br />{/if}
		{if $music.releasedate != ""}<strong>Released:</strong> {$music.releasedate|date_format}<br />{/if}
		<strong>More:</strong> <a class="rndbtn" target="_blank" href="{$site->dereferrer_link}{$music.url}/" title="View Record">Amazon</a>
	</td></tr>
	
	{if $music.tracks != ""}
	<tr><th>Track Listing:</th><td>
		<ol class="tracklist">
			{assign var="tracksplits" value="|"|explode:$music.tracks}
			{foreach from=$tracksplits item=tracksplit}
			<li>{$tracksplit|trim}</li>
			{/foreach}		
		</ol>
	</td></tr>
	{/if}
	
	{if $music.review != ""}<tr><th>Music Review:</th><td>{$music.review|nl2br|magicurl}</td></tr>{/if}	
	
	{/if}	
	
	<tr><th>Poster:</th><td>{$release.fromname|escape:"htmlall"}</td></tr>
	<tr><th>Posted:</th><td title="{$release.postdate}">{$release.postdate|date_format} ({$release.postdate|daysago})</td></tr>
	<tr><th>Added:</th><td title="{$release.adddate}">{$release.adddate|date_format} ({$release.adddate|daysago})</td></tr>
	<tr id="guid{$release.guid}"><th>Download:</th><td>
		<div class="icon icon_nzb"><a title="Download Nzb" href="{$smarty.const.WWW_TOP}/getnzb/{$release.guid}/{$release.searchname|escape:"htmlall"}">&nbsp;</a></div>
		<div class="icon icon_cart" title="Add to Cart"></div>
		<div class="icon icon_sab" title="Send to my Sabnzbd"></div>
	</td></tr>

	{if $release.rageID > 0}
	<tr>
		<th>Rss:</th><td><a href="{$smarty.const.WWW_TOP}/rss?rage={$release.rageID}&dl=1&i={$userdata.ID}&r={$userdata.rsstoken}">Rss Feed for this Series</a></td>
	</tr>
	{/if}

	{if $similars|@count > 1}
	<tr>
		<th>Similar:</th>
		<td>
			{foreach from=$similars item=similar}
				<a title="View similar Nzb details" href="{$smarty.const.WWW_TOP}/details/{$similar.guid}/{$similar.searchname|escape:"htmlall"}">{$similar.searchname|escape:"htmlall"}</a><br/>
			{/foreach}
			<br/>
			<a title="Search for similar Nzbs" href="{$smarty.const.WWW_TOP}/search/{$searchname|escape:"htmlall"}.">Search for similar NZBs...</a><br/>
		</td>
	</tr>
	{/if}
	{if $isadmin}
	<tr><th>Additional Info:</th>
		<td>
			Regex Id (<a href="{$smarty.const.WWW_TOP}/admin/regex-list.php#{$release.regexID}">{$release.regexID}</a>) <br/> 
			{if $release.reqID != ""}
				Request Id ({$release.reqID})
			{/if}
		</td></tr>
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


<h1>{$page->title}</h1>

{if $rage[0].imgdata != ""}
	<img style="display:block;" src="{$smarty.const.WWW_TOP}/getimage.php?type=tvrage&id={$rage[0].ID}">
{/if}

<p>
	{$seriesdescription}
	<br/>
	<a style="float:right;" target="_blank" href="{$site->dereferrer_link}http://www.tvrage.com/shows/id-{$rage[0].rageID}" title="View in TvRage">View in Tv Rage</a>
</p>

<table style="width:100%;" class="data highlight">
	<tr>
		<th>Ep</th>
		<th>Name</th>
		<th>Posted</th>
	</tr>
	{capture name="season"}{/capture}
	{foreach from=$rel item=result}
	{if $smarty.capture.season ne $result.season}
	<tr class="{cycle values=",alt"}">
		<td colspan="3">Season {$result.season}</td>
	</tr>
	{/if}
	{capture name="season"}{$result.season}{/capture}
	<tr class="{cycle values=",alt"}">
		<td width="20">{$result.episode}</td>
		<td><a href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}">{$result.searchname|escape:"htmlall"}</a></td>
		<td width="80" title="{$result.postdate}">{$result.postdate|date_format}</td>
	</tr>
	{/foreach}
	
</table>


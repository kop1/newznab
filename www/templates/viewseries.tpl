
<h1>{$page->title} {$seriesnames}</h1>
<h2><a target="_blank" href="{$site->dereferrer_link}http://www.tvrage.com/shows/id-{$rage[0].rageID}" title="View in TvRage">http://www.tvrage.com/shows/id-{$rage[0].rageID}</a></h2>
<h4>{$seriesdescription}</h4>

<table style="width:100%;" class="data highlight">
	<tr>
		<th>Ep</th>
		<th>Release</th>
	</tr>
	{capture name="season"}{/capture}
	{foreach from=$rel item=result}
	{if $smarty.capture.season ne $result.season}
	<tr class="{cycle values=",alt"}">
		<td colspan="2">Season {$result.season}</td>
	</tr>
	{/if}
	{capture name="season"}{$result.season}{/capture}
	<tr class="{cycle values=",alt"}">
		<td width="20">{$result.episode}</td>
		<td><a href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}">{$result.searchname|escape:"htmlall"}</a></td>
	</tr>
	{/foreach}
	
</table>


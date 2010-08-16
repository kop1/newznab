
<h1>{$page->title}</h1>

{if $rage[0].imgdata != ""}
	<img style="display:block;" src="{$smarty.const.WWW_TOP}/getimage.php?type=tvrage&id={$rage[0].ID}">
{/if}

<p>
	{$seriesdescription}
	<br/>
	<a style="float:right;" target="_blank" href="{$site->dereferrer_link}http://www.tvrage.com/shows/id-{$rage[0].rageID}" title="View in TvRage">View in Tv Rage</a>
</p>

<form id="nzb_multi_operations_form" action="get">

<table style="width:100%;" class="data highlight icons">
	<tr>
		<th>Ep</th>
		<th>Name</th>
		<th>Size</th>
		<th>Stats</th>
		<th style="text-align:center;">Posted</th>
		<th></th>
	</tr>
	{capture name="season"}{/capture}
	{foreach from=$rel item=result}
	{if $smarty.capture.season ne $result.season}
	<tr class="{cycle values=",alt"}" id="guid{$result.guid}">
		<td colspan="5">Season {$result.season}</td>
	</tr>
	{/if}
	{capture name="season"}{$result.season}{/capture}
	<tr class="{cycle values=",alt"}" id="guid{$result.guid}">
		<td width="20">{$result.episode}</td>
		<td>
			<a title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}">{$result.searchname|escape:"htmlall"}</a>
		
			<div class="resextra">
				{if $result.nfoID > 0}<a href="{$smarty.const.WWW_TOP}/nfo/{$result.guid}" title="View Nfo" class="modal_nfo" rel="nfo">[NFO]</a>{/if}

				{if $isadmin}<a href="{$smarty.const.WWW_TOP}/admin/release-edit.php?id={$result.ID}&amp;from={$smarty.server.REQUEST_URI}" title="Edit Release">[Edit]</a>{/if}
			</div>
		</td>
		<td width="40" class="less">{$result.size|fsize_format:"MB"}</td>
		<td width="40" class="less" nowrap="nowrap"><a title="View comments for {$result.searchname|escape:"htmlall"}" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}#comments">{$result.comments} cmt{if $result.comments != 1}s{/if}</a><br/>{$result.grabs} grab{if $result.grabs != 1}s{/if}</td>
		<td style="text-align:center;" class="less" width="40" title="{$result.postdate}">{$result.postdate|timeago}</td>
			<td class="icons">
				<div class="icon icon_nzb"><a title="Download Nzb" href="{$smarty.const.WWW_TOP}/download/{$result.searchname|escape:"htmlall"}/nzb/{$result.guid}">&nbsp;</a></div>
				<div class="icon icon_cart" title="Add to Cart"></div>
				<div class="icon icon_sab" title="Send to my Sabnzbd"></div>
			</td>
	</tr>
	{/foreach}
	
</table>

</form>
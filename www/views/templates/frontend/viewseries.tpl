 
<h1>{$page->title}</h1>

{if $rage[0].imgdata != ""}
	<img alt="{$rage[0].releasetitle} Logo" style="display:block;" src="{$smarty.const.WWW_TOP}/getimage?type=tvrage&amp;id={$rage[0].ID}" />
{/if}

<p>
	{$seriesdescription}
	<br/>
	<div style="float:right;padding-bottom:10px;" >
	<a target="_blank" href="{$site->dereferrer_link}http://www.tvrage.com/shows/id-{$rage[0].rageID}" title="View in TvRage">View in Tv Rage</a>
	| <a href="{$smarty.const.WWW_TOP}/rss?rage={$rage[0].rageID}&dl=1&i={$userdata.ID}&r={$userdata.rsstoken}">Rss Feed for this Series</a>
	</div>
</p>

<form id="nzb_multi_operations_form" action="get">

<table style="width:100%;" class="data highlight icons">
	{capture name="season"}{/capture}
	{foreach from=$rel item=result}
	{if $smarty.capture.season ne $result.season}
		<tr id="guid{$result.guid}">
			<td style="padding-top:15px;" colspan="10"><h2>Season {$result.season}</h2></td>
		</tr>
		<tr>
			<th>Ep</th>
			<th>Name</th>
			<th>Size</th>
			<th>Stats</th>
			<th style="text-align:center;">Posted</th>
			<th>Options</th>
		</tr>
	{/if}
	{capture name="season"}{$result.season}{/capture}

	<tr class="{cycle values=",alt"}" id="guid{$result.guid}">
		<td width="20">{$result.episode}</td>
		<td>
			<a title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/{$result.searchname|escape:"htmlall"}">{$result.searchname|escape:"htmlall"}</a>
		
			<div class="resextra">
				{if $result.nfoID > 0}<a href="{$smarty.const.WWW_TOP}/nfo/{$result.guid}" title="View Nfo" class="modal_nfo rndbtn" rel="nfo">Nfo</a>{/if}
				{if $result.tvairdate != ""}<span class="rndbtn" title="{$result.tvtitle} Aired on {$result.tvairdate|date_format}">Aired {if $result.tvairdate|strtotime > $smarty.now}in future{else}{$result.tvairdate|daysago}{/if}</span>{/if}

				{if $isadmin}
				<div class="admin">
					<a class="rndbtn" href="{$smarty.const.WWW_TOP}/admin/release-edit.php?id={$result.ID}&amp;from={$smarty.server.REQUEST_URI}" title="Edit Release">Edit</a>
				</div>
				{/if}			
			</div>
		</td>
		<td width="40" class="less">{$result.size|fsize_format:"MB"}</td>
		<td width="40" class="less" nowrap="nowrap"><a title="View comments for {$result.searchname|escape:"htmlall"}" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/{$result.searchname|escape:"htmlall"}#comments">{$result.comments} cmt{if $result.comments != 1}s{/if}</a><br/>{$result.grabs} grab{if $result.grabs != 1}s{/if}</td>
		<td class="less mid" width="40" title="{$result.postdate}">{$result.postdate|timeago}</td>
			<td class="icons">
				<div class="icon icon_nzb"><a title="Download Nzb" href="{$smarty.const.WWW_TOP}/getnzb/{$result.guid}/{$result.searchname|escape:"htmlall"}">&nbsp;</a></div>
				<div class="icon icon_cart" title="Add to Cart"></div>
				<div class="icon icon_sab" title="Send to my Sabnzbd"></div>
			</td>
	</tr>
	{/foreach}
	
</table>

</form>
 
<h1>{$page->title}</h1>

{if $rage[0].imgdata != ""}
	<img alt="{$rage[0].releasetitle} Logo" style="display:block;padding:0px 10px 10px 10px;" src="{$smarty.const.WWW_TOP}/getimage?type=tvrage&amp;id={$rage[0].ID}" align="right" />
{/if}
<p>
	{if $seriesgenre != ''}<b>{$seriesgenre}</b><br />{/if}
	{$seriesdescription}
	<br/>
	<div style="float:right;padding-bottom:10px;" >
	<a target="_blank" href="{$site->dereferrer_link}http://www.tvrage.com/shows/id-{$rage[0].rageID}" title="View in TvRage">View in Tv Rage</a>
	| <a href="{$smarty.const.WWW_TOP}/rss?rage={$rage[0].rageID}&dl=1&i={$userdata.ID}&r={$userdata.rsstoken}">Rss Feed for this Series</a>
	</div>
</p>

<form id="nzb_multi_operations_form" action="get">

<table style="width:100%;" class="data highlight icons">
	{foreach $seasons as $seasonnum => $season}
		<tr>
			<td style="padding-top:15px;" colspan="10"><h2>Season {$seasonnum}</h2></td>
		</tr>
		<tr>
			<th>Ep</th>
			<th>Name</th>
			<th>Size</th>
			<th>Stats</th>
			<th style="text-align:center;">Posted</th>
			<th>Options</th>
		</tr>
		{foreach $season as $episodes}
			{foreach $episodes as $result}
				<tr class="{cycle values=",alt"}" id="guid{$result.guid}">
					{if $result@total>1 && $result@index == 0}
						<td width="20" rowspan="{$result@total}" class="static">{$episodes@key}</td>
					{else if $result@total == 1}
						<td width="20" class="static">{$episodes@key}</td>
					{/if}
					<td>
						<a title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/{$result.searchname|escape:"htmlall"}">{$result.searchname|escape:"htmlall"}</a>
					
						<div class="resextra">
							<div class="btns">
								{if $result.nfoID > 0}<a href="{$smarty.const.WWW_TOP}/nfo/{$result.guid}" title="View Nfo" class="modal_nfo rndbtn" rel="nfo">Nfo</a>{/if}
								{if $result.tvairdate != ""}<span class="rndbtn" title="{$result.tvtitle} Aired on {$result.tvairdate|date_format}">Aired {if $result.tvairdate|strtotime > $smarty.now}in future{else}{$result.tvairdate|daysago}{/if}</span>{/if}
							</div>
			
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
		{/foreach}
	{/foreach}
</table>

</form>
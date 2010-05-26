
<h1>Browse {$catname}</h1>
	
{if $results|@count > 0}

{$pager}

<table style="width:100%;margin-top:10px;" class="data highlight">
	<tr>
		<th>name<br/><a title="Sort Descending" href="{$orderbyname_desc}"><img src="images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbyname_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>category<br/><a title="Sort Descending" href="{$orderbycat_desc}"><img src="images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbycat_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>posted<br/><a title="Sort Descending" href="{$orderbyposted_desc}"><img src="images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbyposted_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>size<br/><a title="Sort Descending" href="{$orderbysize_desc}"><img src="images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbysize_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>files<br/><a title="Sort Descending" href="{$orderbyfiles_desc}"><img src="images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbyfiles_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>stats<br/><a title="Sort Descending" href="{$orderbystats_desc}"><img src="images/sorting/arrow_down.gif" alt="" /></a><a title="Sort Ascending" href="{$orderbystats_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
	</tr>

	{foreach from=$results item=result}
		<tr class="{cycle values=",alt"}">
			<td>
				<a title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}">{$result.searchname|escape:"htmlall"|wordwrap:75:"\n":true}</a>
				<div class="resextra">
					<a title="Download Nzb" href="{$smarty.const.WWW_TOP}/download/{$result.searchname|escape:"htmlall"}/nzb/{$result.guid}"><div class="icon icon_nzb"></div></a>
					<a href="#" class="add_to_cart" id="{$result.guid}" title="Add to Cart"><div class="icon icon_cart"></div></a>
					{if $site->sabintegration=="1" && $userdata.sabapikey!="" && $userdata.sabhost!=""}<a href="#" class="add_to_sab" id="{$result.guid}" title="Send to my Sabnzbd"><div class="icon icon_sab"></div></a>{/if}
					{if $result.rageID > 0}<a target="blank" href="http://www.tvrage.com/shows/id-{$result.rageID}" title="View in TvRage"><div class="icon icon_tvrage"></div></a>{/if}
				</div>
			</td>
			<td class="less"><a title="Browse {$result.category_name}" href="{$smarty.const.WWW_TOP}/browse?t={$result.categoryID}">{$result.category_name}</a></td>
			<td class="less" title="{$result.postdate}">{$result.postdate|date_format}</td>
			<td class="less">{$result.size|fsize_format:"MB"}</td>
			<td class="less"><a title="View file list" href="{$smarty.const.WWW_TOP}/filelist/{$result.guid}">{$result.totalpart}</a></td>
			<td class="less" nowrap="nowrap"><a title="View comments for {$result.searchname|escape:"htmlall"}" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}#comments">{$result.comments} cmt{if $result.comments != 1}s{/if}</a><br/>{$result.grabs} grab{if $result.grabs != 1}s{/if}</td>
		</tr>
	{/foreach}
	
</table>
{/if}

<input type="hidden" id="cred-host" value="{$userdata.sabhost|escape:"htmlall"}" />
<input type="hidden" id="cred-key" value="{$userdata.sabapikey|escape:"htmlall"}" />
<input type="hidden" id="cred-uid" value="{$userdata.ID}" />
<input type="hidden" id="cred-rsstoken" value="{$userdata.rsstoken}" />

<br/>
{$pager}

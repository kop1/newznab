
<h1>Browse {$catname}</h1>
	
{if $results|@count > 0}

{$pager}

<table style="width:100%;margin-top:10px;" class="data highlight">
	<tr>
		<th>name<a href="{$orderbyname_desc}"><br/><img src="images/sorting/arrow_down.gif" alt="" /></a><a href="{$orderbyname_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>category<a href="{$orderbycat_desc}"><br/><img src="images/sorting/arrow_down.gif" alt="" /></a><a href="{$orderbycat_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></th>
		<th>posted<a href="{$orderbyposted_desc}"><br/><img src="images/sorting/arrow_down.gif" alt="" /></a><a href="{$orderbyposted_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>size<a href="{$orderbysize_desc}"><br/><img src="images/sorting/arrow_down.gif" alt="" /></a><a href="{$orderbysize_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>files<a href="{$orderbyfiles_desc}"><br/><img src="images/sorting/arrow_down.gif" alt="" /></a><a href="{$orderbyfiles_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
		<th>stats<a href="{$orderbystats_desc}"><br/><img src="images/sorting/arrow_down.gif" alt="" /></a><a href="{$orderbystats_asc}"><img src="images/sorting/arrow_up.gif" alt="" /></a></th>
	</tr>

	{foreach from=$results item=result}
		<tr>
			<td>
				<a title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}">{$result.searchname|escape:"htmlall"|wordwrap:75:"\n":true}</a>
				<div class="resextra">
					<a title="Download Nzb" href="{$smarty.const.WWW_TOP}/download/{$result.searchname|escape:"htmlall"}/nzb/{$result.guid}">[Nzb]</a>
					{if $result.rageID > 0}[<a target="blank" href="http://www.tvrage.com/shows/id-{$result.rageID}" title="View in TvRage">Tv Rage {$result.seriesfull}</a>]{/if}
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

<br/>
{$pager}


<h1>Search</h1>

<form method="get" action="/search/">
	<div style="text-align:center;">
		<label for="search" style="display:none;">Search</label>
		<input id="search" name="search" value="{$search|escape:'htmlall'}" type="text"/>
		<input id="search_search_button" type="submit" value="search" />
	</div>
</form>


{if $results|@count > 0}

<br/>
<div class="nzb_multi_operations">
	<small>With Selected:</small>
	<input type="button" value="Send to SAB" />
	<input type="button" value="Add to Cart" />
	<input type="button" value="Download NZBs" />
</div>

<table style="width:100%;" class="data highlight icons">
	<tr>
		<th><input type="checkbox" class="nzb_check_all" /></th>
		<th>name</th>
		<th>category</th>
		<th>posted</th>
		<th>size</th>
		<th>files</th>
		<th>stats</th>
		<th>download</th>
	</tr>

	{foreach from=$results item=result}
		<tr class="{cycle values=",alt"}">
			<td><input type="checkbox" class="nzb_check" /></td>
			<td>
				<a class="title" title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}">{$result.searchname|escape:"htmlall"|wordwrap:75:"\n":true}</a>
				<div class="resextra">
					{if $result.nfoID > 0}<a href="{$smarty.const.WWW_TOP}/nfo/{$result.guid}" title="View Nfo" class="modal_nfo" rel="nfo">(NFO)</a>{/if}
					{if $result.imdbID > 0}<a target="_blank" href="{$site->dereferrer_link}http://www.imdb.com/title/tt{$result.imdbID}/" title="View IMDB">(IMDB)</a>{/if}
					{if $result.rageID > 0}<a target="_blank" href="{$site->dereferrer_link}http://www.tvrage.com/shows/id-{$result.rageID}" title="View in TvRage">(TVRage)</a>{/if}
				</div>
			</td>
			<td class="less"><a title="Browse {$result.category_name}" href="{$smarty.const.WWW_TOP}/browse?t={$result.categoryID}">{$result.category_name}</a></td>
			<td class="less" title="{$result.postdate}">{$result.postdate|timeago}</td>
			<td class="less" width="55">{$result.size|fsize_format:"MB"}</td>
			<td class="less"><a title="View file list" href="{$smarty.const.WWW_TOP}/filelist/{$result.guid}">{$result.totalpart}</a></td>
			<td class="less" nowrap="nowrap"><a title="View comments for {$result.searchname|escape:"htmlall"}" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}#comments">{$result.comments} cmt{if $result.comments != 1}s{/if}</a><br/>{$result.grabs} grab{if $result.grabs != 1}s{/if}</td>
			<td class="icons">
				<a title="Download Nzb" href="{$smarty.const.WWW_TOP}/download/{$result.searchname|escape:"htmlall"}/nzb/{$result.guid}"><div class="icon icon_nzb"></div></a>
				<a href="#" class="add_to_cart" id="{$result.guid}" title="Add to Cart"><div class="icon icon_cart"></div></a>
				<a href="#" class="add_to_sab" id="{$result.guid}" title="Send to my Sabnzbd"><div class="icon icon_sab"></div></a>
			</td>
		</tr>
	{/foreach}
	
</table>

<div class="nzb_multi_operations">
	<small>With Selected:</small>
	<input type="button" value="Send to SAB" />
	<input type="button" value="Add to Cart" />
	<input type="button" value="Download NZBs" />
</div>

<br/><br/><br/>

{/if}


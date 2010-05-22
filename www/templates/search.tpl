
<h1>Search</h1>

<form method="get" action="/search/">
	<div style="text-align:center;">
		<label for="search" style="display:none;">Search</label>
		<input id="search" name="search" value="{$search|escape:'htmlall'}" type="text"/>
		<input onclick="dosearch();return false;" type="submit" value="search" />
	</div>
</form>

{if $results|@count > 0}

<table style="width:100%;margin-top:40px;" class="data Sortable highlight">
	<tr>
		<th>name</th>
		<th>category</th>
		<th>posted</th>
		<th>size</th>
		<th>files</th>
		<th>stats</th>
	</tr>

	{foreach from=$results item=result}
		<tr>
			<td>
				<a title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}">{$result.searchname|escape:"htmlall"|wordwrap:75:"\n":true}</a>
				<div class="resextra">
					<a title="Download Nzb" href="{$smarty.const.WWW_TOP}/download/{$result.searchname|escape:"htmlall"}/nzb/{$result.guid}">[Nzb]</a>
					<a title="Add to Cart" onclick="addToCart(this, '{$result.guid}'); return false;" href="#">[Cart]</a>
					{if $site->sabintegration=="1" && $userdata.sabapikey!="" && $userdata.sabhost!=""}<a title="Send to my Sabnzbd" onclick="sendToSab(this, '{$userdata.sabhost|escape:"htmlall"}', '{$userdata.sabapikey|escape:"htmlall"}', '{$result.guid}', '{$userdata.ID}', '{$userdata.rsstoken}'); return false;" href="#">[Sab]</a>{/if}
					{if $result.rageID > 0}[<a target="blank" href="http://www.tvrage.com/shows/id-{$result.rageID}" title="View in TvRage">Tv Rage {$result.seriesfull}</a>]{/if}
				</div>
			</td>
			<td class="less"><a title="Browse {$result.category_name}" href="{$smarty.const.WWW_TOP}/browse?t={$result.categoryID}">{$result.category_name}</a></td>
			<td class="less" title="{$result.postdate}">{$result.postdate|date_format}</td>
			<td class="less" width="55">{$result.size|fsize_format:"MB"}</td>
			<td class="less"><a title="View file list" href="{$smarty.const.WWW_TOP}/filelist/{$result.guid}">{$result.totalpart}</a></td>
			<td class="less" nowrap="nowrap"><a title="View comments for {$result.searchname|escape:"htmlall"}" href="{$smarty.const.WWW_TOP}/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}#comments">{$result.comments} cmt{if $result.comments != 1}s{/if}</a><br/>{$result.grabs} grab{if $result.grabs != 1}s{/if}</td>
		</tr>
	{/foreach}
	
</table>
{/if}

{literal}
<script type="text/javascript">
function dosearch()
{
	var v = document.getElementById("search");
	if (v != null)
	{
		//alert(WWW_TOP + "/search/" + encodeUrl(v.value));
		document.location= WWW_TOP + "/search/" + encodeUrl(v.value);
	}
}
</script>
{/literal}

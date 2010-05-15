
<h1>Search</h1>

<form method="get" action="/search/">
	<div style="text-align:center;">
		<label for="search" style="display:none;">Search</label>
		<input id="search" name="search" value="{$search|escape:'htmlall'}" type="text"/>
		<input onclick="dosearch();return false;" type="submit" value="search" />
	</div>
</form>

{if $results|@count > 0}

<table style="width:100%;margin-top:40px;" class="data Sortable">
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
				<a title="View Nzb details" href="/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}">{$result.searchname|escape:"htmlall"}</a>
				{if $result.rageID > 0}<div class="resextra">[<a target="blank" href="http://www.tvrage.com/shows/id-{$result.rageID}" title="View in TvRage">Tv Rage {$result.seriesfull}</a>]</div>{/if}
			</td>
			<td class="less"><a title="Browse {$result.category_name}" href="/browse?t={$result.categoryID}">{$result.category_name}</a></td>
			<td class="less" title="{$result.postdate}">{$result.postdate|date_format}</td>
			<td class="less" width="55">{$result.size|fsize_format:"MB"}</td>
			<td class="less"><a title="View file list" href="/filelist/{$result.guid}">{$result.totalpart}</a></td>
			<td class="less" nowrap="nowrap"><a title="View comments for {$result.searchname|escape:"htmlall"}" href="/details/{$result.searchname|escape:"htmlall"}/viewnzb/{$result.guid}#comments">{$result.comments} cmt{if $result.comments != 1}s{/if}</a><br/>{$result.grabs} grab{if $result.grabs != 1}s{/if}</td>
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
		document.location="/search/" + encodeUrl(v.value);
	}
}
</script>
{/literal}
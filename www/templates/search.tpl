
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
			<td><a href="/details/{$result.searchname}/viewnzb/{$result.guid}">{$result.searchname}</a></td>
			<td>{$result.category_name}</td>
			<td title="{$result.postdate}">{$result.postdate|date_format}</td>
			<td>{$result.size|fsize_format:"MB"}</td>
			<td><a title="View file list" href="/filelist/{$result.guid}">{$result.totalpart}</a></td>
			<td nowrap="nowrap"><a title="View comments for {$result.searchname}" href="/details/{$result.searchname}/viewnzb/{$result.guid}#comments">{$result.comments} cmt{if $result.comments != 1}s{/if}</a><br/>{$result.grabs} grab{if $result.grabs != 1}s{/if}</td>
		</tr>
	{/foreach}
	
</table>
{/if}

{literal}
<script type="text/javascript">
function dosearch()
{
	var v = document.getElementById("search");
	document.location="/search/" + v.value;
}
</script>
{/literal}
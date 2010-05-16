
<h1>Search Binaries</h1>

<form method="get" action="/searchraw/">
	<div style="text-align:center;">
		<label for="search" style="display:none;">Search</label>
		<input id="search" name="search" value="{$search|escape:'htmlall'}" type="text"/>
		<input onclick="dosearch();return false;" type="submit" value="search" />
	</div>
</form>

{if $results|@count > 0}

<table style="width:100%;margin-top:40px;" class="data">
	<tr>
		<th width="10"><a onclick="alert('check/uncheck all');return false;">Sel</a></th>
		<th>filename</th>
		<th>group</th>
		<th>posted</th>
		<th>size</th>
		<th>Nzb</th>
	</tr>

	{foreach from=$results item=result}
		<tr>
			<td><input type="checkbox"/></td>
			<td>
				<a title="{$result.name|escape:"htmlall"}" href="#" onclick="alert('do something?');return false;">{if $result.filename != ""}{$result.filename|escape:"htmlall"}{else}{$result.name|escape:"htmlall"}{/if}</a>
			</td>
			<td class="less">{$result.group_name|replace:"alt.binaries":"a.b"}</td>
			<td class="less" title="{$result.date}">{$result.date|date_format}</td>
			<td class="less" width="55">{if $result.size > 0}{$result.size|fsize_format:"MB"}{else}-{/if}</td>
			<td class="less">{if $result.releaseID > 0}<a title="View Nzb details" href="/details/{$result.filename|escape:"htmlall"}/viewnzb/{$result.guid}">Yes</a>{/if}</td>
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
		document.location="/searchraw/" + encodeUrl(v.value);
	}
}
</script>
{/literal}
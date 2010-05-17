
<h1>Search Binaries</h1>

<form method="get" action="{$smarty.const.WWW_TOP}/searchraw/">
	<div style="text-align:center;">
		<label for="search" style="display:none;">Search</label>
		<input id="search" name="search" value="{$search|escape:'htmlall'}" type="text"/>
		<input onclick="dosearch();return false;" type="submit" value="search" />
	</div>
</form>

{if $results|@count > 0}
<form method="post" id="dl" name="dl">
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
			<td><input name="file{$result.ID}" id="file{$result.ID}" value="{$result.ID}" type="checkbox"/></td>
			<td>
				<a title="{$result.name|escape:"htmlall"}" href="#" onclick="return false;">{if $result.filename != ""}{$result.filename|escape:"htmlall"}{else}{$result.name|escape:"htmlall"}{/if}</a>
			</td>
			<td class="less">{$result.group_name|replace:"alt.binaries":"a.b"}</td>
			<td class="less" title="{$result.date}">{$result.date|date_format}</td>
			<td class="less" width="55">{if $result.size > 0}{$result.size|fsize_format:"MB"}{else}-{/if}</td>
			<td class="less">{if $result.releaseID > 0}<a title="View Nzb details" href="{$smarty.const.WWW_TOP}/details/{$result.filename|escape:"htmlall"}/viewnzb/{$result.guid}">Yes</a>{/if}</td>
		</tr>
	{/foreach}
	
</table>
</form>

<div style="padding-top:20px;">
	<a href="#" onclick="download();return false;">Download selected as Nzb</a>
</div>


{/if}




{literal}
<script type="text/javascript">
function download()
{
	var v = document.getElementById("dl");
	v.submit();
}

function dosearch()
{
	var v = document.getElementById("search");
	if (v != null)
	{
		document.location=WWW_TOP + "/searchraw/" + encodeUrl(v.value);
	}
}
</script>
{/literal}

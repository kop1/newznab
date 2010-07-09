
<h1>{$page->title}</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">


<tr>
	<td>Group:</td>
	<td>
		<select name="groupname">
		{html_options values=$gid output=$gname selected=$gselected}
		</select>
	</td>
</tr>

<tr>
	<td>Regex:</td>
	<td>
		<input id="regex" name="regex" class="long" value="{$gregex|escape:html}" />
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<input type="checkbox" name="unreleased"{if $gunreleased == 'on'}checked="checked"{/if} /> Only scan unreleased binaries
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<input type="submit" value="Test Regex" />
	</td>
</tr>

</table>

</form>

{if $matches}
{$pager}
<table style="margin-top:10px;" class="data Sortable highlight">

	<tr>
		<th>ID</th>
		<th>name</th>
		<th>parts</th>
	</tr>
	
	{foreach from=$matches item=match}
	<tr class="{cycle values=",alt"}">
		<td>{$match.bininfo.ID}</td>
		<td>{$match.name|escape:html}<br /><small>{$match.bininfo.subject|escape:html}</small></td>
		<td>{$match.parts}</td>
	</tr>
	{/foreach}

</table>
<br />{$pager}
{/if}
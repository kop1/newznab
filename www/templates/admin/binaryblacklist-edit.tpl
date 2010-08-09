
<h1>{$page->title}</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">


<tr>
	<td>Group:</td>
	<td>
		<input type="hidden" name="id" value="{$regex.ID}" />
		<input id="groupname" name="groupname" value="{$regex.groupname|escape:html}" />
		<div class="hint">The full name of a valid newsgroup.</div>		
	</td>
</tr>

<tr>
	<td>Regex:</td>
	<td>
		<textarea id="regex" name="regex" >{$regex.regex|escape:html}</textarea>
		<div class="hint">The regex to be applied.</div>		
	</td>
</tr>

<tr>
	<td>Description:</td>
	<td>
		<textarea id="description" name="description" >{$regex.description|escape:html}</textarea>
		<div class="hint">A description for this regex</div>		
	</td>
</tr>

<tr>
	<td><label for="status">Active</label>:</td>
	<td>
		{html_radios id="status" name='status' values=$status_ids output=$status_names selected=$regex.status separator='<br />'}
		<div class="hint">Only active regexes are applied during the release process.</div>		
	</td>
</tr>

<tr>
	<td><label for="optype">Type</label>:</td>
	<td>
		{html_radios id="optype" name='optype' values=$optype_ids output=$optype_names selected=$regex.optype separator='<br />'}
		<div class="hint">Black will exclude all messages for a group which match this regex. White will include only those which match.</div>		
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<input type="submit" value="Save" />
	</td>
</tr>

</table>

</form>

<h1>{$page->title}</h1>

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td>Name:</td>
	<td>
		<input type="hidden" name="id" value="{$group.ID}" />
		<input id="name" class="long" name="name" type="text" value="{$group.name}" />
		<div class="hint">Changing the name to an invalid group will break things.</div>		
	</td>
</tr>

<tr>
	<td><label for="description">Description</label>:</td>
	<td>
		<textarea id="description" name="description">{$group.description}</textarea>
	</td>
</tr>

<tr>
	<td>Backfill Days</td>
	<td>
		<input id="backfill_target" name="backfill_target" type-"text" value="{$group.backfill_target}" />
		<div class="hint">Number of days to attempt to backfill this group.  Adjust as necessary.</div>
	</td>
</td>

<tr>
	<td><label for="active">Active</label>:</td>
	<td>
		{html_radios id="active" name='active' values=$yesno_ids output=$yesno_names selected=$group.active separator='<br />'}
		<div class="hint">Inactive groups will not have headers downloaded for them.</div>		
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


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
	<td>First Record:</td>
	<td>
		<input id="first_record" name="first_record" type="text" value="{$group.first_record}" />
		<div class="hint">Only manually edit the last message numbers if you know what your doing. Leave as 0 for new groups.</div>		
	</td>
</tr>

<tr>
	<td>Last Record:</td>
	<td>
		<input id="last_record" name="last_record" type="text" value="{$group.last_record}" />
		<div class="hint">Only manually edit the last message numbers if you know what your doing. Leave as 0 for new groups.</div>		
	</td>
</tr>

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
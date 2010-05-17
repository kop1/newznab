
<h1>{$page->title}</h1>

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td>Name:</td>
	<td>
		<input type="hidden" name="id" value="{$group.ID}" />
		{$group.name}
	</td>
</tr>

<tr>
	<td><label for="description">Description</label>:</td>
	<td>
		<textarea id="description" name="description">{$group.description}</textarea>
	</td>
</tr>

<tr>
	<td><label for="active">Active</label>:</td>
	<td>
		{html_radios id="active" name='active' values=$yesno_ids output=$yesno_names selected=$group.active separator='<br />'}
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
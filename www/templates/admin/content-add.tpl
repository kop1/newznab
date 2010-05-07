
<h1>{$page->title}</h1>

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td>Title:</td>
	<td>
		<input type="hidden" name="id" value="{$content->id}" />
		<input class="long" name="title" type="text" value="{$content->title}" />
	</td>
</tr>

<tr>
	<td>Url:</td>
	<td>
		<input class="long" name="url" type="text" value="{$content->url}" />
	</td>
</tr>

<tr>
	<td>Body:</td>
	<td>
		<textarea name="body">{$content->body}</textarea>
	</td>
</tr>

<tr>
	<td>Meta Description:</td>
	<td>
		<textarea name="metadescription">{$content->metadescription}</textarea>
	</td>
</tr>

<tr>
	<td>Meta Keywords:</td>
	<td>
		<textarea name="metakeywords">{$content->metakeywords}</textarea>
	</td>
</tr>

<tr>
	<td>Content Type:</td>
	<td>
		{html_options name='contenttype' options=$contenttypelist selected=$content->contenttype}
	</td>
</tr>

<tr>
	<td>Show In Menu:</td>
	<td>
		{html_radios name='showinmenu' values=$yesno_ids output=$yesno_names selected=$content->showinmenu separator='<br />'}
	</td>
</tr>

<tr>
	<td>Status:</td>
	<td>
		{html_radios name='status' values=$status_ids output=$status_names selected=$content->status separator='<br />'}
	</td>
</tr>

<tr>
	<td>Ordinal:</td>
	<td>
		<input name="ordinal" type="text" value="{$content->ordinal}" />
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
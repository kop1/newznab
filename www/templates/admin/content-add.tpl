
<h1>{$page->title}</h1>

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td>Title:</td>
	<td>
		<input type="hidden" name="ID" value="{$content->id}" />
		<input class="long" name="TITLE" type="text" value="{$content->title}" />
	</td>
</tr>

<tr>
	<td>Url:</td>
	<td>
		<input class="long" name="URL" type="text" value="{$content->url}" />
	</td>
</tr>

<tr>
	<td>Body:</td>
	<td>
		<textarea name="BODY">{$content->body}</textarea>
	</td>
</tr>

<tr>
	<td>Meta Description:</td>
	<td>
		<textarea name="METADESCRIPTION">{$content->metadescription}</textarea>
	</td>
</tr>

<tr>
	<td>Meta Keywords:</td>
	<td>
		<textarea name="METAKEYWORDS">{$content->metakeywords}</textarea>
	</td>
</tr>

<tr>
	<td>Content Type:</td>
	<td>
		{html_options name='CONTENTTYPE' options=$contenttypelist selected=$content->contenttype}
	</td>
</tr>

<tr>
	<td>Show In Menu:</td>
	<td>
		{html_radios name='SHOWINMENU' values=$yesno_ids output=$yesno_names selected=$content->showinmenu separator='<br />'}
	</td>
</tr>

<tr>
	<td>Status:</td>
	<td>
		{html_radios name='STATUS' values=$status_ids output=$status_names selected=$content->status separator='<br />'}
	</td>
</tr>

<tr>
	<td>Ordinal:</td>
	<td>
		<input name="ORDINAL" type="text" value="{$content->ordinal}" />
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<input type="submit" value="Save" />
		<input onclick="alert('go back');" type="button" value="Cancel" />
	</td>
</tr>

</table>

</form>
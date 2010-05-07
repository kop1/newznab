
<h1>{$page->title}</h1>

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td>Code Name:</td>
	<td>
		<input name="code" type="text" value="{$fsite->code}">
		<input type="hidden" name="id" value="{$fsite->id}" />
	</td>
</tr>

<tr>
	<td>Title:</td>
	<td>
		<input class="long" name="title" type="text" value="{$fsite->title}"></td>
	</td>
</tr>

<tr>
	<td>Strapline:</td>
	<td>
		<input class="long" name="strapline" type="text" value="{$fsite->strapline}"></td>
	</td>
</tr>

<tr>
	<td>Meta Title:</td>
	<td>
		<input class="long" name="metatitle" type="text" value="{$fsite->meta_title}"></td>
	</td>
</tr>


<tr>
	<td>Meta Description:</td>
	<td>
		<textarea name="metadescription">{$fsite->meta_description}</textarea>
	</td>
</tr>

<tr>
	<td>Meta Keywords:</td>
	<td>
		<textarea name="metakeywords">{$fsite->meta_keywords}</textarea>
	</td>
</tr>

<tr>
	<td>Footer:</td>
	<td>
		<textarea name="footer">{$fsite->footer}</textarea>
	</td>
</tr>

<tr>
	<td>Email:</td>
	<td>
		<input class="long" name="email" type="text" value="{$fsite->email}"></td>
	</td>
</tr>

<tr>
	<td>Google Analytics:</td>
	<td>
		<input class="long" name="google_analytics_acc" type="text" value="{$fsite->google_analytics_acc}"></td>
	</td>
</tr>

<tr>
	<td>Google Adsense Menu:</td>
	<td>
		<input class="long" name="google_adsense_menu" type="text" value="{$fsite->google_adsense_menu}"></td>
	</td>
</tr>

<tr>
	<td>Google Adsense Sidepanel:</td>
	<td>
		<input class="long" name="google_adsense_sidepanel" type="text" value="{$fsite->google_adsense_sidepanel}"></td>
	</td>
</tr>

<tr>
	<td>Google Adsense Search:</td>
	<td>
		<input class="long" name="google_adsense_search" type="text" value="{$fsite->google_adsense_search}"></td>
	</td>
</tr>

<tr>
	<td>Usenet Group Filter:</td>
	<td>
		<textarea name="groupfilter">{$fsite->groupfilter}</textarea>
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
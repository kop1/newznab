
<h1>{$page->title}</h1>

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td><label for="codename">Code Name</label>:</td>
	<td>
		<input id="codename" name="code" type="text" value="{$fsite->code}">
		<input type="hidden" name="id" value="{$fsite->id}" />
	</td>
</tr>

<tr>
	<td><label for="title">Title</label>:</td>
	<td>
		<input id="title" class="long" name="title" type="text" value="{$fsite->title}"></td>
	</td>
</tr>

<tr>
	<td><label for="strapline">Strapline</label>:</td>
	<td>
		<input id="strapline" class="long" name="strapline" type="text" value="{$fsite->strapline}"></td>
	</td>
</tr>

<tr>
	<td><label for="metatitle">Meta Title</label>:</td>
	<td>
		<input id="metatitle" class="long" name="metatitle" type="text" value="{$fsite->meta_title}"></td>
	</td>
</tr>


<tr>
	<td><label for="metadescription">Meta Description</label>:</td>
	<td>
		<textarea id="metadescription" name="metadescription">{$fsite->meta_description}</textarea>
	</td>
</tr>

<tr>
	<td><label for="metakeywords">Meta Keywords</label>:</td>
	<td>
		<textarea id="metakeywords" name="metakeywords">{$fsite->meta_keywords}</textarea>
	</td>
</tr>

<tr>
	<td><label for="footer">Footer</label>:</td>
	<td>
		<textarea id="footer" name="footer">{$fsite->footer}</textarea>
	</td>
</tr>

<tr>
	<td><label for="email">Email</label>:</td>
	<td>
		<input id="email" class="long" name="email" type="text" value="{$fsite->email}"></td>
	</td>
</tr>

<tr>
	<td><label for="google_analytics_acc">Google Analytics</label>:</td>
	<td>
		<input id="google_analytics_acc" class="long" name="google_analytics_acc" type="text" value="{$fsite->google_analytics_acc}"></td>
	</td>
</tr>

<tr>
	<td><label for="google_adsense_menu">Google Adsense Menu</label>:</td>
	<td>
		<input id="google_adsense_menu" class="long" name="google_adsense_menu" type="text" value="{$fsite->google_adsense_menu}"></td>
	</td>
</tr>

<tr>
	<td><label for="google_adsense_sidepanel">Google Adsense Sidepanel</label>:</td>
	<td>
		<input id="google_adsense_sidepanel" class="long" name="google_adsense_sidepanel" type="text" value="{$fsite->google_adsense_sidepanel}"></td>
	</td>
</tr>

<tr>
	<td><label for="google_adsense_search">Google Adsense Search</label>:</td>
	<td>
		<input id="google_adsense_search" class="long" name="google_adsense_search" type="text" value="{$fsite->google_adsense_search}"></td>
	</td>
</tr>

<tr>
	<td><label for="groupfilter">Usenet Group Filter</label>:</td>
	<td>
		<textarea id="groupfilter" name="groupfilter">{$fsite->groupfilter}</textarea>
	</td>
</tr>

<tr>
	<td><label for="apikey">Api Key</label>:</td>
	<td>
		<input id="apikey" class="long" name="apikey" type="text" value="{$fsite->apikey}"></td>
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
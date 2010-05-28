
<h1>{$page->title}</h1>

<form action="{$SCRIPT_NAME}?action=submit" method="post">

<fieldset>
<legend>Main Site Settings, Html Layout, Tags</legend>
<table class="input">

<tr>
	<td style="width:160px;"><label for="codename">Code Name</label>:</td>
	<td>
		<input id="codename" name="code" type="text" value="{$fsite->code}" />
		<input type="hidden" name="id" value="{$fsite->id}" />
		<div class="hint">A just for fun value shown in debug and not on public pages.</div>
	</td>
</tr>

<tr>
	<td><label for="title">Title</label>:</td>
	<td>
		<input id="title" class="long" name="title" type="text" value="{$fsite->title}" />
		<div class="hint">Displayed around the site and contact form as the name for the site.</div>
	</td>
</tr>

<tr>
	<td><label for="strapline">Strapline</label>:</td>
	<td>
		<input id="strapline" class="long" name="strapline" type="text" value="{$fsite->strapline}" />
		<div class="hint">Displayed in the header on every public page.</div>
	</td>
</tr>

<tr>
	<td><label for="metatitle">Meta Title</label>:</td>
	<td>
		<input id="metatitle" class="long" name="metatitle" type="text" value="{$fsite->meta_title}" />
		<div class="hint">Stem meta-tag appended to all page title tags.</div>
	</td>
</tr>


<tr>
	<td><label for="metadescription">Meta Description</label>:</td>
	<td>
		<textarea id="metadescription" name="metadescription">{$fsite->meta_description}</textarea>
		<div class="hint">Stem meta-description appended to all page meta description tags.</div>
	</td>
</tr>

<tr>
	<td><label for="metakeywords">Meta Keywords</label>:</td>
	<td>
		<textarea id="metakeywords" name="metakeywords">{$fsite->meta_keywords}</textarea>
		<div class="hint">Stem meta-keywords appended to all page meta keyword tags.</div>
	</td>
</tr>

<tr>
	<td><label for="footer">Footer</label>:</td>
	<td>
		<textarea id="footer" name="footer">{$fsite->footer}</textarea>
		<div class="hint">Displayed in the footer section of every public page.</div>
	</td>
</tr>

<tr>
	<td><label for="style">Theme</label>:</td>
	<td>
		{html_options id="style" name='style' values=$themelist output=$themelist selected=$fsite->style}
		<div class="hint">The theme folder which will be loaded for css and images. (Use / for default)</div>
	</td>
</tr>

<tr>
	<td><label for="email">Email</label>:</td>
	<td>
		<input id="email" class="long" name="email" type="text" value="{$fsite->email}" />
		<div class="hint">Shown in the contact us page, and where the contact html form is sent to.</div>
	</td>
</tr>

<tr>
	<td><label for="tandc">Terms and Conditions</label>:</td>
	<td>
		<textarea id="tandc" name="tandc">{$fsite->tandc}</textarea>
		<div class="hint">Text displayed in the terms and conditions page.</div>
	</td>
</tr>

</table>
</fieldset>

<fieldset>
<legend>Google Adsense and Analytics</legend>
<table class="input">
<tr>
	<td style="width:160px;"><label for="google_analytics_acc">Google Analytics</label>:</td>
	<td>
		<input id="google_analytics_acc" name="google_analytics_acc" type="text" value="{$fsite->google_analytics_acc}" />
		<div class="hint">e.g. UA-xxxxxx-x</div>
	</td>
</tr>

<tr>
	<td><label for="google_adsense_menu">Google Adsense Menu</label>:</td>
	<td>
		<input id="google_adsense_menu" name="google_adsense_menu" type="text" value="{$fsite->google_adsense_menu}" />
		<div class="hint">The ID of the google adsense link panel displayed at the top of every page.</div>
	</td>
</tr>

<tr>
	<td><label for="google_adsense_sidepanel">Google Adsense Sidepanel</label>:</td>
	<td>
		<input id="google_adsense_sidepanel" name="google_adsense_sidepanel" type="text" value="{$fsite->google_adsense_sidepanel}" />
		<div class="hint">The ID of a google skyscraper link panel displayed at the right side of every page.</div>
	</td>
</tr>

<tr>
	<td><label for="google_adsense_search">Google Adsense Search</label>:</td>
	<td>
		<input id="google_adsense_search" name="google_adsense_search" type="text" value="{$fsite->google_adsense_search}" />
		<div class="hint">The ID of the google search ad panel displayed at the bottom of the left menu.</div>
	</td>
</tr>

</table>
</fieldset>

<fieldset>
<legend>Usenet Settings</legend>
<table class="input">

<tr>
	<td style="width:160px;"><label for="groupfilter">Usenet Group Filter</label>:</td>
	<td>
		<textarea id="groupfilter" name="groupfilter">{$fsite->groupfilter}</textarea>
		<div class="hint">Regex of groups which are to be polled for binaries. e.g. alt.binaries.cd.image.linux|alt.binaries.warez.linux</div>
	</td>
</tr>

</table>
</fieldset>

<fieldset>
<legend>3rd Party Integration</legend>
<table class="input">

<tr>
	<td style="width:160px;"><label for="apikey">Api Key</label>:</td>
	<td>
		<input id="apikey" class="long" name="apikey" type="text" value="{$fsite->apikey}" />
		<div class="hint">The site wide API key which can be used by 3rd parties when calling the /api functions.</div>
	</td>
</tr>

</table>
</fieldset>

<fieldset>
<legend>User Settings</legend>
<table class="input">

<tr>
	<td style="width:160px;"><label for="registerstatus">Registration Status</label>:</td>
	<td>
		{html_radios id="registerstatus" name='registerstatus' values=$registerstatus_ids output=$registerstatus_names selected=$fsite->registerstatus separator='<br />'}
		<div class="hint">The status of registrations to the site.</div>
	</td>
</tr>

</table>
</fieldset>

<input type="submit" value="Save Site Settings" />

</form>

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
	<td><label for="style">Dereferrer Link</label>:</td>
	<td>
		<input id="dereferrer_link" class="long" name="dereferrer_link" type="text" value="{$fsite->dereferrer_link}" />
		<div class="hint">Optional URL to prepend to external links</div>
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

<tr>
	<td><label for="nzbpath">Nzb File Path</label>:</td>
	<td>
		<input id="nzbpath" class="long" name="nzbpath" type="text" value="{$fsite->nzbpath}" />
		<div class="hint">The directory where nzb files will be stored.</div>
	</td>
</tr>

<tr>
	<td><label for="rawretentiondays">Raw Search Retention</label>:</td>
	<td>
		<input class="small" id="rawretentiondays" name="rawretentiondays" type="text" value="{$fsite->rawretentiondays}" />
		<div class="hint">The number of days binary and part data will be retained for use in raw search, regardless of other processes.</div>
	</td>
</tr>

<tr>
	<td><label for="attemptgroupbindays">Days to Attempt To Group</label>:</td>
	<td>
		<input class="tiny" id="attemptgroupbindays" name="attemptgroupbindays" type="text" value="{$fsite->attemptgroupbindays}" />
		<div class="hint">The number of days an attempt will be made to group binaries into releases after being added.</div>
	</td>
</tr>

<tr>
	<td><label for="lookupnfo">Lookup Nfo</label>:</td>
	<td>
		{html_radios id="lookupnfo" name='lookupnfo' values=$yesno_ids output=$yesno_names selected=$fsite->lookupnfo separator='<br />'}
		<div class="hint">Whether to attempt to retrieve the an nfo file from usenet when processing binaries.<br/><strong>NOTE: disabling nfo lookups will disable movie lookups.</strong></div>
	</td>
</tr>


<tr>
	<td><label for="lookuptvrage">Lookup TV Rage</label>:</td>
	<td>
		{html_radios id="lookuptvrage" name='lookuptvrage' values=$yesno_ids output=$yesno_names selected=$fsite->lookuptvrage separator='<br />'}
		<div class="hint">Whether to attempt to lookup tv rage ids on the web when processing binaries.</div>
	</td>
</tr>

<tr>
	<td><label for="lookupimdb">Lookup Movies</label>:</td>
	<td>
		{html_radios id="lookupimdb" name='lookupimdb' values=$yesno_ids output=$yesno_names selected=$fsite->lookupimdb separator='<br />'}
		<div class="hint">Whether to attempt to lookup film information from IMDB or TheMovieDB when processing binaries.</div>
	</td>
</tr>

<tr>
	<td><label for="compressedheaders">Use Compressed Headers</label>:</td>
	<td>
		{html_radios id="compressedheaders" name='compressedheaders' values=$yesno_ids output=$yesno_names selected=$fsite->compressedheaders separator='<br />'}
		<div class="hint">Some USPs allow the use of XZOVER to download the message headers in gzipped format. If enabled this will use much less bandwidth, but not be significantly faster.</div>
	</td>
</tr>


<tr>
	<td><label for="maxmssgs">Max Messages</label>:</td>
	<td>
		<input class="small" id="maxmssgs" name="maxmssgs" type="text" value="{$fsite->maxmssgs}" />
		<div class="hint">The maximum number of messages to fetch at a time from the server.</div>
	</td>
</tr>
<tr>
        <td><label for="newgroupscanmethod">Where to start new groups</label>:</td>
        <td>
                <label><input type="radio" name="newgroupscanmethod" value="1" id="newgroupscanmethod" /><input class="tiny" id="newgroupdaystoscan" name="newgroupdaystoscan" type="text" value="3" /> Days</label><br />

<label><input type="radio" name="newgroupscanmethod" value="0" checked="checked" id="newgroupscanmethod" /><input class="small" id="newgroupmsgstoscan" name="newgroupmsgstoscan" type="text" value="50000" /> Posts</label><br />
                <div class="hint">For newly added groups, do we go back _ days or _ posts?</div>
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

<tr>
	<td><label for="storeuserips">Store User Ip</label>:</td>
	<td>
		{html_radios id="storeuserips" name='storeuserips' values=$yesno_ids output=$yesno_names selected=$fsite->storeuserips separator='<br />'}
		<div class="hint">Whether to store the users ip address when they signup or login.</div>
	</td>
</tr>

</table>
</fieldset>

<input type="submit" value="Save Site Settings" />

</form>

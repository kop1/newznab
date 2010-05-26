
<h1>Profile for {$user.username|escape:"htmlall"}</h1>

<table class="data">
	<tr><th>Username:</th><td>{$user.username|escape:"htmlall"}</td></tr>
	{if $user.ID==$userdata.ID || $userdata.role==2}<tr><th title="Not public">Email:</th><td>{$user.email}</td></tr>{/if}
	<tr><th>Registered:</th><td title="{$user.createddate}">{$user.createddate|date_format}</td></tr>
	<tr><th>Grabs:</th><td>{$user.grabs}</td></tr>
</table>


<br/><br/>
<h2>SABnzbd Integration</h2>

<table class="data">
	<tr><th title="Not public">SABnzbd API Key:</th><td><input id="profile_sab_apikey" type="text" size="40" /></td></tr>
	<tr><th title="Not public">SABnzbd Host:</th><td><input id="profile_sab_host" type="text" size="40" value="http://localhost:8080/sabnzbd/"/><br/><small><i>for example:</i> http://localhost:8080/sabnzbd/</small></td></tr>
	<tr><th title="Not public"></th><td>
		<input id="profile_sab_clear" type="button" value="Clear" style="float:right;" />
		<input id="profile_sab_save" type="button" value="Save to Cookie" style="float:left;" />
		<div class="icon"></div>
		</td></tr>
</table>


{if $commentslist|@count > 0}
<div style="padding-top:20px;">
	<a id="comments"></a>
	<h2>Comments</h2>

	{$pager}

	<table style="margin-top:10px;" class="data Sortable">

		<tr>
			<th>date</th>
			<th>comment</th>
		</tr>

		
		{foreach from=$commentslist item=comment}
		<tr>
			<td width="80" title="{$comment.createddate}">{$comment.createddate|date_format}</td>
			<td>{$comment.text|escape:"htmlall"|nl2br}</td>
		</tr>
		{/foreach}
	</table>
</div>
{/if}
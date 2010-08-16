
<h1>Edit your profile</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<br/><br/>

<form action="{$SCRIPT_NAME}?action=submit" method="post">

<h2>User Details</h2>
<table class="data">
	<tr><th>Username:</th><td>{$user.username|escape:"htmlall"}</td></tr>
	<tr><th>Email:</th><td><input id="email" name="email" type="text" value="{$user.email|escape:"htmlall"}"></input></td></tr>
	<tr><th>Password:</th><td><input id="password" name="password" type="password" value=""></input>
		<div class="hint">Only enter your password if you want to change it.</div>
	</td></tr>
	<tr><th>Confirm Password:</th><td><input id="confirmpassword" name="confirmpassword" type="password" value=""></input>
	</td></tr>
	<tr><th>Site Api/Rss Key:</th><td>{$user.rsstoken}<br/><a onclick="return confirm('Are you sure?');" href="?action=newapikey">Generate</a></td></tr>
	<tr><th></th><td><input type="submit" value="Save" /></td></tr>
</table>

<br/><br/>

</form>

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

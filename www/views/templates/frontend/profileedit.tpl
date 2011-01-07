 
<h1>Edit your profile</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<br/><br/>

<form action="profileedit?action=submit" method="post">

<h2>User Details</h2>
<table class="data">
	<tr><th>Username:</th><td>{$user.username|escape:"htmlall"}</td></tr>
	<tr><th>Email:</th><td><input id="email" name="email" type="text" value="{$user.email|escape:"htmlall"}"></input></td></tr>
	<tr><th>Password:</th>
		<td>
			<input autocomplete="off" id="password" name="password" type="password" value=""></input>
			<div class="hint">Only enter your password if you want to change it.</div>
		</td>
	</tr>
	<tr><th>Confirm Password:</th><td><input autocomplete="off" id="confirmpassword" name="confirmpassword" type="password" value=""></input>
	</td></tr>
	<tr><th>Site Api/Rss Key:</th><td>{$user.rsstoken}<br/><a class="confirm_action" href="?action=newapikey">Generate</a></td></tr>
	<tr><th>View Movie Page:</th>
		<td>
			<input id="movieview" name="movieview" value="1" type="checkbox" {if $user.movieview=="1"}checked="checked"{/if}></input>
			<div class="hint">Browse movie covers. Only shows movies with known IMDB info.</div>
		</td>
	</tr>
	<tr><th>Excluded Categories:</th>
		<td>
			{html_options style="height:105px;" multiple=multiple name=exccat[] options=$catlist selected=$userexccat}
			<div class="hint">Use Ctrl and click to exclude multiple categories.</div>
		</td>
	</tr>
	<tr><th></th><td><input type="submit" value="Save" /></td></tr>
</table>

<br/><br/>

</form>

<h2>SABnzbd Integration</h2>
<table class="data">
	<tr>
		<th title="Not public">API Key:</th>
		<td>
			<input id="profile_sab_apikey" type="text" size="40" />
		</td>
	</tr>
	<tr>
		<th title="Not public">Hostname:</th>
		<td>
			<input id="profile_sab_host" type="text" size="40" value="http://localhost:8080/sabnzbd/"/>
			<div class="hint">for example: http://localhost:8080/sabnzbd/</div>
		</td>
	</tr>

	<tr>
		<th title="Not public">Added Priority:</th>
		<td>
			<select id="profile_sab_priority">
				<option value="1">High</option>
				<option value="2">Normal</option>
				<option value="3">Low</option>
			</select>
		</td>
	</tr>

	<tr><th title="Not public"></th><td>
		<input id="profile_sab_clear" type="button" value="Clear" style="float:right;" />
		<input id="profile_sab_save" type="button" value="Save to Cookie" style="float:left;" />
		<div class="icon"></div>
		</td></tr>
</table>

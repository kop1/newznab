
<h1>Register</h1>

<form method="post" action="{$SCRIPT_NAME}?action=submit">

	{if $error != ''}
		<div class="error">{$error}</div>
	{/if}

	<table class="data">
		<tr><td class="label">Username:</td><td><input name="username" value="{$username}" type="text"/></td></tr>
		<tr><td class="label">Password:</td><td><input name="password" value="{$password}" type="password"/></td></tr>
		<tr><td class="label">Confirm Password:</td><td><input name="confirmpassword" value="{$confirmpassword}" type="password"/></td></tr>
		<tr><td class="label">Email:</td><td><input name="email" value="{$email}"/></td></tr>
		<tr><td></td><td><input type="submit" value="Register"/></td></tr>
	</table>

</form>

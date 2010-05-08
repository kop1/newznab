
<h1>Register</h1>

<form method="post" action="{$SCRIPT_NAME}?action=submit">

	{if $error != ''}
		<div class="error">{$error}</div>
	{/if}

	<table class="data">
		<tr><th>Username:</th><td><input name="username" value="{$username}" type="text"/></td></tr>
		<tr><th>Password:</th><td><input name="password" value="{$password}" type="password"/></td></tr>
		<tr><th>Confirm Password:</th><td><input name="confirmpassword" value="{$confirmpassword}" type="password"/></td></tr>
		<tr><th>Email:</th><td><input name="email" value="{$email}"/></td></tr>
		<tr><th></th><td><input type="submit" value="Register"/></td></tr>
	</table>

</form>


<h1>Register</h1>

<form method="post" action="{$SCRIPT_NAME}?action=submit">

	{if $error != ''}
		<div class="error">{$error}</div>
	{/if}

	<table class="data">
		<tr><th><label for="username">Username</label>:</th><td><input id="username" name="username" value="{$username}" type="text"/></td></tr>
		<tr><th><label for="password">Password</label>:</th><td><input id="password" name="password" value="{$password}" type="password"/></td></tr>
		<tr><th><label for="confirmpassword">Confirm Password</label>:</th><td><input id="confirmpassword" name="confirmpassword" value="{$confirmpassword}" type="password"/></td></tr>
		<tr><th><label for="email">Email</label>:</th><td><input id="email" name="email" value="{$email}"/></td></tr>
		<tr><th></th><td><input type="submit" value="Register"/></td></tr>
	</table>

</form>

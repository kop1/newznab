
<h1>Login</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<form method="post">
	<table class="data">
		<tr><th>Username:</th><td><input value="{$username}" name="username" type="text"/></td></tr>
		<tr><th>Password:</th><td><input name="password" type="password"/></td></tr>
		<tr><th</th><td><input type="submit" value="Login"/></td></tr>
	</table>
</form>

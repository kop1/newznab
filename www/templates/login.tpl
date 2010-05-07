
<h1>Login</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<form method="post">
	<table class="data">
		<tr><td class="label">Username:</td><td><input value="{$username}" name="username" type="text"/></td></tr>
		<tr><td class="label">Password:</td><td><input name="password" type="password"/></td></tr>
		<tr><td></td><td><input type="submit" value="Login"/></td></tr>
	</table>
</form>

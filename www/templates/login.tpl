
<h1>Login</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<form method="post">
	<table class="data">
		<tr><th><label for="username">Username</label>:</th><td><input id="username" value="{$username}" name="username" type="text"/></td></tr>
		<tr><th><label for="password">Password</label>:</th><td><input id="password" name="password" type="password"/></td></tr>
		<tr><th</th><td><input type="submit" value="Login"/></td></tr>
	</table>
</form>

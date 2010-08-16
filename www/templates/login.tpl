
<h1>Login</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<form method="post">
	<table class="data">
		<tr><th><label for="username">Username</label>:</th>
			<td>
				<input style="width:120px;" id="username" value="{$username}" name="username" type="text"/>
			</td></tr>
		<tr><th><label for="password">Password</label>:</th>
			<td>
				<input style="width:120px;" id="password" name="password" type="password"/>
			</td></tr>
		<tr><th><label for="rememberme">Remember Me</label>:</th><td><input id="rememberme" {if $rememberme == 1}checked="checked"{/if} name="rememberme" type="checkbox"/></td>
		<tr><th></th><td><input type="submit" value="Login"/></td></tr>
	</table>
</form>
<br/>
<a href="{$smarty.const.WWW_TOP}/forgottenpassword.php">Forgotten your password?</a>

{if !$cfg->doCheck || $cfg->error}

<p>You must setup an admin user. Please provide the following information:</p>
<form action="?" method="post">
	<table width="100%" border="0" style="margin-top:10px;" class="data highlight">
		<tr class="alt">
			<td>Username:</td>
			<td><input type="text" name="user" value="{$cfg->ADMIN_USER}" /></td>
		</tr>
		<tr class="">
			<td>Password:</td>
			<td><input type="text" name="pass" value="{$cfg->ADMIN_PASS}" /></td>
		</tr>
		<tr class="alt">
			<td>Email: </td>
			<td><input type="text" name="email" value="{$cfg->ADMIN_EMAIL}" /></td>
		</tr>
		<tr class="">
			<td colspan="2">
			{if $cfg->error}
				The following error was encountered:<br />
				{if $cfg->ADMIN_USER == ''}<span class="error">&bull; Invalid username</span><br />{/if}
				{if $cfg->ADMIN_PASS == ''}<span class="error">&bull; Invalid password</span><br />{/if}
				{if $cfg->ADMIN_EMAIL == ''}<span class="error">&bull; Invalid email</span><br />{/if}
				<br />
			{/if}
			<input type="submit" value="Create Admin User" />
			</td>
		</tr>
	</table>
</form>

{/if}

{if $cfg->doCheck && !$cfg->error}
	<div align="center">
		<p>The admin user has been setup, you may continue to the next step.</p>
		<form action="step6.php"><input type="submit" value="Step Six: Set NZB File Path" /></form> 
	</div>             
{/if}

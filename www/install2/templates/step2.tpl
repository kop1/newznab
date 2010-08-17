{if !$cfg->doCheck || $cfg->error}

<p>We need some information about your MySQL database, please provide the following information</p>
<p>Note: If your database already exists, <u>it will be overwritten</u> with this version.</p>
<form action="?" method="post">
	<table width="100%" border="0" style="margin-top:10px;" class="data highlight">
		<tr class="">
			<td><label for="host">Hostname:</label></td>
			<td><input type="text" name="host" id="host" value="{$cfg->DB_HOST}" /></td>
		</tr>
		<tr class="alt">
			<td><label for="user">Username:</label></td>
			<td><input type="text" name="user" id="user" value="{$cfg->DB_USER}" /></td>
		</tr>
		<tr class="">
			<td><label for="pass">Password:</label></td>
			<td><input type="text" name="pass" id="pass" value="{$cfg->DB_PASSWORD}" /></td>
		</tr>
		<tr class="alt">
			<td><label for="db">Database:</label></td>
			<td><input type="text" name="db" id="db" value="{$cfg->DB_NAME}" /></td>
		</tr>
		<tr class="">
			<td colspan="2">
			{if $cfg->error}
				The following error(s) were encountered:<br />
				{if $cfg->dbConnCheck === false}<span class="error">&bull; Unable to connect to database</span><br />{/if}
				{if $cfg->dbNameCheck === false}<span class="error">&bull; Unable to select database</span><br />{/if}
				<br />
			{/if}
			<input type="submit" value="Setup Database" />
			</td>
		</tr>
	</table>
</form>

{/if}

{if $cfg->doCheck && !$cfg->error}
	<div align="center">
		<p>The database setup is correct, you may continue to the next step.</p>
		<form action="step3.php"><input type="submit" value="Step three: Setup news server connection" /></form> 
	</div>             
{/if}

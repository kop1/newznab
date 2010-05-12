
<h1>{$page->title}</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td>Name:</td>
	<td>
		<input type="hidden" name="id" value="{$user.ID}" />
		<input class="long" name="username" type="text" value="{$user.username}" />
	</td>
</tr>

<tr>
	<td>Email:</td>
	<td>
		<input class="long" name="email" type="text" value="{$user.email}" />
	</td>
</tr>

<tr>
	<td>Grabs:</td>
	<td>
		<input class="short" name="grabs" type="text" value="{$user.grabs}" />
	</td>
</tr>

<tr>
	<td>Role:</td>
	<td>
		{if $user.role == "2"}Admin{else}User{/if}
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<input type="submit" value="Save" />
	</td>
</tr>

</table>

</form>
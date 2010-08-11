
<h1>{$page->title}</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

{if $confirmed == ''}
	<p>
		Please enter the email address you used to register and we will send an email to reset your password. If you cannot remember your email, or no longer have access to it, please <a href="{$smarty.const.WWW_TOP}/contact-us.php">contact us</a>.
	</p>

	<form method="post" action="{$SCRIPT_NAME}?action=submit">

		<table class="data">
			<tr><th><label for="email">Email</label>: <em>*</em></th>
				<td>
					<input id="email" autocomplete="off" name="email" value="{$email}" type="email"/>
				</td>
			</tr>
			<tr><th></th><td><input type="submit" /><div style="float:right;" class="hint"><em>*</em> Indicates mandatory field.</div></td></tr>
		</table>
	</form>
{else}
	<p>
		Your password has been reset and sent to you in an email.
	</p>
{/if}


<h1>Profile for {$user.username|escape:"htmlall"}</h1>

<table class="data">
	<tr><th>Username:</th><td>{$user.username|escape:"htmlall"}</td></tr>
	{if $user.ID==$userdata.ID || $userdata.role==2}<tr><th title="Not public">Email:</th><td>{$user.email}</td></tr>{/if}
	{if $user.ID==$userdata.ID || $userdata.role==2}<tr><th title="Not public">Sab Api Key:</th><td>{$user.sabapikey}</td></tr>{/if}
	{if $user.ID==$userdata.ID || $userdata.role==2}<tr><th title="Not public">Sab Host:</th><td><a href="{$user.sabhost}">{$user.sabhost}</a></td></tr>{/if}
	<tr><th>Registered:</th><td title="{$user.createddate}">{$user.createddate|date_format}</td></tr>
	<tr><th>Grabs:</th><td>{$user.grabs}</td></tr>
</table>


{if $commentslist|@count > 0}
<div style="padding-top:20px;">
	<a id="comments"></a>
	<h2>Comments</h2>

	{$pager}

	<table style="margin-top:10px;" class="data Sortable">

		<tr>
			<th>date</th>
			<th>comment</th>
		</tr>

		
		{foreach from=$commentslist item=comment}
		<tr>
			<td width="80" title="{$comment.createddate}">{$comment.createddate|date_format}</td>
			<td>{$comment.text|escape:"htmlall"|nl2br}</td>
		</tr>
		{/foreach}
	</table>
</div>
{/if}
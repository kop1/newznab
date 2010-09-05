{if !$cfg->doCheck || $cfg->error}

<p>Please note that the following function may take up to 5 minutes to run.</p>
<form action="?" method="post">
	<table width="100%" border="0" style="margin-top:10px;" class="data highlight">
		<tr class="alt">
			<td colspan="2">
			{if $cfg->error}
				An error was encountered<br />
				<br />
			{/if}
			<input type="submit" value="Install Sample Data" />
			</td>
		</tr>
	</table>
</form>

{/if}

{if $cfg->doCheck && !$cfg->error}
	<div align="center">
		<h1>Sample Data Installed!</h1>
		<h3>Processed {$proccount} releases.</h3>
		<p><a href="../browse">Browse</a> the sample data</p>
		<p>or</p>
		<p>Continue to <a href="../admin/site-edit.php">Site Edit</a> to give your site a name.</p>
	</div>
{/if}

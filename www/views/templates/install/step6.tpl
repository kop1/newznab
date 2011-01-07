{if $page->isSuccess()}
	<div align="center">
		<h1>Install Complete!</h1>
		<p>First time users may want to install some sample data to get started:<br /><a href="step7.php">Install Sample Data</a></p>
		<p>or</p>
		<h3>Continue to <a href="../admin/site-edit.php">Site Edit</a> to give your site a name.</h3>
		<p>&nbsp;</p>
		<h3><b >Note:</b> It is a good idea to remove the www/install directory after setup</h3>
	</div>   
{else}

<p>You must set the NZB file path. This is the location where the NZB files are stored:</p>
<form action="?" method="post">
	<table width="100%" border="0" style="margin-top:10px;" class="data highlight">
		<tr class="alt">
			<td>Location:</td>
			<td><input type="text" name="nzbpath" value="{$cfg->NZB_PATH}" size="70" /></td>
		</tr>
		<tr class="">
			<td colspan="2">
			{if $cfg->error}
				The following error was encountered:<br />
				{if !$cfg->nzbPathCheck}<br /><span class="error">The installer cannot write to {$cfg->NZB_PATH}. A quick solution is to run:<br />chmod -R 777 {$cfg->NZB_PATH}</span><br />{/if}
				<br />
			{/if}
			<input type="submit" value="Set NZB File Path" />
			</td>
		</tr>
	</table>
</form>

{/if}
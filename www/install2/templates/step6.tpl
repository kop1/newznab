<h1>{$page->title}</h1>

{if !$cfg->doCheck || $cfg->error}

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

{if $cfg->doCheck && !$cfg->error}
	<div align="center">
		<p>The NZB File Path has been set, you may continue to the next step.</p>
		<form action="../admin/group-edit.php"><input type="submit" value="Step seven: Add Groups" /></form> 
	</div>             
{/if}

<div align="center">
{if !$cfg->error}
	<p>The configuration has been saved, you may continue to the next step.</p>
	<form action="step5.php"><input type="submit" value="Step five: Setup admin user" /></form>
{else}
	<div class="error">Error saving {$cfg->WWW_DIR}/config.php.</div> 
{/if}
</div>
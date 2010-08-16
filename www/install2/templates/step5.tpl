<h1>{$page->title}</h1>

<div align="center">
{if !$cfg->error}
	<p>The configuration has been saved, you may continue to the next step.</p>
	<form action="step6.php"><input type="submit" value="Step six: Set NZB File Path" /></form>
{else}
	<div class="error">Error saving {$cfg->WWW_DIR}/config.php.</div> 
{/if}
</div>
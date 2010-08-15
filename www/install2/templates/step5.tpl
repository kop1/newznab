<h1>{$page->title}</h1>

<div align="center">
{if !$cfg->error}
	<h2>Install Complete</h2>
	<p>Please proceed to configure your site.</p>
	<p>Make sure to set the NZB File Path.</p>
	<form action="../admin/site-edit.php"><input type="submit" value="Configure Site" /></form>              
{else}
	<div class="error">Error saving {$cfg->WWW_DIR}/config.php.</div> 
{/if}
</div>
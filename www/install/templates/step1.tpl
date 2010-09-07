<table width="100%" border="0" style="margin-top:10px;" class="data highlight">
	<tr>
		<th>check</th>
		<th style="width:75px;">status</th>
	</tr>
	<tr class="">
		<td>Checking for sha1():{if !$cfg->sha1Check}<br /><span class="error">The PHP installation lacks support for sha1.</span>{/if}</td>
		<td>{if $cfg->sha1Check}<span class="success">OK</span>{else}<span class="error">Error</span>{/if}</td>
	</tr>
	<tr class="alt">
		<td>Checking for mysql_connect():{if !$cfg->mysqlCheck}<br /><span class="error">The PHP installation lacks support for MySQL(mysql_connect).</span>{/if}</td>
		<td>{if $cfg->mysqlCheck}<span class="success">OK</span>{else}<span class="error">Error</span>{/if}</td>
	</tr>
	<tr class="alt">
		<td>Checking for GD support:{if !$cfg->gdCheck}<br /><span class="warn">The PHP installation lacks support for GD.</span>{/if}</td>
		<td>{if $cfg->gdCheck}<span class="success">OK</span>{else}<span class="warn">Warning</span>{/if}</td>
	</tr>
	<tr class="">
		<td>Checking that Smarty cache is writeable:{if !$cfg->cacheCheck}<br /><span class="error">The template cache folder must be writable. A quick solution is to run:<br />chmod 777 {$cfg->SMARTY_DIR}/templates_c</span>{/if}</td>
		<td>{if $cfg->cacheCheck}<span class="success">OK</span>{else}<span class="error">Error</span>{/if}</td>
	</tr>
	<tr class="alt">
		<td>Checking that covers dir is writeable:{if !$cfg->coversCheck}<br /><span class="error">The images/covers dir must be writable. A quick solution is to run:<br />chmod 777 {$cfg->WWW_DIR}/images/covers</span>{/if}</td>
		<td>{if $cfg->coversCheck}<span class="success">OK</span>{else}<span class="error">Error</span>{/if}</td>
	</tr>
	<tr class="">
		<td>Checking that config.php is writeable:{if !$cfg->configCheck}<br /><span class="error">The installer cannot write to {$cfg->WWW_DIR}/config.php. A quick solution is to run:<br />chmod 777 {$cfg->WWW_DIR}/config.php</span>{/if}</td>
		<td>{if $cfg->configCheck}<span class="success">OK</span>{else}<span class="error">Error</span>{/if}</td>
	</tr>
	<tr class="alt">
		<td>Checking that install.lock is writeable:{if !$cfg->lockCheck}<br /><span class="error">The installer cannot write to {$cfg->INSTALL_DIR}/install.lock. A quick solution is to run:<br />chmod 777 {$cfg->INSTALL_DIR}</span>{/if}</td>
		<td>{if $cfg->lockCheck}<span class="success">OK</span>{else}<span class="error">Error</span>{/if}</td>
	</tr>
	<tr class="">
		<td>Checking for Pear Net_NNTP:{if !$cfg->pearCheck}<br /><span class="error">The PEAR package 'Net_NNTP' is missing. This can normally be fixed by running:<br />pear install Net_NNTP</span>{/if}</td>
		<td>{if $cfg->pearCheck}<span class="success">OK</span>{else}<span class="error">Error</span>{/if}</td>
	</tr>
	<tr class="alt">
		<td>Checking for schema.sql file:{if !$cfg->schemaCheck}<br /><span class="error">The schema.sql file is missing, please make sure it is placed in: {$cfg->DB_DIR}/schema.sql</span>{/if}</td>
		<td>{if $cfg->schemaCheck}<span class="success">OK</span>{else}<span class="error">Error</span>{/if}</td>
	</tr>
	<tr class="">
		<td>Checking max_execution_time:{if !$cfg->timelimitCheck}<br /><span class="warn">Your PHP installation's max_execution_time setting is low, please consider increasing it >= 60</span>{/if}</td>
		<td>{if $cfg->timelimitCheck}<span class="success">OK</span>{else}<span class="warn">Warning</span>{/if}</td>
	</tr>
	<tr class="alt">
		<td>Checking PHP's memory_limit:{if !$cfg->memlimitCheck}<br /><span class="warn">Your PHP installation's memory_limit setting is low, please consider increasing it >= 256MB</span>{/if}</td>
		<td>{if $cfg->memlimitCheck}<span class="success">OK</span>{else}<span class="warn">Warning</span>{/if}</td>
	</tr>
</table>

<div align="center">
{if !$cfg->error}
	<p>No problems were found and you are ready to install.</p>
	<form action="step2.php"><input type="submit" value="Go to step two: Set up the database" /></form>              
{else}
	<div class="error">Errors encountered - Newznab will not function correctly unless these problems are solved.</div> 
{/if}
</div>

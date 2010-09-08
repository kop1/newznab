
<h1>{$page->title}</h1>

<p>
	<ol style="list-style-type:decimal;">
		<li><a href="{$smarty.const.WWW_TOP}/site-edit.php">Edit site</a> settings.</li>
		<li><a href="{$smarty.const.WWW_TOP}/group-edit.php">Add groups</a> to the site.</li>
		<li><a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/binary-update.php">Get</a> latest headers for active groups.</li>
		<li><a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/release-update.php">Update</a> releases.</li>
		<li>Rinse and repeat.</li>
	</ol>
</p>

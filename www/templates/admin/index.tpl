
<h1>{$page->title}</h1>

<p>
	<ol style="list-style-type:decimal;">
		<li><a href="{$smarty.const.WWW_TOP}/site-edit.php">Edit site</a> settings.</li>
		<li><a href="{$smarty.const.WWW_TOP}/group-update.php">Update group</a> list based on group regex.</li>
		<li><a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/binary-update.php">Get</a> latest headers for active groups.</li>
		<li><a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/release-update.php">Update</a> releases.</li>
		<li>While groupfilter not changed goto 3.</li>
	</ol>
</p>

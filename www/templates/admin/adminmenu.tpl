		<h2>Admin Functions</h2> 
		<ul>
		<li><a title="Home" href="{$smarty.const.WWW_TOP}/../">Home</a></li>
		<li><a title="Admin Home" href="{$smarty.const.WWW_TOP}/">Admin Home</a></li>
		<li><a title="Edit Site" href="{$smarty.const.WWW_TOP}/site-edit.php">Edit Site</a></li>
		<li><a href="{$smarty.const.WWW_TOP}/content-add.php?action=add">Add</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/content-list.php">Edit</a> Content Page</li>
		<li><a href="{$smarty.const.WWW_TOP}/category-list.php?action=add">Edit</a> Categories</li>
		<li><a href="{$smarty.const.WWW_TOP}/group-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/group-edit.php">Add</a> <a style="padding:0;" onclick="return confirm('Are you sure?');" href="{$smarty.const.WWW_TOP}/group-update.php">Update</a> Groups</li>
		<li><a href="{$smarty.const.WWW_TOP}/regex-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/regex-edit.php?action=add">Add</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/regex-test.php">Test</a> Regex</li>
		<li><a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/binary-update.php">Get Latest Headers</a></li>
		<li><a href="{$smarty.const.WWW_TOP}/release-list.php">View</a> <a style="padding:0;" onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/release-update.php">Update</a> Releases</li>
		<li><a href="{$smarty.const.WWW_TOP}/rage-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/rage-edit.php?action=add">Add</a> TVRage List</li>
		<li><a href="{$smarty.const.WWW_TOP}/rage-process.php">Process</a> TV Manually</li>
		<li><a href="{$smarty.const.WWW_TOP}/movie-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/movie-add.php">Add</a> Movie List</li>
		<li><a href="{$smarty.const.WWW_TOP}/nzb-import.php">Import</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/nzb-export.php">Export</a> Nzb's</li>
		<li><a href="{$smarty.const.WWW_TOP}/db-optimise.php">Optimise</a> Tables</li>
		<li><a href="{$smarty.const.WWW_TOP}/comments-list.php">View Comments</a></li>
		<li><a href="{$smarty.const.WWW_TOP}/user-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/user-edit.php?action=add">Add</a> Users</li>
		<li><a href="{$smarty.const.WWW_TOP}/site-stats.php">Site Stats</a></li>
		</ul>

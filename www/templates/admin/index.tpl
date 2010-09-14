
<h1>{$page->title}</h1>

<p>
		Welcome to newznab. In this area you will be able to configure many aspects of your site.<br>
		If this is your first time here, you need to start the scripts which will fill newznab.
</p>

		<ol style="list-style-type:decimal;">
		<li>Configure your <a href="{$smarty.const.WWW_TOP}/site-edit.php">site options</a>. The defaults will probably work fine.</li>
		<li>There a default list of usenet groups provided. To get started, you will need to <a href="{$smarty.const.WWW_TOP}/group-list.php">enable some groups.</a>
		You can <a href="{$smarty.const.WWW_TOP}/group-edit.php">add your own groups</a> manually.</li>
		<li>Next you will want to <a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/binary-update.php">get the latest headers.</a> <b>This should be done from the command line</b>, using the linux or windows shell scripts, as it can take some time.</li>
		</li>
		<li>After obtaining headers, the next step is to <a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/release-update.php">create releases.</a> <b>This is best done from the command line.</b>
		</li>
		</ol>
<br/><br/>
	<p>
	<b>Note: We recommend using the linux screen or init.d scripts, or if you are a Windows user, batch files to update headers and create releases. They can be found in /misc/update_scripts
	</p>

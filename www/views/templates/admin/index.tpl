 
<h1>{$page->title}</h1>

<p>
		Welcome to newznab. In this area you will be able to configure many aspects of your site.<br>
		If this is your first time here, you need to start the scripts which will fill newznab.
</p>

		<ol style="list-style-type:decimal;">
		<li>Configure your <a href="{$smarty.const.WWW_TOP}/site-edit.php">site options</a>. The defaults will probably work fine.</li>
		<li>There a default list of usenet groups provided. To get started, you will need to <a href="{$smarty.const.WWW_TOP}/group-list.php">enable some groups.</a>
		You can <a href="{$smarty.const.WWW_TOP}/group-edit.php">add your own groups</a> manually.</li>
		<li>Go and sign up for api keys from <a href="http://www.themoviedb.org/account/signup">tmdb</a> and <a href="http://aws.amazon.com/">amazon</a>.</li>
		<li>Next you will want to get the latest headers. <b>This should be done from the command line</b>, using the linux or windows shell scripts found in /misc/update_scripts/cron_scripts (or batch_scripts for windows users), as it can take some time.</li>
		</li>
		<li>After obtaining headers, the next step is to create releases. <b>This is best done from the command line</b> using the linux or windows shell scripts found in /misc/update_scripts/cron_scripts (or batch_scripts for windows users).
		</li>
		</ol>

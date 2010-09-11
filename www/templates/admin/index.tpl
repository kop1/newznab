
<h1>{$page->title}</h1>
<br>
	<ol style="list-style-type:decimal;">
		Welcome to the Admin Hangout for Newznab! In this area you will be able to configure many aspects of your site.<br>
		There are some unique features of Newznab that we would like to share with you. First we are going to go over the <br>
		basic and most important parts of configuring a Newznab site. To return to this page, simply click Admin Home on the left<br>
		toolbar.
		
		<p><p>
		<li>The first thing you should do upon installing Newznab is to configure your <a href="{$smarty.const.WWW_TOP}/site-edit.php">site options</a></li>
		<li>As a feature of Newznab we have provided you a default list of groups that only need to be enabled to work. To get started, you will need to <a href="{$smarty.const.WWW_TOP}/group-list.php">enable some groups.</a>
		You can also feel free to <a href="{$smarty.const.WWW_TOP}/group-edit.php">add your own groups.</a></li>
		<li>Next you will want to run <a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/binary-update.php">Get Latest Headers.</a></li>
		</li>
		<li>After obtaining headers, the next step is to <a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/release-update.php">Update Releases.</a>
		</li>
		<li>Once your site is up and running, you may want to backfill some of the groups. You will need to <a href="{$smarty.const.WWW_TOP}/group-list.php">edit the amount</a> of backfill days on a per group basis.	
		Next you will want to run <a onclick="return confirm('Are you sure? This is best performed from the command line.');" href="{$smarty.const.WWW_TOP}/backfill.php">Backfill.</a></li>
		<p><p>
		<b>Note: We recommend using Cron Scripts, or if you are a Windows user, Batch Files to Update Binaries and Releases, and to also Backfill.
		Cron and Batch Scripts can be found in the Misc/Update_Scripts Folder. Backfill should be run as a seperate process from your normal Update Binaries / Releases routine.</b>
</p>

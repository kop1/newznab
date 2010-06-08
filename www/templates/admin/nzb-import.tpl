
<h1>{$page->title}</h1>

<p>
Import nzbs from a folder into the system. Specify the full file path to a folder containing nzbs. <b>Once imported the nzb will be deleted.</b>
<br/>
If you are importing a large number of nzb files, run this script from the command line and pass in the folder path as the first argument.
<br/>
Importing will enter the nzbs into the binaries/parts tables, but not create any releases. The update_releases function should be run to create new releases from the imported nzbs.
</p>


<form action="{$SCRIPT_NAME}" method="POST">

<table class="input">

<tr>
	<td><label for="folder">Folder</label>:</td>
	<td>
		<input id="folder" class="long" name="folder" type="text" value="" />
		<div class="hint">Windows file paths should be specified with forward slashes e.g. c:/temp/</div>
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<input type="submit" value="Import" />
	</td>
</tr>

</table>

</form>

<div>
{$output}
</div>
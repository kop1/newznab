
<h1>{$release.searchname}</h1>

<table class="data">
	<tr><td class="label">Subject:</td><td>{$release.name}</td></tr>
	<tr><td class="label">Group:</td><td>{$release.group_name|replace:"alt.binaries":"a.b"}</td></tr>
	<tr><td class="label">Size:</td><td>{$release.size|fsize_format:"MB"}</td></tr>
	<tr><td class="label">Files:</td><td>{$release.totalpart}</td></tr>
	<tr><td class="label">File List:</td><td><a href="/filelist/{$release.guid}">View file list</a></td></tr>
	<tr><td class="label">Poster:</td><td>{$release.fromname}</td></tr>
	<tr><td class="label">Posted:</td><td>{$release.postdate|date_format}</td></tr>
	<tr><td class="label">Added:</td><td>{$release.adddate|date_format}</td></tr>
</table>

<a title="Download Nzb for {$release.searchname}" href="/download/{$release.searchname}/nzb/{$release.guid}">Download Nzb for {$release.searchname}</a>

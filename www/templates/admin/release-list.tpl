
<h1>{$page->title}</h1>

{$pager}

<table style="margin-top:10px;" class="data Sortable">

	<tr>
		<th>name</th>
		<th>category</th>
		<th>size</th>
		<th>files</th>
		<th>postdate</th>
		<th>adddate</th>
		<th>grabs</th>
		<th>options</th>
	</tr>
	
	{foreach from=$releaselist item=release}
	<tr>
		<td title="{$release.name}"><a href="release-edit.php?id={$release.ID}">{$release.searchname|escape:"htmlall"}</a></td>
		<td>{$release.category_name}</td>
		<td>{$release.size|fsize_format:"MB"}</td>
		<td>{$release.totalpart}</td>
		<td>{$release.postdate|date_format}</td>
		<td>{$release.adddate|date_format}</td>
		<td>{$release.grabs}</td>
		<td><a href="release-delete.php?id={$release.ID}">delete</a></td>
	</tr>
	{/foreach}

</table>
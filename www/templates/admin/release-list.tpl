
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
		<td class="less">{$release.category_name}</td>
		<td class="less">{$release.size|fsize_format:"MB"}</td>
		<td class="less">{$release.totalpart}</td>
		<td class="less">{$release.postdate|date_format}</td>
		<td class="less">{$release.adddate|date_format}</td>
		<td class="less">{$release.grabs}</td>
		<td><a href="release-delete.php?id={$release.ID}">delete</a></td>
	</tr>
	{/foreach}

</table>
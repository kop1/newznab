
<h1>{$page->title}</h1>

<h2>For <a href="/details/{$rel.searchname|escape:'htmlall'}/viewnzb/{$rel.guid}">{$rel.searchname|escape:'htmlall'}</a></h2>

<table style="width:100%;" class="data Sortable">

	<tr>
		<th>part</th>
		<th>filename</th>
		<th>size</th>
		<th>date</th>
	</tr>

	{foreach from=$binaries item=binary}
	<tr>
		<td title="{$binary.relpart}/{$binary.reltotalpart}">{$binary.relpart}</td>
		<td title="{$binary.name|escape:'htmlall'}">{$binary.filename}</td>
		<td>{$binary.size|fsize_format:"MB"}</td>
		<td title="{$binary.date}">{$binary.date|date_format}</td>
	</tr>
	{/foreach}

</table>	
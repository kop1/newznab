
<h1>{$page->title}</h1>

<h2>For <a href="/details/{$rel.searchname}/viewnzb/{$rel.guid}">{$rel.searchname}</a></h2>

<table style="width:100%;" class="data Sortable">

	<tr>
		<th>filename</th>
		<th>part</th>
		<th>date</th>
	</tr>

	{foreach from=$binaries item=binary}
	<tr>
		<td title="{$binary.name}">{$binary.filename}</td>
		<td title="{$binary.relpart}/{$binary.reltotalpart}">{$binary.relpart}</td>
		<td title="{$binary.date}">{$binary.date|date_format}</td>
	</tr>
	{/foreach}

</table>	
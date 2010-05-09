
<h1>{$page->title}</h1>

<table class="data Sortable">

	<tr>
		<th>filename</th>
		<th>part</th>
		<th>total parts</th>
		<th>date</th>
	</tr>

	{foreach from=$binaries item=binary}
	<tr>
		<td title="{$binary.name}">{$binary.filename}</td>
		<td>{$binary.relpart}</td>
		<td>{$binary.reltotalpart}</td>
		<td>{$binary.date|date_format}</td>
	</tr>
	{/foreach}

</table>	
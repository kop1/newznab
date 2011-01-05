
<h1>{$page->title}</h1>

<h2>For <a href="{$smarty.const.WWW_TOP}/details/{$rel.guid}/{$rel.searchname|escape:"htmlall"}">{$rel.searchname|escape:'htmlall'}</a></h2>

<table style="width:100%;margin-bottom:10px;" class="data Sortable highlight">

	<tr>
		<th>#</th>
		<th>filename</th>
		<th style="text-align:center;">size</th>
	</tr>

	{foreach item=i name=iteration from=$files item=file}
	<tr class="{cycle values=",alt"}">
		<td width="20">{$smarty.foreach.iteration.index+1}</td>
		<td>{$file.title|escape:'htmlall'}</td>
		<td class="less right">{if $file.size < 100000}{$file.size|fsize_format:"KB"}{else}{$file.size|fsize_format:"MB"}{/if}</td>
	</tr>
	{/foreach}

</table>	


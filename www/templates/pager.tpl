{if $pagertotalitems > $pageritemsperpage}
	{section name=pager loop=$pagertotalitems start=0 step=$pageritemsperpage}
		{if $pageroffset == $smarty.section.pager.index}
			{$smarty.section.pager.iteration}
		{else}
			<a href="{$pagerquerybase}{$smarty.section.pager.index}">{$smarty.section.pager.iteration}</a>
		{/if}    
		&nbsp;
	{/section}
{/if}  
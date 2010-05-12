{if $pagertotalitems > $pageritemsperpage}
	{section name=pager loop=$pagertotalitems start=0 step=$pageritemsperpage}
		{if $pageroffset == $smarty.section.pager.index}
			{$smarty.section.pager.iteration}
		&nbsp;
		{elseif $pageroffset-$smarty.section.pager.index == $pageritemsperpage || 
					$pageroffset+$pageritemsperpage == $smarty.section.pager.index}
		<a href="{$pagerquerybase}{$smarty.section.pager.index}{$pagerquerysuffix}">{$smarty.section.pager.iteration}</a>
		&nbsp;
		{/if}    
	{/section}
{/if}  
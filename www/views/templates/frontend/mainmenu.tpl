{if $menulist|@count > 0}
<li class="menu_main">
	<h2>Menu</h2> 
	
	<ul>
			{foreach from=$menulist item=menu}
			{strip}
				{assign var="var" value=$menu.menueval}	
				{eval var=$var, assign='menuevalresult'}
				{if $menuevalresult|replace:",":"1" == "1"}
					<li onclick="document.location='{$menu.href}';"><a title="{$menu.tooltip}" href="{$menu.href}">{$menu.title}</a></li>
				{/if}
			{/strip}
			{/foreach}
	</ul>
</li>
{/if}
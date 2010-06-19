<div id="menucontainer">
	<div id="menulink"> 
		<ul>
		{foreach from=$parentcatlist item=parentcat}
			<li><a title="Browse {$parentcat.title}" href="{$smarty.const.WWW_TOP}/browse?t={$parentcat.ID}">{$parentcat.title}</a>
				<ul>
				{foreach from=$parentcat.subcatlist item=subcat}
					<li><a title="Browse {$subcat.title}" href="{$smarty.const.WWW_TOP}/browse?t={$subcat.ID}">{$subcat.title}</a></li>
				{/foreach}
				</ul>
			</li>
		{/foreach}
		</ul>
	</div>
	
	<div id="menusearchlink">
	<form id="headsearch_form" action="get">
		<label style="display:none;" for="headsearch">Search Text</label>
		<input id="headsearch" name="headsearch" value="{if $header_menu_search == ""}Enter keywords{else}{$header_menu_search|escape:"htmlall"}{/if}" style="width:85px;" type="text" /> 
		<label style="display:none;" for="headcat">Search Category</label>
		<select id="headcat" name="headcat">
			<optgroup label="All">
				<option value="-1">-- Everything --</option>
			</optgroup>
		{foreach from=$parentcatlist item=parentcat}
			<optgroup label="{$parentcat.title}">
				{foreach from=$parentcat.subcatlist item=subcat}
					<option {if $header_menu_cat==$subcat.ID}selected{/if} value="{$subcat.ID}">{$subcat.title}</option>
				{/foreach}
			</optgroup>
		{/foreach}
		</select>
		<input id="headsearch_go" type="submit" value="Go"/>
	</form>
	</div>
</div>

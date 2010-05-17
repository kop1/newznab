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
		<input value="Enter keywords" onfocus="if(this.value == 'Enter keywords') this.value = ''" style="width:85px;color:#555;" type="text" /> 
		<!-- category dropdown here -->
		<input onclick="alert('do search'); return false;" type="submit" value="Go"/>
	</div>
</div>

<?xml version="1.0" encoding="UTF-8"?>
<caps>
	<server version="{$site->version}" title="{$site->title|escape}" strapline="{$site->strapline|escape}" email="{$site->email}" url="{$serverroot}" image="{if $site->style != "" && $site->style != "/"}{$serverroot}theme/{$site->style}/images/banner.jpg{else}{$serverroot}images/banner.jpg{/if}" />
	<search>
	{foreach from=$parentcatlist item=parentcat}
		<category id="{$parentcat.ID}" name="{$parentcat.title}" >
			{foreach from=$parentcat.subcatlist item=subcat}
				<subcat id="{$subcat.ID}" name="{$subcat.title}" />
			{/foreach}
			</category>
	{/foreach}
	</search>
</caps>

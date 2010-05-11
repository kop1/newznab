<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:report="http://www.newzbin.com/DTD/2007/feeds/report/">
<channel>
<atom:link href="{$scheme}{$smarty.server.SERVER_NAME}{$port}/rss" rel="self" type="application/rss+xml" />
<title>{$site->title|escape}</title>
<description>{$site->title|escape} Nzb Feed</description>
<link>{$scheme}{$smarty.server.SERVER_NAME}{$port}/</link>
<language>en-gb</language>
<webMaster>{$site->email} ({$site->title|escape})</webMaster>
<category>{$site->meta_keywords}</category>
<image>
	<url>{$scheme}{$smarty.server.SERVER_NAME}{$port}/images/banner.jpg</url>
	<title>{$site->title|escape}</title>
	<link>{$scheme}{$smarty.server.SERVER_NAME}{$port}/</link>
	<description>Visit {$site->title|escape} - A usenet indexing community</description>
</image>

{foreach from=$releases item=release}
<item>
	<title><![CDATA[{$release.searchname}]]></title>
	<guid isPermaLink="true">{$scheme}{$smarty.server.SERVER_NAME}{$port}/rss/viewnzb/{$release.guid}</guid>
	<link>{$scheme}{$smarty.server.SERVER_NAME}{$port}/rss/{if $dl=="1"}nzb{else}viewnzb{/if}/{$release.guid}{if $dl=="1"}&i={$userdata.ID}&r={$userdata.rsstoken}{/if}</link>
	<comments>{$scheme}{$smarty.server.SERVER_NAME}{$port}/rss/viewnzb/{$release.guid}#comments</comments> 	
	<pubDate>{$release.adddate|phpdate_format:"DATE_RSS"}</pubDate> 
	<category>{$release.category_name}</category> 	
	<description>
	<![CDATA[
	<ul>
	<li>ID: <a href="{$scheme}{$smarty.server.SERVER_NAME}{$port}/rss/viewnzb/{$release.guid}">{$release.guid}</a> (Size: {$release.size|fsize_format:"MB"}) </li>
	<li>Attributes: Category - {$release.category_name} </li>
	<li>Groups: {$release.group_name}</li>
	<li>Poster: {$release.fromname}</li>
	<li>PostDate: {$release.postdate|phpdate_format:"DATE_RSS"}</li>
	</ul>]]>
	</description>
	{if $dl=="1"}<enclosure url="{$scheme}{$smarty.server.SERVER_NAME}{$port}/rss/nzb/{$release.guid}&i={$userdata.ID}&r={$userdata.rsstoken}" length="{$release.size}" type="application/x-nzb" />{/if}

	<!-- Additional attributes-->
	<report:id>{$release.guid}</report:id>
	<report:category parentID="{$release.parentCategoryID}" id="{$release.categoryID}">{$release.category_name}</report:category>
	<report:groups>
		<report:group>{$release.group_name}</report:group>
	</report:groups>
	<report:nzb>{$scheme}{$smarty.server.SERVER_NAME}{$port}/rss/nzb/{$release.guid}</report:nzb>
	<report:poster><![CDATA[{$release.fromname}]]></report:poster>
	<report:size type="bytes">{$release.size}</report:size>
	<report:postdate>{$release.postdate|phpdate_format:"DATE_RSS"}</report:postdate>
	<report:stats>
		<report:views>{$release.grabs}</report:views>
		<report:comments>{$release.comments}</report:comments>
	</report:stats>				
</item>
{/foreach}

</channel>
</rss>
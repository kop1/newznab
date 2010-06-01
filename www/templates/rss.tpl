<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:report="http://www.newzbin.com/DTD/2007/feeds/report/">
<channel>
<atom:link href="{$serverroot}rss" rel="self" type="application/rss+xml" />
<title>{$site->title|escape}</title>
<description>{$site->title|escape} Nzb Feed</description>
<link>{$serverroot}</link>
<language>en-gb</language>
<webMaster>{$site->email} ({$site->title|escape})</webMaster>
<category>{$site->meta_keywords}</category>
<image>
	<url>{if $site->style != "" && $site->style != "/"}{$serverroot}theme/{$site->style}/images/banner.jpg{else}{$serverroot}images/banner.jpg{/if}</url>
	<title>{$site->title|escape}</title>
	<link>{$serverroot}</link>
	<description>Visit {$site->title|escape} - {$site->strapline|escape}</description>
</image>

{foreach from=$releases item=release}
<item>
	<title>{$release.searchname}</title>
	<guid isPermaLink="true">{$serverroot}rss/viewnzb/{$release.guid}</guid>
	<link>{$serverroot}rss/{if $dl=="1"}nzb{else}viewnzb{/if}/{$release.guid}{if $dl=="1"}&amp;i={$uid}&amp;r={$rsstoken}{/if}</link>
	<comments>{$serverroot}rss/viewnzb/{$release.guid}#comments</comments> 	
	<pubDate>{$release.adddate|phpdate_format:"DATE_RSS"}</pubDate> 
	<category>{$release.category_name}</category> 	
	<description>
	<![CDATA[
	<ul>
	<li>ID: <a href="{$serverroot}rss/viewnzb/{$release.guid}">{$release.guid}</a> (Size: {$release.size|fsize_format:"MB"}) </li>
	<li>Name: {$release.searchname}</li>
	<li>Attributes: Category - {$release.category_name} </li>
	<li>Groups: {$release.group_name}</li>
	<li>Poster: {$release.fromname}</li>
	<li>PostDate: {$release.postdate|phpdate_format:"DATE_RSS"}</li>
	</ul>]]>
	</description>
	{if $dl=="1"}<enclosure url="{$serverroot}rss/nzb/{$release.guid}&amp;i={$uid}&amp;r={$rsstoken}" length="{$release.size}" type="application/x-nzb" />{/if}

	<!-- Additional attributes-->
	<report:id>{$release.guid}</report:id>
	<report:category parentID="{$release.parentCategoryID}" id="{$release.categoryID}">{$release.category_name}</report:category>
	<report:groups>
		<report:group>{$release.group_name}</report:group>
	</report:groups>
	{if $release.rageID > 0}
<report:tv>
		<report:tvrageid>{$release.rageID}</report:tvrageid>
		<report:seasonfull>{$release.seriesfull}</report:seasonfull>
		<report:season>{$release.season|replace:"S":""|string_format:"%d"}</report:season>
		<report:episode>{$release.episode|replace:"E":""|string_format:"%d"}</report:episode>
	</report:tv>
	{/if}
<report:nzb>{$serverroot}rss/nzb/{$release.guid}</report:nzb>
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

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:report="http://www.newznab.com/DTD/2010/feeds/report/" xmlns:tv="http://www.newznab.com/DTD/2010/feeds/tv/">
<channel>
<atom:link href="{$serverroot}rss" rel="self" type="application/rss+xml" />
<title>{$site->title|escape}</title>
<description>{$site->title|escape} API Results</description>
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
	<link>{$serverroot}rss/nzb/{$release.guid}&amp;i={$uid}&amp;r={$rsstoken}</link>
	<comments>{$serverroot}rss/viewnzb/{$release.guid}#comments</comments> 	
	<pubDate>{$release.adddate|phpdate_format:"DATE_RSS"}</pubDate> 
	<category>{$release.category_name|escape:html}</category> 	
	<description>{$release.searchname}</description>
	<enclosure url="{$serverroot}rss/nzb/{$release.guid}&amp;i={$uid}&amp;r={$rsstoken}" length="{$release.size}" type="application/x-nzb" />

	<report:categories>
	{foreach from=$release.category_ids|parray:"," item=cat}
	<report:category>{$cat}</report:category>
	{/foreach}</report:categories>
	<report:size type="bytes">{$release.size}</report:size>

</item>
{/foreach}

</channel>
</rss>

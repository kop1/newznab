<?xml version="1.0" encoding="us-ascii"?>
<!DOCTYPE nzb PUBLIC "-//newznzb//DTD NNCAP 0.1//EN" "http://www.newznab.com/DTD/nn/cap-0.1.dtd">
<newznab xmlns="http://www.newznab.com/DTD/2010/nn">
<site>
	<version>{$site->version}</version>
	<title>{$site->title|escape}</title>
	<strapline>{$site->strapline|escape}</strapline>
	<email>{$site->email}</email>
	<url>{$serverroot}</url>
	<image>{if $site->style != "" && $site->style != "/"}{$serverroot}theme/{$site->style}/images/banner.jpg{else}{$serverroot}images/banner.jpg{/if}</image>
</site>
<categories>
{foreach from=$cats item=cat}
	<category parentID="{$cat.parentID}" id="{$cat.ID}">{$cat.title}</category>
{/foreach}
</categories>
</newznab>

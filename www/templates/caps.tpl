<?xml version="1.0" encoding="us-ascii"?>
<!DOCTYPE nzb PUBLIC "-//newznzb//DTD NNCAP 0.1//EN" "http://www.newznab.com/DTD/nn/cap-0.1.dtd">
<newznab xmlns="http://www.newznab.com/DTD/2010/nn">
<version>{$site->version}</version>
<categories>
{foreach from=$cats item=cat}
	<category parentID="{$cat.parentID}" id="{$cat.ID}">{$cat.title}</category>
{/foreach}
</categories>
</newznab>

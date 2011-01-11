{if not $modal} 
<h1>{$page->title}</h1>
<h2>For <a href="{$smarty.const.WWW_TOP}/details/{$rel.searchname|escape:'htmlall'}/viewnzb/{$rel.guid}">{$rel.searchname|escape:'htmlall'}</a></h2>
{/if}

<div id="backdrop"><img src="{$smarty.const.WWW_TOP}/covers/music/backdrop.jpg" alt="" /></div>

<div id="movieinfo">

<h1>{$music.title|ss} ({$music.year})</h1>
<h2>{if $music.cover == 1}<img src="{$smarty.const.WWW_TOP}/covers/music/{$music.ID}.jpg" class="cover" alt="{$movie.title|ss}" align="left" />{/if}</h2>

<h3>Genre: {$music.genre|ss}</h3>

{if $music.tracks != ""}
<h3>Track Listing:</h3>
<ol class="tracklist">
	{assign var="tracksplits" value="|"|explode:$music.tracks}
	{foreach from=$tracksplits item=tracksplit}
	<li>{$tracksplit|trim}</li>
	{/foreach}		
</ol>
{/if}


</div>
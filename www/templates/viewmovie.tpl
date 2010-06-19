{if not $modal}
<h1>{$page->title}</h1>
<h2>For <a href="{$smarty.const.WWW_TOP}/details/{$rel.searchname|escape:'htmlall'}/viewnzb/{$rel.guid}">{$rel.searchname|escape:'htmlall'}</a></h2>
{/if}

{if $movie.backdrop == 1}<div id="backdrop"><img src="{$smarty.const.WWW_TOP}/images/covers/{$movie.imdbID}-backdrop.jpg" alt="" /></div>{/if}

<div id="movieinfo">

<h1>{$movie.title|ss} ({$movie.year})</h1>
<h2>{if $movie.cover == 1}<img src="{$smarty.const.WWW_TOP}/images/covers/{$movie.imdbID}-cover.jpg" class="cover" alt="{$movie.title|ss}" align="left" />{/if}{$movie.plot|ss}</h2>
<h3>Rating: {$movie.rating}/10<br />Genre: {$movie.genre|ss}</h3>

</div>
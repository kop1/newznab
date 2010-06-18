{if not $modal}
<h1>{$page->title}</h1>
<h2>For <a href="{$smarty.const.WWW_TOP}/details/{$rel.searchname|escape:'htmlall'}/viewnzb/{$rel.guid}">{$rel.searchname|escape:'htmlall'}</a></h2>
{/if}

{if $movie.backdrop == 1}<div id="backdrop"><img src="{$smarty.const.WWW_TOP}/images/covers/{$movie.imdbID}-backdrop.jpg" /></div>{/if}

<div id="movieinfo">

<h1{if $movie.backdrop == 1} class="backdrop"{/if}>{$movie.title|escape:"htmlall"} ({$movie.year})</h1>
<h2{if $movie.backdrop == 1} class="backdrop"{/if}>{if $movie.cover == 1}<img src="{$smarty.const.WWW_TOP}/images/covers/{$movie.imdbID}-cover.jpg" alt="{$movie.title|escape:"htmlall"}" align="left" hspace="10" />{/if}{$movie.plot|escape:"htmlall"}</h2>
<h3{if $movie.backdrop == 1} class="backdrop"{/if}>Rating: {$movie.rating}/10<br />Genre: {$movie.genre|escape:"htmlall"}</h3>

</div>
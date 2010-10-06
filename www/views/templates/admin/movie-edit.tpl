
<h1>{$page->title}</h1>

<form enctype="multipart/form-data" action="{$SCRIPT_NAME}?action=submit" method="post">

<input type="hidden" name="id" value="{$movie.imdbID}" />

<table class="input">

<tr>
	<td><label for="title">IMDB ID</label>:</td>
	<td>{$movie.imdbID}</td>
</tr>

<tr>
	<td><label for="title">TMDb ID</label>:</td>
	<td>{$movie.tmdbID}</td>
</tr>

<tr>
	<td><label for="title">Title</label>:</td>
	<td>
		<input id="title" class="long" name="title" type="text" value="{$movie.title|escape:'htmlall'}" />
	</td>
</tr>

<tr>
	<td><label for="plot">Plot</label>:</td>
	<td>
		<textarea id="plot" name="plot">{$movie.plot|escape:'htmlall'}</textarea>
	</td>
</tr>

<tr>
	<td><label for="year">Year</label>:</td>
	<td>
		<input id="year" class="short" name="year" type="text" value="{$movie.year|escape:'htmlall'}" />
	</td>
</tr>

<tr>
	<td><label for="rating">Rating</label>:</td>
	<td>
		<input id="rating" class="short" name="rating" type="text" value="{$movie.rating|escape:'htmlall'}" />
	</td>
</tr>

<tr>
	<td><label for="genre">Genre</label>:</td>
	<td>
		<input id="genre" class="long" name="genre" type="text" value="{$movie.genre|escape:'htmlall'}" />
	</td>
</tr>

<tr>
	<td><label for="cover">Cover Image</label>:</td>
	<td>
		<input type="file" id="cover" name="cover" />
		{if $movie.cover == 1}
			<img style="max-width:200px; display:block;" src="{$smarty.const.WWW_TOP}/../views/images/covers/{$movie.imdbID}-cover.jpg" alt="" />
		{/if}
	</td>
</tr>

<tr>
	<td><label for="backdrop">Backdrop Image</label>:</td>
	<td>
		<input type="file" name="backdrop" />
		{if $movie.backdrop == 1}
			<img style="max-width:200px; display:block;" src="{$smarty.const.WWW_TOP}/../views/images/covers/{$movie.imdbID}-backdrop.jpg" alt="" />
		{/if}
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<input type="submit" value="Save" />
	</td>
</tr>

</table>

</form>
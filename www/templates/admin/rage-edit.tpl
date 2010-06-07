
<h1>{$page->title}</h1>

<form enctype="multipart/form-data" action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td><label for="rageID">Rage Id</label>:</td>
	<td>
		<input type="hidden" name="id" value="{$rage.ID}" />
		<input id="rageID" class="short" name="rageID" type="text" value="{$rage.rageID}" />
	</td>
</tr>

<tr>
	<td><label for="releasetitle">Release Name</label>:</td>
	<td>
		<input id="releasetitle" class="long" name="releasetitle" type="text" value="{$rage.releasetitle|escape:'htmlall'}" />
	</td>
</tr>

<tr>
	<td><label for="description">Description</label>:</td>
	<td>
		<textarea id="description" name="description">{$rage.description|escape:'htmlall'}</textarea>
	</td>
</tr>

<tr>
	<td><label for="imagedata">Series Image</label>:</td>
	<td>
		{if $rage.imgdata != ""}
			<img style="max-width:200px; display:block;" src="{$smarty.const.WWW_TOP}/../getimage.php?type=tvrage&id={$rage.ID}">
		{/if}
		<input type="file" id="imagedata" name="imagedata">
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
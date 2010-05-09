
<h1>{$page->title}</h1>

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td>Name:</td>
	<td>
		<input type="hidden" name="id" value="{$release.ID}" />
		<input class="long" name="name" type="text" value="{$release.name}" />
	</td>
</tr>

<tr>
	<td>Search Name:</td>
	<td>
		<input class="long" name="searchname" type="text" value="{$release.searchname}" />
	</td>
</tr>

<tr>
	<td>From Name:</td>
	<td>
		<input class="long" name="fromname" type="text" value="{$release.fromname}" />
	</td>
</tr>

<tr>
	<td>Category:</td>
	<td>
		
	</td>
</tr>

<tr>
	<td>Parts:</td>
	<td>
		<input class="short" name="totalpart" type="text" value="{$release.totalpart}" />
	</td>
</tr>

<tr>
	<td>Grabs:</td>
	<td>
		<input class="short" name="grabs" type="text" value="{$release.grabs}" />
	</td>
</tr>

<tr>
	<td>Group:</td>
	<td>
		{$release.group_name}
	</td>
</tr>

<tr>
	<td>Size:</td>
	<td>
		<input class="long" name="size" type="text" value="{$release.size}" />
	</td>
</tr>

<tr>
	<td>Posted Date:</td>
	<td>
		<input class="long" name="postdate" type="text" value="{$release.postdate}" />
	</td>
</tr>

<tr>
	<td>Added Date:</td>
	<td>
		<input class="long" name="adddate" type="text" value="{$release.adddate}" />
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<input disabled="disabled" type="submit" value="Save" />
	</td>
</tr>

</table>

</form>
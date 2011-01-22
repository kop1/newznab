
<h1><a href="{$smarty.const.WWW_TOP}/forum">Forum</a></h1>

{if $results|@count > 0}

<h2>{$results[0].subject|escape:"htmlall"}</h2>

<div style="float:right;margin-bottom:5px;"><a href="#reply">Reply</a></div>

<a id="top"></a>
<table style="width:100%;" class="data highlight">
	<tr>
		<th>By</th>
		<th>Message</th>
	</tr>

	{foreach from=$results item=result name=result}
		<tr class="{cycle values=",alt"}">
			<td width="15%;">
			
			
				<a {if $smarty.foreach.result.last}id="last"{/if} title="View profile" href="{$smarty.const.WWW_TOP}/profile/?name={$result.username}">{$result.username}</a>
				<br/>
				on <span title="{$result.createddate}">{$result.createddate|date_format}</span> <div class="hint">({$result.createddate|timeago})</div>
			</td>
			<td>{$result.message|escape:"htmlall"|regex_replace:"/[\r\t\n]/":"<br/>"}</td>
		</tr>
	{/foreach}
	
</table>

<div style="float:right;margin-top:5px;"><a href="#">Top</a></div>


<div style="margin-top:10px;">
<a id="reply"></a>
<form method="post" action="#last">
	<label for="addReply">Add Reply</label>:<br/>
	<textarea maxlength="5000" id="addReply" name="addReply"></textarea>
	<br/>
	<input type="submit" value="submit"/>
</form>
</div>

{/if}


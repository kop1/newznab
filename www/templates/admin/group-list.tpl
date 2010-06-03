<div id="group_list">

    <h1>{$page->title}</h1>

    {if $grouplist}
    <div id="message">hi mom!</div>
    <table class="data Sortable highlight">

        <tr>
            <th>group</th>
            <th>category</th>
            <th>last record</th>
            <th>last updated</th>
            <th>active</th>
            <th>releases</th>
        </tr>
        
        {foreach from=$grouplist item=group}
        <tr class="{cycle values=",alt"}">
            <td><a href="{$smarty.const.WWW_TOP}/group-edit.php?id={$group.ID}">{$group.name|replace:"alt.binaries":"a.b"}</a></td>
            <td class="less">{$group.category_name}</td>
            <td class="less">{$group.last_record}</td>
            <td class="less">{$group.last_updated}</td>
            <td class="less" id="group-{$group.ID}">{if $group.active=="1"}<a href="javascript:ajax_group_status({$group.ID}, 0)" class="group_active">Deactivate</a>{else}<a href="javascript:ajax_group_status({$group.ID}, 1)" class="group_deactive">Activate</a>{/if}</td>
            <td class="less">{$group.num_releases}</td>
        </tr>
        {/foreach}

    </table>
    {else}
    <p>No groups available (eg. none have been added).<br />If you still see this message, after updating your group list - please check the "<strong>group filter</strong>" option, under your site configuration.<br /><a href="{$smarty.const.WWW_TOP}/group-update.php">Click here to update your group list</a></p>
    {/if}

</div>		


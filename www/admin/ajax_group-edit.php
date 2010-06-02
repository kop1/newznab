<?php
require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/groups.php");

// login check
$admin = new AdminPage;
$group  = new Groups();

if (isset($_GET['group_id']))
{
    $id     = (int)$_GET['group_id'];
    $status = isset($_GET['group_status']) ? (int)$_GET['group_status'] : 0;
}

print $group->updateGroupStatus($id, $status);


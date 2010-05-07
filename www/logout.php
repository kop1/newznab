<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");

$users = new Users();
$users->logout();

header("Location:/");

?>
<?php

require_once("config.php");
require_once(WWW_DIR."/lib/users.php");

$users = new Users();
$users->logout();

header("Location: ".WWW_TOP);

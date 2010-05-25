<?php

//=========================
// Config you must change
//=========================
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_USER', 'newznab');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'newznab');

//NNTP_USERNAME: 'Username' or NULL (same for password)
define('NNTP_USERNAME', 'account');
define('NNTP_PASSWORD', 'password');
define('NNTP_SERVER', 'newz.server');
define('NNTP_PORT', '119');

define('GOOGLE_ADSENSE_ACC', '');



//=========================
// Config you can leave alone
//=========================
define('WWW_DIR', str_replace("\\","/",dirname(__FILE__))."/");
define('SMARTY_DIR', WWW_DIR.'lib/smarty/');

require("automated.config.php");

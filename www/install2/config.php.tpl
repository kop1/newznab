<?php

//=========================
// Config you must change
//=========================
define('DB_TYPE', 'mysql');
define('DB_HOST', '%%DB_HOST%%');
define('DB_USER', '%%DB_USER%%');
define('DB_PASSWORD', '%%DB_PASSWORD%%');
define('DB_NAME', '%%DB_NAME%%');

define('NNTP_USERNAME', '%%NNTP_USERNAME%%');
define('NNTP_PASSWORD', '%%NNTP_PASSWORD%%');
define('NNTP_SERVER', '%%NNTP_SERVER%%');
define('NNTP_PORT', '%%NNTP_PORT%%');

define('GOOGLE_ADSENSE_ACC', '');

define('INSTALL_CHECK', false);

//=========================
// Config you can leave alone
//=========================
define('WWW_DIR', str_replace("\\","/",dirname(__FILE__))."/");
define('SMARTY_DIR', WWW_DIR.'lib/smarty/');

require("automated.config.php");

<?php
/*
	File that handles automatic definition of some configuration and some other global checks
*/

//=========================
// Figure out WWW_TOP
//=========================
$www_top = str_replace("\\","/",dirname( $_SERVER['PHP_SELF'] ));
if(strlen($www_top) == 1)
        $www_top = "";

define('WWW_TOP', $www_top);

//=========================
// Make sure install folder is removed
//=========================
if(INSTALL_CHECK == true) {
	if(is_dir('install')) {
		die("Please remove the install folder for increased security<br />Hint: rm -r ".realpath('install'));
	}
}

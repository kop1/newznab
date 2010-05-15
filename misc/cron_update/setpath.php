<?php

//
// Bit of a cludge to set the path to the root of the website so all the web files includes work.
//
$_SERVER['DOCUMENT_ROOT'] = str_replace("\\","/",dirname(__FILE__))."/../../www/";

?>
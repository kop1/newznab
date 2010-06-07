<?php
$path = str_replace('/install', '', dirname(__FILE__));
if (file_exists("$path/install.lock"))
{
    header('Content-type: text/plain');
    print "Please remove the file 'install.lock' (rm -f install.lock) located in the 'www' folder, before attemping to reinstall.";
    exit;
}

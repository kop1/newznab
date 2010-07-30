<?php

require("../../www/config.php");
require_once(WWW_DIR."/lib/nzb.php");
$nntp = new nntp;
$nntp->doConnect();
$nzb = new NZB;
print_r($nzb->daytopost($nntp,"alt.binaries.teevee",1));
$nntp->doQuit();
?>

<?php

require("../../www/config.php");
require_once(WWW_DIR."/lib/nzb.php");
$nntp = new nntp;
$nntp->doConnect();
$nzb = new NZB;
$data = $nntp->selectGroup("alt.binaries.nintendo.ds");
$output = $nzb->postdate($nntp,"7434768");
echo date('r',$output)."\n";
print_r($data);
$nntp->doQuit();
?>

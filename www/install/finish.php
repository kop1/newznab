<?php 
// check if system is already installed
require_once './is_locked.php';

include('header.html');
include('../lib/nntp.php'); 

$nntp = new Nntp(); $nntp->doConnect();
$data = array();
$data =  $nntp->selectGroup('alt.binaries.boneless'); 
$results = $data['last'];

$nntpError = 0;
if (PHP_INT_MAX == $results) {
	$nntpError = 1;
}
else
{
    // create .lock somewhere..
    $path = str_replace('/install', '', dirname(__FILE__));
    if (function_exists('file_put_contents'))
    {
        file_put_contents("$path/install.lock", 'lock');
    }
    else
    {
        $fp = fopen("$path/install.lock", 'w');
        fwrite($fp, 'lock');
        fclose($fp);
    }

    if (!file_exists("$path/install.lock"))
    {
        // TODO: do stuff
    }
}

?>
    <h1>Finish up</h1>
	<p>The installation is now finished. Please remove your /install folder and click below to go to your newly installed site.
	<br /><br />
	Hint: rm -rf <?=realpath('.')?></p>

	<br /><br />
	<?php if($nntpError == 1): ?>
		<div class="error">
			Warning!<br />
			We have detected a bug in your NNTP-version handling large numbers used by some newsgroups.<br />
			Run the following command:  <b>pear list-files NET_NNTP-alpha</b><br />
			This will give you the location of the NNTP installation<br />
			<br />
			locate the following file under /Net/NNTP/Protocol/Client.php	
			<br />
			edit the following lines 816, 817, and 818:<br />
			=========================================<br />
                             'first' => (int) $response_arr[1],<br />
                             'last'  => (int) $response_arr[2],<br />
                             'count' => (int) $response_arr[0]);<br />
			<br />
			Change to:<br />
			==========================================<br />
                             'first' => $response_arr[1],<br />
                             'last'  => $response_arr[2],<br />
                             'count' => $response_arr[0]);<br />
			<br />
			==========================================<br />
			You've removed (int) from three lines<br />
			<br />
			<b>You can test this again by refreshing this page</b>
		</div>
	<?php endif; ?>

	<form action="../" method="GET">
		<div align="center">
        		<input type="submit" value="Take me to the homepage" />
	       </div>
	</form>

<?php include('footer.html'); ?>

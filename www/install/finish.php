<?php 
include('header.html');
include('../lib/nntp.php'); 

$nntp = new Nntp(); $nntp->doConnect();
$data =  $nntp->selectGroup('alt.binaries.boneless'); 
$results = $data['last'];

$nntpError = 0;
if (PHP_INT_MAX == $results) {
	$nntpError = 1;
} 

?>
        <h1>Finish up</h1>
	<p>The installation is now finished. Please remove your /install folder and click below to go to your newly installed site.
	<br /><br />
	Hint: rm -rf <?=realpath('.')?></p>

	<br /><br />
	<?php if($nntpError == 1) { ?>
		<div class="error">
			Ugh!<br />
			We have detected a bug in your NNTP-version.<br />
			run the following command:  <b>pear list-files NET_NNTP</b><br />
			This will give you the location of the NNTP installation<br />
			<br />
			locate the following file under /Net/NNTP/Protocol/Client.php	
			<br />
			edit the following lines 754, 755, 756:<br />
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
	<?php } ?>

	<form action="../" method="GET">
		<div align="center">
        		<input type="submit" value="Take me to the frontpage" />
	       </div>
	</form>

<?php include('footer.html'); ?>

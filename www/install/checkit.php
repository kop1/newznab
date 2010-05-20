<?php 
include('header.html');


$errors = array();
//=====================
//Make shure that needed functions is in place
//=====================

//mcrypt function
if(function_exists('mcrypt') == false)
	$errors[] = "The PHP installation lack support for mcrypt, this is normally found in the package 'php5-mcrypt'.";

//the templates cache holder must be writable
if(is_writable("../ib/smarty/templates_c") == false) {
	$errors[] = "The template cache folder must be writable. A quick solution is to run:<br />chmod 777 ".realpath("../lib/smarty/templates_c");
}

//A PEAR package is needed
if(!include('Net/NNTP/Client.php')) {
        $errors[] = "The PEAR package 'Net_NNTP' is missing. This can normally fix this by running:<br />pear install Net_NNTP";
}

//We need either write access to config.php, or write access in the root folder
if(is_writable("../config.php") == false) {
	if(is_writable("../") == false) {
		$errors[] = "The installer needs write access to either ".realpath('../')."/config.php or to the folder ".realpath('../')." in order to setup the configuration.<br />Quick fix: chmod 777 ".realpath('../');
	}
}

?>
		<h1>Pre flight checklist</h1>
		<?php if(count($errors) == 0) { ?>
			Allright! No problems where found and you are ready to install!
        	        <div align="center">
	                        <form action="database.php"><input type="submit" value="Go to step two: Set up the database" /></form>              
                	</div>
		<?php } else { ?>
			Nuts! You have to fix the following errors:<br />
			<?php foreach($errors as $err) { ?>
				<div class="error"><?=$err?></div>
			<?php } ?>
			<br />
			You have to correct theese problems before you can continue the installation.
		<?php } ?>

<?php include('footer.html'); ?>

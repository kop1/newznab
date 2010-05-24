<?php 
include('header.html');

$errors = array();

//mcrypt function
if(function_exists('sha1') == false)
	$errors[] = "The PHP installation lacks support for sha1.";

//mysql libs
if(function_exists('mysql_connect') == false)
	$errors[] = "The PHP installation lacks support for MySQL(mysql_connect).";

//the templates cache holder must be writable
if(is_writable("../lib/smarty/templates_c") == false) {
	$errors[] = "The template cache folder must be writable. A quick solution is to run:<br />chmod 777 ".realpath("../lib/smarty/templates_c");
}

//A PEAR package is needed
if(!include('Net/NNTP/Client.php')) {
        $errors[] = "The PEAR package 'Net_NNTP' is missing. This can normally be fixed by running:<br />pear install Net_NNTP";
}

//We need either write access to config.php, or write access in the root folder
if(is_writable("../config.php") == false) {
	if(is_file("../config.php") == true) {
		$errors[] = "The installer cannot write to ".realpath('../config.php').", please fix the permissions.<br /> Quick Fix: chmod 777 ".realpath('../config.php');
	} else if(is_writable("../") == false) {
		$errors[] = "The installer needs write access to either ".realpath('../')."/config.php or to the folder ".realpath('../')." in order to setup the configuration.<br />Quick fix: chmod 777 ".realpath('../');
	}
}


//We need access to the sql file
if(is_file("../../db/schema.sql") == false) {
	$errors[] = "The schema.sql file is missing, please make shure it is placed in ".realpath("../../db/schema.sql");
}

?>
	<h1>Pre flight checklist</h1>
	<?php if(count($errors) == 0) { ?>
		<p>
		No problems where found and you are ready to install.
		</p>
				<div align="center">
					<form action="database.php"><input type="submit" value="Go to step two: Set up the database" /></form>              
				</div>
	<?php } else { ?>
		Installation checks found the following errors:<br />
		<?php foreach($errors as $err) { 
			echo "<div class=\"error\">$err</div>";
		} ?>
		<br />
		Newznab will not function correctly unless these problems are solved.
	<?php } ?>

<?php include('footer.html'); ?>

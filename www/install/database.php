<?php 
include('header.html');

//Try to save config and import data
if($_GET['do'] == 'run') {

	$failed = 0;
	$host = $_POST['host'] ? "" : "localhost";
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	$db   = $_POST['db'];

	//Test connetion
	$connTest = @mysql_connect($host, $user, $pass);
	if($connTest == false) {
		$failed = 1;
	}

	//Import it
	if($failed == 0) {

	}
}

?>
	<h1>Database setup</h1>
	<p>We need some information about your MySQL database, please provide the following information</p>
	<form action="database.php?do=run" method="POST">
		<table>
			<tr>
				<th>Hostname:</th>
				<td><input type="host" value="<?=$host?>" /></td>
			</tr>
			<tr>
				<th>Username:</th>
				<td><input type="user" value="<?=$user?>" /></td>
			</tr>
                        <tr>
                                <th>Password:</th>
                                <td><input type="pass" value="<?=$pass?>" /> (It`s visible)</td>
                        </tr>
                        <tr>
                                <th>Database:</th>
                                <td><input type="db" value="<?=$db?>" /></td>
                        </tr>
			<tr> 
				<td colspan="2">
					<div align="center">
						<input type="submit" value="Step three: Setup news server connection" />
					</div>
				</td>
			</tr>
		</table>
	<?php
		if($failed == 1) {
			echo '<div class="error">Connection to MySQL failed! The error returned was: '.mysql_error()."</div>";
		}
	?>
	</form>

<?php include('footer.html'); ?>

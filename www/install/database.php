<?php 
include('header.html');

//Try to save config and import data
if($_GET['do'] == 'run') {

	$failed = 0;
	$host = $_POST['host'];
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	$db   = $_POST['db'];

	//Test connetion
	$connTest = @mysql_connect($host, $user, $pass);
	if($connTest == false) {
		$failed = 1;
	} else {
		$dbTest = mysql_select_db($db, $connTest);
		if($dbTest == false)
			$failed = 1;
	}

	//Save config and import database
	if($failed == 0) {

		//Fetch config and set new values
		$confTpl = file("../config.dist.php");
		$i = 0;
		foreach($confTpl as $c) {
			if(stristr($c, "DB_HOST")) {
				$confTpl[$i] = "define('DB_HOST', '{$host}');\n";
			} else if(stristr($c, "DB_USER")) {
				$confTpl[$i] = "define('DB_USER', '{$user}');\n";
			} else if(stristr($c, "DB_PASSWORD")) {
				 $confTpl[$i] = "define('DB_PASSWORD', '{$pass}');\n";
			} else if(stristr($c, "DB_NAME")) {
				$confTpl[$i] = "define('DB_NAME', '{$db}');\n";
			} else {
				$confTpl[$i] = str_replace("\r\n", "\n", $c);
			}
			$i++;
		}

		//Save new config
		$fp = fopen('../config.php', 'w');
		foreach($confTpl as $c) {
			fwrite($fp, $c);
		}
		fclose($fp);
	}
}

if(!$host)
	$host = "localhost";

?>
	<h1>Database setup</h1>
	<p>We need some information about your MySQL database, please provide the following information</p>
	<form action="database.php?do=run" method="post">
		<table>
			<tr>
				<th>Hostname:</th>
				<td><input type="text" name="host" value="<?=$host?>" /></td>
			</tr>
			<tr>
				<th>Username:</th>
				<td><input type="text" name="user" value="<?=$user?>" /></td>
			</tr>
                        <tr>
                                <th>Password:</th>
                                <td><input type="password" name="pass" value="<?=$pass?>" /></td>
                        </tr>
                        <tr>
                                <th>Database:</th>
                                <td><input type="text" name="db" value="<?=$db?>" /></td>
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

<?php 

include('Net/NNTP/Client.php');

//Try to save config and import data
if($_GET['do'] == 'run') {

	$failed = 0;
	$host = $_POST['host'];
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	$port = $_POST['port'];

	$test = new Net_NNTP_Client();
	$conntest = $test->connect($host, false, $port);
	if(strlen($conntest->message) > 0) {
		$error = $conntest->message;
	}

	if(strlen($error) == 0) {
		//NNTP connection works, try to auth
		$authtest = $test->authenticate($user, $pass);
		if(strlen($authtest->message) > 0) { 
			$error = $authtest->message;
		} else {
			//it all works! Save config
                	$conf = file("../config.php");
        	        $i = 0;
	                foreach($conf as $c) {
				if(stristr($c, "NNTP_USERNAME")) {
					$conf[$i] = "define('NNTP_USERNAME', '{$user}');\n";
				} else if(stristr($c, "NNTP_PASSWORD")) {
					$conf[$i] = "define('NNTP_PASSWORD', '{$pass}');\n";
				} else if(stristr($c, "NNTP_SERVER")) {
					$conf[$i] = "define('NNTP_SERVER', '{$host}');\n";
				} else if(stristr($c, "NNTP_PORT")) {
					$conf[$i] = "define('NNTP_PORT', '{$port}');\n";
				}	
				$i++;
			}

	                //Save new config
        	        $fp = fopen('../config.php', 'w');
                	foreach($conf as $c) {
                        	fwrite($fp, $c);
	                }
        	        fclose($fp);

			//Done
			header("location: createadminuser.php");
			exit();
		}
	}
}

if(is_numeric($port) == false) {
	$port = 119;
}

include('header.html');
?>
	<h1>News server setup</h1>
	<p>We need some information about your News server(NNTP), please provide the following information</p>
	<form action="nntp.php?do=run" method="post">
		<table>
			<tr>
				<th>Server:</th>
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
                                <th>Port:</th>
                                <td><input type="text" name="port" value="<?=$port?>" /></td>
                        </tr>
			<tr> 
				<td colspan="2">
					<div align="center">
						<input type="submit" value="Step five: Setup admin user" />
					</div>
				</td>
			</tr>
		</table>
	<?php
		if(strlen($error) > 0) {
			echo '<div class="error">Connection to the news server failed! The error returned was: '.$error."</div>";
		}
	?>
	</form>

<?php include('footer.html'); ?>

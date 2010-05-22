<?php include('header.html'); ?>
        <h1>Finish up</h1>
	<p>The installation is now finished. Please remove your /install folder and click below to go to your newly installed site.
	<br /><br />
	Hint: rm -rf <?=realpath('.')?></p>

	<form action="../" method="GET">
		<div align="center">
        		<input type="submit" value="Take me to the frontpage" />
	       </div>
	</form>

<?php include('footer.html'); ?>

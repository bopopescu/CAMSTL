
<div class="tabblock2">
	<div class="tabs2">
	
		<!-- Main tabs -->
		
		<ul>
			<!-- The What's New and Known Issues tabs were removed because their content wasn't being updated for each release -->
			<!-- <li <?php if (stripos($_SERVER["REQUEST_URI"],"/support/whatsnew/index.php") === 0) echo " class='active' ";?>><a href="/support/whatsnew/index.php">What's New</a></li> -->
			<!-- <li <?php if (stripos($_SERVER["REQUEST_URI"],"/support/knownissues/index.php") === 0) echo " class='active' ";?>><a href="/support/knownissues/index.php">Known Issues</a></li> -->
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/support/changepassword/index.php") === 0) echo " class='active' ";?>><a href="/support/changepassword/index.php">Change Password</a></li>
			<li <?php if(isSuperAdmin()){ if (stripos($_SERVER["REQUEST_URI"],"/support/ReleaseNotes.php") === 0) echo " class='active' ";?>><a href="/support/releasenotes.php">Release Notes</a></li><?php } ?>
			<li <?php if(isSuperAdmin()){ if (stripos($_SERVER["REQUEST_URI"],"/support/update.php") === 0) echo " class='active' ";?>><a href="/support/update.php">Software Update</a></li><?php } ?>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/support/debug.php") === 0) echo " class='active' ";?>><a href="/support/debug.php">Debug</a></li>
		</ul>
	</div>
</div>

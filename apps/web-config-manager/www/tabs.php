
<div id="tabblock">
	<div id="tabs1">
		<!-- Main tabs -->		
		<ul>
			<li <?php if (strpos($_SERVER['REQUEST_URI'], "/device") === 0) echo " class='active' ";?>><a href="/device/general/index.php">Device Configuration</a></li>
			<li <?php if (strpos($_SERVER['REQUEST_URI'], "/network") === 0) echo " class='active' ";?>><a href="/network/ethernet/index.php">Network Configuration</a></li>
			<li <?php if ($_SERVER["REQUEST_URI"] == "/support/changepassword/index.php") echo " class='active' ";?>><a href="/support/changepassword/index.php">Support</a></li>
		</ul>
	</div>
</div>
<?php if(empty($_SESSION['M2M_SESH_USERNAME'])) { exit;}  ?>
<div class="tabblock2">
	<div class="tabs2">

		<!-- Main tabs -->

		<ul>
		<?php if(hasInstallerAccess()){ ?>	<!-- visible to installer and super admin -->
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/device/installersettings/index.php") === 0) echo " class='active' ";?>><a href="/device/installersettings/index.php">Installer Settings</a></li>
		<?php } ?>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/device/general/index.php") === 0) echo " class='active' ";?>><a href="/device/general/index.php">Status</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/device/settings/index.php") === 0) echo " class='active' ";?>><a href="/device/settings/index.php">Settings</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/device/gps/index.php") === 0) echo " class='active' ";?>><a href="/device/gps/index.php">GPS</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/device/zigbee/index.php") === 0) echo " class='active' ";?>><a href="/device/zigbee/index.php">SafetyLink Pendant</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/device/modbus/index.php") === 0) echo " class='active' ";?>><a href="/device/modbus/index.php">Modbus</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/device/j1939/index.php") === 0) echo " class='active' ";?>><a href="/device/j1939/index.php">J1939</a></li>
		</ul>
	</div>
</div>

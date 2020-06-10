
<div class="tabblock2">
	<div class="tabs2">
	
		<!-- Main tabs -->
		<ul>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/network/ethernet/index.php") === 0) echo " class='active' ";?>><a href="/network/ethernet/index.php">Ethernet</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/network/wifi/index.php") === 0) echo " class='active' ";?>><a href="/network/wifi/index.php">Wi-Fi</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/network/cellular/index.php") === 0) echo " class='active' ";?>><a href="/network/cellular/index.php">Cellular</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/network/iridium/index.php") === 0) echo " class='active' ";?>><a href="/network/iridium/index.php">Iridium</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/network/vpn/index.php") === 0) echo " class='active' ";?>><a href="/network/vpn/index.php">VPN</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/network/portforwarding/index.php") === 0) echo " class='active' ";?>><a href="/network/portforwarding/index.php">Port Forwarding</a></li>
			<li <?php if (stripos($_SERVER["REQUEST_URI"],"/network/ipreservation/index.php") === 0) echo " class='active' ";?>><a href="/network/ipreservation/index.php">IP Reservation</a></li>
		</ul>
	</div>
</div>

<!-- Sockets subsubtab -->
<form id="socketsCheck" method="post" action="/inc/socket_processor.php">
	<div class="inversetab">GPS</div>
	<div class="hr">
		<hr />
	</div>
	<div id="GpsSection">
		<div class="row">
			<span class="label2">NMEA</span>
			<span class="formw2">
				<input type="radio" name="gpsSocketServer" value="1" <?php echo (isOn($sock_nmea_status) && isOn($sock_ser_gps)) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
				<input type="radio" name="gpsSocketServer" value="0" <?php echo (isOff($sock_nmea_status) || isOff($sock_ser_gps))  ? 'checked="checked"' : '';?> /> Off
			</span>
		</div>
		<div class="row">
			<span class="label2">Port</span>
			<span class="formw2 reg lzero">
				<input type="text" size="10" name="gpsSocketServerPort" value="<?php echo $sock_nmea_port; ?>">&nbsp;&nbsp;  
				<!-- this should go into a tool tip <span class="fieldMessage">output port for feeding NMEA out of gps-socket-server</span> -->
				<span class="errorMsg"></span>
			</span>
		</div>
	</div>

	<div class="spacer">&nbsp;</div>
	<?php
	if(hasSubmitAccess())
	{
	?>
		<div class="row">
			<span class="formw2">
				<button type="submit" class="button2-link">Save</button>&nbsp;
				<button type="reset" class="button3-link">Cancel</button>&nbsp;
				<!-- <button class="button4-link">Sync</button> -->
				<br/><br/><br/>
			</span>
		</div>
	<?php 
	}							
	?>
</form>
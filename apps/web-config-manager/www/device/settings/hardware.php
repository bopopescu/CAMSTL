<!-- Hardware subsubtab -->
<form id="hardwareCheck" method="post" action="/inc/hardware_processor.php">
	<div class="inversetab">Hardware</div>
	<!--- <a href="/TL3000_HTML5/Default_CSH.htm#HARDWARE" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
	<div class="hr"><hr /></div>

	<div class="row">
		<span class="label2">GPS Chip</span>
		<span class="formw2">
			<input type="text" size="15" readonly="readonly" name="gpsType" value="<?php echo $hw_gps_type; ?>"/>
		</span>
	</div>

	<div class="row">
		<span class="label2">GPS Source</span>
		<span class="formw2">
			<input type="text" readonly="readonly" name="gpsSource" value="<?php echo $hw_gps_source; ?>"/>
		</span>
	</div>
	<div class="row">
		<span class="label2">Reboot On GPS Loss</span>
		<span class="formw2">
			<input type="radio" name="hardwareRebootGPS" value="On" <?php echo isOn($hw_reboot_gps) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
			<input type="radio" name="hardwareRebootGPS" value="Off" <?php echo isOff($hw_reboot_gps) ? 'checked="checked"' : '';?> /> Off
		</span>
	</div>
	<div class="row">
		<span class="label2">Speed Source</span>
		<span class="formw2">
			<select name="hardwareSpeedSource" style="width: 156px;">
				<option value="">Select speed source...</option>
				<option value="GPS" <?php echo ((strcasecmp($hw_speed_src,'GPS') == 0) ? 'selected="selected"':'');?>>GPS</option>
				<option value="OBD" <?php echo ((strcasecmp($hw_speed_src,'OBD') == 0) ? 'selected="selected"':'');?>>OBD</option>
			</select>
		</span>
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
				<!--<button class="button4-link">Sync</button>-->
			</span>
		</div>
	<?php
	}
	?>
</form>

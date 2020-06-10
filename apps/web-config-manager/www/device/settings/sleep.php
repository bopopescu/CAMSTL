	<!-- Sleep subsubtab -->
	<form id="outputCheck" method="post" action="/inc/sleep_processor.php">
		
		<!-- Header -->
		<div class="inversetab">Sleep Conditions</div>
		<!--- <a href="/TL3000_HTML5/Default_CSH.htm#SLEEPCONDITIONS" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
		<div class="hr"><hr /></div>
		The Sleep Conditions determine when a TruLink will shut down after the wake up conditions are cleared.
		<br/>
		<br/>
		
		<div class="inversetab">Priority 1</div>
 		<div class="hr"><hr /></div>
		<div id="Priority1Section">
			<div class="row">
				<span class="label2">Shutdown Voltage</span>
				<span class="formw2 reg">
					<input type="text" size="5" name="LowBatt" value="<?php echo $sleep_low_batt; ?>">&nbsp;V
					<span class="errorMsg"></span> 
					<br>
				</span>
			</div>
		</div>
		<br/>
 		<div class="row">
			<span>If the input voltage falls below the Shutdown Voltage for more than a minute the system will clear any high priority messages and then shut down.</span>
		</div>
		<br/>
		<br/>
		<br/>

		<div class="inversetab">Priority 2</div>
		<div class="hr"><hr /></div>
		<div class="row">
			<table class="collapse">
				<tr class="collapes">
					<th class="collapse"></th>
					<th class="collapse">No Activity Timeout</th>
					<th class="collapse">Max Timeout</th>
				</tr>
				<tr>
					<td>Wi-Fi Access Point Activity</td>
					<td>
						<select name="WiFiActivity" style="width: 60px;">
							<option value="0" <?php echo (($sleep_wifi_activity == 0) ? 'selected="selected"':'');?>>Off</option>
							<option value="1" <?php echo (($sleep_wifi_activity == 1) ? 'selected="selected"':'');?>>1</option>
							<option value="2" <?php echo (($sleep_wifi_activity == 2) ? 'selected="selected"':'');?>>2</option>
							<option value="3" <?php echo (($sleep_wifi_activity == 3) ? 'selected="selected"':'');?>>3</option>
							<option value="4" <?php echo (($sleep_wifi_activity == 4) ? 'selected="selected"':'');?>>4</option>
							<option value="5" <?php echo (($sleep_wifi_activity == 5) ? 'selected="selected"':'');?>>5</option>
							<option value="10" <?php echo (($sleep_wifi_activity == 10) ? 'selected="selected"':'');?>>10</option>
							<option value="15" <?php echo (($sleep_wifi_activity == 15) ? 'selected="selected"':'');?>>15</option>
							<option value="20" <?php echo (($sleep_wifi_activity == 20) ? 'selected="selected"':'');?>>20</option>
							<option value="25" <?php echo (($sleep_wifi_activity == 25) ? 'selected="selected"':'');?>>25</option>
							<option value="30" <?php echo (($sleep_wifi_activity == 30) ? 'selected="selected"':'');?>>30</option>
							<option value="45" <?php echo (($sleep_wifi_activity == 45) ? 'selected="selected"':'');?>>45</option>
							<option value="60" <?php echo (($sleep_wifi_activity == 60) ? 'selected="selected"':'');?>>60</option>
						</select>&nbsp;&nbsp;Minutes
					</td>
					<td>
						<select name="WiFiActivityMax" style="width: 60px;">
							<option value="0" <?php echo (($sleep_wifi_activity_max == 0) ? 'selected="selected"':'');?>>Off</option>
							<option value="1" <?php echo (($sleep_wifi_activity_max == 1) ? 'selected="selected"':'');?>>1</option>
							<option value="2" <?php echo (($sleep_wifi_activity_max == 2) ? 'selected="selected"':'');?>>2</option>
							<option value="3" <?php echo (($sleep_wifi_activity_max == 3) ? 'selected="selected"':'');?>>3</option>
							<option value="4" <?php echo (($sleep_wifi_activity_max == 4) ? 'selected="selected"':'');?>>4</option>
							<option value="5" <?php echo (($sleep_wifi_activity_max == 5) ? 'selected="selected"':'');?>>5</option>
							<option value="10" <?php echo (($sleep_wifi_activity_max == 10) ? 'selected="selected"':'');?>>10</option>
							<option value="15" <?php echo (($sleep_wifi_activity_max == 15) ? 'selected="selected"':'');?>>15</option>
							<option value="20" <?php echo (($sleep_wifi_activity_max == 20) ? 'selected="selected"':'');?>>20</option>
							<option value="25" <?php echo (($sleep_wifi_activity_max == 25) ? 'selected="selected"':'');?>>25</option>
							<option value="30" <?php echo (($sleep_wifi_activity_max == 30) ? 'selected="selected"':'');?>>30</option>
							<option value="45" <?php echo (($sleep_wifi_activity_max == 45) ? 'selected="selected"':'');?>>45</option>
							<option value="60" <?php echo (($sleep_wifi_activity_max == 60) ? 'selected="selected"':'');?>>60</option>
						</select>&nbsp;&nbsp;Minutes
					</td>
				</tr>
			
				<tr>
					<td>ZigBee Radio Activity</td>
					<td>
						<select name="ZigBeeActivity" style="width: 60px;">
							<option value="0" <?php echo (($sleep_zigbee_activity == 0) ? 'selected="selected"':'');?>>Off</option>
							<option value="1" <?php echo (($sleep_zigbee_activity == 1) ? 'selected="selected"':'');?>>1</option>
							<option value="2" <?php echo (($sleep_zigbee_activity == 2) ? 'selected="selected"':'');?>>2</option>
							<option value="3" <?php echo (($sleep_zigbee_activity == 3) ? 'selected="selected"':'');?>>3</option>
							<option value="4" <?php echo (($sleep_zigbee_activity == 4) ? 'selected="selected"':'');?>>4</option>
							<option value="5" <?php echo (($sleep_zigbee_activity == 5) ? 'selected="selected"':'');?>>5</option>
							<option value="10" <?php echo (($sleep_zigbee_activity == 10) ? 'selected="selected"':'');?>>10</option>
							<option value="15" <?php echo (($sleep_zigbee_activity == 15) ? 'selected="selected"':'');?>>15</option>
							<option value="20" <?php echo (($sleep_zigbee_activity == 20) ? 'selected="selected"':'');?>>20</option>
							<option value="25" <?php echo (($sleep_zigbee_activity == 25) ? 'selected="selected"':'');?>>25</option>
							<option value="30" <?php echo (($sleep_zigbee_activity == 30) ? 'selected="selected"':'');?>>30</option>
							<option value="45" <?php echo (($sleep_zigbee_activity == 45) ? 'selected="selected"':'');?>>45</option>
							<option value="60" <?php echo (($sleep_zigbee_activity == 60) ? 'selected="selected"':'');?>>60</option>
						</select>&nbsp;&nbsp;Minutes
					</td>
					<td>
						&nbsp;&nbsp;N/A
					</td>
				</tr>
			</table>
		</div>
		<br/>
		<br/>

		<div class="inversetab">Priority 3</div>
		<div class="hr"><hr /></div>
 		<div class="row">
			<span class="label">Keep Awake</span>
			<span class="formw2">
					<select name="SleepKeepAwake" style="width: 60px;">
						<option value="0" <?php echo (($sleep_keep_awake == 0) ? 'selected="selected"':'');?>>Off</option>
						<option value="1" <?php echo (($sleep_keep_awake == 1) ? 'selected="selected"':'');?>>1</option>
						<option value="2" <?php echo (($sleep_keep_awake == 2) ? 'selected="selected"':'');?>>2</option>
						<option value="3" <?php echo (($sleep_keep_awake == 3) ? 'selected="selected"':'');?>>3</option>
						<option value="4" <?php echo (($sleep_keep_awake == 4) ? 'selected="selected"':'');?>>4</option>
						<option value="5" <?php echo (($sleep_keep_awake == 5) ? 'selected="selected"':'');?>>5</option>
						<option value="10" <?php echo (($sleep_keep_awake == 10) ? 'selected="selected"':'');?>>10</option>
						<option value="15" <?php echo (($sleep_keep_awake == 15) ? 'selected="selected"':'');?>>15</option>
						<option value="20" <?php echo (($sleep_keep_awake == 20) ? 'selected="selected"':'');?>>20</option>
						<option value="25" <?php echo (($sleep_keep_awake == 25) ? 'selected="selected"':'');?>>25</option>
						<option value="30" <?php echo (($sleep_keep_awake == 30) ? 'selected="selected"':'');?>>30</option>
						<option value="45" <?php echo (($sleep_keep_awake == 45) ? 'selected="selected"':'');?>>45</option>
						<option value="60" <?php echo (($sleep_keep_awake == 60) ? 'selected="selected"':'');?>>60</option>
						<option value="120" <?php echo (($sleep_keep_awake == 120) ? 'selected="selected"':'');?>>120</option>
						<option value="180" <?php echo (($sleep_keep_awake == 180) ? 'selected="selected"':'');?>>180</option>
						<option value="240" <?php echo (($sleep_keep_awake == 240) ? 'selected="selected"':'');?>>240</option>
					</select>&nbsp;&nbsp;Minutes &nbsp;&nbsp;&nbsp;&nbsp;
				</span>
			</div>		

		<div class="spacer">&nbsp;</div>
		<div class="hr"><hr /></div>
		<?php
		if(hasSubmitAccess())
		{
		?>
			<div class="row">
				<span class="formw2">
					<button type="submit" class="button2-link">Save</button>&nbsp;
					<button type="reset" class="button3-link">Cancel</button>&nbsp;
				</span>
			</div>
		<?php 
		}							
		?>
	</form>
	

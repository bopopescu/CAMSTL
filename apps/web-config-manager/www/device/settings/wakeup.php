	<!-- wakeup subsubtab -->
	<form id="wakeupCheck" method="post" action="/inc/wakeup_processor.php">
		<!-- Header -->
		<div class="inversetab">Wakeup Triggers</div>
		<!--- <a href="/TL3000_HTML5/Default_CSH.htm#WAKEUPTRIGGERS" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
		<div class="hr"><hr /></div>
		<br/>
		<div>
			<?php if ($UseLowBatt) {?>
				<span><input type="checkbox" name="useLowBatt" value="low_batt" checked/>Critical Low Voltage</span>
			<?php } else { ?>
				<span><input type="checkbox" name="useLowBatt" value="low_batt"/>Critical Low Voltage</span>
			<?php } ?>
				<span>
					<input type="text" size="3" name="wakeupLowBattV" value="<?php echo $wakeup_low_battery_voltage; ?>" />&nbsp; V
					<span class="errorMsg"></span>
				</span>
		</div>
		<br/>
		<div class="row">
			<span>If the system is off and the input voltage drops below the Critical Low Voltage the system will wake up once to send a 'Critical Battery' message then shutdown again and ignore all of the wakeup signals until full voltage is restored.</span>
		</div>
		<div class="row">
			<span>If the system is on and the input voltage drops below the Critical Low Voltage the system will send a 'Critical Battery' message then shutdown  and ignore all of the wakeup signals until full voltage is restored</span>
		</div>
		<br/>

		<div>
		<div class="hr"><hr /></div>
			<?php if ($UseRTC) {?>
				<span><input type="checkbox" name="useRTC" value="rtc" checked disabled="disabled"/>Daily Heartbeat</span>
			<?php } else { ?>
				<span><input type="checkbox" name="useRTC" value="rtc" disabled="disabled"/>Daily Heartbeat</span>
			<?php } ?>
		</div>

		<div>
			<?php if ($UseAccel) {?>
				<span><input type="checkbox" name="useAccel" value="accel" checked/>Accelerometer:</span>
			<?php } else { ?>
				<span><input type="checkbox" name="useAccel" value="accel"/>Accelerometer:</span>
			<?php } ?>
				<span class="label2">if g-force exceeds:</span>
				<span id="pcalamp_user_msg">
					<input type="text" size="2" name="wakeupGForce" value="<?php echo $wakeup_g_force; ?>" />&nbsp; g
				</span>
		</div>
		<div >
			<?php if ($UseIgnition) {?>
				<span><input type="checkbox" name="useIgnition" value="inp1" checked/>Ignition Input</span>
			<?php } else { ?>
				<span><input type="checkbox" name="useIgnition" value="inp1"/>Ignition Input</span>
			<?php } ?>
		</div>

		<?php
		if (file_exists("/mnt/nvram/rom/hw/red-green-led"))
		{
		?>
			<div>
				<?php if ($UseInp2) {?>
					<span><input type="checkbox" name="useInp2" value="inp2" checked/>Input 2</span>
				<?php } else { ?>
					<span><input type="checkbox" name="useInp2" value="inp2"/>Input 2</span>
				<?php } ?>
			</div>
		<?php
		}
		?>

<!--- Removed until daughter card is kept alive when Iridium is being used.
		<div>
			<?php if ($UseIridium) {?>
				<span><input type="checkbox" name="useIridium" value="inp3" checked/>Iridium Ring</span>
			<?php } else { ?>
				<span><input type="checkbox" name="useIridium" value="inp3"/>Iridium Ring</span>
			<?php } ?>
		</div>
-->
<!---		<div>
			<?php if ($UseCAN) {?>
				<span><input type="checkbox" name="useCAN" value="can" checked/>CAN Bus Activity</span>
			<?php } else { ?>
				<span><input type="checkbox" name="useCAN" value="can"/>CAN Bus Activity</span>
			<?php } ?>
		</div>
-->
		<div>
			<div>
			<?php if ($UseVoltage) {?>
				<span><input type="checkbox" name="useVoltage" value="batt_volt" checked/>Battery voltage:</span>
			<?php } else { ?>
				<span><input type="checkbox" name="useVoltage" value="batt_volt"/>Battery voltage:</span>
			<?php } ?>
				<span class="label2">if voltage exceeds:</span>
				<span id="pcalamp_user_msg">
					<input type="text" size="5" name="wakeupBatteryVoltage" value="<?php echo $wakeup_battery_voltage; ?>" />&nbsp; V
				</span>
			</div>
		</div>

		<br />
		<span class="fieldMessage">* At least one Wakeup Trigger must be selected.</span>



		<div class="spacer">&nbsp;</div>
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

		<br/>
		<br/>
		<br/>
		<br/>
		<br/>
		<div class="inversetab">Trigger of most recent wakeups</div>
		<div class="hr"><hr /></div>
		<div class="row">
			<span class="label2">
				<?php
					if (substr($wakeup_reason, 0, 5) === 'power')
						echo '<b>Power</b>';
					else if (substr($wakeup_reason,0 ,4) === 'inp1')
						echo '<b>Ignition</b>';
					else
						echo "<b>$wakeup_reason</b>";
				?>
			</span>
			<span class="label">
				<?php
					echo "<b>$wakeup_time</b>";
				?>
			</span>
		</div>
		<div class="row">
			<span class="label2">
				<?php
					if (substr($wakeup_reason2, 0, 5) === 'power')
						echo 'Power';
					else if (substr($wakeup_reason2,0 ,4) === 'inp1')
						echo 'Ignition';
					else
						echo $wakeup_reason2;
				?>
			</span>
			<span class="label2">
				<?php
					echo $wakeup_time2;
				?>
			</span>
		</div>
	</form>


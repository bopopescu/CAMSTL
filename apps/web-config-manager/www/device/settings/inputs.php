<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/session_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/inputs-settings.inc';
?>

<!-- Inputs subsubtab -->

<script type="text/javascript" src="/js/message_priority.js"></script>

<form id="inputCheck" method="post" action="/inc/gpi_processor.php">
	<?php
		// Determine the number of inputs
		$max=6;
		if (file_exists("/mnt/nvram/rom/hw/red-green-led"))
		{
			$max=4;
		}
	?>

	<div id="GpiSection">
	<!-- Header -->
	<div class="inversetab">Inputs</div>
	<!--- <a href="/TL3000_HTML5/Default_CSH.htm#INPUTS" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
	<div class="hr"><hr /></div>

		<div class="row">
			<span class="label2">GPIO Monitor</span> <span class="formw2">
				<input type="radio" name="gpiMonitor" value="1" <?php echo isOn($enable) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
				<input type="radio" name="gpiMonitor" value="0" <?php echo isOff($enable) ? 'checked="checked"' : '';?> /> Off
			</span>
		</div>
	</div>
	<br>
	<br>
	<!-- start of "smart table" for entering all inputs data -->
	<div id="gpio_smart_tables">
	<div class="row">
		<table class="collapse">
				<tr class="collapse" >
					<th class="collapse" style="width: 10%;">Input</th>
					<th class="collapse" style="width: 10%;">Monitor</th>
					<th class="collapse" style="width: 10%;">Voltage Level</th>
					<th class="collapse" style="width: 10%;">Debounce Time (sec) <!--- <a href="/TL3000_HTML5/Default_CSH.htm#DEBOUNCE_INTERVAL" TARGET="_blank"><img src="../../images/help.png" alt="help" border="0" > --> </th>
				</tr>

			<tbody>
			<?php
				for($i=1;$i<=$max;$i++)
				{
					$isMonitored=${"gpiInputMonitor".$i};

					echo ' <tr> <td>'.$i.'</td>';

					// Active: On/Off
					echo ' <td>'; // Active: On/Off
					echo '<div class="input'.$i.'">	<select class="inputMonitored" name="gpiInputMonitor'.$i.'" ';
					print ((!$enable) ? "disabled='true' " : "").'>';

					if (isOn($isMonitored))
					{
						echo '<option value="0">Off</option>	<option value="1" selected="selected">On</option>';
					}
					else
					{
						echo '<option value="0" selected="selected">Off</option>	<option value="1" >On</option>';
					}
					echo '</select></div></td>';

					// Voltage Level: High/Low
					echo ' <td>';// Voltage Level: High/Low
					$isActive=${"gpiInputActive".$i};

					echo '<div class="input'.$i.'"><select class="VoltageLevel" name="gpiInputActive'.$i.'"';
					print ((!$enable || !isOn($isMonitored)) ? "disabled='true'" : "").'>';

					if (isOff($isActive))
					{
						echo '<option value="1">High</option>	<option value="0"	selected="selected">Low</option>';
					}
					else
					{
						echo '<option value="1" selected="selected">High</option>	<option value="0" >Low</option>';
					}
					echo '</select></div></td>';

					echo ' <td>'; // Debounce Time
					$debounce=${"gpiInputDebounce".$i};
					echo '<div class="input'.$i.'">';
					echo '<select class="debounce'.$i.'" name="gpiInputDebounce'.$i.'" ';
					print ((!$enable || !isOn($isMonitored)) ? "disabled='true'" : "").'>';
					print "
								<option value=\"0\"".(empty($debounce)?"selected=\"selected\"":'').">OFF</option>
								<option value=\"0.25\"".(($debounce<=0.25&&$debounce>0)?"selected=\"selected\"":'').">0.25</option>
								<option value=\"0.5\"".(($debounce<=0.5&&$debounce>0.25)?"selected=\"selected\"":'').">0.5</option>
								<option value=\"0.75\"".(($debounce<=0.75&&$debounce>0.5)?"selected=\"selected\"": '').">0.75</option>
								<option value=\"1.0\"". (($debounce<=1.0&&$debounce>0.75)?"selected=\"selected\"": '').">1.0</option>
								<option value=\"1.25\"".(($debounce<=1.25&&$debounce>1.0)?"selected=\"selected\"": '').">1.25</option>
								<option value=\"1.5\"".(($debounce<=1.5&&$debounce>1.25)?"selected=\"selected\"": '').">1.5</option>
								<option value=\"1.75\"".(($debounce<=1.75&&$debounce>1.5)?"selected=\"selected\"": '').">1.75</option>
								<option value=\"2.0\"".(($debounce<=2.0&&$debounce>1.75)?"selected=\"selected\"": '').">2.0</option>
								<option value=\"5\"".(($debounce<=5&&$debounce>2.0)?"selected=\"selected\"": '').">5</option>
								<option value=\"10\"".(($debounce<=10&&$debounce>5)?"selected=\"selected\"": '').">10</option>
								<option value=\"30\"".(($debounce<=30&&$debounce>10)?"selected=\"selected\"": '').">30</option>
								<option value=\"60\"".(($debounce>30)?"selected=\"selected\"": '').">60</option>";
					echo '</select></div>';
					echo '</td>'; // End of Debounce Time
				}
			?>
			</tbody>
		</table>
	</div>
	<br>
<!--
	<div id="MessageOnTypeTrigger">
		<div class="row">
			<table >
				<thead>
					<tr>
						<th colspan="4">Define On/Off Trigger Messages </th>
					</tr>
					<tr>
						<th style="width: 10%;">Input</th>
						<th style="width: 10%;">Msg</th>
						<th style="width: 35%;">Type</th>
						<th style="width: 45%;">Priority</th>
					</tr>
				</thead>
				<tbody>
					<?php
						for($i=1;$i<=$max;$i++)
						{
							$isMonitored=${"gpiInputMonitor".$i};
							echo '<tr>';
							echo '<td rowspan="2">'.$i.'</td>';
							echo '<td>On</td>';
							echo ' <td>'; // On Message Type
							echo '<div class="input'.$i.'">
										<select class="onmessagetype"	name="onmessage_type'.$i.'" ';
							print ((!$enable || !isOn($isMonitored)) ? "disabled" : "").'>';
							echo '<option value="*" selected disabled>Select a Message Type</option>';

								foreach ($messageTypes as $type => $v)
								{
									$a=${'onmessage_type'.$i};
									$s='';
									if( $a == $v )
									{
										$s="selected=\"selected\"";
									}
									print " <option value=\"$v\" $s>".$type."</option> ";
								};
								print '</select></div>';
								echo '</td>'; // End of On Message Type

								echo ' <td>'; // On Message Priority
							?>
							<div class="row">
								<span class="formw-slider-table">
									<div class="slider-row">
										<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
										<div class="slider-label-center slider-label">Cell</div>
										<div class="slider-label-right slider-label">Iridium*</div>
									</div>
									<div class="slider-row">
										<?php
											$a=${"onpri".$i};
											echo '<div class="input'.$i.'">
														<input type="range" name="onpri'.$i.'" min="1" max="3" step="1"
														value="'.$a.'" ';
											print ((!$enable || !isOn($isMonitored)) ? "disabled" : "").'></div>';
										?>
									</div>
								</span>
							</div>
						<?php
							echo '</td></tr>'; // End of On Message Type
								$isMonitored=${"gpiInputMonitor".$i};
								echo '<tr>';
								echo '<td>Off</td>';
								echo ' <td>'; // Off Message Type
								echo '<div class="input'.$i.'">
											<select class="offmessagetype"	name="offmessage_type'.$i.'" ';
								print ((!$enable || !isOn($isMonitored)) ? "disabled" : "").'>';
								echo '<option value="*" selected disabled>Select a Message Type</option>';  // this is the unselectable top line in the drop down list

								foreach ($messageTypes as $type => $v)
								{
									$a=${'offmessage_type'.$i};
									$s='';
									if( $a == $v )
									{
										$s="selected=\"selected\"";
									}
									print " <option value=\"$v\" $s>".$type."</option> ";
								};
								print '</select></div>';
								echo '</td>'; // End of On Message Type
							echo ' <td>'; // Off Message Priority
						?>
							<div class="row">
								<span class="formw-slider-table">
									<div class="slider-row">
										<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
										<div class="slider-label-center slider-label">Cell</div>
										<div class="slider-label-right slider-label">Iridium*</div>
									</div>
									<div class="slider-row">
										<?php
											$a=${"offpri".$i};
											echo '<input id="offPRIslide'.$i.'" type="range" name="offpri'.$i.'" min="1" max="3" step="1" value="'.$a.'" ';
											print ((!$enable || !isOn($isMonitored)) ? ' disabled="disabled"' : "").' />';
										?>
									</div>
								</span>
							</div>
						<?php
							echo '</td></tr>'; // End of On Message Type
						}
						?>
				</tbody>
			</table>
		</div>
	</div>
	<br>
-->

	</div>
	<br>
	<div class="row">
		<span class="footNote">
			* Note: Sending messages over Iridium will incur higher costs.
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
		</span>
	</div>
	<?php
		}
	?>
</form>
<script type="text/javascript" src="/js/mainFunctions.js"></script>
<script type="text/javascript" src="/js/inputs.js"></script>


<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/session_controller.inc';
?>
<!DOCTYPE HTML>

<?php
function isTemplateDeletable($templateName) // can the template be deleted? (Must have submit access and not be a default template)
{
	if (!hasSubmitAccess())
	  return false;
	if ($templateName == "Cummins" || $templateName == "Murphy" || $templateName == "VFD")
		return false;
	
	return true;
}
?>

<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Modbus - <?php echo DEVICE_NAME; ?></title>

<?php
include $_SERVER['DOCUMENT_ROOT'] . 'mainscriptsgroup.php';
?>
	<script type="text/javascript" src="/js/modbus.js"></script>
</head>

<body>
	<div id="dialog" class="ui-dialog-content ui-widget-content"></div>
	<div class="container">
		<?php
		include $_SERVER['DOCUMENT_ROOT'] . 'header.php';
		?>

		<div class="clear"></div>
		<div class="clear"></div>

		<?php
		include $_SERVER['DOCUMENT_ROOT'] . 'tabs.php';
		?>


		<div class="contentblock">

			<!-- Device  tab -->
			<h2>Device Configuration</h2>

			<?php
			include '../devicetabs.php';
			?>

				<div class="contentblock2">
				<div class="msgBox"></div>
				<?php
					require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/modbus-settings.inc';
					print "
				<script type=\"text/javascript\" >
					var g_templateAssignments = {";
					foreach ($slave_array as $slave => $template)
					{
						print "
						$slave : '$template',";
					}
					print "
					};
					var g_templateNames = {";
					foreach ($templates_array as $key => $template)
					{
						print "
						$key : '$template',";
					}
					print "
					};
				</script>";
				?>
				<div id="modbusSettings">
					<div class="inversetab">Modbus</div>
					<!--- <a href="/TL3000_HTML5/Default_CSH.htm#MODBUS" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
					<div class="hr"><hr /></div>
					<br/>
						<form id="modbusSubSettings" method="post" action="/inc/modbus_controller.php">
							<input type="hidden" name="op" value="updateSettings" />
							<fieldset id="modbusSettingsField">
								<legend>Settings</legend>
								<div class="row">
									<span class="label">Enable Modbus</span>
									<span class="formw units">
										<input type="radio" size="6"  name="enable" value="On" <?php print(($enable) ? "checked=\"checked\"" : ""); ?> /> On &nbsp;
										<input type="radio" size="6"  name="enable" value="Off" <?php print((!$enable) ? "checked=\"checked\"" : ""); ?> /> Off
									</span>
								</div>
								<div class="row">
									<span class="label">Mode</span>
									<span class="formw units">
										<input type="radio" size="6"  name="modbusMode" value="rtu" <?php print(($modbusMode == "rtu") ? "checked=\"checked\"" : ""); print ((!$enable) ? "disabled='true'" : "");?> /> RS485 &nbsp;
										<input type="radio" size="6"  name="modbusMode" value="tcp" <?php print(($modbusMode == "tcp" ) ? "checked=\"checked\"" : ""); print ((!$enable) ? "disabled='true'" : "");?> /> TCP
									</span>
								</div>
								<div class="row rtu">
									<span class="label">Baud Rate</span>
									<span class="formw units">
										<select  name="baudrate" <?php print((!$enable) ? " disabled='true'" : " ");?> >
											<?php
											print "
											<option value=\"1200\"" . (($baudrate == 1200) ? "selected=1" : "") . "> 1200</option>
											<option value=\"2400\"" . (($baudrate == 2400) ? "selected=1" : "") . "> 2400</option>
											<option value=\"4800\"" . (($baudrate == 4800) ? "selected=1" : "") . "> 4800</option>
											<option value=\"9600\"" . (($baudrate == 9600) ? "selected=1" : "") . "> 9600</option>
											<option value=\"19200\"" . (($baudrate == 19200) ? "selected=1" : "") . "> 19200</option>
											<option value=\"38400\"" . (($baudrate == 38400) ? "selected=1" : "") . "> 38400</option>
											<option value=\"57600\"" . (($baudrate == 57600) ? "selected=1" : "") . "> 57600</option>
											<option value=\"115200\"" . (($baudrate == 115200) ? "selected=1" : "") . "> 115200</option>
											";
											?>
										</select>&nbsp;bps
									</span>
								</div>

								<div class="row tcp">
								<span class="label">TCP Master IP</span>
								<span class="formw " id="mip">
									<input type="text" size="2" maxlength="3" name="mipoct1" class="autotab" value="<?php print $mip[0]; print ((!$enable) ? "\" disabled='true'" : "\"");?>">.
									<input type="text" size="2" maxlength="3" name="mipoct2" class="autotab" value="<?php print $mip[1]; print ((!$enable) ? "\" disabled='true'" : "\"");?>">.
									<input type="text" size="2" maxlength="3" name="mipoct3" class="autotab" value="<?php print $mip[2]; print ((!$enable) ? "\" disabled='true'" : "\"");?>">.
									<input type="text" size="2" maxlength="3" name="mipoct4" class="autotab" value="<?php print $mip[3]; print ((!$enable) ? "\" disabled='true'" : "\"");?>">
									<span class="errorMsg" name="errorEIP"></span>
								</span>
							</div>

							<div class="row tcp">
								<span class="label">TCP Master Port</span>
								<span class="formw" id="mport">
									<input type="text" size="5" maxlength="6" name="mportdata" class="autotab" value="<?php print $mport; print ((!$enable) ? "\" disabled='true'" : "\"");?>">
									<span class="errorMsg" name="errorEPORT"></span>
								</span>
							</div>

								<div class="row rtu">
									<span class="label">Data Bits</span>
									<span class="formw">
										<select  name="data_bits" <?php print((!$enable) ? " disabled='true'" : ""); ?>>
											<?php
											print "
											<option value=\"5\"" . (($data_bits == 5) ? "selected=1" : "") . "> 5</option>
											<option value=\"7\"" . (($data_bits == 7) ? "selected=1" : "") . "> 7</option>
											<option value=\"8\"" . (($data_bits == 8) ? "selected=1" : "") . "> 8</option>
											<option value=\"9\"" . (($data_bits == 9) ? "selected=1" : "") . "> 9</option>
											";
											?>
										</select>
									</span>
								</div>
								<div class="row rtu">
									<span class="label">Parity</span>
									<span class="formw units">
										<select name="parity" <?php print((!$enable) ? " disabled='true'" : ""); ?>>
										<?php
										print "
											<option value=\"E\"" . (($parity == 'E') ? "selected=1" : "") . "> Even</option>
											<option value=\"O\"" . (($parity == 'O') ? "selected=1" : "") . "> Odd</option>
											<option value=\"N\"" . (($parity == 'N') ? "selected=1" : "") . "> None</option>
										";
										?>
										</select>
									</span>
								</div>
								<div class="row rtu">
									<span class="label">Stop Bits</span>
									<span class="formw">
										<select name="stop_bits" <?php print((!$enable) ? " disabled='true'" : ""); ?> >
											<?php
											print "
											<option value=\"0\"" . (($stop_bits == 0) ? "selected=1" : "") . "> 0</option>
											<option value=\"1\"" . (($stop_bits == 1) ? "selected=1" : "") . "> 1</option>
											<option value=\"2\"" . (($stop_bits == 2) ? "selected=1" : "") . "> 2</option>
											";
											?>
										</select>
									</span>
								</div>
								<div class="row">
									<span class="label" >Normal Mode Threshold Delay</span>
									<span class="formw units" >
										<input type="text" size="5" name="qDelaySeconds" value="<?php print $qDelaySeconds;
										print((!$enable) ? "\" disabled='true'" : "\"");
										?> />&nbsp;
										seconds
										<br />
										<span class="errorMsg"></span>
									</span>
								</div>
								<div class="row">
									<span class="label" >Periodic Message Frequency</span>
									<span class="formw units">
										<input type="text" size="5" name="periodicSeconds" value="<?php print $periodicSeconds;
										print((!$enable) ? "\" disabled='true'" : "\"");
										?> />&nbsp;
										seconds
										<br />
										<span class="errorMsg"></span>
									</span>
								</div>
								<div class="row">
									<span class="label" >Engine Data Reporting Interval (Satellite)</span>
									<span class="formw units">
										<input type="text" size="5" name="periodicOveriridiumMinutes" value="<?php print $periodicOveriridiumMinutes;
										print((!$enable) ? "\" disabled='true'" : "\"");
										?> />&nbsp;
										minutes
										<br />
										<span class="errorMsg"></span>
									</span>
								</div>
								<div class="spacer">&nbsp;</div>
								<div class="row">

									<?php
									if(hasSubmitAccess())
									{
									?>
									<span class="formw">
										<button type="submit" class="button2-link">Save</button>&nbsp;
										<button type="reset" class="button3-link" >Clear</button>
									</span>
									<?php
									}
									?>
								</div><!-- end row-wide-->
								<div class="spacer">&nbsp;</div>
							</fieldset>
						</form>
						<br />
						<form id="addModbusTemplate" method="post" enctype="multipart/form-data" action="/inc/modbus_controller.php">
							<div class="inversetab">Modbus Configuration</div>
							<!--- <a href="/TL3000_HTML5/Default_CSH.htm#MODBUS_TEMPLATE" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
							<div class="hr"><hr /></div>
							<br/>
							

							<fieldset>
								<legend> Add Configuration</legend>
								<input type="hidden" name="op" value="addTemplate" />
								<div class="row-wide">
									<div class="column">
										<div class="column-label"><label for="templateName">Configuration Name</label></div>
										<div class="column-field">
											<input type="text" size="30" maxlength="30" id="templateName" name="templateName" <?php
											if ($templateName != '')
												print "value='$templateName'";
										?> />
										</div>
										<div><span class="fieldMessage">* Only a-z, 0-9, -, _ are allowed<br /> 30 characters max</span></div>
									</div>
									<div class="column">
										<div class="column-label"><label for="templateFile">Configuration File</label></div>
										<div class="column-field">
											<input type="file" name="templateFile" />
										</div>
										<div>&nbsp;<br>&nbsp;</div>
									</div>

									<?php
									if(hasSubmitAccess())
									{
									?>
									<div class="column button-box-inline">
										<button type="submit" class="button2-link" disabled="true" >Add</button>&nbsp;
										<button type="reset" class="button3-link" >Clear</button>
										<div>&nbsp;<br /> &nbsp;</div>
									</div>
									<?php
									}
									?>
								</div><!-- end row-wide-->
							</fieldset>
						</form>
						<div class="spacer">&nbsp;</div>
						<legend>Saved Configurations</legend>
						<table id="saved-templates">
						<thead>
							<tr>
								<th style="width: 70%;">Configuration Name</th>
								<th style="width: 30%;">Delete</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($templates_array as $template) {
								print "
							<tr id=\"tr_$template\">
								<td> $template </td>
								<td class=\"delete\">" . (isTemplateDeletable($template) ? '<a class="deleteTemplate" href="#" title="Delete template"><img src="/images/DeleteIcon16.png" alt="Delete" /></a>' : '&nbsp;') . "</td>
							</tr>
							";
							}
							if (empty($templates_array)) {
								print "
							<tr>
								<td colspan=2> No templates have been saved. </td>
							</tr>
								";
							}
							?>
						</tbody>
						</table>
						<div class="spacer">&nbsp;</div>
						<form id="assignModbusTemplate" method="post" action="/inc/modbus_controller.php">
							<fieldset>
								<legend> Assign Configuration</legend>
								<input type="hidden" name="op" value="addAssignment" />
								<div class="row-wide">
									<div class="column">
										<div class="column-label"><label for="ruleName">Slave Id</label></div>
										<div class="column-field"><select name="slave_name"  >
											<option value="*" selected=""> Select a Slave Id </option>
											<?php
											for ($i = 1; $i < 248; $i++)
											{
												print "
											<option value=\"$i\" > $i</option>
											";
											}
											?>
										</select></div>
									</div>
									<div class="column">
										<div class="column-label"><label for="template_name">Configuration</label></div>
										<div class="column-field">
											<select name="template_name" >
												<option value="*" selected >Select a Configuration</option>
												<?php
												foreach ($templates_array as $template)
												{
													print "
												<option value=\"$template\" > $template </option>
													";
												}
												?>
											</select>
										</div>
									</div>

									<?php
									if(hasSubmitAccess())
									{
									?>
									<div class="column button-box-inline">
										<button type="submit" class="button2-link" disabled="true">Assign</button>&nbsp;
										<button type="reset" class="button3-link"  >Clear</button>

									</div>
									<?php
									}
									?>
								</div><!-- end row-wide-->
							</fieldset>
						</form>
						<div class="spacer">&nbsp;</div>

					<!-- Table of assignments -->
					<legend>Configuration Assignments</legend>
					<table id="template-assignments">
						<thead>
							<tr>
								<th style="width: 25%;">Slave Id</th>
								<th style="width: 60%;">Configuration</th>
								<th style="width: 15%;">Delete</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($slave_array as $slave => $template) {
								print "
							<tr id='slave$slave' >
								<td> $slave </td>
								<td> $template </td>
								<td class=\"delete\">" . (hasSubmitAccess() ? '<a class="deleteAssignment" href="#" title="Delete assignment"><img src="/images/DeleteIcon16.png" alt="Delete" /></a>' : '&nbsp;') . "</td>
							</tr>
							";
							}
							if (empty($slave_array)) {
								print "
							<tr>
								<td colspan=3 > No slaves have been assigned configurations. </td>
							</tr>
								";
							}
							?>
						</tbody>
					</table>
					</div>
				</div>
			</div>
	</div><!-- end of entire div container -->
</body>
</html>

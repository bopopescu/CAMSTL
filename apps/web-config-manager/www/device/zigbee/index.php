<?php require_once $_SERVER['DOCUMENT_ROOT'].'/inc/session_controller.inc'; ?>
<!DOCTYPE HTML>

<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Zigbee - <?php echo DEVICE_NAME; ?></title>

		<?php include $_SERVER['DOCUMENT_ROOT'].'mainscriptsgroup.php'; ?>
		<script type="text/javascript" src="/js/mainFunctions.js"></script>
		<script type="text/javascript" src="/js/zigbee.js"></script>

	</head>

<body>
	<div class="container">
		<?php include $_SERVER['DOCUMENT_ROOT'].'header.php'; ?>

		<div class="clear"></div>
		<div class="clear"></div>

		<?php include $_SERVER['DOCUMENT_ROOT'].'tabs.php'; ?>


		<div class="contentblock">

			<!-- Device  tab -->
			<h2>Device Configuration</h2>

			<?php include '../devicetabs.php'; ?>

				<div class="contentblock2">
				<div class="msgBox"></div>
				<div id="zigbeeSettings">
					<?php require_once $_SERVER['DOCUMENT_ROOT'].'inc/zigbee_view.php'; ?>
						<!-- Zigbee subtab -->
						<h3>
							<div class="inversetab">SafetyLink Pendant (SLP)</div>
							<!--- <a href="/TL3000_HTML5/Default_CSH.htm#SLP" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
							<div class="hr"><hr /></div>
						</h3>
						<form id="zigbeeSettings" method="post" action="/inc/zigbee_processor.php">
							<div id="zcontrol" class="row" >
								<span class="label">SLP Monitoring</span>
								<span class="formw">
									<input type="radio" name="zcontrol" <?php print(($enable) ? "checked=\"checked\"" : ""); ?> value="On" />On&nbsp;
									<input type="radio" name="zcontrol" <?php print((!$enable) ? "checked=\"checked\"" : ""); ?> value="Off" />Off
								</span>
							</div>
							<div class="row">
								<span class="label">Set Link Key (Optional)</span>
								<span class="formw">
								<?php 
									if(isSuperAdmin() || isInstaller())
									{
										for ($i=1; $i <= 8; $i++) 
										{
											echo '
												<input type="text" size="4" maxlength="4" name="zkey'.$i.'" class="autotab" value="'.$link_key[$i-1].
												'" '.(((!$enable) ? 'disabled="true"' : '')).' >';
										}
									}
									else
									{										
										for ($i=1; $i <= 8; $i++) 
										{
											echo '
											<input type="text" size="4" maxlength="4" name="zkey'.$i.'" class="autotab" value="" disabled="true" >';
										}
									}
									?>
								<br />
								<span class="fieldMessage">* The link key is hidden for security. Empty keys will be ignored.</span>
								</span>
							</div>

							<div class="row">
							  <?php $title="Enable SLP to notify user via flashing and vibrating when overdue timers are about to expire"; ?>
								<span class="label2" <?php echo 'title="'.$title.'"';?> >Allow Overdue Notifications</span>
								<span class="formw">
									<input type="radio" name="SLP_AllowOverdue" <?php echo 'title="'.$title.'"';?> value="On" <?php echo isOn($SLP_AllowOverdue) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
								  <?php $title="SLP will NOT notify user via flashing and vibrating when overdue timers are about to expire"; ?>
									<input type="radio" name="SLP_AllowOverdue" <?php echo 'title="'.$title.'"';?> value="Off" <?php echo isOff($SLP_AllowOverdue) ? 'checked="checked"' : '';?> /> Off
								</span>
							</div>
							
							<div class="row" >
							
								<span class="label2" title="Pressing checkin will extend the timer window" >Allow Timer Extensions</span>
								<span class="formw">
									<input type="radio" class="SLP_AllowExtensions" id="SLP_AllowExtensions" name="SLP_AllowExtensions" value="On" <?php echo isOn($SLP_AllowExtensions) ? 'checked="checked"' : '';?> <?php echo isOff($SLP_AllowOverdue) ? '  disabled="disabled"' : '';?>/>On&nbsp;&nbsp;
									<input type="radio" class="SLP_AllowExtensions" id="SLP_AllowExtensions" name="SLP_AllowExtensions" value="Off" <?php echo isOff($SLP_AllowExtensions) ? 'checked="checked"' : '';?> <?php echo isOff($SLP_AllowOverdue) ? '  disabled="disabled"' : '';?>/> Off
								</span>
							</div>
							
							<div class="row" >
								<span class="label2">Notification Time</span>
								<span class="formw reg lzero" >
									<input type="text" size="10" id="NotificationTime" name="NotificationTime" value="<?php echo $notification_time; ?>" <?php echo isOff($SLP_AllowOverdue) ? ' disabled="disabled"' : '';?> title="Warning will be issued this many minutes before timer expires."/>&nbsp;minutes
									<br/>
									<span class="fieldMessage">*0 indicates off</span>
									<span class="errorMsg"></span>
								</span>
							</div>

							<div class="row">
								<span class="label2">Hazard extension</span>
								<span class="formw reg lzero" >
									<input type="text" size="10" id="HazardExtension" name="HazardExtension" value="<?php echo $hazard_time; ?>" <?php echo (isOff($SLP_AllowOverdue) ||isOff($SLP_AllowExtensions)) ? ' disabled="disabled"' : '';?>title="Hazard expiration will be extended by this time" />&nbsp;minutes
									<br/>
									<span class="fieldMessage">*0 indicates off</span>
									<span class="errorMsg"></span>
								</span>
							</div>

							<div class="row">
								<span class="label2">Shift extension</span>
								<span class="formw reg lzero">
									<input type="text" size="10" id="ShiftExtension" name="ShiftExtension" value="<?php echo $shift_extension; ?>" <?php echo (isOff($SLP_AllowOverdue) ||isOff($SLP_AllowExtensions)) ? '  disabled="disabled"' : '';?> title="Shift expiration will be extended by this time"/>&nbsp;minutes
									<br/>
									<span class="fieldMessage">*0 indicates off</span>
									<span class="errorMsg"></span>
								</span>
							</div>

							
							
							
							
							<div class="spacer"></div>
							<fieldset>
								<legend>SLP Message Priorities</legend>
								<div class="row">
									<span class="label">
										<div class="slider-row"></div>
										<div class="slider-row">SOS</div>
									</span>
									<span class="formw">
										<div class="slider-row">
											<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
											<div class="slider-label-center slider-label">Cell</div>
											<div class="slider-label-right slider-label">Iridium*</div>
										</div>
										<div class="slider-row">
											<input class="readonly" type="range" name="SOS_pri" min="1" max="3" step="1"
												value="<?php echo $SOS_pri; ?>" disabled="disabled"/>
										</div>
									</span>
								</div>
								<div class="row">
									<span class="label">
										<div class="slider-row"></div>
										<div class="slider-row">SOS Cancel</div>
									</span>
									<span class="formw">
										<div class="slider-row">
											<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
											<div class="slider-label-center slider-label">Cell</div>
											<div class="slider-label-right slider-label">Iridium*</div>
										</div>
										<div class="slider-row">
											<input class="readonly" type="range" name="SOSCancel_pri" min="1" max="3" step="1"
												value="<?php echo $SOSCancel_pri; ?>" disabled="disabled"/>
										</div>
									</span>
								</div>
								<div class="row">
									<span class="label">
										<div class="slider-row"></div>
										<div class="slider-row">Check In</div>
									</span>
									<span class="formw">
										<div class="slider-row">
											<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
											<div class="slider-label-center slider-label">Cell</div>
											<div class="slider-label-right slider-label">Iridium*</div>
										</div>
										<div class="slider-row">
											<?php
												echo '
											<input type="range" name="ci_pri" min="1" max="3" step="1"
												value="'.$ci_pri.'" '.(((!$enable) ? 'disabled="true"' : '')).' />';
											?>
										</div>
									</span>
								</div>
								<div class="row">
									<span class="label">
										<div class="slider-row"></div>
										<div class="slider-row">Check Out</div>
									</span>
									<span class="formw">
										<div class="slider-row">
											<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
											<div class="slider-label-center slider-label">Cell</div>
											<div class="slider-label-right slider-label">Iridium*</div>
										</div>
										<div class="slider-row">
											<?php
												echo '
											<input type="range" name="co_pri" min="1" max="3" step="1"
												value="'.$co_pri.'" '.(((!$enable) ? 'disabled="true"' : '')).' />';
											?>
										</div>
									</span>
								</div>
								<div class="row">
									<span class="label">
										<div class="slider-row"></div>
										<div class="slider-row">State Request</div>
									</span>
									<span class="formw">
										<div class="slider-row">
											<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
											<div class="slider-label-center slider-label">Cell</div>
											<div class="slider-label-right slider-label">Iridium*</div>
										</div>
										<div class="slider-row">
											<?php
												echo '
											<input type="range" name="statereq_pri" min="1" max="3" step="1"
												value="'.$statereq_pri.'" '.(((!$enable) ? 'disabled="true"' : '')).' />';
											?>
										</div>
									</span>
								</div>
								<div class="row">
									<span class="footNote">
										* Note: Sending messages over Iridium will incur higher costs.
									</span>
								</div>
							</fieldset>
							<?php
								if(hasSubmitAccess())
								{
								?>
								<div class="row">
									<span class="formw">
									<button type="submit" class="button2-link">Save</button>&nbsp;
									<button type="reset" class="button3-link">Cancel</button>&nbsp;
									</span>
								</div>
								<?php
								}
								?>
						</form>
					</div>
				</div>
			</div>
	</div><!-- end of entire div container -->
</body>
</html>

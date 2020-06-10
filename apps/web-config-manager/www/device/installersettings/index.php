<?php require_once $_SERVER['DOCUMENT_ROOT'].'/inc/session_controller.inc'; ?>
<!DOCTYPE HTML>

<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Installer Settings - <?php echo DEVICE_NAME; ?></title>

		<?php include $_SERVER['DOCUMENT_ROOT'].'mainscriptsgroup.php'; ?>
		<script type='text/javascript' src='/js/installer.js'></script>
	</head>

<body>
	<div class="container">
		<?php include $_SERVER['DOCUMENT_ROOT'].'header.php'; ?>

		<div class="clear"></div>
		<div class="clear"></div>

		<?php include $_SERVER['DOCUMENT_ROOT'].'tabs.php'; ?>


		<div class="contentblock">

			<!-- Device Configuration tab -->
			<h2>Device Configuration</h2>
			
			
			<?php include '../devicetabs.php'; ?>

				<div class="contentblock2">
				<div class="msgBox"></div>
				<div id="installerSettings">
					<?php require_once $_SERVER['DOCUMENT_ROOT'].'inc/installer_view.php'; ?>
						<!-- Installer Settings subtab -->
						<h3>Installer Settings</h3>
						<form id="installerSettings" method="post" action="/inc/installer_processor.php">
								<div class="row">
									<span class="label">Owner</span>
									<span class="formw reg">
										<input type="text" size="26" name="RedStoneOwner" value="<?php echo $RedStoneOwner; ?>">&nbsp;&nbsp;
										<span class="errorMsg"></span> 
									</span>
								</div>
							<div class="row">
								<span class="label">Position Reporting Interval</span>
								<span class="formw reg lzero" id="pTime">
									<input type="text" size="10" name="positionUpdateTime" value="<?php echo $posup_time; ?>" />&nbsp;seconds
									<br/>
									<span class="fieldMessage">*0 indicates off</span>
									<span class="errorMsg"></span>
								</span>
							</div>
							<div class="row">
								<span class="label">Heading Change</span>
								<span class="formw reg degreeChange" id="pHeading">
									<input type="text" size="10" name="positionHeading" value="<?php echo $posup_heading; ?>" />&nbsp;degrees
									<br/>
									<span class="fieldMessage">* Entry must be 0 or between 5 and 30 (inclusive). 0 indicates off</span>
									<span class="errorMsg"></span>
								</span>
							</div>						
							<div class="row">
								<span class="label">Notify On Stop/Start</span>
								<span class="formw">
									<input type="radio" name="positionReportStopStart" value="On" <?php echo isOn($posup_report_start_stop) ? 'checked="checked"' : '';?> />Yes&nbsp;&nbsp;
									<input type="radio" name="positionReportStopStart" value="Off" <?php echo isOff($posup_report_start_stop) ? 'checked="checked"' : '';?> /> No
								</span>
							</div>
							<div class="row">
								<span class="label">Report When Stopped</span>
								<span class="formw">
									<input type="radio" name="positionReportWhenStopped" value="On" <?php echo isOn($posup_report_when_stopped) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;&nbsp;
									<input type="radio" name="positionReportWhenStopped" value="Off" <?php echo isOff($posup_report_when_stopped) ? 'checked="checked"' : '';?> /> Off
								</span>
							</div>
							<div class="row">
								<span class="label">Keep Awake</span>
								<span class="formw reg lzero" id="hKeepAwakeID">
									<input type="text" size="10" name="hardwareKeepAwake" value="<?php echo $hw_keep_awake; ?>">&nbsp;minutes 
									<br />
									<span class="fieldMessage">*Entry must be between 0 and 180 (inclusive).</span> 
									<span class="errorMsg"></span> 
								</span>
							</div>
							<div class="row">
								<span class="label">Cellular APN</span>
								<span class="formw reg">
									<input type="text" size="25" name="apn" value="<?php echo $cell_apn; ?>" /><br/>
									<span class="errorMsg"></span>
								</span>
							</div>
							<br/>
							<br/>
							<div class="inversetab">CAMS</div>
							<div class="hr">
								<hr />
							</div>
							<div id="CAMSSection">
								<div class="row">
									<span class="label">CAMS</span> 
									<span class="formw"> 
										<input	type="radio" name="cams" value="1" <?php echo isOn($cams_status) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
										<input type="radio" name="cams" value="0" <?php echo isOff($cams_status) ? 'checked="checked"' : '';?> /> Off
									</span>
								</div>
								<div class="row">
									<span class="label">Host</span>
									<span class="formw reg">
										<input type="text" size="26" name="camsHost" value="<?php echo $cams_host; ?>">&nbsp;&nbsp;
										<span class="errorMsg"></span> 
									</span>
								</div>
								<div class="row">
									<span class="label">Port</span>
									<span class="formw reg lzero">
										<input type="text" size="10" name="camsPort" value="<?php echo $cams_port; ?>">&nbsp;&nbsp;
										<span class="errorMsg"></span> 
									</span>
								</div>
							</div>
						<div class="spacer">&nbsp;</div>

						<div class="inversetab">Current Status</div>
							<div class="hr">
								<hr />
								<div class="row">
									<span class="label">Cellular IP</span>
									<span class="formw">
									  <label><?php echo $cellIP; ?> </label>
									</span>
								</div>
								<div class="row">
									<span class="label">Cellular RSSI</span>
									<span class="formw">
										<input type="text" size="30" readonly="readonly" id="XcellRSSI" value="<?php echo $cellRSSI; ?>"/>
									</span>
								</div>
								<div class="row">
									<span class="label">GPS Satellites</span>
									<span class="formw">
									  <label><?php echo $gpsSats; ?> </label>
									</span>
								</div>
							</div>
						<div class="spacer">&nbsp;</div>

						<?php
						//if($_SESSION['M2M_SESH_USERAL'] < 300)
						if(hasSubmitAccess())
						{
						?>
						<div class="row">
							<span class="formw">
								<button type="submit" class="button2-link">Save</button>&nbsp;
								<button type="reset" class="button3-link">Cancel</button>&nbsp;
								<!-- <button class="button4-link">Sync</button> -->
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

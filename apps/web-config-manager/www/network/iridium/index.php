<?php require_once $_SERVER['DOCUMENT_ROOT'].'/inc/session_controller.inc'; ?>
<!DOCTYPE HTML>
<?php
function isEditable()
{
	if(isInstaller() || isSuperAdmin())
	{
		return true;
	}
	return false;
}
$radio_disable= "";
$field_disable="";

if(!isSuperAdmin() && !isInstaller())
{
	$radio_disable = " disabled=\"disabled\"";
	$field_disable = " disabled=\"disabled\"";
}
?>

<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<!-- Header -->
		<title>Iridium - <?php echo DEVICE_NAME; ?></title>

		<?php include $_SERVER['DOCUMENT_ROOT'].'mainscriptsgroup.php';
		if(isEditable())
		{?>
		<script type='text/javascript' src='/js/satellite.js'></script>
		<?php
		}?>
	</head>

	<body>
	<div class="container">
		<?php include $_SERVER['DOCUMENT_ROOT'].'header.php'; ?>

		<div class="clear"></div>
		<div class="clear"></div>

		<?php include $_SERVER['DOCUMENT_ROOT'].'tabs.php'; ?>


		<div class="contentblock">

			<!-- Network Config tab -->
			<h2>Network Configuration</h2>
			<?php include '../networktabs.php'; ?>

			<div class="contentblock2">
				<div class="msgBox"></div>
				<?php require_once $_SERVER['DOCUMENT_ROOT'].'/inc/satellite_view.inc'; ?>
				<!-- Satellite subtab -->
				<div class="inversetab">Iridium</div>
				<!--- <a href="/TL3000_HTML5/Default_CSH.htm#SATELLITE" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
				<div class="hr"><hr /></div>
				
				<form  id="satelliteSettings" method="post" action="/inc/satellite_processor.php">
					<div id="satellitediv">
						<fieldset>
							<legend>Iridium Settings</legend>
							<div class="row">
								<span class="label2">Iridium Enable</span>
								<span class="formw2">
									<input type="radio" name="IridiumEnableCtl" value="1" <?php echo !isOff($IridiumEnable) ? 'checked="checked"' : '';echo $radio_disable;?> />On&nbsp;&nbsp;
									<input type="radio" name="IridiumEnableCtl" value="0" <?php echo isOff($IridiumEnable) ? 'checked="checked"' : '';echo $radio_disable;?> /> Off
								</span>
							</div>
							<div class="row">
								<span class="label2">Iridium data limit per transmission period</span>
								<span class="formw2">
									<input  type="text" name="IridiumDataLimit" size="5" value="<?php echo $iridiumDataLimit; ?>" <?php echo $field_disable; ?> /> bytes
									<br />
									<span class="fieldMessage">* Entry must be between 10240 - 40960 (10K to 40K)</span>
									<span class="errorMsg"></span>
								</span>
							</div>
							<div class="row">
								<span class="label2">Iridium data transmission period</span>
								<span class="formw2">
									<input  type="text" name="IridiumDataLimitTimeout" size="5" value="<?php echo $iridiumDataLimitTimeout;?>" <?php echo $field_disable; ?> /> seconds
									<span class="fieldMessage">* Entry range 14400 - 345600 (4 hours to 4 days)</span>
									<br />
									<span class="errorMsg"></span>
								</span>
							</div>
							<div class="spacer">&nbsp;</div>
						</fieldset>
						<div class="spacer">&nbsp;</div>
						<fieldset>
							<legend>Position Update Settings</legend>
						<div class="row">
							<span class="label">Reporting Interval</span>
							<span class="formw2 reg lzero" id="pDistance">
								<input type="text" size="10" name="IridiumUpdateIntervalCtl" value=<?php echo '"'.$IridiumUpdateInterval.'"'. $field_disable; ?>" />&nbsp;minutes
								<br/>
								<span class="fieldMessage">*0 indicates off, max of 1440 (1 day)</span>
								<span class="errorMsg"></span>
							</span>
						</div>
						<div class="spacer">&nbsp;</div>
						</fieldset>
						<div class="spacer">&nbsp;</div>
						<fieldset>
							<legend>Modbus Settings</legend>
						<div class="row">
							<span class="label">Modbus Engine Data Reporting Interval</span>
							<span class="formw2 reg lzero" id="pDistance">
								<input type="text" size="10" name="ModbusReportingIntervalCtl" value=<?php echo '"'.$ModbusReportingInterval.'"'. $field_disable; ?>" />&nbsp;minutes
								<br/>
								<span class="fieldMessage">*0 indicates off, max of 1440 (1 day)</span>
								<span class="errorMsg"></span>
							</span>
						</div>
						<div class="spacer">&nbsp;</div>
						</fieldset>
						<div class="spacer">&nbsp;</div>
						<fieldset>
							<legend>CAMS Settings</legend>
							<div id="CAMSIridiumSection" >
								<div class="row">
									<span class="label2">Priority Level for Iridium messages</span> <span class="formw2">
									<select name="IridiumPri"  <?php echo $radio_disable;?> >
										<?php
										for($i=1;$i<256;$i++)
										{
											print "
										<option value=\"$i\"". (($i == $cams_IridiumPri)? 'selected="selected"': '') . " > $i </option>";
										}
										?>
									</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">Maximum retries (before switching to Iridium)</span>
									<span class="formw2">
										<input  type="text" name="camsRetries" size="5" value="<?php echo $camsRetryLimit;?>" <?php 	echo $radio_disable;?>" />
										<br />
										<span class="errorMsg"></span>
									</span>
								</div>
								<div class="row">
									<span class="label2">Optimized Iridium Reporting</span>
									<span class="formw2">
										<input  type="radio" name="CellFailMode" value="1" <?php echo isOn($cellFailMode) ? 'checked="checked"' : '';echo $radio_disable;?> />On&nbsp;&nbsp;
										<input type="radio" name="CellFailMode" value="0" <?php echo isOff($cellFailMode) ? 'checked="checked"' : '';echo $radio_disable;?> /> Off
									</span>
								</div>
								<div class="row">
									<span class="label2">Iridium timeout</span>
									<span class="formw2">
										<input  type="text" name="camsIridiumTimeout" size="5" value="<?php echo $camsIridiumTimeout;?>" <?php echo $radio_disable;?>" />&nbsp;seconds
										<br />
										<span class="errorMsg"></span>
									</span>
								</div>
								<div class="row">
									<span class="label2">Message priorities excluded from data limit:</span> <span class="formw2">
									1 -
									<select name="camsIridiumDataLimitPriority"  <?php echo $radio_disable;?>>
										<?php
										for($i=1; $i < $cams_IridiumPri; $i++)
										{
											print "
											<option value=\"$i\" ". (($i == $camsIridiumDataLimitPriority)? 'selected="selected"': '') . " >$i </option>";
										}
										?>
									</select>
									</span>
								</div>
							<div class="spacer">&nbsp;</div>
						</fieldset>

						<div class="spacer">&nbsp;</div>
						<?php
						if(!isUser())
						{
						?>
						<div class="row">
							<span class="formw">
								<button type="submit" class="button2-link"<?php if(!isEditable()) { print " disabled=\"1\"";}?>>Save</button>&nbsp;
								<button type="reset" class="button3-link"<?php if(!isEditable()) { print " disabled=\"1\"";}?>>Cancel</button>&nbsp;
								<!--<button class="button4-link">Sync</button>-->
							</span>
						</div>
						<?php
						}
						?>
					</div>
				</form>
			</div>
		</div> <!-- end of content block (main tab container) -->
	</div><!-- end of entire div container -->
</body>
</html>

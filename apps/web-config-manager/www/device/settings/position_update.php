<?php

  //require_once $_SERVER['DOCUMENT_ROOT'].'inc/modbus-settings.inc';
  //$pList = getParameterList();

?>

<!-- Position Update subsubtab -->

<form id="positionCheck" method="post" action="/inc/position_update_processor.php">
	<div class="inversetab">PositionUpdate</div>
	<!--- <a href="/TL3000_HTML5/Default_CSH.htm#POSITIONUPDATE" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
	<div class="hr"><hr /></div>

	<br/>
	<div class="row">
		<span class="label2">Reporting Interval</span>
		<span class="formw2 " id="pTime">
			<input type="text" size="10" name="positionUpdateTime" value="<?php echo $posup_time; ?>" />&nbsp;seconds
			<br/>
			<span class="fieldMessage">*0 indicates off</span>
			<span class="errorMsg"></span>
		</span>
	</div>
	<div class="row">
		<span class="label2">Reporting Distance</span>
		<span class="formw2 " id="pDistance">
			<input type="text" size="10" name="positionUpdateDistance" value="<?php echo $posup_distance; ?>" />&nbsp;metres
			<br/>
			<span class="fieldMessage">*0 indicates off</span>
			<span class="errorMsg"></span>
		</span>
	</div>
	<div class="row">
		<span class="label2">Heading Change</span>
		<span class="formw2" id="pHeading">
			<input type="text" size="10" name="positionHeading" value="<?php echo $posup_heading; ?>" />&nbsp;degrees
			<br/>
			<span class="fieldMessage">* Entry must be 0 or between 5 and 30 (inclusive). 0 indicates off</span>
			<span class="errorMsg"></span>
		</span>
	</div>
	<div class="row">
		<span class="label2">Pinning</span>
		<span class="formw2">
			<input type="radio" name="positionPinning" value="On" <?php echo isOn($posup_pinning) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
			<input type="radio" name="positionPinning" value="Off" <?php echo isOff($posup_pinning) ? 'checked="checked"' : '';?> /> Off
		</span>
	</div>
	<div class="row">
		<span class="label2">Stop Velocity</span>
		<span class="formw2" id="pSVelocity">
			<input type="text" size="10" name="positionStopVelocity" value="<?php echo $posup_stop_velocity; ?>" />&nbsp;kph
			<br />
			<span class="fieldMessage">* Entry must be between 0 and 6 (inclusive). 0 indicates off</span>
			<span class="errorMsg"></span>
		</span>
	</div>
	<div class="row">
		<span class="label2">Stopped Time</span>
		<span class="formw2" id ="pStopTime">
			<input type="text" size="10" name="positionStopTime" value="<?php echo $posup_stop_time; ?>" />&nbsp;seconds
			<span class="fieldMessage">* 0 indicates off</span>
			<span class="errorMsg"></span>
		</span>
	</div>
	<div class="row">
		<span class="label2">Notify On Stop/Start</span>
		<span class="formw2">
			<input type="radio" name="positionReportStopStart" value="On" <?php echo isOn($posup_report_start_stop) ? 'checked="checked"' : '';?> />Yes&nbsp;&nbsp;
			<input type="radio" name="positionReportStopStart" value="Off" <?php echo isOff($posup_report_start_stop) ? 'checked="checked"' : '';?> /> No
		</span>
	</div>
	<div class="row">
		<span class="label2">Report When Stopped</span>
		<span class="formw2">
			<input type="radio" name="positionReportWhenStopped" value="On" <?php echo isOn($posup_report_when_stopped) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
			<input type="radio" name="positionReportWhenStopped" value="Off" <?php echo isOff($posup_report_when_stopped) ? 'checked="checked"' : '';?> /> Off
		</span>
	</div>
	<div class="row">
		<span class="label2">Reporting Interval (Iridium)</span>
		<span class="formw2">
			<input type="text" size="8" name="IridiumUpdateIntervalCtl" value="<?php echo $IridiumUpdateInterval; ?>" />&nbsp;minutes
			<span class="fieldMessage">* 0 indicates off</span>
			<br/>
			<span class="errorMsg"></span>
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

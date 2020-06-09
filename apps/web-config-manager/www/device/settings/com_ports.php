<!-- Com Ports Settings -->
<form id="comportsCheck" method="post" action="/inc/comports_processor.php">
<div id="comportsSection">
		<!-- Header -->
		<div class="inversetab">COM Ports</div>
		<!--- <a href="/TL3000_HTML5/Default_CSH.htm#COMPORTS" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
		<div class="hr"><hr /></div>
		<br/><br/>

	<div class="inversetab">COM1</div>

	<div class="hr">
		<hr />
	</div>
	<div id="com1Section">
		<div class="row">
			<span class="label2">Serial over TCP</span>
			<span class="formw2">
				<input type="radio" name="CPCom1Enable" value="On" <?php echo isOn($cp_Com1Enable) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
				<input type="radio" name="CPCom1Enable" value="Off" <?php echo isOff($cp_Com1Enable) ? 'checked="checked"' : '';?> /> Off
			</span>
		</div>

		<div class="row">
			<span class="label2">Baud Rate:</span>
			<span class="formw2" id="ptext">
				<input type="text" size="5" name="CPCom1Baud" value="<?php echo $cp_Com1Baud; ?>" />&nbsp;
				<br />
				<span class="fieldMessage">* Entry must be between 1200 - 115200 (inclusive)</span>
				<span class="errorMsg"></span>
			</span>
		</div>

		<div class="row">
			<span class="label2">Destination</span>
			<span class="formw2">
				<input type="radio" name="CPCom1Dest" value="0" <?php echo ($cp_Com1Dest == 0) ? 'checked="checked"' : '';?> />WiFi&nbsp;&nbsp;
				<input type="radio" name="CPCom1Dest" value="1" <?php echo ($cp_Com1Dest == 1) ? 'checked="checked"' : '';?> /> Ethernet&nbsp;&nbsp;
				<input type="radio" name="CPCom1Dest" value="2" <?php echo ($cp_Com1Dest == 2) ? 'checked="checked"' : '';?> /> Cell
			</span>
		</div>

		<div class="row">
			<span class="label2">Port</span>
			<span class="formw2">
				<input type="text" size="10" name="CPCom1Port" value="<?php echo $cp_Com1Port; ?>" />&nbsp;&nbsp;
				<br />
				<span class="fieldMessage">* Entry must be between 1 - 65000 (inclusive)</span>
				<span class="errorMsg"></span>
			</span>
		</div>
	</div>

	<br/>
	<br/>
	<br/>
	<br/>

	<div class="inversetab">COM2</div>
	<div class="hr">
		<hr />
	</div>
	<div id="com2Section">
		<div class="row">
			<span class="label2">Serial over TCP</span>
			<span class="formw2">
				<input type="radio" name="CPCom2Enable" value="On" <?php echo isOn($cp_Com2Enable) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
				<input type="radio" name="CPCom2Enable" value="Off" <?php echo isOff($cp_Com2Enable) ? 'checked="checked"' : '';?> /> Off
			</span>
		</div>

		<div class="row">
			<span class="label2">Baud Rate:</span>
			<span class="formw2" id="ptext">
				<input type="text" size="5" name="CPCom2Baud" value="<?php echo $cp_Com2Baud; ?>" />&nbsp;
				<br />
				<span class="fieldMessage">* Entry must be between 1200 - 115200 (inclusive)</span>
				<span class="errorMsg"></span>
			</span>
		</div>

		<div class="row">
			<span class="label2">Destination</span>
			<span class="formw2">
				<input type="radio" name="CPCom2Dest" value="0" <?php echo ($cp_Com2Dest == 0) ? 'checked="checked"' : '';?> />WiFi&nbsp;&nbsp;
				<input type="radio" name="CPCom2Dest" value="1" <?php echo ($cp_Com2Dest == 1) ? 'checked="checked"' : '';?> /> Ethernet&nbsp;&nbsp;
				<input type="radio" name="CPCom2Dest" value="2" <?php echo ($cp_Com2Dest == 2) ? 'checked="checked"' : '';?> /> Cell
			</span>
		</div>

		<div class="row">
			<span class="label2">Port</span>
			<span class="formw2">
				<input type="text" size="10" name="CPCom2Port" value="<?php echo $cp_Com2Port; ?>">&nbsp;&nbsp;
				<br />
				<span class="fieldMessage">* Entry must be between 1 - 65000 (inclusive)</span>
				<span class="errorMsg"></span>
			</span>
		</div>
	</div>

</div> <!-- <div id="comportsSection"> -->
<!-- Save/Cancel buttons -->
<div class="spacer">&nbsp;</div>
<?php
	if(hasSubmitAccess())
	{
		?>
		<div class="row">
			<span class="formw2">
				<button type="submit" class="button2-link">Save</button>&nbsp;
				<button type="reset" class="button3-link">Cancel</button>&nbsp;
				<!--button class="button4-link">Sync</button>-->
				<br/>
			</span>
		</div>
		<?php
	}
?>
</form>

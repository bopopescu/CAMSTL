	<!-- Protocols (formerly Output) subsubtab -->
	<form id="outputCheck" method="post" action="/inc/output_processor.php">
	
		<!-- Header -->
		<div class="inversetab">CAMS</div>
		<!--- <a href="/TL3000_HTML5/Default_CSH.htm#PROTOCOLS" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
		<div class="hr"><hr /></div>
		
		<div id="CAMSSection">
			<div class="row">
				<span class="label2">CAMS</span> <span class="formw2">
					<input	type="radio" name="cams" value="1" <?php echo isOn($cams_status) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
					<input type="radio" name="cams" value="0" <?php echo isOff($cams_status) ? 'checked="checked"' : '';?> /> Off
				</span>
			</div>
			<div class="row">
				<span class="label2">Host</span>
				<span class="formw2 reg">
					<input type="text" size="26" name="camsHost" value="<?php echo $cams_host; ?>">&nbsp;&nbsp;
					<span class="errorMsg"></span>
				</span>
			</div>
			<div class="row">
				<span class="label2">Port</span>
				<span class="formw2 reg lzero">
					<input type="text" size="10" name="camsPort" value="<?php echo $cams_port; ?>">&nbsp;&nbsp;
					<br />
					<span class="fieldMessage">* Entry must be between 1 - 65000 (inclusive)</span>
					<span class="errorMsg"></span>
				</span>
			</div>
			<div class="row">
				<span class="label2">Compression</span> <span class="formw2">
					<input	type="radio" name="camsCompress" value="1" <?php echo isOn($cams_compression) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
					<input type="radio" name="camsCompress" value="0" <?php echo isOff($cams_compression) ? 'checked="checked"' : '';?> /> Off
				</span>
			</div>
			
			<!-- Iridium section  -  disabled if the Iridum-monitor is off --> 
			<?php
				$IridiumEnableRaw = $dbconfig->getDbconfigData('feature', 'iridium-monitor');
				$IridiumEnable = (isValidOnOff($IridiumEnableRaw) ? $IridiumEnableRaw : '0');
			?>			<div class="row">
				<span class="label2">Enable reporting over Iridium</span> <span class="formw2">
				<input type="hidden" name="IridiumStatus" value="<?php print (isOn($iridium_status))? '1': '0'; ?>" />
				<input type="radio" name="IridiumEnable" value="1" <?php echo isOn($iridiumEnable) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
				<input type="radio" name="IridiumEnable" value="0" <?php echo isOff($iridiumEnable) ? 'checked="checked"' : '';?> /> Off
				</span>
			</div>
			
			<div id="CAMSIridiumSection" >
				<div class="row">
					<span class="label2">Priority Level for Iridium messages</span> <span class="formw2">
					<select name="IridiumPri" >
						<?php
							for($i=1;$i<256;$i++)
							{
								print "	<option value=\"$i\"". (($i == $cams_IridiumPri)? 'selected="selected"': '') . " > $i </option>";
							}
						?>
					</select>
					</span>
				</div>
				<div class="row">
					<span class="label2">Maximum retries (before switching to Iridium)</span> <span class="formw2">
					<input  type="text" name="camsRetries" size="5" value="<?php echo $camsRetryLimit;?>" />
					<br />
					<span class="errorMsg"></span>
					</span>
				</div>
				<div class="row">
					<span class="label2">Optimized Iridium Reporting</span> <span class="formw2">
					<input  type="radio" name="CellFailMode" value="1" <?php echo isOn($cellFailMode) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
					<input type="radio" name="CellFailMode" value="0" <?php echo isOff($cellFailMode) ? 'checked="checked"' : '';?> /> Off
					</span>
				</div>
				<div class="row">
					<span class="label2">Iridium timeout</span> <span class="formw2">
					<input  type="text" name="camsIridiumTimeout" size="5" value="<?php echo $camsIridiumTimeout;?>" />&nbsp;seconds
					<br />
					<span class="errorMsg"></span>
					</span>
				</div>
				<div class="row">
					<span class="label2">Message priorities excluded from data limit:</span> <span class="formw2">
					1 -
					<select name="camsIridiumDataLimitPriority">
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
			</div>
			<?php
//			}
			?>

			<?php if($SuperAdminStatus){ ?>
			<div class="row">
				<span class="label2">Session keep alive interval</span> <span class="formw2">
				<input  type="text" name="camsKeepAlive" size="5" value="<?php echo $camsKeepAlive;?>" />&nbsp;seconds
				<br />
				<span class="errorMsg"></span>
				</span>
			</div>
			<div class="row">
				<span class="label2">Ack Timeout</span> <span class="formw2">
				<input  type="text" name="camsTimeout" size="5" value="<?php echo $camsTimeout;?>" />&nbsp;seconds
				<br />
				<span class="errorMsg"></span>
				</span>
			</div>
		<?php } ?>
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

<?php require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_controller.inc'; ?>
<!DOCTYPE HTML>

<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>IP Reservation - <?php echo DEVICE_NAME; ?></title>

		<?php include $_SERVER['DOCUMENT_ROOT'].'mainscriptsgroup.php'; ?>
		<script type="text/javascript" src="/js/jquery.blockUI.js"></script>
		<script type="text/javascript" src="/js/ipreservation.js"></script>
		
	
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
					<!-- Ethernet subtab -->
					<div class="msgBox"></div>
					<?php include_once $_SERVER['DOCUMENT_ROOT'].'inc/ipreservation_view.inc'; ?>
					
					<!-- Header -->
					<div class="inversetab">IP Reservation</div>
					<!--- <a href="/TL3000_HTML5/Default_CSH.htm#IP_RESERVATIONS" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
					<div class="hr"><hr /></div>

					<?php
						if(hasSubmitAccess())
						{
					?>
						<form id="ipReservationForm" method="post" action="/inc/ipreservation_processor.php">
							<fieldset>
								<legend><span><?php echo ucfirst($operation_type); ?></span> IP Reservation Rule</legend>
								<div class="row-wide">
									<div class="column">
										<input type="hidden" name="newRuleIndex" value="<?php echo $new_rule_index; ?>" />
										<input type="hidden" name="op" value="<?php echo $operation_type; ?>" />
									
										<div class="column-label"><label for="ruleName">Rule Name</label></div>
										<div class="column-field"><input type="text" size="27" maxlength="25" name="ruleName" value="<?php echo $rule_name;  ?>" /></div>
										<div><span class="fieldMessage">* Only a-z, 0-9, -, _ are allowed</span></div>
									</div>
									<div class="column">
										<div class="column-label"><label for="mac1">MAC Address</label></div>
										<div class="column-field">
											<input type="text" size="1" maxlength="2" name="mac1" class="autotab" value="<?php echo $mac[0]; ?>" />:<input type="text" size="1" maxlength="2" name="mac2" class="autotab" value="<?php echo $mac[1]; ?>" />:<input type="text" size="1" maxlength="2" name="mac3" class="autotab" value="<?php echo $mac[2]; ?>" />:<input type="text" size="1" maxlength="2" name="mac4" class="autotab" value="<?php echo $mac[3]; ?>" />:<input type="text" size="1" maxlength="2" name="mac5" class="autotab" value="<?php echo $mac[4]; ?>" />:<input type="text" size="1" maxlength="2" name="mac6" class="autotab" value="<?php echo $mac[5]; ?>" />
										</div>
										<div>&nbsp;</div>
									</div>
									<div class="column" style="margin-right:0;">
										<div class="column-label"><label for="ip4">Reserved IP</label></div>
										<div class="column-field">
											<select name="interface">
												<option value="all">All interfaces</option>
												<option value="eth0" <?php echo ((strcasecmp($interface,'eth0') == 0) ? 'selected="selected"':'');?>>Ethernet - <?php echo $eip_subnet.".x"; ?></option>
												<option value="ra0" <?php echo ((strcasecmp($interface,'ra0') == 0) ? 'selected="selected"':'');?>>Wireless - <?php echo $wip_subnet.".x"; ?></option>
											</select>
											.
											<input type="text" size="1" maxlength="3" name="ip4" value="<?php echo $ip4; ?>" />
										</div>
										<div>&nbsp;</div>
									</div>
									<?php
									if(hasSubmitAccess())
									{
									?>
										<div class="column button-box-inline">
											<button type="submit" class="button2-link">Save</button>&nbsp;
											<button type="reset" class="button3-link" <?php echo ($operation_type != "add" ? 'style="display:none;"' : ''); ?>>Clear</button>&nbsp;
											<button type="button" class="cancelEdit button3-link" <?php echo ($operation_type != "edit" ? 'style="display:none;"' : ''); ?>>Cancel Edit</button>&nbsp;
											<div>&nbsp;</div>
										</div>
									<?php 
									}		

									
									?>									
								</div><!-- end row-wide-->
						</fieldset>						
					</form>
					
					<div class="spacer">&nbsp;</div>
					<?php
						}
					?>
					<!-- Table of rules -->	
					<table id="ip-reservation-listings">
						<thead>
							<tr>
								<th style="width: 30%;">Rule Name</th>
								<th style="width: 30%;">MAC Address</th>
								<th style="width: 30%;">IP Reservation</th>
								<th style="width: 5%;">Edit</th>
								<th style="width: 5%;">Delete</th>
							</tr>
						</thead>
						<tbody>
						<?php echo $table_rows; ?>
						</tbody>
					</table>		
					<div class="spacer">&nbsp;</div>
					<fieldset>
						<legend><span>DHCP Leases</span></legend>
						<div id="leasestable" class="row-wide">
							<div class="spacer">&nbsp;</div>
							<span>
								<div style="width: 100%; overflow-x: auto;">
										<?php echo (!empty($iprdhcp_leases) ? $iprdhcp_leases : ''); ?>
								</div>
							</span>
						</div>
					</fieldset>

					<script type="text/javascript">var newRuleIndex = $("input[name = newRuleIndex]").val();</script>
						
			</div>
			

		</div> <!-- end of content block (main tab container) -->
	</div><!-- end of entire div container -->
</body>
</html>

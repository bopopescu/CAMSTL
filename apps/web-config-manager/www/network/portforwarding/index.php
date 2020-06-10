<?php require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_controller.inc'; ?>
<!DOCTYPE HTML>

<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Port Forwarding - <?php echo DEVICE_NAME; ?></title>

		<?php include $_SERVER['DOCUMENT_ROOT'].'mainscriptsgroup.php'; ?>		
		<script type="text/javascript" src="/js/portforwarding.js"></script>
		
	
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
					<?php include_once $_SERVER['DOCUMENT_ROOT'].'inc/portforwarding_view.inc'; ?>
					
					<!-- Header -->
					<div class="inversetab">Port Forwarding</div>
					<!--- <a href="/TL3000_HTML5/Default_CSH.htm#PORT_FORWARDING" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
					<div class="hr"><hr /></div>

					<?php
						if(hasSubmitAccess())
						{
					?>
						<form id="portForwardingForm" method="post" action="/inc/portforwarding_processor.php">
							<fieldset>
								<legend><span><?php echo ucfirst($operation_type); ?></span> Forwarding Rule</legend>
								<div class="row-wide">
									<div class="column">
									
										<input type="hidden" name="newRuleIndex" value="<?php echo $new_rule_index; ?>" />
										<input type="hidden" name="op" value="<?php echo $operation_type; ?>" />
									
										<div class="column-label"><label for="ruleName">Rule Name</label></div>
										<div class="column-field"><input type="text" size="30" maxlength="25" name="ruleName" value="<?php echo $rule_name;  ?>" /></div>
										<div><span class="fieldMessage">* Only a-z, 0-9, -, _ are allowed</span></div>
									</div>
								</div>
								<div class="row-wide">
									<div class="column">
										<div class="column-label"><label for="sPortStart">Source Port Range</label></div>
										<div class="column-field">
											<input type="text" size="4" name="sPortStart" value="<?php echo $sPort_range_start; ?>" /> - <input type="text" size="4" name="sPortEnd" value="<?php echo $sPort_range_end; ?>" />
										</div>
										<div>&nbsp;</div>
									</div>									
									<div class="column">
										<div class="column-label"><label for="ip4">Reserved Destination IP</label></div>
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
									
									<div class="column">
										<div class="column-label"><label for="portStart">Port Range</label></div>
										<div class="column-field">
											<input type="text" size="4" name="portStart" value="<?php echo $port_range_start; ?>" /> - <input type="text" size="4" name="portEnd" value="<?php echo $port_range_end; ?>" />
										</div>
										<div>&nbsp;</div>
									</div>									
									
									<!-- <div class="spacer">&nbsp;</div> -->
									<div class="column">
										<div class="column-label"><label for="protocol">Protocol</label></div>
										<div class="column-field">
											<select name="protocol">
												<option value="All">All protocols</option>
												<?php echo $protocol_list;?>
											</select>
										</div>
										<div>&nbsp;</div>
									</div>
								</div><!-- end row-wide-->
								
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
								<div class="spacer">&nbsp;</div>
						</fieldset>						
					</form>
					
					<div class="spacer">&nbsp;</div>
					
					<?php
						}
					?>
					
					<!-- Table of rules -->	
					<table id="port-forwarding-listings">
						<thead>
							<tr>
								<th style="width: 25%;">Rule Name</th>
								<th style="width: 20%;">IP Reservation</th>
								<th style="width: 15%;">Port(s)</th>
								<th style="width: 10%;">Protocol</th>
								<th style="width: 5%;">Edit</th>
								<th style="width: 5%;">Delete</th>
							</tr>
						</thead>
						<tbody>
						<?php echo $table_rows; ?>
						</tbody>
					</table>			
			</div>
			

		</div> <!-- end of content block (main tab container) -->
	</div><!-- end of entire div container -->
</body>
</html>

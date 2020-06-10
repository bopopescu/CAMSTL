<?php require_once $_SERVER['DOCUMENT_ROOT'].'/inc/session_controller.inc'; ?>
<!DOCTYPE HTML>

<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Network Diagnostics - Redstone M2M Local Config Manager</title>

<?php include $_SERVER['DOCUMENT_ROOT'].'mainscriptsgroup.php'; ?>

</head>

<body>
	<div class="container">
	
	<?php include $_SERVER['DOCUMENT_ROOT'].'header.php'; ?>
	
		
		<div class="clear"></div>
		<div class="clear"></div>
		
		<?php include $_SERVER['DOCUMENT_ROOT'].'tabs.php'; ?>		


			<div class="contentblock" id="networkd">
			
				<!-- Network Diagnostics tab -->
<h2>Network Diagnostics</h2>
				<div style="padding-left:25px;padding-right:25px;padding-bottom:25px;">
				<form id="networkdf">
				<div class="row">
										<span class="label">Network Ping: Enter IP Address</span><span class="formw"><input
											type="text" size="26" name="traceRouteIP">&nbsp;
												<button class="button-link">
													Go
												</button>
										
										</span>
									</div>
										<div class="row">
										<span class="label">Iridium</span><span class="formw">
												<button class="button-link">
													Send Message
												</button>
												<button class="button-link">
													Read Last Message
												</button>
										
										</span>
									</div>
									
				<div class="row">
										<span class="label">Traceroute: Enter IP Address</span><span class="formw"><input
											type="text" size="26" name="traceRouteIP">&nbsp;
												<button class="button-link">
													Go
												</button>
										
										</span>
									</div>
									<div class="row">
										<span class="label">NetStat: Enter Command</span><span class="formw"><input
											type="text" size="26" name="netstat">&nbsp;
												<button class="button-link">
													Go
												</button>
										
										</span>
									</div>
									<div class="row">
										<span class="label">Database Viewer: Enter Command</span><span class="formw"><input
											type="text" size="26" name="dbviewer">&nbsp;
												<button class="button-link">
													Go
												</button>
										
										</span>
									</div>
									<div class="row">
										<span class="label">System Log: Type in Date</span><span class="formw"><input
											type="text" size="26" name="syslog">&nbsp;
												<button class="button-link">
													View
												</button>
										
										</span>
									</div>
									
									<div class="row">
										<span class="label"><textarea name="commandWindow" cols="83" rows="20"></textarea></span>
									</div>
									
				
				</form>
			</div>
			</div>
</div>
</body>
</html>

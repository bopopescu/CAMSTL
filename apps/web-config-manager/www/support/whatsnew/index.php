<?php require_once $_SERVER['DOCUMENT_ROOT'].'/inc/session_controller.inc'; ?>
<!DOCTYPE HTML>

<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>What's New - <?php echo DEVICE_NAME; ?></title>

<?php include $_SERVER['DOCUMENT_ROOT'].'mainscriptsgroup.php'; ?>

</head>

<body>
	<div class="container">

		<?php include $_SERVER['DOCUMENT_ROOT'].'header.php'; ?>


		<div class="clear"></div>
		<div class="clear"></div>

		<?php include $_SERVER['DOCUMENT_ROOT'].'tabs.php'; ?>


		<div class="contentblock">
			<!-- Support tab -->
			<h2>Support</h2>

			<div>
				<div class="hr" style="padding-left:25px;padding-right:25px;">
					<hr />
				</div>
				<p style="font-family: Arial; font-size: 14px; color: #555555;padding-left:25px;">
					<scan style="color:#143d8d;font-weight:bold;">Toll Free:</scan>
						1-877-352-8522&nbsp;&nbsp;
					<scan style="color:#143d8d;">|</scan>
					&nbsp;&nbsp;
					<scan style="color:#143d8d;font-weight:bold;"> In Calgary:</scan>
						403-252-5007&nbsp;&nbsp;
					<scan style="color:#143d8d;">|</scan>
					&nbsp;&nbsp;www.<span style="color: #143d8d; font-weight: bold;">aware360</span>.com
				</p>
				<div class="msgBox"></div>
				<?php include '../supporttabs.php'; ?>


				<div class="contentblock2">
				<div id="whatsNew">

				<h3>What's New</h3>
				<div class="expand">
					<h4>
						<div class="expandIcon"></div>
						<div class="collapseIcon"></div>
						Configuration Manager
					</h4>
					<div class="expblock">
						<p>The Configuration Manager offers a web interface for the configuration of device settings. The following features have been added in release 1.1 (Firmware version 1.4):</p>
						<ul class="lineheight">
							<li><strong>New feature:</strong> Port Forwarding<p>The Configuration Manager now has the ability to setup port forwarding rules.</p></li>
						</ul>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>
</body>
</html>

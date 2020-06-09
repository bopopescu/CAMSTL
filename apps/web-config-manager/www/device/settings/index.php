<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_controller.inc';
$SuperAdminStatus = isSuperAdmin();
?>
<!DOCTYPE HTML>

<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Settings - <?php echo DEVICE_NAME; ?></title>

<?php include $_SERVER['DOCUMENT_ROOT'].'mainscriptsgroup.php'; ?>
<script type='text/javascript' src='/js/settings.js'></script>

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

				<?php require_once $_SERVER['DOCUMENT_ROOT'].'inc/settings_view.php'; ?>


				<!-- Settings subtab -->

				<h3>Settings</h3>

				<div id="settings" class="level3tabs" style="min-height:400px;">
					<!-- Settings subsubtabs -->
					<ul>
						<li><a href="#settings-position" title="Controls the generation of regular position messages">Position Update</a></li>
						<li><a href="#settings-output">Protocols</a></li>
						<li><a href="#settings-input">Inputs</a></li>
						<li><a href="#settings-wakeup">Wake Up Triggers</a></li>
						<li><a href="#settings-sleep">Sleep Conditions</a></li>
						<?php if($SuperAdminStatus){ ?> <li><a href="#settings-msgpriority">Message Priorities</a></li> <?php } ?>
						<li><a href="#settings-comports">Com Ports</a></li>
					</ul>

					<div id="settings-position">
						<?php include_once 'position_update.php'; ?>
					</div>

					<div id="settings-output">
						<?php include_once 'protocols.php'; ?>
					</div>

					<div id="settings-input">
						<?php include_once 'inputs.php'; ?>
					</div>

					<div id="settings-sleep">
						<?php include_once 'sleep.php'; ?>
					</div>

					<div id="settings-wakeup">
						<?php include_once 'wakeup.php'; ?>
					</div>

					<?php if($SuperAdminStatus){ ?>
					<?php require_once $_SERVER['DOCUMENT_ROOT'].'inc/message_priority_view.php'; ?>
					<div id="settings-msgpriority">
						<?php include_once 'message_priority.php'; ?>
					</div>
					<?php } ?>

					<div id="settings-comports">
						<?php include_once 'com_ports.php'; ?>
					</div>
				</div> <!--  end settings -->
			</div> <!--  end contentblock2 -->
		</div> <!--  end contentblock -->
	</div> <!--  end container -->
</body>
</html>

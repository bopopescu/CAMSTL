<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';			//contains functions for getting, settings dbconfig parameters.
require_once $_SERVER['DOCUMENT_ROOT'].'inc/util.inc';				//contains functions for input validation, socket interaction, error message display, and logging.

$dbconfig = new dbconfigController();

			$use_email_logging = '';
			if(isset($_GET['ctlDebugEmail']) && $_GET['ctlDebugEmail'] !== false)
			{
				$use_email_logging = $_GET['ctlDebugEmail'];
			}
			else
			{
				$use_email_logging_raw = $dbconfig->getDbconfigData('system','remote-logging');
				$use_email_logging = (isValidOnOff($use_email_logging_raw) ? $use_email_logging_raw : '');
			}
?>

<!DOCTYPE HTML>

<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Debug - <?php echo DEVICE_NAME; ?></title>
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
						<span style="color:#002539;font-weight:bold;">Toll Free:</span>
						1-877-352-8522&nbsp;&nbsp;
						<span style="color:#002539;">|</span>
						&nbsp;&nbsp;
						<span style="color:#002539;font-weight:bold;"> In Calgary:</span>
						403-252-5007&nbsp;&nbsp;
						<span style="color:#002539;">|</scan>
						&nbsp;&nbsp;<a style="color:#002539;font-weight:bold; text-decoration:none;" href="http://www.aware360.com" target="_blank">www.aware360.com</a>
					</p>
			<?php include './supporttabs.php'; ?>
						<div class="contentblock2">
							<div class="msgBox"></div>

							<h3>Debug</h3>

						<form method="post" id="chgDebug" action="/inc/debug_processor.php">
							<div><hr class="hr"></hr></div>

							<div class="row">
								<span class="label">Enable E-mail Logging</span>
								<span class="formw2">
									<input type="radio" name="ctlDebugEmail" value="On" <?php echo isOn($use_email_logging) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
									<input type="radio" name="ctlDebugEmail" value="Off" <?php echo isOff($use_email_logging) ? 'checked="checked"' : '';?> /> Off
								</span>
							</div>

							<div class="spacer">&nbsp;</div>
							<div class="row">
								<span class="formw">
									<button type="submit" class="button2-link">Save</button>&nbsp;
									<button type="reset" class="button3-link">Cancel</button>&nbsp;
								</span>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>

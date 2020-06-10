<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/session_controller.inc';
?>
<!DOCTYPE HTML>

<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>J1939 - <?php echo DEVICE_NAME; ?></title>

<?php
include $_SERVER['DOCUMENT_ROOT'] . 'mainscriptsgroup.php';
?>
	<script type="text/javascript" src="/js/j1939.js"></script>
</head>

<body>
	<div id="dialog" class="ui-dialog-content ui-widget-content"></div>
	<div class="container">
		<?php
		include $_SERVER['DOCUMENT_ROOT'] . 'header.php';
		?>

		<div class="clear"></div>
		<div class="clear"></div>

		<?php
		include $_SERVER['DOCUMENT_ROOT'] . 'tabs.php';
		?>


		<div class="contentblock">

			<!-- Device  tab -->
			<h2>Device Configuration</h2>

			<?php
			include '../devicetabs.php';
			?>

				<div class="contentblock2">
				<div class="msgBox"></div>
				<?php
					require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/j1939-settings.inc';
					print "
				<script type=\"text/javascript\" >
					var g_templateNames = {";
					foreach ($templates_array as $key => $template)
					{
						print "
						$key : '$template',";
					}
					print "
					};
					var g_activeTemplate = '$active_template'
				</script>";
				?>
				<div id="j1939Settings">
					<div class="inversetab">J1939</div>
					<!--- <a href="/TL3000_HTML5/Default_CSH.htm#J1939" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
					<div class="hr"><hr /></div>
					<br/>
						<form id="j1939SettingsForm" method="post" action="/inc/j1939_controller.php">
							<fieldset>
								<legend>Settings</legend>
								<input type="hidden" name="op" value="updateSettings" />
								<div class="row">
									<span class="label">Enable J1939</span>
									<span class="formw units">
										<input type="radio" size="6"  name="enable" value="On" <?php print(($enable) ? "checked=\"checked\"" : ""); ?> /> On &nbsp;
										<input type="radio" size="6"  name="enable" value="Off" <?php print((!$enable) ? "checked=\"checked\"" : ""); ?> /> Off
									</span>
								</div>
								<div class="row">
									<span class="label">Source Address</span>
									<span class="formw units">
										<input  name="SourceAddress" value="<?php printf("0x%02X",$srcAddress); ?>" />
										<br />
										<span class="errorMsg"></span>
									</span>
								</div>
								<div class="row">
									<span class="formw" >
										<span class="column2 column2-label" >Cellular (minutes)</span>
										<span class="column2 column2-label" >Iridium (minutes)</span>
									</span>
								</div>
								<div class="row" style="padding-top: 0px">
									<span class="label">Periodic Message Reporting Interval</span>
									<span class="formw">
										<span class="column2" >
											<input name="CellPMRepInt" class="column2-field" value="<?php print $cellPMRepInt; ?>" />
											<br />
											<span class="errorMsg"></span>
										</span>
										<span class="column2" >
											<input name="IrdPMRepInt" class="column2-field" value="<?php print $irdPMRepInt; ?>" />
											<br />
											<span class="errorMsg"></span>
										</span>
									</span>
								</div>
								<div class="row">
									<span class="label">Exceedance Message Count Reporting Interval</span>
									<span class="formw">
										<span class="column2" >
											<input name="CellEMCRepInt" class="column2-field" value="<?php print $EMCRepInt; ?>" />
											<br />
											<span class="errorMsg"></span>
										</span>
										<span class="column2" >
											<input name="IrdEMCRepInt" class="column2-field" value="<?php print $EMCRepInt; ?>" readonly="readonly"  />
										</span>
									</span>
								</div>
								<div class="row">
									<span class="label">Fault Message Count Reporting Interval</span>
									<span class="formw">
										<span class="column2" >
											<input name="CellFMCRepInt" class="column2-field" value="<?php print $FMCRepInt; ?>" />
											<br />
											<span class="errorMsg"></span>
										</span>
										<span class="column2" >
											<input name="IrdFMCRepInt" class="column2-field" value="<?php print $FMCRepInt; ?>" readonly="readonly" />
										</span>
									</span>
								</div>

								<div class="row">
									<span class="label" >
										<div class="slider-row"></div>
										<div class="slider-row">Periodic Message Priority</div>
									</span>
									<span class="formw">
											<div class="slider-row">
												<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
												<div class="slider-label-center slider-label">Cell</div>
												<div class="slider-label-right slider-label">Iridium*</div>
											</div>
											<div class="slider-row">
												<?php
													echo '
												<input type="range" name="periodic_pri" min="1" max="3" step="1"
													value="'.$periodic_pri.'" '.(((!$enable) ? 'disabled="true"' : '')).' />';
												?>
											</div>
									</span>
								</div>
								<div class="row">
									<span class="label">
										<div class="slider-row"></div>
										<div class="slider-row">Exceedence Message Priority</div>
									</span>
									<span class="formw">
										<div class="slider-row">
											<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
											<div class="slider-label-center slider-label">Cell</div>
											<div class="slider-label-right slider-label">Iridium*</div>
										</div>
										<div class="slider-row">
											<?php
												echo '
											<input type="range" name="exceed_pri" min="1" max="3" step="1"
												value="'.$exceed_pri.'" '.(((!$enable) ? 'disabled="true"' : '')).' />';
											?>
										</div>
									</span>
								</div>
								<div class="row">
									<span class="label">
										<div class="slider-row"></div>
										<div class="slider-row">Fault Message Priority</div>
									</span>
									<span class="formw">
										<div class="slider-row">
											<div class="slider-label-left slider-label" >WiFi/Ethernet</div>
											<div class="slider-label-center slider-label">Cell</div>
											<div class="slider-label-right slider-label">Iridium*</div>
										</div>
										<div class="slider-row">
											<?php
												echo '
											<input type="range" name="fault_pri" min="1" max="3" step="1"
												value="'.$fault_pri.'" '.(((!$enable) ? 'disabled="true"' : '')).' />';
											?>
										</div>
									</span>
								</div>
								<div class="row">
									<span class="footNote">
										* Note: Sending messages over Iridium will incur higher costs.
									</span>
								</div>
								<div class="row">

									<?php
									if(hasSubmitAccess())
									{
									?>
									<span class="formw">
										<button type="submit" class="button2-link">Save</button>&nbsp;
										<button type="reset" class="button3-link" >Clear</button>
									</span>
									<?php
									}
									?>
								</div><!-- end row-wide-->
								<div class="spacer">&nbsp;</div><br />
							</fieldset>
						</form>
						<br />
						<form id="addJ1939Template" method="post" enctype="multipart/form-data" action="/inc/j1939_controller.php">
							<fieldset>
								<legend> Add Template</legend>
								<input type="hidden" name="op" value="addTemplate" />
								<div class="row-wide">
									<div class="column">
										<div class="column-label"><label for="templateName">Template Name</label></div>
										<div class="column-field">
											<input type="text" size="30" maxlength="30" id="templateName" name="templateName" <?php
											if ($templateName != '')
												print "value='$templateName'";
										?> />
										</div>
										<div><span class="fieldMessage">* Only a-z, 0-9, -, _ are allowed<br /> 30 characters max</span></div>
									</div>
									<div class="column">
										<div class="column-label"><label for="templateFile">Template File</label></div>
										<div class="column-field">
											<input type="file" name="templateFile" />
										</div>
										<div>&nbsp;<br>&nbsp;</div>
									</div>

									<?php
									if(hasSubmitAccess())
									{
									?>
									<div class="column button-box-inline">
										<button type="submit" class="button2-link" disabled="true" >Add</button>&nbsp;
										<button type="reset" class="button3-link" <?php print((!$enable) ? " disabled='true'" : "''"); ?> >Clear</button>
										<div>&nbsp;<br /> &nbsp;</div>
									</div>
									<?php
									}
									?>
								</div><!-- end row-wide-->
							</fieldset>
						</form>
						<div class="spacer">&nbsp;</div>
						<legend>Saved Templates</legend>
						<table id="saved-templates">
						<thead>
							<tr>
								<th style="width: 70%;">Template Name</th>
								<th style="width: 30%;">Delete</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$t_disabled = '';
							if (!$enable) {
								$t_disabled = "Disabled";
							}
							foreach ($templates_array as $template) {
								print "
							<tr id=\"tr_$template\">
								<td> $template </td>
								<td class=\"delete\">" . (hasSubmitAccess() ? '<a class="deleteTemplate' . $t_disabled . '" href="#" title="Delete template"><img src="/images/' . $t_disabled . 'DeleteIcon16.png" alt="Delete" /></a>' : '&nbsp;') . "</td>
							</tr>
							";
							}
							if (empty($templates_array)) {
								print "
							<tr>
								<td colspan=2> No templates have been saved. </td>
							</tr>
								";
							}
							?>
						</tbody>
						</table>
						<div class="spacer">&nbsp;</div>
						<form id="activateJ1939Template" method="post" action="/inc/j1939_controller.php">
							<fieldset>
								<legend> Activate Template</legend>
								<input type="hidden" name="op" value="activateTemplate" />
								<div class="row-wide">
									<div class="column">
										<div class="column-label"><label for="template_name">Template</label></div>
										<div class="column-field">
											<select name="template_name" <?php print((!$enable) ? " disabled='true'" : "''"); ?> >
												<option value="*" selected >Select a Template</option>
												<?php
												foreach ($templates_array as $template)
												{
													print "
												<option value=\"$template\" ".(($template == $active_template)?'selected="selected"':'')." > $template </option>
													";
												}
												?>
											</select>
										</div>
									</div>
									<?php
									if(hasSubmitAccess())
									{
									?>
									<div class="column button-box-inline">
										<button type="submit" class="button2-link" disabled="true">Activate</button>&nbsp;
										<button type="reset" class="button3-link" <?php print((!$enable) ? " disabled='true'" : "''"); ?> >Clear</button>

									</div>
									<?php
									}
									?>
								</div><!-- end row-wide-->
							</fieldset>
						</form>
						<div class="spacer">&nbsp;</div>
					</div>
				</div>
			</div>
	</div><!-- end of entire div container -->
</body>
</html>

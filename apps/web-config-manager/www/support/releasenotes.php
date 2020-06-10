<?php require_once $_SERVER['DOCUMENT_ROOT'].'/inc/session_controller.inc'; ?>
<!DOCTYPE HTML>

<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Release Notes - <?php echo DEVICE_NAME; ?></title>

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
					&nbsp;&nbsp;<a style="color:#002539;font-weight:bold; text-decoration:none;" href="http://www.aware360.com" target="_blank">www.aware360.com</a>
				</p>
				<div class="msgBox"></div>
				<?php include './supporttabs.php'; ?>


				<div class="contentblock2">
				<div id="whatsNew">


				<h3>Release Notes</h3>
				
				<div class="expand">
					<h4>
						<div class="expandIcon"></div>
						<div class="collapseIcon"></div>
						Version 1.9.3
					</h4>
					<div class="expblock">

		        Release Notes - TRULink Firmware Development - Version 1.9.3
            
					<h2>        Bug
					</h2>
					<ul>
						<li>[<a href='https://support.aware360.net/browse/TFD-873'>TFD-873</a>] -         UI Naming error					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-877'>TFD-877</a>] -         Overdue On / Timer Extensions Off over Cell/Sat functionality is broken in 1.9.2-RLS.10387					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-882'>TFD-882</a>] -         Modbus page Save button is grayed out					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-887'>TFD-887</a>] -         Exceedence correction has wrong value in output string - 1.9.2-10415					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-908'>TFD-908</a>] -         Ignition On/Off not being sent					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-909'>TFD-909</a>] -         Modbus - Recovery timer is not working properly					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-910'>TFD-910</a>] -         LCM - Save fails on Hardware Page					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-911'>TFD-911</a>] -         LCM - PositionUpdate page fails to save					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-912'>TFD-912</a>] -         Cathodic - strange values reported when no current coming in.					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-913'>TFD-913</a>] -         LCM - No Sensor message in message page					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-914'>TFD-914</a>] -         SLP is buzzing on next state update after buzzing was acknowledged					</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-916'>TFD-916</a>] -         The default value for sending out GPS over the ethernet port should be On					</li>
					</ul>
                
					<h2>        Improvement
					</h2>
					<ul>
						<li>[<a href='https://support.aware360.net/browse/TFD-875'>TFD-875</a>] -         Help added to LCM						</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-879'>TFD-879</a>] -         Add a &#39;Return to Normal&#39; message in the modbus and J1939 monitors						</li>
					</ul>
  				</div>                					
				</div>                					
                                                                


				
				<div class="expand">
					<h4>
						<div class="expandIcon"></div>
						<div class="collapseIcon"></div>
						Version 1.9.2
					</h4>
					<div class="expblock">
		        Release Notes - TRULink Firmware Development - Version 1.9.2
        
						<h2>        Bug
						</h2>
						<ul>
						<li>[<a href='https://support.aware360.net/browse/TFD-555'>TFD-555</a>] -         Reboot Doesn&#39;t Log a Shutdown</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-632'>TFD-632</a>] -         SLP connection sleep logic problem</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-767'>TFD-767</a>] -         [1.76.4.8875] LCM form validation is inconsistent, save/apply button is disabled even though form is valid.</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-809'>TFD-809</a>] -         Trunk.9699: &quot;reboot&quot; command does not reboot if &quot;go-to-sleep&quot; already exists</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-849'>TFD-849</a>] -         1.9-RLS.9839: LCM exports &quot;zigbee written-link-key&quot; in db-config file which prevents the TRULink from writing the new Link key</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-862'>TFD-862</a>] -         Remove CAN wakeup - we no longer reference CAN</li>
						<li>[<a href='https://support.aware360.net/browse/TFD-870'>TFD-870</a>] -         LCM: Support page: web address should be a link</li>
						</ul>
                        
						<h2>        New Feature</h2>						
						<ul>
							<li>[<a href='https://support.aware360.net/browse/TFD-866'>TFD-866</a>] -         CerMaq Alarm Generation</li>
							<li>[<a href='https://support.aware360.net/browse/TFD-867'>TFD-867</a>] -         Add support for No-Motion alerts from the SLP</li>
							<li>[<a href='https://support.aware360.net/browse/TFD-868'>TFD-868</a>] -         Add Hardware Identifier to LCM Page</li>
							<li>[<a href='https://support.aware360.net/browse/TFD-869'>TFD-869</a>] -         Option to output &#39;Average&#39; value for period instead of &#39;Current&#39; value in ModBus output</li>
						</ul>
  				</div>                					
				</div>                					
				<div class="expand">
					<h4>
						<div class="expandIcon"></div>
						<div class="collapseIcon"></div>
						Version 1.9.1
					</h4>
					<div class="expblock">
        Release Notes - TRULink Firmware Development - Version 1.9.1
                
<h2>        Bug
</h2>
<ul>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-363'>TFD-363</a>] -         [1.7-alpha.5929] Proc Seatbelt needs to log using ats-logger
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-802'>TFD-802</a>] -         TestBench 9583: Cellular modem is put into Airplane-Mode
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-803'>TFD-803</a>] -         Export process needs to remove XML from files
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-824'>TFD-824</a>] -         Database gets  Timestamp format wrong 
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-825'>TFD-825</a>] -         LCM changes suggested by Operations - 1
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-830'>TFD-830</a>] -         TRULink  failover from SAT to Cell not working
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-845'>TFD-845</a>] -         Version 1.8-RLS.9539 Cannot change the SSID in the LCM
</li>
</ul>
                
<h2>        Improvement
</h2>
<ul>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-804'>TFD-804</a>] -         LCM - Installer settings page changes
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-805'>TFD-805</a>] -         Get rid of the &quot;Optimized Reporting&quot; selection on the Settings Tab of the LCM
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-806'>TFD-806</a>] -         UI - Get rid of &quot;sleep mode&quot; setting on the Settings &gt; Hardware tab of the LCM
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-807'>TFD-807</a>] -         UI - Move the Iridium parameters from the Protocols LCM page and  move to the Iridium Page.
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-826'>TFD-826</a>] -         LCM changes suggested by Operations - 2
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-827'>TFD-827</a>] -         LCM changes suggested by Operations - 3
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-834'>TFD-834</a>] -         Cell Fringe Testing
</li>
</ul>
        
<h2>        New Feature
</h2>
<ul>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-548'>TFD-548</a>] -         Add DHCP Lease List to IP Reservation page
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-790'>TFD-790</a>] -         Additional OBD-2 Data Collection
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-799'>TFD-799</a>] -         LED status lights need to be revisited - especially sat light.
</li>
</ul>
  				</div>                					
				</div>                					
				<div class="expand">
					<h4>
						<div class="expandIcon"></div>
						<div class="collapseIcon"></div>
						Version 1.9
					</h4>
					<div class="expblock">
			      Release Notes - TruLink Firmware Development - Version Redstone V1.9
<h2>        Bug
</h2>
<ul>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-754'>TFD-754</a>] -         Update state command to include packetizer-cams host and port
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-796'>TFD-796</a>] -         Using compression is disabling Iridium output in packetizer-cams
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-797'>TFD-797</a>] -         Setting GPS port in LCM causes hang
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-810'>TFD-810</a>] -         IPSEC VPN page - No save button for admin users.
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-813'>TFD-813</a>] -         &quot;state&quot; script does not check def-rootfs umount failure, then runs &quot;rm -rf&quot; on def-rootfs
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-819'>TFD-819</a>] -         Satellite LED does not come on when Iridium is enabled
</li>
</ul>
                
<h2>        Improvement
</h2>
<ul>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-794'>TFD-794</a>] -         LCM Re-branding
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-795'>TFD-795</a>] -         Auto-reload LCM on TruLink reboot
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-812'>TFD-812</a>] -         DHCP modifications for eth0
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-817'>TFD-817</a>] -         Add JIRA release notes to the LCM from super admin
</li>
</ul>
        
<h2>        New Feature
</h2>
<ul>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-789'>TFD-789</a>] -         Overdue notifications on the SLP
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-791'>TFD-791</a>] -         NTPC Primary/Secondary communications
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-820'>TFD-820</a>] -         Add a simpler way of pushing messages to the apps via socat protocol.
</li>
<li>[<a href='https://itracker.atlassian.net/browse/TFD-821'>TFD-821</a>] -         Make the gpio status human readable.
</li>
</ul>
                
					</div>                					
				</div>                					
					
				<div class="expand">
					<h4>
						<div class="expandIcon"></div>
						<div class="collapseIcon"></div>
						Version 1.8
					</h4>
					<div class="expblock">
		        Release Notes - TruLink Firmware Development - Version Redstone V1.8
                
						<h2>        Bug
						</h2>
						<ul>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-100'>TFD-100</a>] -         SVN 2013: Heartbeat message may contain no GPS coordinates</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-333'>TFD-333</a>] -         Dodge vehicles need modification to wake up as CAN bus is silent by default</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-636'>TFD-636</a>] -         [SVN8084]IgnitionMonitor does not wait for avl-monitor socket server to start</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-646'>TFD-646</a>] -         Modbus should turn off can-odb2-monitor</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-717'>TFD-717</a>] -         Intermittent loss of cellular connectivity on TL-857</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-722'>TFD-722</a>] -         703 - NMEA was OFF</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-745'>TFD-745</a>] -         [1.76.3.8827] When resetting a form the &quot;disable&quot;/&quot;enable&quot; of the associated fields are not updated</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-757'>TFD-757</a>] -         Fix RedstoneAP000 issue</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-766'>TFD-766</a>] -         Ignition Off to On seem to be being sent out Iridium however On to Off are not</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-772'>TFD-772</a>] -         Restore custom user WiFi SSID and Password for updates from 1.72 release</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-779'>TFD-779</a>] -         Export function in LCM should be available to &#39;installer&#39; users</li>
						</ul>                        
						<h2>        New Feature
						</h2>
						<ul>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-569'>TFD-569</a>] -         Message Priority Selection LCM Web Page Design</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-699'>TFD-699</a>] -         Display micro software version in the LCM</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-764'>TFD-764</a>] -         We need to add the ability to have 2 types of low battery shutdown use cases in the LCM</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-765'>TFD-765</a>] -         We need to make sure we wake up to send out low battery indication messages one last time before going to sleep for good.</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-769'>TFD-769</a>] -         Configuring Input Event Messaging: Stage 2</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-777'>TFD-777</a>] -         Ability to set which Modbus Periodic Message parameters will be transmitted over Iridium</li>
						</ul>
            
						<h2>        Task</h2>
						<ul>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-693'>TFD-693</a>] -         Add reading voltage on LRADC3 for hardware ID/revision</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-771'>TFD-771</a>] -         Add force update option to MFG site</li>
							<li>[<a href='https://itracker.atlassian.net/browse/TFD-773'>TFD-773</a>] -         TruLink message transmission timeouts</li>
						</ul>
    
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>
</body>
</html>

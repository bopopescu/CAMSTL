<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/network_controller.inc'; //network (ethernet, wireless) controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/wifi_controller.inc';	//wifi (AP + client) controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dhcp_controller.inc';	//dhcp controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/db_sqlite3.inc';		//contains functions for db interaction

//OBJECT INSTANTIATION
$nt_ctrl = new networkcontroller();
$wifi_ctrl = new wificontroller();
$dhcp_ctrl = new dhcpcontroller();
$dbconfig = new dbconfigController();

//Check form submission
if(!empty($_REQUEST))
{
	debug('=========_REQUEST=============', $_REQUEST);	//DEBUG

	//WiFi form submission
	$result = submitWifi($nt_ctrl, $wifi_ctrl, $dhcp_ctrl, $dbconfig, trimRequest($_REQUEST));
	header("location:https://".$_SERVER['HTTP_HOST']."/network/wifi/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:https://".$_SERVER['HTTP_HOST']."/network/wifi/index.php");
}


/**
 * submitWifi
 *
 * Submits the wifi form content to the wifi controller for processing.
 * If Wifi settings are successfully saved, then proceeds to save DHCP settings.
 * @param object $nt_ctrl network controller object
 * @param object $wifi_ctrl wifi controller object
 * @param object $dhcp_ctrl dhcp controller object
 * @param array - the _REQUEST variable that contains the form submission data
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitWifi($nt_ctrl, $wifi_ctrl, $dhcp_ctrl, $dbconfig, $request)
{
	$result = array("success" => "false", "module" => "wifi", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	//assign wifi ap data from
	$interface = array();
	$interface['name'] = wireless;
	$interface['type'] = 'wifiap';

	$wifi_result = array();		//store the success/failure state for each setting
	$wifi_net_result = false;
	$wifi_ap_result = false;
	$result_ap_fail_codes = array();
	$error_fields_highlighted = false;

	// if the wifi enable is not set we default to true
	if(isValidOnOff(isset($request['wifi-ap-enable']) ? $request['wifi-ap-enable'] : true))
	{
		$dbconfig->setDbconfigData('WiFi', 'ap-enabled', isOn($request['wifi-ap-enable']) ? 1 : 0);
	}

    if ($request['wifi-ap-enable'])
	{
		if(isValidOnOff(isset($request['wifi-ssh-enable']) ? $request['wifi-ssh-enable'] : true))
		{
			$dbconfig->setDbconfigData('WiFi', 'EnableSSH', isOn($request['wifi-ssh-enable']) ? 1 : 0);
		}

		// Save network settings

		// Parse IP
		$ip = '';		//IP
		if( (isset($request['wipoct1']) && $request['wipoct1'] != '') &&
			(isset($request['wipoct2']) && $request['wipoct2'] != '') &&
			(isset($request['wipoct3']) && $request['wipoct3'] != '') &&
			(isset($request['wipoct4']) && $request['wipoct4'] != ''))
		{
			$ip = $request['wipoct1'].'.'.$request['wipoct2'].'.'.$request['wipoct3'].'.'.$request['wipoct4'];
		}

		// Save IP
		$wifi_result['wipoct1'] = (isValidIP($ip) ? $dbconfig->setDbconfigData('system', 'ra0addr', $ip) : false);

		// Parse Subnet Mask
		$mask = '';	//Subnet Mask
		if( (isset($request['wsoct1']) && $request['wsoct1'] != '') &&
			(isset($request['wsoct2']) && $request['wsoct2'] != '') &&
			(isset($request['wsoct3']) && $request['wsoct3'] != '') &&
			(isset($request['wsoct4']) && $request['wsoct4'] != ''))
		{
			$mask = $request['wsoct1'].'.'.$request['wsoct2'].'.'.$request['wsoct3'].'.'.$request['wsoct4'];
		}

		// Save Subnet Mask
		$wifi_result['wsoct1'] = (isValidIP($mask) ? $dbconfig->setDbconfigData('system', 'ra0mask', $mask) : false);

		// Parse MAC Address
		$mac = '';		//MAC
		$wifi_result['wmac1'] = true;

		if($wifi_result['wipoct1'] === true && $wifi_result['wsoct1'] === true)		//wifi settings successfully saved
		{
			debug('(wifi_processor.php|submitWifi()) WiFi network settings saved');	//DEBUG

			// Proceed to save dhcp settings
			if(!empty($request['dhcpserver']))
			{
				$wifi_result['dhcpserver'] = $wifi_result['sdhcpoct1'] = $wifi_result['edhcpoct1'] = true;

				// Set DHCP State On | Off
				if(isOn($request['dhcpserver']))			//DHCP Server Enabled
				{
					$wifi_result['dhcpserver'] = $dbconfig->unsetDbconfigData('system', 'ra0dhcp');
				}
				else if(isOff($request['dhcpserver']))		//DHCP Server Disabled
				{
					$wifi_result['dhcpserver']  = $dbconfig->setDbconfigData('system', 'ra0dhcp', 1);
				}
				else
				{
					$wifi_result['dhcpserver'] = false;
				}

				if(isOn($request['dhcpserver']) /*&& $ethernet_result['dhcpserver'] === true*/)
				{
					// SET DHCP START IP RANGE
					$dhcp_sip = '';
					if( (isset($request['sdhcpoct1']) && $request['sdhcpoct1'] != '') &&
						(isset($request['sdhcpoct2']) && $request['sdhcpoct2'] != '') &&
						(isset($request['sdhcpoct3']) && $request['sdhcpoct3'] != '') &&
						(isset($request['sdhcpoct4']) && $request['sdhcpoct4'] != ''))
					{
						$dhcp_sip = $request['sdhcpoct1'].'.'.$request['sdhcpoct2'].'.'.$request['sdhcpoct3'].'.'.$request['sdhcpoct4'];
					}

					$wifi_result['sdhcpoct1'] = (isValidIP($dhcp_sip) ? $dbconfig->setDbconfigData('system', 'ra0startip', $dhcp_sip) : false);

					// SET DHCP END IP RANGE
					$dhcp_eip = '';
					if( (isset($request['edhcpoct1']) && $request['edhcpoct1'] != '') &&
						(isset($request['edhcpoct2']) && $request['edhcpoct2'] != '') &&
						(isset($request['edhcpoct3']) && $request['edhcpoct3'] != '') &&
						(isset($request['edhcpoct4']) && $request['edhcpoct4'] != ''))
					{
						$dhcp_eip = $request['edhcpoct1'].'.'.$request['edhcpoct2'].'.'.$request['edhcpoct3'].'.'.$request['edhcpoct4'];
					}

					$wifi_result['edhcpoct1'] = (isValidIP($dhcp_eip) ? $dbconfig->setDbconfigData('system', 'ra0endip', $dhcp_eip) : false);
				}

				if($wifi_result['dhcpserver'] === true && $wifi_result['sdhcpoct1'] === true && $wifi_result['edhcpoct1'] === true)
				{
					// Unset the dhcpd.conf file in dbconfig
					if($dbconfig->unsetDbconfigData('system', 'dhcpd.conf'))
					{
						$wifi_net_result = true;
						//$result['success'] = 'true';
						//$result['codes'][] = 10;
						//$result['codes'][] = 14;
						debug('(wifi_processor.php|submitWifi()) DHCP settings saved');	//DEBUG
					}
					else
					{
						$wifi_net_result = false;
						//$result['success'] = 'false';
						//$result['codes'][] = 156;
						debug('(wifi_processor.php|submitWifi()) Failed to unset dhcpd.conf in db-config');	//DEBUG
					}
				}
				else
				{
					$wifi_net_result = false;
				//$result['success'] = 'false';
				//$result['codes'][] = 221;
				debug('(wifi_processor.php|submitWifi()) Failed to save DHCP settings');	//DEBUG
				}
			}
		}
		else
		{
			$wifi_net_result = false;
			//$result['success'] = 'false';
			//$result['codes'][] = 201;
			//$result['codes'] = array_merge($result['codes'], $result_eth['codes']);
			debug('(wifi_processor.php|submitWifi()) Failed to save Wi-Fi settings');	//DEBUG
		}

		// Save the Wi-Fi AP settings

		if(!empty($request['ssid']) && !empty($request['authtype']) && !empty($request['encryptype']))
		{
			$password = ((isset($request['wifipassword']) && $request['wifipassword'] != '') ? $request['wifipassword'] : '');

			$result_ap = $wifi_ctrl->APupdate(array('interface' => $interface['name'], 'essid'=> $request['ssid'], 'authmode'=>$request['authtype'], 'encryptype'=>$request['encryptype'], 'password'=>$password, 'mac'=>$interface['mac']));

			if($result_ap['success'] == 'true')		//wifi ap settings successfully saved
			{
				$wifi_ap_result = true;
				debug('(wifi_processor.php|submitWifi()) Wi-Fi AP settings saved');	//DEBUG
				//$result['success'] = 'true';
				//$result['codes'][] = 210;
			}
			else
			{
				$wifi_ap_result = false;

				$result_ap_fail_codes[] = 211;
				$result_ap_fail_codes = array_merge($result_ap_fail_codes, $result_ap['codes']);

				debug('(wifi_processor.php|submitWifi()) Failed to save Wi-Fi AP settings');	//DEBUG
			}
		}
		else
		{
			if(empty($request['ssid']))
				$wifi_result['ssid'] = false;

			if(empty($request['authtype']))
				$wifi_result['authtype'] = false;

			if(empty($request['encryptype']))
				$wifi_result['encryptype'] = false;

			$wifi_ap_result = false;
			$error_fields_highlighted = true;
		}

		if($wifi_net_result === true && $wifi_result['wmac1'] === true && $wifi_ap_result === true)
		{
			$result['success'] = 'true';
			$result['codes'][] = 10;
			$result['codes'][] = 14;
		}
		else
		{
			$result['success'] = 'false';

			if(isSuperAdmin())
			{
				$result['codes'] = array_merge($result['codes'], $result_ap_fail_codes);
			}

			if($wifi_net_result === false || $wifi_result['wmac1'] === false || $error_fields_highlighted === true)
			{
				$result['codes'][] = 12;
			}
			else
			{
				$result['codes'][] = 11;
			}
		}
	}

	// 1) find all the keys in the $wifi_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($wifi_result, false, true);
	$result['fields'] = implode(',', $failed_results);

	foreach($failed_results as $field)
	{
		if($field == 'wipoct1')			// IP
		{
			$result['getParams'] .= '&ip='.$request['wipoct1'].'.'.$request['wipoct2'].'.'.$request['wipoct3'].'.'.$request['wipoct4'];
		}
		else if($field == 'wsoct1')		// Subnet Mask
		{
			$result['getParams'] .= '&mask='.$request['wsoct1'].'.'.$request['wsoct2'].'.'.$request['wsoct3'].'.'.$request['wsoct4'];
		}
		else if($field == 'sdhcpoct1')	// DHCP Start IP
		{
			$result['getParams'] .= '&dhcpsip='.$request['sdhcpoct1'].'.'.$request['sdhcpoct2'].'.'.$request['sdhcpoct3'].'.'.$request['sdhcpoct4'];
		}
		else if($field == 'edhcpoct1')	// DHCP End IP
		{
			$result['getParams'] .= '&dhcpeip='.$request['edhcpoct1'].'.'.$request['edhcpoct2'].'.'.$request['edhcpoct3'].'.'.$request['edhcpoct4'];
		}
		else if($field == 'wmac1')		// MAC
		{
			$result['getParams'] .= '&mac='.$request['wmac1'].':'.$request['wmac2'].':'.$request['wmac3'].':'.$request['wmac4'].':'.$request['wmac5'].':'.$request['wmac6'];
		}
		else	// Other fields
		{
			$result['getParams'] .= '&'.$field.'='.$request[$field];
		}
	}

	debug('(wifi_processor.php|submitWifi()) $result: ', $result); 	//DEBUG

	return $result;

} //END submitWifi

/** DEPRECATED - January 2014
/**
 * submitDhcp
 * Submits the dhcp form content to the dhcp controller for processing
 *
 * @param object $dhcp_ctrl - dhcp controller object
 * @param array $request -  the _REQUEST variable that contains the form submission data
 * @param array $interface	- network interface data( Start/End IP, Mask, Gateway)
 * @return boolean returns true if DHCP settings were successfully saved, false otherwise
 * @author Sean Toscano (sean@absolutetrac.com)
 */
/*function submitDhcp($dhcp_ctrl, $request, $interface)
{
		$dhcp['name'] = $interface['name']; //eth0/ra0

		if(strcasecmp(trim($request['dhcpserver']),'Enabled') == 0)		//DHCP Server Enabled
		{
			//$dhcp['dhcp_status'] = 1;
			$dhcp['dhcp_status'] = "on";
		}
		else if(strcasecmp(trim($request['dhcpserver']),'Disabled') == 0)	//DHCP Server Disabled
		{
			//$dhcp['dhcp_status'] = 0;
			$dhcp['dhcp_status'] = "off";
		}


		if($dhcp['dhcp_status'] == "on")
		{

			$dhcp['dhcp_sip'] = '';			//DHCP Range Start Ip
			if( (isset($request['sdhcpoct1']) && $request['sdhcpoct1'] != '') &&
					(isset($request['sdhcpoct2']) && $request['sdhcpoct2'] != '') &&
					(isset($request['sdhcpoct3']) && $request['sdhcpoct3'] != '') &&
					(isset($request['sdhcpoct4']) && $request['sdhcpoct4'] != ''))
			{
				$dhcp['dhcp_sip'] = $request['sdhcpoct1'].'.'.$request['sdhcpoct2'].'.'.$request['sdhcpoct3'].'.'.$request['sdhcpoct4'];
			}

			$dhcp['dhcp_eip'] = '';			//DHCP Range End Ip
			if( (isset($request['edhcpoct1']) && $request['edhcpoct1'] != '') &&
					(isset($request['edhcpoct2']) && $request['edhcpoct2'] != '') &&
					(isset($request['edhcpoct3']) && $request['edhcpoct3'] != '') &&
					(isset($request['edhcpoct4']) && $request['edhcpoct4'] != ''))
			{
				$dhcp['dhcp_eip'] = $request['edhcpoct1'].'.'.$request['edhcpoct2'].'.'.$request['edhcpoct3'].'.'.$request['edhcpoct4'];
			}

			$dhcp['dhcp_gateway'] = $interface['ip'];		//DHCP Gateway
		}

		debug('(wifi_processor.php|submitDhcp()) dhcp array', $dhcp);	//DEBUG
		return $dhcp_ctrl->setDhcp($dhcp);

} //END submitDhcp
*/

?>

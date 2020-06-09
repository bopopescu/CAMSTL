<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/config.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';		//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$dbconfig = new dbconfigController();



//Check form submission
if(!empty($_REQUEST))
{
	debug('=====================================================================================================');	//DEBUG
	debug('_REQUEST', $_REQUEST);	//DEBUG

	$result = submitZigbeeSettings($dbconfig, trimRequest($_REQUEST));
	header("location:http://".$_SERVER['HTTP_HOST']."/device/zigbee/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:http://".$_SERVER['HTTP_HOST']."/device/zigbee/index.php");
}


/**
 * submitZigbeeSettings
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitZigbeeSettings($dbconfig, $request)
{
	debug('', $request);	//DEBUG

	$result = array("success" => 'false', "module" => "", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$zigbee_result = array();			//store the success/failure state for each setting
	$zkey = '';

	if(!empty($request['zcontrol']))
	{
		$zcontrol = $request['zcontrol'];
		if(isOff($zcontrol))
		{
			$zigbee_result['zcontrol'] = $dbconfig->setDbconfig('feature', 'zigbee-monitor', 0);
		}
		else
		{
			$zigbee_result['zcontrol'] = $dbconfig->setDbconfig('feature', 'zigbee-monitor', 1);
			if( (isset($request['zkey1']) && $request['zkey1'] != '') &&
				(isset($request['zkey2']) && $request['zkey2'] != '') &&
				(isset($request['zkey3']) && $request['zkey3'] != '') &&
				(isset($request['zkey4']) && $request['zkey4'] != '') &&
				(isset($request['zkey5']) && $request['zkey5'] != '') &&
				(isset($request['zkey6']) && $request['zkey6'] != '') &&
				(isset($request['zkey7']) && $request['zkey7'] != '') &&
				(isset($request['zkey8']) && $request['zkey8'] != ''))
			{
				$zkey = $request['zkey1'].$request['zkey2'].$request['zkey3'].$request['zkey4'].$request['zkey5'].$request['zkey6'].$request['zkey7'].$request['zkey8'];
			}

			//Zigbee Link Key
			if (!empty($request['zkey1']))
				$zigbee_result['zkey1'] = (isValidAlphaNumeric($zkey) ? $dbconfig->setDbconfig('zigbee','link-key', $zkey) : false);
			else
				$zigbee_result['zkey1'] = true;
			//Msg Priorities
			if( (isset($request['statereq_pri'])))
			{
				$pri = getMsgPriorityFromSlider($request['statereq_pri']);
			}
			$zigbee_result['statereq_pri'] = (isValidNumber($pri)? $dbconfig->setDbconfig('zigbee', 'State_Request_Priority', $pri) : false);

			if( (isset($request['ci_pri'])))
			{
				$pri = getMsgPriorityFromSlider($request['ci_pri']);
			}
			$zigbee_result['ci_pri'] = (isValidNumber($pri)? $dbconfig->setDbconfig('zigbee', 'Check_In_Priority', $pri) : false);

			if( (isset($request['co_pri'])))
			{
				$pri = getMsgPriorityFromSlider($request['co_pri']);
			}
			$zigbee_result['co_pri'] = (isValidNumber($pri)? $dbconfig->setDbconfig('zigbee', 'Check_Out_Priority', $pri) : false);

			#SLP extensions stuff (V1.9)
			$SLP_AllowOverdue = $request['SLP_AllowOverdue'];
			
			if (isOff($SLP_AllowOverdue))
			{
				$zigbee_result['SLP_AllowOverdue'] = $dbconfig->setDbconfig('zigbee', 'overdueAllow', '0');
			}
			else
			{
				$zigbee_result['SLP_AllowOverdue'] = $dbconfig->setDbconfig('zigbee', 'overdueAllow', '1');

				$SLP_AllowExtensions = $request['SLP_AllowExtensions'];
				$zigbee_result['NotificationTime'] = (isValidNumber($request['NotificationTime']) ? $dbconfig->setDbconfigData('zigbee', 'timerExpireMinutes', $request['NotificationTime']) : false);
			
				if (isOff($SLP_AllowExtensions))
				{
					$zigbee_result['SLP_AllowExtensions'] = $dbconfig->setDbconfig('zigbee', 'timerExtensionAllow', '0');
				}
				else
				{
					$zigbee_result['SLP_AllowExtensions'] = $dbconfig->setDbconfig('zigbee', 'timerExtensionAllow', '1');
					$zigbee_result['HazardExtension'] = (isValidNumber($request['HazardExtension']) ? $dbconfig->setDbconfigData('zigbee', 'hazardExtensionMinutes', $request['HazardExtension']) : false);
					$zigbee_result['ShiftExtension'] = (isValidNumber($request['ShiftExtension']) ? $dbconfig->setDbconfigData('zigbee', 'shiftExtensionMinutes', $request['ShiftExtension']) : false);
				}
			}
		}
	}

	// 1) find all the keys in the $installer_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($zigbee_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(installer_settings_processor.php|submitZigbeeSettings()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

	foreach($failed_results as $field)
	{
		$result['getParams'] .= '&'.$field.'='.$request[$field];
	}

	if(empty($result['fields']))
	{
		$result['success'] = 'true';
		$result['codes'][] = 10;
		$result['codes'][] = 14;
	}
	else
	{
		$result['success'] = 'false';
		$result['codes'][] = 12;
	}
	debug('(installer_settings_processor.php|submitZigbeeSettings()) $result: ', $result); 	//DEBUG
	return $result;

} //END submitZigbeeSettings

?>

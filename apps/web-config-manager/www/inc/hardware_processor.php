<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$dbconfig = new dbconfigController();

//Check form submission
if(!empty($_REQUEST))
{
	debug('=========_REQUEST=============', $_REQUEST);	//DEBUG

	$result = submitHardware($dbconfig, trimRequest($_REQUEST));
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php");
}


/**
 * submitHardware
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitHardware($dbconfig, $request)
{
	$result = array("success" => 'false', "module" => "Hardware", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$hardware_result = array();			//store the success/failure state for each setting

	//Disable Sleep: On/Off
//	$hardware_result['hardwareDisableSleep'] = (isValidOnOff($request['hardwareDisableSleep']) ? $dbconfig->setDbconfigData('RedStone', 'DisableSleep', $request['hardwareDisableSleep']) : false);


	//Speed Source: OBD | GPS
//	$hardware_result['hardwareSpeedSource'] = ((isset($request['hardwareSpeedSource']) && ((strcasecmp($request['hardwareSpeedSource'],'OBD') == 0) || (strcasecmp($request['hardwareSpeedSource'],'GPS') == 0))) ? $dbconfig->setDbconfigData('RedStone', 'SpeedSource', $request['hardwareSpeedSource']) : false);

	//Keep Awake: >= 0
//	$hardware_result['hardwareKeepAwake'] = (isValidNumber($request['hardwareKeepAwake']) ? $dbconfig->setDbconfigData('RedStone', 'KeepAwakeMinutes', $request['hardwareKeepAwake']) : false);

	debug('(hardware_processor.php|submitHardware()) $hardware_result: ', $hardware_result); 	//DEBUG

	// 1) find all the keys in the $positionup_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($hardware_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(hardware_processor.php|submitHardware()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

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

	debug('(hardware_processor.php|submitHardware()) $result: ', $result); 	//DEBUG
	return $result;

} //END submitHardware

?>

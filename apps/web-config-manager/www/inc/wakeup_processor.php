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

	$result = submitwakeup($dbconfig, trimRequest($_REQUEST));
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php");
}


/**
 * submitwakeup
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitwakeup($dbconfig, $request)
{
	debug('', $request);	//DEBUG
	$result = array("success" => 'false', "module" => "wakeup", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$wakeup_result = array();	//store the success/failure state for each setting
	$atLeastOne = FALSE;

	$wakeup_mask = array_flip(explode(",", $dbconfig->getDbconfigData('wakeup', 'mask')));

//	$UseRTC = isset($wakeup_mask['rtc']);

	if ($request['useRTC'] === 'rtc')
	{
		$wakeup_mask = 'rtc,';
		$atLeastOne = TRUE;
	}
	else
		$wakeup_mask = '~rtc,';

	if ($request['useAccel'] === 'accel')
	{
		$wakeup_mask .= 'accel,';
		$atLeastOne = TRUE;
	}
	else
		$wakeup_mask .= '~accel,';

	if ($request['useIgnition'] === 'inp1')
	{
		$wakeup_mask .= 'inp1,';
		$atLeastOne = TRUE;
	}
	else
		$wakeup_mask .= '~inp1,';

	if ($request['useInp2'] === 'inp2')
	{
		$wakeup_mask .= 'inp2,';
		$atLeastOne = TRUE;
	}
	else
	$wakeup_mask .= '~inp2,';

	if ($request['useIridium'] === 'inp3')
	{
		$wakeup_mask .= 'inp3,';
		$atLeastOne = TRUE;
	}
	else
		$wakeup_mask .= '~inp3,';

	if ($request['useCAN'] === 'can')
	{
		$wakeup_mask .= 'can,';
		$atLeastOne = TRUE;
	}
	else
		$wakeup_mask .= '~can,';

	if ($request['useVoltage'] === 'batt_volt')
	{
		$wakeup_mask .= 'batt_volt,';
		$atLeastOne = TRUE;
	}
	else
		$wakeup_mask .= '~batt_volt,';

	if ($request['useLowBatt'] === 'low_batt')
		$wakeup_mask .= 'low_batt';
	else
		$wakeup_mask .= '~low_batt';

	if ($atLeastOne == FALSE)
		$wakeup_result['useRTC'] = false;
	else
		$wakeup_result['useRTC'] = $dbconfig->setDbconfigData('wakeup', 'mask', $wakeup_mask);

	$wakeup_result['wakeupLowBattV'] = (isValidNumber($request['wakeupLowBattV']) ? $dbconfig->setDbconfigData('wakeup', 'CriticalVoltage', $request['wakeupLowBattV'] * 1000) : false);
	$wakeup_result['wakeupGForce'] = (isValidNumber($request['wakeupGForce']) ? $dbconfig->setDbconfigData('wakeup', 'AccelTriggerG', $request['wakeupGForce'] * 10) : false);
	$wakeup_result['wakeupBatteryVoltage'] = (isValidNumber($request['wakeupBatteryVoltage']) ? $dbconfig->setDbconfigData('wakeup', 'WakeupVoltage', $request['wakeupBatteryVoltage'] * 1000) : false);

	debug('(wakeup_processor.php|submitwakeup()) $wakeup_result: ', $wakeup_result); 	//DEBUG

	// 1) find all the keys in the $wakeup_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($wakeup_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(wakeup_processor.php|submitwakeup()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

	foreach($failed_results as $field)
	{
		$result['getParams'] .= '&'.$field.'='.$request[$field];
	}

	if(empty($result['fields']))
	{
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); //create a socket connection
		$result = socket_connect($socket, '127.0.0.1', 39000);	//connect to localhost at port 39000

		if($result === false)
		{
			return false;
		}

		$cmd = "wakeup\r";				//admin client API format: phpcmd <command name> [parameters]\r
		socket_write($socket, $cmd, strlen($cmd));
		socket_close($socket);
		debug('(wakeup_processor.php) setting wakeup signals'); 	//DEBUG

		$result['success'] = 'true';
		$result['codes'][] = 10;
		$result['codes'][] = 14;
	}
	else
	{
		$result['success'] = 'false';
		$result['codes'][] = 12;
	}

	debug('(wakeup_processor.php|submitwakeup()) $result: ', $result); 	//DEBUG
	return $result;

} //END submitwakeup

?>

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

	$result = submitPositionUpdate($dbconfig, trimRequest($_REQUEST));
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php");
}


/**
 * submitPositionUpdate
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitPositionUpdate($dbconfig, $request)
{
	debug('', $request);	//DEBUG

	$result = array("success" => 'false', "module" => "PositionUpdate", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$positionup_result = array();		//store the success/failure state for each setting

	//Time: > 0; 0 == off
	$positionup_result['positionUpdateTime'] = (isValidNumber($request['positionUpdateTime']) ? $dbconfig->setDbconfigData('PositionUpdate','Time', $request['positionUpdateTime']) : false);

	//Distance: > 0; 0 == off
	$positionup_result['positionUpdateDistance'] = (isValidNumber($request['positionUpdateDistance']) ? $dbconfig->setDbconfigData('PositionUpdate','Distance', $request['positionUpdateDistance']) : false);

	//Accumulated Heading Change: 5 - 30; 0 == off
	$positionup_result['positionHeading'] = ((isValidNumber($request['positionHeading']) && ($request['positionHeading'] == 0 || ($request['positionHeading'] >= 5 && $request['positionHeading'] <= 30))) ? $dbconfig->setDbconfigData('PositionUpdate','Heading', $request['positionHeading']) : false);

	//Pinning: On/Off
	$positionup_result['positionPinning'] = (isValidOnOff($request['positionPinning']) ? $dbconfig->setDbconfigData('PositionUpdate','Pinning', $request['positionPinning']) : false);

	//Stop Velocity: valid range: 0 - 6
	$positionup_result['positionStopVelocity'] = ((isValidNumber($request['positionStopVelocity'])  && $request['positionStopVelocity'] <= 6) ? $dbconfig->setDbconfigData('PositionUpdate', 'StopVel', $request['positionStopVelocity']) : false);

	//Stop Time: >= 0
	$positionup_result['positionStopTime'] = (isValidNumber($request['positionStopTime']) ? $dbconfig->setDbconfigData('PositionUpdate', 'StopTime', $request['positionStopTime']) : false);

	//Notify On Stop/Start: On/Off
	$positionup_result['positionReportStopStart'] = (isValidOnOff($request['positionReportStopStart']) ? $dbconfig->setDbconfigData('PositionUpdate', 'ReportStopStart', $request['positionReportStopStart']) : false);

	//Report When Stopped: On/Off
	$positionup_result['positionReportWhenStopped'] = (isValidOnOff($request['positionReportWhenStopped']) ? $dbconfig->setDbconfigData('PositionUpdate', 'ReportWhenStopped', $request['positionReportWhenStopped']) : false);

	$positionup_result['IridiumUpdateIntervalCtl'] = (isValidNumber($request['IridiumUpdateIntervalCtl']) ? $dbconfig->setDbconfigData('PositionUpdate', 'IridiumReportTime', $request['IridiumUpdateIntervalCtl']) : false);

	debug('(position_update_processor.php|submitPositionUpdate()) $positionup_result: ', $positionup_result); 	//DEBUG

	// 1) find all the keys in the $positionup_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($positionup_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(position_update_processor.php|submitPositionUpdate()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

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

	debug('(position_update_processor.php|submitPositionUpdate()) $result: ', $result); 	//DEBUG
	return $result;

} //END submitPositionUpdate

?>

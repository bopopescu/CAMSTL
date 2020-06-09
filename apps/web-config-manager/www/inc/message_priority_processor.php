<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$dbconfig = new dbconfigController();

//Check form submission
if(!empty($_REQUEST))
{
	debug('=========_REQUEST=============', $_REQUEST);	//DEBUG

	$result = submitMessagePriority($dbconfig, trimRequest($_REQUEST));
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php");
}


/**
 * submitMessagePriority
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitMessagePriority($dbconfig, $request)
{
	debug('', $request);	//DEBUG

	$result = array("success" => 'false', "module" => "MessagePriority", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$msgpriority_result = array();		//store the success/failure state for each setting

//	$msgpriority_result['MP_acceleration_pri'] = (isValidNumber($request['MP_acceleration_pri']) ?
//		$dbconfig->setDbconfig('MSGPriority','acceleration', getMsgPriorityFromSlider($request['MP_acceleration_pri'])) : false);
//	$msgpriority_result['MP_accel_ok_pri'] = (isValidNumber($request['MP_accel_ok_pri']) ?
//		$dbconfig->setDbconfig('MSGPriority','accept_accel_resumed', getMsgPriorityFromSlider($request['MP_accel_ok_pri'])) : false);
//	$msgpriority_result['MP_decel_ok_pri'] = (isValidNumber($request['MP_decel_ok_pri']) ?
//		$dbconfig->setDbconfig('MSGPriority','accept_deccel_resumed', getMsgPriorityFromSlider($request['MP_decel_ok_pri'])) : false);
//	$msgpriority_result['MP_driver_status_pri'] = (isValidNumber($request['MP_driver_status_pri']) ?
//		$dbconfig->setDbconfig('MSGPriority','driver_status', getMsgPriorityFromSlider($request['MP_driver_status_pri'])) : false);
//	$msgpriority_result['MP_engine_off_pri'] = (isValidNumber($request['MP_engine_off_pri']) ?
//		$dbconfig->setDbconfig('MSGPriority','engine_off', getMsgPriorityFromSlider($request['MP_engine_off_pri'])) : false);
//	$msgpriority_result['MP_engine_on_pri'] = (isValidNumber($request['MP_engine_on_pri']) ?
//		$dbconfig->setDbconfig('MSGPriority','engine_on', getMsgPriorityFromSlider($request['MP_engine_on_pri'])) : false);
//	$msgpriority_result['MP_ping_pri'] = (isValidNumber($request['MP_ping_pri']) ?
//		$dbconfig->setDbconfig('MSGPriority','ping', getMsgPriorityFromSlider($request['MP_ping_pri'])) : false);
//	$msgpriority_result['MP_power_off_pri'] = (isValidNumber($request['MP_power_off_pri']) ?
//		$dbconfig->setDbconfig('MSGPriority','power_off', getMsgPriorityFromSlider($request['MP_power_off_pri'])) : false);
//	$msgpriority_result['MP_power_on_pri'] = (isValidNumber($request['MP_power_on_pri']) ?
//		$dbconfig->setDbconfig('MSGPriority','power_on', getMsgPriorityFromSlider($request['MP_power_on_pri'])) : false);
	$msgpriority_result['MP_calamp_user_pri'] = (isValidNumber($request['MP_calamp_user_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','calamp_user_msg', getMsgPriorityFromSlider($request['MP_calamp_user_pri'])) : false);
	$msgpriority_result['MP_ci_pri'] = (isValidNumber($request['MP_ci_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','check_in', getMsgPriorityFromSlider($request['MP_ci_pri'])) : false);
	$msgpriority_result['MP_co_pri'] = (isValidNumber($request['MP_co_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','check_out', getMsgPriorityFromSlider($request['MP_co_pri'])) : false);
		
	$msgpriority_result['MP_heartbeat_pri'] = (isValidNumber($request['MP_heartbeat_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','heartbeat', getMsgPriorityFromSlider($request['MP_heartbeat_pri'])) : false);
	$msgpriority_result['MP_crit_batt_pri'] = (isValidNumber($request['MP_crit_batt_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','crit_batt', getMsgPriorityFromSlider($request['MP_crit_batt_pri'])) : false);
	$msgpriority_result['MP_low_batt_pri'] = (isValidNumber($request['MP_low_batt_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','low_batt', getMsgPriorityFromSlider($request['MP_low_batt_pri'])) : false);
	$msgpriority_result['MP_sensor_pri'] = (isValidNumber($request['MP_sensor_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','sensor', getMsgPriorityFromSlider($request['MP_sensor_pri'])) : false);

	$msgpriority_result['MP_sched_msg_pri'] = (isValidNumber($request['MP_sched_msg_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','scheduled_message', getMsgPriorityFromSlider($request['MP_sched_msg_pri'])) : false);
	$msgpriority_result['MP_start_pri'] = (isValidNumber($request['MP_start_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','start_condition', getMsgPriorityFromSlider($request['MP_start_pri'])) : false);
	$msgpriority_result['MP_stop_pri'] = (isValidNumber($request['MP_stop_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','stop_condition', getMsgPriorityFromSlider($request['MP_stop_pri'])) : false);
	$msgpriority_result['MP_ignition_on_pri'] = (isValidNumber($request['MP_ignition_on_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','ignition_on', getMsgPriorityFromSlider($request['MP_ignition_on_pri'])) : false);
	$msgpriority_result['MP_ignition_off_pri'] = (isValidNumber($request['MP_ignition_off_pri']) ?
		$dbconfig->setDbconfig('MSGPriority','ignition_off', getMsgPriorityFromSlider($request['MP_ignition_off_pri'])) : false);

	$msgpriority_result['MP_j1939_periodic_pri'] = (isValidNumber($request['MP_j1939_periodic_pri']) ?
		$dbconfig->setDbconfig('CanJ1939Monitor','overiridium_priority_periodic', getMsgPriorityFromSlider($request['MP_j1939_periodic_pri'])) : false);
	$msgpriority_result['MP_j1939_exceed_pri'] = (isValidNumber($request['MP_j1939_exceed_pri']) ?
		$dbconfig->setDbconfig('CanJ1939Monitor','overiridium_priority_exceedance', getMsgPriorityFromSlider($request['MP_j1939_exceed_pri'])) : false);
	$msgpriority_result['MP_j1939_fault_pri'] = (isValidNumber($request['MP_j1939_fault_pri']) ?
		$dbconfig->setDbconfig('CanJ1939Monitor','overiridium_priority_fault', getMsgPriorityFromSlider($request['MP_j1939_fault_pri'])) : false);

	$msgpriority_result['MP_modbus_periodic_pri'] = (isValidNumber($request['MP_modbus_periodic_pri']) ?
		$dbconfig->setDbconfig('modbus','overiridium_priority_periodic', getMsgPriorityFromSlider($request['MP_modbus_periodic_pri'])) : false);
	$msgpriority_result['MP_modbus_exceed_pri'] = (isValidNumber($request['MP_modbus_exceed_pri']) ?
		$dbconfig->setDbconfig('modbus','overiridium_priority_exceedance', getMsgPriorityFromSlider($request['MP_modbus_exceed_pri'])) : false);
	$msgpriority_result['MP_modbus_fault_pri'] = (isValidNumber($request['MP_modbus_fault_pri']) ?
		$dbconfig->setDbconfig('modbus','overiridium_priority_fault', getMsgPriorityFromSlider($request['MP_modbus_fault_pri'])) : false);
	/*-- add above here -*/

	debug('(message_priority_processor.php|submitMessagePriority()) $msgpriority_result: ', $msgpriority_result); 	//DEBUG

	// 1) find all the keys in the $msgpriority_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($msgpriority_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(message_priority_processor.php|submitMessagePriority()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

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

	debug('(message_priority_processor.php|submitMessagePriority()) $result: ', $result); 	//DEBUG
	return $result;

} //END submitMessagePriority

?>

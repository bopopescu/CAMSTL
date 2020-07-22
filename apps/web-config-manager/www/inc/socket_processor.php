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

	$result = submitSocket($dbconfig, trimRequest($_REQUEST));
	header("location:https://".$_SERVER['HTTP_HOST']."/device/settings/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:https://".$_SERVER['HTTP_HOST']."/device/settings/index.php");
}


/**
 * submitSocket
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitSocket($dbconfig, $request)
{

	$result = array("success" => 'false', "module" => "Socket", "codes" => array(), "fields" => null,  "getParams" => null);	//array for capturing result status, status code, and field names.

	$socket_result = array();		//store the success/failure state for each setting

	$set_nmea = $set_serGps = '';
	//NMEA (and SER_GPS): On/Off
	if(isValidOnOff($request['gpsSocketServer']))
	{
		$set_nmea = $dbconfig->setDbconfigData('feature', 'gps-socket-server', $request['gpsSocketServer']);

		if($set_nmea)
		{
			if(isOn($request['gpsSocketServer']))
			{
				$set_serGps = $dbconfig->setDbconfigData('SER_GPS', 'sendGPS', 'On');
			}
			else
			{
				$set_serGps = $dbconfig->setDbconfigData('SER_GPS', 'sendGPS','Off');
			}
		}


		if($set_nmea === false || $set_serGps === false)
		{
			$socket_result['gpsSocketServer'] = false;
		}
		else
		{
			$socket_result['gpsSocketServer'] = true;
		}
	}

	//NMEA Port: > 0
	if(isOn($request['gpsSocketServer']))
	{
		$socket_result['gpsSocketServerPort'] = (isValidNumber($request['gpsSocketServerPort']) ? $dbconfig->setDbconfigData('gps-socket-server', 'listen_server_port', $request['gpsSocketServerPort']) : false);
	}
	debug('(socket_processor.php|submitSocket()) $socket_result: ', $socket_result); 	//DEBUG


	// 1) find all the keys in the $positionup_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($socket_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(socket_processor.php|submitSocket()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

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



	debug('(socket_processor.php|submitSocket()) $result: ', $result); 	//DEBUG

	return $result;

} //END submitSocket

?>

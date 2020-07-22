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

	$result = submitOutput($dbconfig, trimRequest($_REQUEST));
	header("location:https://".$_SERVER['HTTP_HOST']."/device/settings/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:https://".$_SERVER['HTTP_HOST']."/device/settings/index.php");
}


/**
 * submitoutput
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitOutput($dbconfig, $request)
{
	debug('', $request);	//DEBUG
	$result = array("success" => 'false', "module" => "Output", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$output_result = array();	//store the success/failure state for each setting

	//CAMS: On/Off
	$output_result['cams'] = (isValidOnOff($request['cams']) ? $dbconfig->setDbconfigData('feature', 'packetizer-cams', $request['cams']) : false);

	//CAMS Server Host & Port
	if(isOn($request['cams']))
	{
		if(isValidIP($request['camsHost']) || isValidDNS($request['camsHost']))
		{
			$output_result['camsHost'] = $dbconfig->setDbconfigData('packetizer-cams', 'host', $request['camsHost']);
		}
		else
		{
			$output_result['camsHost'] = false;
		}

		//CAMS Server Port
		$output_result['camsPort'] = (isValidNumber($request['camsPort']) ? $dbconfig->setDbconfigData('packetizer-cams', 'port', $request['camsPort']) : false);
		// CAMS Compression
		$output_result['camsCompress'] = (isValidOnOff($request['camsCompress']) ? $dbconfig->setDbconfigData('packetizer-cams', 'UseCompression', $request['camsCompress']) : false);


		$output_result['IridiumEnable'] = (isValidOnOff($request['IridiumEnable'])? $dbconfig->setDbconfigData('packetizer-cams', 'IridiumEnable', $request['IridiumEnable']): false);
		$dbconfig->setDbconfigData('feature', 'iridium-monitor', isOn($request['IridiumEnable']) ? 1 : 0);

		if(isOn($request['IridiumEnable']))
		{
			//CAMS Irdium Settings
			$output_result['IridiumPri'] = (isValidNumber($request['IridiumPri'])? $dbconfig->setDbconfigData('packetizer-cams', 'IridiumPriorityLevel', $request['IridiumPri']): false);
			$output_result['camsRetries'] = (isValidNumber($request['camsRetries'])? $dbconfig->setDbconfigData('packetizer-cams', 'retry_limit', $request['camsRetries']) : false);
			$output_result['CellFailMode'] = (isValidNumber($request['CellFailMode'])? $dbconfig->setDbconfigData('packetizer-cams', 'CellFailModeEnable', $request['CellFailMode']): false);
			$output_result['camsIridiumTimeout'] = (isValidNumber($request['camsIridiumTimeout'])? $dbconfig->setDbconfigData('packetizer-cams', 'iridium_timeout', $request['camsIridiumTimeout']): false);
			$output_result['camsIridiumDataLimitPriority'] =
				(isValidNumber($request['camsIridiumDataLimitPriority'])? $dbconfig->setDbconfigData('packetizer-cams', 'IridiumDataLimitPriority', $request['camsIridiumDataLimitPriority']): false);
		}
	}
/*
	//Trakopolis: On/Off
	$output_result['Trak'] = (isValidOnOff($request['Trak']) ? $dbconfig->setDbconfigData('feature', 'packetizer', $request['Trak']) : false);
	//Trakopolis Server Host & Port
	if(isOn($request['Trak']))
	{
		if(isValidIP($request['trakHost']) || isValidDNS($request['trakHost']))
		{
			$output_result['trakHost'] = $dbconfig->setDbconfigData('packetizer', 'host', $request['trakHost']);
		}
		else
		{
			$output_result['trakHost'] = false;
		}

		//Trakopolis Server Port
		$output_result['trakPort'] = (isValidNumber($request['trakPort']) ? $dbconfig->setDbconfigData('packetizer', 'port', $request['trakPort']) : false);
	}

	//RDS/TRULink: On/Off
	$output_result['RDS'] = (isValidOnOff($request['RDS']) ? $dbconfig->setDbconfigData('feature', 'packetizer-dash', $request['RDS']) : false);

	//RDS Server Host
	if(isOn($request['RDS']))
	{
		if(isValidIP($request['rdsHost']) || isValidDNS($request['rdsHost']))
		{
			$output_result['rdsHost'] = $dbconfig->setDbconfigData('packetizer-dash', 'host', $request['rdsHost']);
		}
		else
		{
			$output_result['rdsHost'] = false;
		}

		//RDS Server Port
		$output_result['rdsPort'] = (isValidNumber($request['rdsPort']) ? $dbconfig->setDbconfigData('packetizer-dash', 'port', $request['rdsPort']) : false);

	}
*/
	debug('(output_processor.php|submitOutput()) $output_result: ', $output_result); 	//DEBUG

	// 1) find all the keys in the $positionup_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($output_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(output_processor.php|submitOutput()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

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

	debug('(output_processor.php|submitOutput()) $result: ', $result); 	//DEBUG
	return $result;

} //END submitoutput

?>

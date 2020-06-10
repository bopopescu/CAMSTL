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

	$result = submitComPorts($dbconfig, trimRequest($_REQUEST));
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php");
}

/**
 * submitComPorts
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitComPorts($dbconfig, $request)
{
	debug('', $request);	//DEBUG

	$result = array("success" => 'false', "module" => "ComPorts", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$comports_result = array(); //store the success/failure state for each setting

	if(($comports_result['CPCom1Enable'] = ((isValidOnOff($request['CPCom1Enable'])) ? $dbconfig->setDbconfigData('ComPorts','Com1Enable', $request['CPCom1Enable']) : false)) && isOn($request['CPCom1Enable']))
	{
		$comports_result['CPCom1Dest'] = ((isValidNumber($request['CPCom1Dest']) && (($request['CPCom1Dest'] >= 0 && $request['CPCom1Dest'] <= 2))) ?
				$dbconfig->setDbconfigData('ComPorts','Com1Dest', $request['CPCom1Dest']) : false);

		$comports_result['CPCom1Port'] = ((isValidNumber($request['CPCom1Port']) && (($request['CPCom1Port'] >= 1 &&	$request['CPCom1Port'] <= 65000))) ?
				$dbconfig->setDbconfigData('ComPorts','Com1Port', $request['CPCom1Port']) : false);

		$comports_result['CPCom1Baud'] = ((isValidNumber($request['CPCom1Baud']) && (($request['CPCom1Baud'] >= 1200 &&	$request['CPCom1Baud'] <= 115200))) ?
				$dbconfig->setDbconfigData('ComPorts','Com1Baud', $request['CPCom1Baud']) : false);
	}

	if(($comports_result['CPCom2Enable'] = ((isValidOnOff($request['CPCom2Enable'])) ? $dbconfig->setDbconfigData('ComPorts','Com2Enable', $request['CPCom2Enable']) : false)) && isOn($request['CPCom2Enable']))
	{
		$comports_result['CPCom2Dest'] = ((isValidNumber($request['CPCom2Dest']) && (($request['CPCom2Dest'] >= 0 && $request['CPCom2Dest'] <= 2))) ?
				$dbconfig->setDbconfigData('ComPorts','Com2Dest', $request['CPCom2Dest']) : false);

		$comports_result['CPCom2Port'] = ((isValidNumber($request['CPCom2Port']) && (($request['CPCom2Port'] >= 1 &&	 $request['CPCom2Port'] <= 65000))) ?
				$dbconfig->setDbconfigData('ComPorts','Com2Port', $request['CPCom2Port']) : false);

		$comports_result['CPCom2Baud'] = ((isValidNumber($request['CPCom2Baud']) && (($request['CPCom2Baud'] >= 1200 &&	$request['CPCom2Baud'] <= 115200))) ?
				$dbconfig->setDbconfigData('ComPorts','Com2Baud', $request['CPCom2Baud']) : false);
	}

	debug('(comports_processor.php|submitComPorts()) $comports_result: ', $comports_result); 	//DEBUG

	// 1) find all the keys in the $comports_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($comports_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(comports_processor.php|submitComPorts()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

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

	debug('(comports_processor.php|submitComPorts()) $result: ', $result); 	//DEBUG
	return $result;

} //END submitComPorts

?>

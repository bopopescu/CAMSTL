<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/cell_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$dbconfig = new dbconfigController();
$cell_ctrl = new cellcontroller();


//Check form submission
if(!empty($_REQUEST))
{
	debug('=====================================================================================================');	//DEBUG
	debug('_REQUEST', $_REQUEST);	//DEBUG

	$result = submitInstallerSettings($dbconfig, $cell_ctrl, trimRequest($_REQUEST));
	header("location:http://".$_SERVER['HTTP_HOST']."/device/installersettings/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
	header("location:http://".$_SERVER['HTTP_HOST']."/device/installersettings/index.php");
}


/**
 * submitInstallerSettings
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitInstallerSettings($dbconfig, $cell_ctrl, $request)
{
	debug('', $request);	//DEBUG

	$result = array("success" => 'false', "module" => "InstallerSettings", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$installer_result = array();			//store the success/failure state for each setting
	$installer_result['RedStoneOwner'] = (isValidString($request['RedStoneOwner']) ? $dbconfig->setDbconfig('RedStone','Owner', $request['RedStoneOwner']) : false);

	//Time: > 0; 0 == off
	$installer_result['positionUpdateTime'] = (isValidNumber($request['positionUpdateTime']) ? $dbconfig->setDbconfigData('PositionUpdate','Time', $request['positionUpdateTime']) : false);

	//Accumulated Heading Change: 5 - 30; 0 == off
	$installer_result['positionHeading'] = ((isValidNumber($request['positionHeading']) && ($request['positionHeading'] == 0 || ($request['positionHeading'] >= 5 && $request['positionHeading'] <= 30))) ? $dbconfig->setDbconfigData('PositionUpdate','Heading', $request['positionHeading']) : false);

	//Notify On Stop/Start: On/Off
	$installer_result['positionReportStopStart'] = (isValidOnOff($request['positionReportStopStart']) ? $dbconfig->setDbconfigData('PositionUpdate', 'ReportStopStart', $request['positionReportStopStart']) : false);

	//Report When Stopped: On/Off
	$installer_result['positionReportWhenStopped'] = (isValidOnOff($request['positionReportWhenStopped']) ? $dbconfig->setDbconfigData('PositionUpdate', 'ReportWhenStopped', $request['positionReportWhenStopped']) : false);

	//Keep Awake: >= 0
	$installer_result['hardwareKeepAwake'] = (isValidNumber($request['hardwareKeepAwake']) ? $dbconfig->setDbconfigData('RedStone', 'KeepAwakeMinutes', $request['hardwareKeepAwake']) : false);

	//APN
	$installer_result['apn'] = (isValidString($request['apn']) ? $dbconfig->setDbconfigData('Cellular','carrier', htmlspecialchars(strip_tags($request['apn']))) : false);

	//CAMS: On/Off
	$installer_result['cams'] = (isValidOnOff($request['cams']) ? $dbconfig->setDbconfigData('feature', 'packetizer-cams', $request['cams']) : false);

	//CAMS Server Host & Port
	if(isOn($request['cams']))
	{
		if(isValidIP($request['camsHost']) || isValidDNS($request['camsHost']))
		{
			$installer_result['camsHost'] = $dbconfig->setDbconfigData('packetizer-cams', 'host', $request['camsHost']);
		}
		else
		{
			$installer_result['camsHost'] = false;
		}

		//CAMS Server Port
		$installer_result['camsPort'] = (isValidNumber($request['camsPort']) ? $dbconfig->setDbconfigData('packetizer-cams', 'port', $request['camsPort']) : false);
	}

	// 1) find all the keys in the $installer_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($installer_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(installer_settings_processor.php|submitInstallerSettings()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

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


	debug('(installer_settings_processor.php|submitInstallerSettings()) $result: ', $result); 	//DEBUG
	return $result;

} //END submitInstallerSettings

?>

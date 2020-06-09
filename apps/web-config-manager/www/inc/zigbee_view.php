<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/config.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';		//contains functions for socket interaction, error message display, and logging.


if(!empty($_GET))
{
	if(!empty($_GET['codes']))
	{
		foreach(explode(",",$_GET['codes']) as $key)
		{
			translateStatusCode($key);
		}
	}

	//highlight the errored fields
	if(!empty($_GET['fields']))
	{
		foreach(explode(",",$_GET['fields']) as $field)
		{
			highlightField($field);
		}
	}
}


//OBJECT INSTANTIATION
$dbconfig = new dbconfigController();

//VARIABLE INSTANTIATION
$link_key = '';
$app = 'zigbee';

$link_key_raw = $dbconfig->getDbconfigData($app, 'link-key');
$link_key = (!empty($link_key_raw) ? str_split($link_key_raw, 4) : "");

if(isset($_GET['zcontrol']) && $_GET['zcontrol'] !== false)
{
	$value = $_GET['zcontrol'];
	$enable = ((strcmp($value, "On") == 0));
}
else
{
	$value = $dbconfig->getDbconfig('feature', 'zigbee-monitor');
	$value = hex2bin(trim($value));
	debug("enable=", $value);
	$enable = ($value == 1);
}
// Display the value of any fields that failed to save. (they will be part of the url params)
// Name
if(!empty($_GET['linkKey']))
{
	$link_key = $_GET['linkKey'];
}

if(validateGetVariable('statereq_pri'))
{
	$statereq_pri = $_GET['statereq_pri'];
}
else
{
	$val = trim($dbconfig->getDbconfig($app, 'State_Request_Priority'));
	if($val != '')
	{
		$statereq_pri = getSliderValue($val);
	}
	else
	{
		$statereq_pri = Iridium_Slider_Value;
	}
}

if(validateGetVariable('SOSCancel_pri'))
{
	$SOSCancel_pri = $_GET['SOSCancel_pri'];
}
else
{
	$val = trim($dbconfig->getDbconfig($app, 'SOS_Cancel_Priority'));
	if($val != '')
	{
		$SOSCancel_pri = getSliderValue($val);
	}
	else
	{
		$SOSCancel_pri = Iridium_Slider_Value;
	}
}

if(validateGetVariable('SOS_pri'))
{
	$SOS_pri = $_GET['SOS_pri'];
}
else
{
	$val = trim($dbconfig->getDbconfig($app, 'SOS_Priority'));
	if($val != '')
	{
		$SOS_pri = getSliderValue($val);
	}
	else
	{
		$SOS_pri = Iridium_Slider_Value;
	}
}

if(validateGetVariable('ci_pri'))
{
	$ci_pri = $_GET['ci_pri'];
}
else
{
	$ci_pri = getSliderValue(trim($dbconfig->getDbconfig($app, 'Check_In_Priority')));
}

if(validateGetVariable('co_pri'))
{
	$co_pri = $_GET['co_pri'];
}
else
{
	$co_pri = getSliderValue(trim($dbconfig->getDbconfig($app, 'Check_Out_Priority')));
}


if(validateGetVariable('SLP_AllowOverdue'))
{
	$value = $_GET['SLP_AllowOverdue'];
	$SLP_AllowOverdue = ((strcmp($value, "1") == 0));
}
else
{
	$value = $dbconfig->getDbconfigData($app, 'overdueAllow');
	$SLP_AllowOverdue = (isValidOnOff($value) ? $value : '0');	
}

if(validateGetVariable('SLP_AllowExtensions'))
{
	$value = $_GET['SLP_AllowExtensions'];
	$SLP_AllowExtensions = ((strcmp($value, "1") == 0));
}
else
{
	$value = $dbconfig->getDbconfigData($app, 'timerExtensionAllow');
	$SLP_AllowExtensions = (isValidOnOff($value) ? $value : '0');	
}

if(isset($_GET['NotificationTime']) && $_GET['NotificationTime'] !== false)
{
	$notification_time = $_GET['NotificationTime'];
}
else
{
	$value = $dbconfig->getDbconfigData($app,'timerExpireMinutes');
	$notification_time = (isValidNumber($value) ? $value : '');
}

if(isset($_GET['HazardExtension']) && $_GET['HazardExtension'] !== false)
{
	$hazard_time = $_GET['HazardExtension'];
}
else
{
	$value = $dbconfig->getDbconfigData($app,'hazardExtensionMinutes');
	$hazard_time = (isValidNumber($value) ? $value : '');
}

if(isset($_GET['ShiftExtension']) && $_GET['ShiftExtension'] !== false)
{
	$shift_extension = $_GET['ShiftExtension'];
}
else
{
	$value = $dbconfig->getDbconfigData($app,'shiftExtensionMinutes');
	$shift_extension = (isValidNumber($value) ? $value : '');
}



function validateGetVariable($var)
{
	return (isset($_GET[$var]) && $_GET[$var] !== false);
}

?>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

define('WiFi_pri_Value', '255');
define('Cell_pri_Value', '20');
define('Iridium_pri_Value', '9');
define('Iridium_pri_Value_SOS', '1');
define('SOSMessageType', '19');

function getSliderValueNew($pri, $sosmessage)
{
  if ( $sosmessage == true )
      return Iridium_pri_Value_SOS;

  if($pri == 2 )
  {
    return Cell_pri_Value;
  }
  else if( $pri == 3 )
  {
    return Iridium_pri_Value;
  }

  return WiFi_pri_Value;
}

function updateInput($index, $mask, $p_dbconfig, $request)
{
	if (!(($mask>>($index - 1)) & 1))  // is Input being monitored?
		return;  // leave if it isn't

	debug("DRH updateInput index:", $index);
	debug("DRH gpiInputActive request ", $request['gpiInputActive'.$index]);
	
	if( strlen($request['gpiInputActive'.$index]))
	{
		$inputs_result['gpiInputActive'.$index] = isValidNumber( $request['gpiInputActive'.$index])?($p_dbconfig->setDbconfigData('i2c-gpio-monitor', 'input'.$index, $request['gpiInputActive'.$index])): false;
		$inputs_result['gpiInputDebounce'.$index] = isValidNumber( $request['gpiInputDebounce'.$index])?($p_dbconfig->setDbconfigData('i2c-gpio-monitor', 'inputDebounce'.$index, $request['gpiInputDebounce'.$index])):'0.75';		
	}
				
	if( strlen( $request['onmessage_type'.$index]))
		$inputs_result['onmessage_type'.$index] = isValidNumber( $request['onmessage_type'.$index])?($p_dbconfig->setDbconfigData('i2c-gpio-monitor', 'inputOnType'.$index, $request['onmessage_type'.$index])):false;

	if(($inputs_result['onmessage_type'.$index] == true) && $request['onmessage_type'.$index] != 0 )
	{
		$pri = getSliderValueNew($request['onpri'.$index],(($request['onmessage_type'.$index] == SOSMessageType)?true:false));
		$inputs_result['onpri'.$index] = isValidNumber( $request['onpri'.$index])?($p_dbconfig->setDbconfigData('i2c-gpio-monitor', 'inputOnPriority'.$index, $pri)):false;
	}

	if( strlen( $request['offmessage_type'.$index]))
		$inputs_result['offmessage_type'.$index] = isValidNumber( $request['offmessage_type'.$index])?($p_dbconfig->setDbconfigData('i2c-gpio-monitor', 'inputOffType'.$index, $request['offmessage_type'.$index])):false;
			
	if(($inputs_result['offmessage_type'.$index] == true) && $request['offmessage_type'.$index] != 0 )
	{
		$pri = getSliderValueNew($request['offpri'.$index],(($request['offmessage_type'.$index] == SOSMessageType)?true:false));
		$inputs_result['offpri'.$index] = isValidNumber( $request['offpri'.$index])?($p_dbconfig->setDbconfigData('i2c-gpio-monitor', 'inputOffPriority'.$index, $pri)):false;
	}
}
function updateSettings($p_dbconfig, $request)
{
  $result = array("success" => 'false', "module" => "inputs", "codes" => array(), "fields" => null, "getParams" => null);
  $inputs_result=array();

	$inputs_result['gpiMonitor'] = (isValidOnOff($request['gpiMonitor']) ? $p_dbconfig->setDbconfigData('feature', 'i2c-gpio-monitor', $request['gpiMonitor']) : false);

	if( $request['gpiMonitor']==1 && $inputs_result['gpiMonitor'] == true) 
	{
		if (!file_exists("/mnt/nvram/rom/hw/red-green-led"))
		{
			$mask=63;
		}
		else
		{
			$mask=15;
		}

		if(empty($request['gpiInputMonitor1']) && $request['gpiInputMonitor1'] == 0)
			$mask=$mask&62;
		if(empty($request['gpiInputMonitor2']))
			$mask=$mask&61;
		if(empty($request['gpiInputMonitor3']))
			$mask=$mask&59;
		if(empty($request['gpiInputMonitor4']))
			$mask=$mask&55;

		if (!file_exists("/mnt/nvram/rom/hw/red-green-led"))
		{
			if(empty($request['gpiInputMonitor5']))
				$mask=$mask&47;
			if(empty($request['gpiInputMonitor6']))
				$mask=$mask&31;
		}

		$inputs_result['mask'] = isValidNumber( $mask)?($p_dbconfig->setDbconfigData('i2c-gpio-monitor', 'inputMask', $mask)):false;

		updateInput(1, $mask, $p_dbconfig, $request);
		updateInput(2, $mask, $p_dbconfig, $request);
		updateInput(3, $mask, $p_dbconfig, $request);
		updateInput(4, $mask, $p_dbconfig, $request);
		if (!file_exists("/mnt/nvram/rom/hw/red-green-led"))
		{
			updateInput(5, $mask, $p_dbconfig, $request);
			updateInput(6, $mask, $p_dbconfig, $request);
		}
	}
  debug('(gpi_processor.php|updateSettings()) $inputs_result: ', $inputs_result); 	//DEBUG

	// 1) find all the keys in the $gpi_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($inputs_result, false, true);
	$result['fields'] = implode(',', $failed_results);
	debug('(gpi_processor.php|updtaSettings()) $result[\'fields\']: ', $result['fields']); 	//DEBUG

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

	debug('(gpi_processor.php|updateSettings()) $result: ', $result); 	//DEBUG
	return $result;
}

$dbconfig = new dbconfigController();

if(!empty($_REQUEST))
{
  debug('=========_REQUEST=============', $_REQUEST);

  $result = updateSettings($dbconfig, trimRequest($_REQUEST));
  header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
}
else
{
  header("location:http://".$_SERVER['HTTP_HOST']."/device/settings/index.php");
}



<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/inputs_controller.php';

debug('../inc/inputs-setting.inc entry');
//get page variable values
$dbconfig = new dbconfigController();
$inputs_ctrl = new inputs_controller();
$mask = $dbconfig->getDbconfigData('i2c-gpio-monitor', 'inputMask');

 

//-----------------------------------------------------
// SetInputMonitor - set the value of the input monitor dropdown based on the index value (up to 6 inputs)
function SetInputMonitor($index, $mask)
{
	$name='gpiInputMonitor'.$index;
	
 	if (isset($_GET[$name]) && ($_GET[$name] !== false))
	{
		$ret = 1;
	}
	else
	{
		$ret= (isValidNumber($mask)?((($mask)>>($index - 1)) & 1):'1');
	}
	return $ret;
}

//-----------------------------------------------------
// SetInputActive - set the value of the input active (hi/low) dropdown based on the index value (up to 6 inputs)
function SetInputActive($index, $dbconfig)
{
	$name='gpiInputActive'.$index;
 	if (isset($_GET[$name]) && ($_GET[$name] !== false))
	{
		$ret = $_GET[$name];
	}
	else
	{
		$key = 'input'.$index;
	 	$raw = $dbconfig->getDbconfigData('i2c-gpio-monitor', $key);
		$ret = (isValidNumber($raw) ? $raw: '0');
	}
	return $ret;
}

//-----------------------------------------------------
// SetInputDebounce - set the value of the input debounce (seconds) dropdown based on the index value (up to 6 inputs)
function SetInputDebounce($index, $dbconfig)
{
	$name='gpiInputDebounce'.$index;
	
 	if (isset($_GET[$name]) && ($_GET[$name] !== false))
	{
		$ret = $_GET[$name];
	}
	else
	{
		$key = 'inputDebounce'.$index;
	 	$raw = $dbconfig->getDbconfigData('i2c-gpio-monitor', $key);
		$ret = (isValidNumber($raw) ? $raw: '0');
	}
	return $ret;
}

//-----------------------------------------------------
// SetInputONMsg - set the input on message type
// defaults to sensor (10)
function SetInputONMsg($index, $dbconfig)
{
	if(isset($_GET['onmessage_type'.$index]) && $_GET['onmessage_type'.$index] !== false)
	{
		$ret= $_GET['onmessage_type'.$index];
	}
	else
	{
	 	$raw= $dbconfig->getDbconfigData('i2c-gpio-monitor', 'inputOnType'.$index);
		$ret = (isValidNumber($raw) ? $raw: '10');
	}
	return $ret;
}
//-----------------------------------------------------
// SetInputOFFMsg - set the input on message type
// defaults to sensor (10)
function SetInputOFFMsg($index, $dbconfig)
{
	if(isset($_GET['offmessage_type'.$index]) && $_GET['offmessage_type'.$index] !== false)
	{
		$ret= $_GET['offmessage_type'.$index];
	}
	else
	{
	 	$raw= $dbconfig->getDbconfigData('i2c-gpio-monitor', 'inputOffType'.$index);
		$ret = (isValidNumber($raw) ? $raw: '10');
	}
	return $ret;
}

//-----------------------------------------------------
// SetInputONPriority - set the input on message type
// defaults to sensor (10)
function SetInputONPriority($index, $dbconfig)
{
	if(isset($_GET['onpri'.$index]) && $_GET['onpri'.$index] !== false)
	{
		$ret= $_GET['onpri'.$index];
	}
	else
	{
 		$raw= trim($dbconfig->getDbconfigData('i2c-gpio-monitor', 'inputOnPriority'.$index));
  	$ret = getSliderValueNew(isValidNumber($raw) ? $raw: '20');
	}
	return $ret;
}

//-----------------------------------------------------
// SetInputOFFPriority - set the input on message type
// defaults to sensor (10)
function SetInputOFFPriority($index, $dbconfig)
{
	if(isset($_GET['offpri'.$index]) && $_GET['offpri'.$index] !== false)
	{
		$ret= $_GET['offpri'.$index];
	}
	else
	{
 		$raw= trim($dbconfig->getDbconfigData('i2c-gpio-monitor', 'inputOffPriority'.$index));
  	$ret = getSliderValueNew(isValidNumber($raw) ? $raw: '20');
	}
	return $ret;
}


//-----------------------------------------------------
// Is i2c-gpio-monitor on?
if(isset($_GET['gpiMonitor']) && $_GET['gpiMonitor'] !== false)
{
	$value = $_GET['gpiMonitor'];
	$enable = ((strcmp($value, "On") == 0));
}
else
{
	$value = $dbconfig->getDbconfig('feature', 'i2c-gpio-monitor');
	$value = hex2bin(trim($value));
	$enable = ($value == 1)?1:0;
}

/* ===== INPUT TAB ====== */

for ($i=1; $i <= 6; $i++)
{
	$val = gpiInputMonitor.$i;	$$val = SetInputMonitor($i,$mask); 
	$val = gpiInputActive.$i;	  $$val = SetInputActive($i, $dbconfig);
	$val = gpiInputDebounce.$i;	$$val = SetInputDebounce($i, $dbconfig);
	$val = onmessage_type.$i;		$$val = SetInputONMsg($i, $dbconfig);
	$val = offmessage_type.$i;	$$val = SetInputOFFMsg($i, $dbconfig);
	$val = onpri.$i;						$$val = SetInputONPriority($i, $dbconfig);
	$val = offpri.$i;						$$val = SetInputOFFPriority($i, $dbconfig);
}

$messageTypes = $inputs_ctrl->getMessagesType();

?>

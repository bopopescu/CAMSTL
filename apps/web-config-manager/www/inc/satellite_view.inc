<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/network_controller.inc'; //network (ethernet, wireless) controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dhcp_controller.inc';	//dhcp controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/db_sqlite3.inc';		//contains functions for db interaction

if(!empty($_GET))
{
	if(!empty($_GET['codes']))
	{
		foreach(explode(",",$_GET['codes']) as $key)
		{
			translateStatusCode($key, $_GET['module']);
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
$nt_ctrl = new networkcontroller();
$dhcp_ctrl = new dhcpcontroller();
$dbconfig = new dbconfigController();

//VARIABLE INSTANTIATION
//wifi
$satellite_enable_status = $IridiumUpdateInterval = $IridiumTimeout = $ModbusReportingInterval = '';


// Satellite enable status (whether Iridium is being used - runs iridium-monitor)
if(isset($_GET['IridiumEnableCtl']) && $_GET['IridiumEnableCtl'] !== false)
{
	$IridiumEnable = $_GET['IridiumEnableCtl'];
}
else
{
	$IridiumEnableRaw = $dbconfig->getDbconfigData('feature', 'iridium-monitor');
	$IridiumEnable = (isValidOnOff($IridiumEnableRaw) ? $IridiumEnableRaw : '0');
}

// Iridium update interval (how often the iridium sends a position update
if(isset($_GET['IridiumUpdateIntervalCtl']) && $_GET['IridiumUpdateIntervalCtl'] !== false)
{
	$IridiumUpdateInterval = $_GET['IridiumUpdateIntervalCtl'];
}
else
{
	$IridiumUpdateIntervalraw = $dbconfig->getDbconfigData('PositionUpdate', 'IridiumReportTime');
	$IridiumUpdateInterval = (isValidNumber($IridiumUpdateIntervalraw) ? $IridiumUpdateIntervalraw : '');  //720
}

// Iridium data limit
if(isset($_GET['IridiumDataLimit']) && $_GET['IrdiumDataLimit'] !== false)
{
	$iridiumDataLimit = $_GET['IridiumDataLimit'];
}
else
{
	$iridiumDataLimit = $dbconfig->getDbconfigData('Iridium', 'byteLimit');
}

// Iridium data limit interval
if(isset($_GET['IridiumDataLimitTimeout']) && $_GET['IridiumDataLimitTimeout'] !== false)
{
	$iridiumDataLimitTimeout = $_GET['IridiumDataLimitTimeout'];
}
else
{
	$iridiumDataLimitTimeout = $dbconfig->getDbconfigData('Iridium', 'LimitTimePeriod');
}

if(isset($_GET['ModbusReportingIntervalCtl']) && $_GET['ModbusReportingIntervalCtl'] !== false)
{
	$ModbusReportingInterval = $_GET['ModbusReportingIntervalCtl'];
}
else
{
	$ModbusReportingIntervalraw = $dbconfig->getDbconfigData('modbus', 'periodic_overiridium_seconds');
	$ModbusReportingIntervalraw = (isValidNumber($ModbusReportingIntervalraw) ? $ModbusReportingIntervalraw/60 : ''); //60
	$ModbusReportingInterval = (isValidNumber($ModbusReportingIntervalraw) ? $ModbusReportingIntervalraw : ''); //60
}

if(isset($_GET['IridiumEnable']) && $_GET['IridiumEnable'] !== false)
{
	$iridiumEnable = $_GET['IridiumEnable'];
}
else
{
	$iridiumEnable_raw = $dbconfig->getDbconfigData('packetizer-cams', 'IridiumEnable');
	$iridiumEnable = (isValidOnOff($iridiumEnable_raw) ? $iridiumEnable_raw : '');
}

if(isset($_GET['CellFailMode']) && $_GET['CellFailMode'] !== false)
{
	$cellFailMode = $_GET['CellFailMode'];
}
else
{
	$cellFailMode_raw = $dbconfig->getDbconfigData('packetizer-cams', 'CellFailModeEnable');
	$cellFailMode = (isValidOnOff($cellFailMode_raw) ? $cellFailMode_raw : '');
}

if(isset($_GET['camsIridiumTimeout']) && $_GET['camsIridiumTimeout'] !== false)
{
	$camsIridiumTimeout = $_GET['camsIridiumTimeout'];
}
else
{
	$camsIridiumTimeout = $dbconfig->getDbconfigData('packetizer-cams', 'iridium_timeout');
}

if(isset($_GET['camsIridiumDataLimitPriority']) && $_GET['camsIridiumDataLimitPriority'] !== false)
{
	$camsIridiumDataLimitPriority = $_GET['camsIridiumDataLimitPriority'];
}
else
{
	$camsIridiumDataLimitPriority = $dbconfig->getDbconfigData('packetizer-cams', 'IridiumDataLimitPriority');
}

if(isset($_GET['IridiumPri']))
{
	$cams_IridiumPri = $_GET['IridiumPri'];
}
else
{
	$cams_IridiumPri = $dbconfig->getDbconfigData('packetizer-cams', 'IridiumPriorityLevel');
}

if(isset($_GET['camsRetries']))
{
	$camsRetryLimit = $_GET['camsRetries'];
}
else
{
	$camsRetryLimit = $dbconfig->getDbconfigData('packetizer-cams', 'retry_limit');
}
?>

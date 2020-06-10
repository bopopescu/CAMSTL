<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/cell_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$dbconfig = new dbconfigController();
$cell_ctrl = new cellcontroller();

//VARIABLE INSTANTIATION
$posup_time = $posup_heading = $posup_report_start_stop = $posup_report_when_stopped =  '';
$cell_apn = '';
$hw_disable_sleep = $hw_ignition_src = $hw_speed_src = $hw_keep_awake = '';
$cams_status = $cams_host = $cams_port = $RedStoneOwner = '';
$cellIP = '';

// set the cell rssi value
$cell_rssi_raw = $cell_ctrl->getRssi();
$cellRSSI = (is_numeric($cell_rssi_raw) ? $cell_rssi_raw.' dBm' : 'unknown');

if(!empty($_GET) && $_GET['op'] == 'RSSIupdate')
{
	echo trim($cellRSSI);
}
else if(!empty($_GET) && !empty($_GET['codes']))
{
	foreach(explode(",",$_GET['codes']) as $key)
	{	
		translateStatusCode($key, $_GET['module']);
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



if(isset($_GET['positionUpdateTime']) && $_GET['positionUpdateTime'] !== false)
{
	$posup_time = $_GET['positionUpdateTime'];
}
else
{
	$posup_time_raw = $dbconfig->getDbconfigData('PositionUpdate','Time');
	$posup_time = (isValidNumber($posup_time_raw) ? $posup_time_raw : '');
}


if(isset($_GET['positionHeading']) && $_GET['positionHeading'] !== false)
{
	$posup_heading = $_GET['positionHeading'];
}
else
{
	$posup_heading_raw = $dbconfig->getDbconfigData('PositionUpdate','Heading');
	$posup_heading = (isValidNumber($posup_heading_raw) ? $posup_heading_raw : '');
}

if(isset($_GET['positionReportStopStart']) && $_GET['positionReportStopStart'] !== false)
{
	$posup_report_start_stop = $_GET['positionReportStopStart'];
}
else
{
	$posup_report_start_stop_raw = $dbconfig->getDbconfigData('PositionUpdate','ReportStopStart');
	$posup_report_start_stop = (isValidOnOff($posup_report_start_stop_raw) ? $posup_report_start_stop_raw : '');
}

if(isset($_GET['positionReportWhenStopped']) && $_GET['positionReportWhenStopped'] !== false)
{
	$posup_report_when_stopped = $_GET['positionReportWhenStopped'];
}
else
{
	$posup_report_when_stopped_raw = $dbconfig->getDbconfigData('PositionUpdate','ReportWhenStopped');
	$posup_report_when_stopped = (isValidOnOff($posup_report_when_stopped_raw) ? $posup_report_when_stopped_raw : '');
}

if(isset($_GET['hardwareDisableSleep']) && $_GET['hardwareDisableSleep'] !== false)
{
	$hw_disable_sleep = $_GET['hardwareDisableSleep'];
}
else
{
	$hw_disable_sleep_raw = $dbconfig->getDbconfigData('RedStone','DisableSleep');
	$hw_disable_sleep = (isValidOnOff($hw_disable_sleep_raw) ? $hw_disable_sleep_raw : '');
}


if(isset($_GET['hardwareKeepAwake']) && $_GET['hardwareKeepAwake'] !== false)
{
	$hw_keep_awake = $_GET['hardwareKeepAwake'];
}
else
{
	$hw_keep_awake_raw = $dbconfig->getDbconfigData('RedStone','KeepAwakeMinutes');
	$hw_keep_awake = (isValidNumber($hw_keep_awake_raw) ? $hw_keep_awake_raw : '');
}

if(isset($_GET['apn']))
{
	$cell_apn = $_GET['apn'];
}
else
{
	//$cell_apn_raw = $cell_ctrl->getApn();
	$cell_apn_raw = $dbconfig->getDbconfigData('Cellular','carrier');
	$cell_apn = (isValidString($cell_apn_raw) ? $cell_apn_raw : '');
}


if(isset($_GET['cams']) && $_GET['cams'] !== false)
{
	$cams_status = $_GET['cams'];
}
else
{
	$cams_status_raw = $dbconfig->getDbconfigData('feature','packetizer-cams');
	$cams_status = (isValidOnOff($cams_status_raw) ? $cams_status_raw : '');
}

if(!empty($_GET['camsHost']))
{
	$cams_host = $_GET['camsHost'];
}
else
{
	$cams_host_raw = $dbconfig->getDbconfigData('packetizer-cams', 'host');
	$cams_host = ((isValidIP($cams_host_raw) || isValidString($cams_host_raw)) ? $cams_host_raw : '');
}

if(isset($_GET['camsPort']) && $_GET['camsPort'] !== false)
{
	$cams_port = $_GET['camsPort'];
}
else
{
	$cams_port_raw = $dbconfig->getDbconfigData('packetizer-cams', 'port');
	$cams_port = (isValidNumber($cams_port_raw) ? $cams_port_raw : '');
}

if(!empty($_GET['RedStoneOwner']))
{
	$RedStoneOwner = $_GET['RedStoneOwner'];
}
else
{
	$RedStoneOwner_raw = $dbconfig->getDbconfigData('RedStone', 'Owner');
	$RedStoneOwner = ((isValidIP($RedStoneOwner_raw) || isValidString($RedStoneOwner_raw)) ? $RedStoneOwner_raw : '');
}
$cellIP = atsexec(escapeshellcmd("CellIP"));
$gpsSats = atsexec(escapeshellcmd("getGPSSats"));

?>

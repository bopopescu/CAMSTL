<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';
 	require_once $_SERVER['DOCUMENT_ROOT'].'inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$comports_ctrl = new dbconfigController();

//VARIABLE INSTANTIATION
$cp_EnableCom1 = 
$cp_DestinationCom1 =
$cp_PortCom1 =
$cp_BaudCom1 = " ";

if(!empty($_GET) && !empty($_GET['codes']) && $_GET['module'] == 'ComPorts' )
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
else if(!empty($_GET) && $_GET['success'] == 'true' && $_GET['module'] == 'ComPorts')
{
	display_msg('Successfully saved Com Ports settings.','success');
	display_msg('The new settings will only take effect after the device has been rebooted.','Warning');
	display_msg('Existing values will not be altered.','Warning');
	unset($_GET['success']);
}
else if(!empty($_GET) && $_GET['success'] == 'false' && $_GET['module'] == 'ComPorts')
{
	display_msg('Failed to save Com Ports settings.','fail');
	unset($_GET);
}


//READ ComPorts Update values
$cp_Com1Enable = $comports_ctrl->getDbconfigData('ComPorts', 'Com1Enable');
$cp_Com1Dest = $comports_ctrl->getDbconfigData('ComPorts', 'Com1Dest');
$cp_Com1Port = $comports_ctrl->getDbconfigData('ComPorts', 'Com1Port');
$cp_Com1Baud =  $comports_ctrl->getDbconfigData('ComPorts', 'Com1Baud');
$cp_Com2Enable = $comports_ctrl->getDbconfigData('ComPorts', 'Com2Enable');
$cp_Com2Dest = $comports_ctrl->getDbconfigData('ComPorts', 'Com2Dest');
$cp_Com2Port = $comports_ctrl->getDbconfigData('ComPorts', 'Com2Port');
$cp_Com2Baud =  $comports_ctrl->getDbconfigData('ComPorts', 'Com2Baud');

//if settings cannot be read from the device; display an error
if (
$cp_EnableCom1  === false ||
$cp_DestinationCom1  === false ||
$cp_PortCom1  === false ||
$cp_BaudCom1  === false ||
$cp_EnableCom2  === false ||
$cp_DestinationCom2  === false ||
$cp_PortCom2  === false ||
$cp_BaudCom2  === false )
	translateStatusCode('502', $_GET['module']);


?>


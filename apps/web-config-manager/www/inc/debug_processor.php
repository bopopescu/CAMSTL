<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc'; //validate session
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/db_sqlite3.inc';	//contains functions for db interaction
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';			//contains functions for socket interaction, error message display, and logging.
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';			//contains functions for getting, settings dbconfig parameters.

//OBJECT INSTANTIATION
$DebugCtrl = new dbconfigController();

//Check form submission
if(!empty($_REQUEST))
{
	$result = submitDebug($DebugCtrl, $_REQUEST);
	header("location:https://".$_SERVER['HTTP_HOST']."/support/debug.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields']);
}
else
{
	header("location:https://".$_SERVER['HTTP_HOST']."/support/debug.php");
}

function submitDebug($dbconfig, $request)
{
	$result = array("success" => 'false', "module" => "Hardware", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$debug_result = array();			//store the success/failure state for each setting

	//Disable email logging: On/Off
	$debug_result['ctlDebugEmail'] = (isValidOnOff($request['ctlDebugEmail']) ? $dbconfig->setDbconfigData('system', 'remote-logging', $request['ctlDebugEmail']) : false);
}
?>

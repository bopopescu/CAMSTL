<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/network_controller.inc'; //network (ethernet, wireless) controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/wifi_controller.inc';	//wifi (AP + client) controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dhcp_controller.inc';	//dhcp controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/db_sqlite3.inc';		//contains functions for db interaction

$dbconfig = new dbconfigController();

	if(isValidOnOff(isset($_REQUEST['wifi-client-enable']) ? $_REQUEST['wifi-client-enable'] : false))
	{
		$dbconfig->setDbconfigData('feature', 'wifi-monitor', isOn($_REQUEST['wifi-client-enable']) ? 1 : 0);
	}

	$ssid = '';

	if(isset($_GET['updateSSID']))
	{
		$ssid = bin2hex($_GET[updateSSID]);
	}

	if(isset($_GET['delete']) && "1" == $_GET['delete'])
	{
		$cmd = "printf 'unsetdb username=$ssid\\x0d'|socat - unix-connect:/var/run/redstone/wifi-monitor";
		shell_exec($cmd);
	}
	else
	{
		if(isset($_GET['oldSSID']))
		{
			$oldSSID = bin2hex($_GET['oldSSID']);
			$cmd = "printf 'unsetdb username=$oldSSID\\x0d'|socat - unix-connect:/var/run/redstone/wifi-monitor";
			shell_exec($cmd);
		}

		$pwd = '';

		if(isset($_GET['updatePass']))
		{
			$pwd = bin2hex($_GET[updatePass]);
		}

		$cmd = "printf 'updatedb username=\"$ssid\" password=\"$pwd\"\\x0d'|socat - unix-connect:/var/run/redstone/wifi-monitor";
		shell_exec($cmd);
	}

	header( 'Location: /network/wifi/index.php');
?>

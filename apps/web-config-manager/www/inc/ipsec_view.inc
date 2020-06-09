<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/inc/ipsec_controller.inc';	//ipsec controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/db_sqlite3.inc';		//contains functions for db interaction

//OBJECT INSTANTIATION
$ipsec_ctrl = new ipseccontroller();

//VARIABLE INSTANTIATION
$ipsec = '';
	
if(!empty($_GET) && !empty($_GET['codes']))
{
	foreach(explode(",",$_GET['codes']) as $key)
	{	
		translateStatusCode($key, $_GET['module']);
	}
}
else if(!empty($_GET) && $_GET['success'] == 'true')
{
	display_msg('Successfully saved IPSec settings.','success');
	display_msg('It may take upto 1 minute for the ipsec settings to take effect. Please <a href="#" class="page_refresh">refresh</a> this page in a minute.','warning');
	unset($_GET['success']);
}
else if(!empty($_GET) && $_GET['success'] == 'false')
{
	display_msg('Failed to save IPSec settings.','fail');
	unset($_GET['success']);
}

$ipsec_ctrl->ipsecDBSanityCheck();

//READ IPSEC Status
$active_ipsec = $ipsec_ctrl->getActiveIpsecPolicies();

//READ VPN IPSEC
$ipsec = $ipsec_ctrl->getIpSec();


?>

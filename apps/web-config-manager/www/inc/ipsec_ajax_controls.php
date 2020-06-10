<?php 
/**
 * This script handles the connection/disconnection of ipsec policies and is called by an ajax script
 * @author - Sean Toscano (sean@absolutetrac.com)
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/ipsec_controller.inc';	
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$ipsec_ctrl = new ipseccontroller();

//VARIABLE INSTANTIATION
$ipsec = '';

if(!empty($_GET) && $_GET['op'] == 'ipsecconnect' && !empty($_GET['policy']))
{
	$ipsec_ctrl->ipsecConnect($_GET['policy']);
	
	$start_time = time();
	
	//periodically,every 5 seconds, check whether the policy has connected
	while($ipsec_ctrl->getIpsecStatus($_GET['policy']) === false && time() < ($start_time + 120))
	{
		sleep(5);			
	}
	
	if($ipsec_ctrl->getIpsecStatus($_GET['policy']) !== false)
		vpnXML(array("type" => "ipsec", "policy" => $_GET['policy'], "status" => "connected"));
	else
		debug('(ipsec_ajax_controls.php) Failed to connect ipsec policy: '.$_GET['policy']); 		//DEBUG
}
else if(!empty($_GET) && $_GET['op'] == 'ipsecdisconnect' && !empty($_GET['policy']))
{
	$ipsec_ctrl->ipsecDisconnect($_GET['policy']);
	
	$start_time = time();
	
	//periodically,every 5 seconds, check whether the policy has disconnected	
	while($ipsec_ctrl->getIpsecStatus($_GET['policy']) === true && time() < ($start_time + 120))
	{
		sleep(5);			
	}
	
	if($ipsec_ctrl->getIpsecStatus($_GET['policy']) !== true)
		vpnXML(array("type" => "ipsec", "policy" => $_GET['policy'], "status" => "disconnected"));
	else
		debug('(ipsec_ajax_controls.php) Failed to disconnect ipsec policy: '.$_GET['policy']); 		//DEBUG
}

/**
 * vpnXML
 * 
 * Create an xml tree with the status of the vpn policy to send back to the client's browser via the ajax response 
 * @param array $vpn
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function vpnXML($vpn)
{
	$xml = new SimpleXMLElement('<vpn/>');
	array_walk_recursive(array_flip($vpn), array ($xml, 'addChild'));
	
	debug('(ipsec_ajax_controls.php|vpnXML()) $xml', $xml); 		//DEBUG
	
	print $xml->asXML();
} //END vpnXML


?>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/ipsec_controller.inc';	//ipsec controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/db_sqlite3.inc';		//contains functions for db interaction

//OBJECT INSTANTIATION
$ipsec_ctrl = new ipseccontroller();

//VARIABLE INSTANTIATION

//if a filled form has been submitted
if(!empty($_REQUEST) && $_REQUEST['vpn'] == 'ipsec')
{
	//debug('_REQUEST', $_REQUEST);	//DEBUG
	debug('(ipsec_processor.php) $_REQUEST param:', $_REQUEST); 	//DEBUG

	//Vpn IPSec form submission
	$result = submitIPSec($ipsec_ctrl, $_REQUEST);

	header("location:https://".$_SERVER['HTTP_HOST']."/network/vpn/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes']));

	/*
	if($result === true)			//ipsec form successfully processed
	{
		header("location:https://".$_SERVER['HTTP_HOST']."/network/vpn/index.php?success=true");
	}
	else if($result === false)		//ipsec form processing failed
	{
		header("location:https://".$_SERVER['HTTP_HOST']."/network/vpn/index.php?success=false");
	}
	else
	{
		header("location:https://".$_SERVER['HTTP_HOST']."/network/vpn/index.php");
	}*/
}
else
{
	header("location:https://".$_SERVER['HTTP_HOST']."/network/vpn/index.php");
}


/**
 * submitIPSec
 * Parse the response from the ipsec form submit and pass it to the ipseccontroller to write to db and device (ipsec conf)
 *
 * @param object $ipsec_ctrl ipsec controller object
 * @param array $request - the _REQUEST variable that contains the form submission data
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function submitIPSec($ipsec_ctrl,$request)
{
	//debug('(ipsec_processor.php) $_REQUEST param:', $_REQUEST); 	//DEBUG

	$ipsec = array();
	$ipsec['policy'] = (isset($request['ipsecPolicyName']) && $request['ipsecPolicyName'] != '') ? trim($request['ipsecPolicyName']) : 'ipsec policy';
	$ipsec['protocol'] = 1;	//1 = ESP (default), 0 = AH

	//IPSEC MODE
	switch ($request['ipsecmode'])
	{
		case 'Tunnel':
			$ipsec['mode'] = 1;
			break;
		case 'Transport':
			$ipsec['mode'] = 0;
			break;
		default:
			$ipsec['mode'] = 1;
	}

	//get remote endpoint type
	$ipsec['remote_ep_type'] = $request['ipsecEPtype'];
	$ipsec['vpnmode'] = $request['vpnMode'];

	//remote endpoint ip
	$remote_ep_ip = '';
	if( (isset($request['ipsecREMSIP1']) && $request['ipsecREMSIP1'] != '') &&
			(isset($request['ipsecREMSIP2']) && $request['ipsecREMSIP2'] != '') &&
			(isset($request['ipsecREMSIP3']) && $request['ipsecREMSIP3'] != '') &&
			(isset($request['ipsecREMSIP4']) && $request['ipsecREMSIP4'] != ''))
	{
		$remote_ep_ip = $request['ipsecREMSIP1'].'.'.$request['ipsecREMSIP2'].'.'.$request['ipsecREMSIP3'].'.'.$request['ipsecREMSIP4'];
	}

	//parse remote endpoint type
	switch($ipsec['remote_ep_type'])
	{
		case 'FQDN':
			$ipsec['remote_ep_val'] = $request['ipsecREMfqdn'];
			break;
		case 'IP':
			$ipsec['remote_ep_val'] = $remote_ep_ip;
			break;
		default:
			$ipsec['remote_ep_val'] = ''; 	//ask Lee what a default FQDN value should be and add here
	}

	//Parse Local Network Type
	switch($request['ipsecLOCtype'])
	{
		case 'Any':
			$ipsec['local_type'] = 0;
			break;
		case 'Single':
			$ipsec['local_type'] = 1;
			break;
		case 'Range':
			$ipsec['local_type'] = 2;
			break;
		case 'Subnet':
			$ipsec['local_type'] = 3;
			break;
		default:
			$ipsec['local_type'] = 0;
	}

	//Local Start IP
	$ipsec['local_sip'] = '';
	if( (isset($request['ipsecLSIP1']) && $request['ipsecLSIP1'] != '') &&
			(isset($request['ipsecLSIP2']) && $request['ipsecLSIP2'] != '') &&
			(isset($request['ipsecLSIP3']) && $request['ipsecLSIP3'] != '') &&
			(isset($request['ipsecLSIP4']) && $request['ipsecLSIP4'] != ''))
	{
		$ipsec['local_sip'] = $request['ipsecLSIP1'].'.'.$request['ipsecLSIP2'].'.'.$request['ipsecLSIP3'].'.'.$request['ipsecLSIP4'];
	}

	//Local End IP
	$ipsec['local_eip'] = '';
	if( (isset($request['ipsecLEIP1']) && $request['ipsecLEIP1'] != '') &&
			(isset($request['ipsecLEIP2']) && $request['ipsecLEIP2'] != '') &&
			(isset($request['ipsecLEIP3']) && $request['ipsecLEIP3'] != '') &&
			(isset($request['ipsecLEIP4']) && $request['ipsecLEIP4'] != ''))
	{
		$ipsec['local_eip'] = $request['ipsecLEIP1'].'.'.$request['ipsecLEIP2'].'.'.$request['ipsecLEIP3'].'.'.$request['ipsecLEIP4'];
	}

	//Local Subnet Mask
	$ipsec['local_subnet'] = '';
	if( (isset($request['ipsecLIPSN1']) && $request['ipsecLIPSN1'] != '') &&
			(isset($request['ipsecLIPSN2']) && $request['ipsecLIPSN2'] != '') &&
			(isset($request['ipsecLIPSN3']) && $request['ipsecLIPSN3'] != '') &&
			(isset($request['ipsecLIPSN4']) && $request['ipsecLIPSN4'] != ''))
	{
		$ipsec['local_subnet'] = $request['ipsecLIPSN1'].'.'.$request['ipsecLIPSN2'].'.'.$request['ipsecLIPSN3'].'.'.$request['ipsecLIPSN4'];
	}

	//Remote Network Type
	switch($request['ipsecREMtype'])
	{
		case 'Any':
			$ipsec['remote_type'] = 0;
			break;
		case 'Single':
			$ipsec['remote_type'] = 1;
			break;
		case 'Range':
			$ipsec['remote_type'] = 2;
			break;
		case 'Subnet':
			$ipsec['remote_type'] = 3;
			break;
		default:
			$ipsec['remote_type'] = 0;
	}

	//Remote Start IP
	$ipsec['remote_sip'] = '';
	if( (isset($request['ipsecRSIP1']) && $request['ipsecRSIP1'] != '') &&
			(isset($request['ipsecRSIP2']) && $request['ipsecRSIP2'] != '') &&
			(isset($request['ipsecRSIP3']) && $request['ipsecRSIP3'] != '') &&
			(isset($request['ipsecRSIP4']) && $request['ipsecRSIP4'] != ''))
	{
		$ipsec['remote_sip'] = $request['ipsecRSIP1'].'.'.$request['ipsecRSIP2'].'.'.$request['ipsecRSIP3'].'.'.$request['ipsecRSIP4'];
	}

	//Remote End IP
	$ipsec['remote_eip'] = '';
	if( (isset($request['ipsecREIP1']) && $request['ipsecREIP1'] != '') &&
			(isset($request['ipsecREIP2']) && $request['ipsecREIP2'] != '') &&
			(isset($request['ipsecREIP3']) && $request['ipsecREIP3'] != '') &&
			(isset($request['ipsecREIP4']) && $request['ipsecREIP4'] != ''))
	{
		$ipsec['remote_eip'] = $request['ipsecREIP1'].'.'.$request['ipsecREIP2'].'.'.$request['ipsecREIP3'].'.'.$request['ipsecREIP4'];
	}

	//Remote Subnet Mask
	$ipsec['remote_subnet'] = '';
	if( (isset($request['ipsecRIPSN1']) && $request['ipsecRIPSN1'] != '') &&
			(isset($request['ipsecRIPSN2']) && $request['ipsecRIPSN2'] != '') &&
			(isset($request['ipsecRIPSN3']) && $request['ipsecRIPSN3'] != '') &&
			(isset($request['ipsecRIPSN4']) && $request['ipsecRIPSN4'] != ''))
	{
		$ipsec['remote_subnet'] = $request['ipsecRIPSN1'].'.'.$request['ipsecRIPSN2'].'.'.$request['ipsecRIPSN3'].'.'.$request['ipsecRIPSN4'];
	}

	//Keep Alive
	switch($request['ipseckeepAlive'])
	{
		case 'On':
			$ipsec['keep_alive'] = 1;
			break;
		case 'Off':
			$ipsec['keep_alive'] = 0;
			break;
		default:
			$ipsec['keep_alive'] = 1;
	}

	//Exchange mode
	switch($request['ipsecexchangeMode'])
	{
		case 'Aggressive':
			$ipsec['exchange_mode'] = 1;
			break;
		case 'Main':
			$ipsec['exchange_mode'] = 0;
			break;
		default:
			$ipsec['exchange_mode'] = 1;
	}

	//NAT
	switch($request['ipsecnatTrav'])
	{
		case 'On':
			$ipsec['nat'] = 1;
			break;
		case 'Off':
			$ipsec['nat'] = 0;
			break;
		default:
			$ipsec['nat'] = 1;
	}

	$ipsec['nat_ka_time'] = (isset($request['ipsecNatFreq']) ? $request['ipsecNatFreq'] : '');

	//Local ID
	$ipsec['local_id_type'] = (isset($request['ipsecLIDT']) ? $request['ipsecLIDT'] : '');
	$local_id_fqdn_email = (isset($request['ipseclocalID']) ? $request['ipseclocalID'] : '');
	$local_id_ip = '';
	if( (isset($request['ipsecLWANIP1']) && $request['ipsecLWANIP1'] != '') &&
			(isset($request['ipsecLWANIP2']) && $request['ipsecLWANIP2'] != '') &&
			(isset($request['ipsecLWANIP3']) && $request['ipsecLWANIP3'] != '') &&
			(isset($request['ipsecLWANIP4']) && $request['ipsecLWANIP4'] != ''))
	{
		$local_id_ip = $request['ipsecLWANIP1'].'.'.$request['ipsecLWANIP2'].'.'.$request['ipsecLWANIP3'].'.'.$request['ipsecLWANIP4'];
	}

	switch($ipsec['local_id_type'])
	{
		case 'FQDN':
			$ipsec['local_id_val'] = $local_id_fqdn_email;
			break;
		case 'Email':
			$ipsec['local_id_val'] = $local_id_fqdn_email;
			break;
		case 'IP':
			$ipsec['local_id_val'] = $local_id_ip;
			break;
		default:
			$ipsec['local_id_val'] = ''; 	//ask Lee what a default FQDN value should be and add here
	}

	//Remote ID
	$ipsec['remote_id_type'] = (isset($request['ipsecRIT']) ? $request['ipsecRIT'] : '');
	$remote_id_fqdn = (isset($request['ipsecremoteID']) ? $request['ipsecremoteID'] : '');
	$remote_id_ip = '';
	if( (isset($request['ipsecRWANIP1']) && $request['ipsecRWANIP1'] != '') &&
			(isset($request['ipsecRWANIP2']) && $request['ipsecRWANIP2'] != '') &&
			(isset($request['ipsecRWANIP3']) && $request['ipsecRWANIP3'] != '') &&
			(isset($request['ipsecRWANIP4']) && $request['ipsecRWANIP4'] != ''))
	{
		$remote_id_ip = $request['ipsecRWANIP1'].'.'.$request['ipsecRWANIP2'].'.'.$request['ipsecRWANIP3'].'.'.$request['ipsecRWANIP4'];
	}

	switch($ipsec['remote_id_type'])
	{
		case 'FQDN':
			$ipsec['remote_id_val'] = $remote_id_fqdn;
			break;
		case 'IP':
			$ipsec['remote_id_val'] = $remote_id_ip;
			break;
		default:
			$ipsec['remote_id_val'] = ''; 	//ask Lee what a default FQDN value should be and add here
	}

	//encryption
	$ipsec['encryp_p1'] = $request['ipsecphase1e'];
	$ipsec['encryp_p2'] = $request['ipsecphase2e'];

	//authentication
	$ipsec['auth_p1'] = $request['ipsecphase1a'];
	$ipsec['auth_p2'] = $request['ipsecphase2a'];

	//Auth Type
	$ipsec['auth_type'] = $request['ipsecphase1am'];
	switch($request['ipsecphase1am'])
	{
		case 'RSA':
			$ipsec['auth_type'] = 1;
			break;
		case 'Preshared':
			$ipsec['auth_type'] = 0;
			break;
		case 'X509':
			$ipsec['auth_type'] = 2;
			break;
		default:
			$ipsec['auth_type'] = 0;  //check with Lee what the default should be
	}


	$ipsec['pskey'] = (isset($request['ipsecpskey']) ? $request['ipsecpskey'] : '');

	$ipsec['dhgroup'] = $request['ipsecphase1dh'];

	$ipsec['salife_p1'] = (isset($request['ipsecsalife']) ? $request['ipsecsalife'] : '');
	$ipsec['salife_p2'] = (isset($request['ipsecphase2salife']) ? $request['ipsecphase2salife'] : '');

	//DPD
	switch($request['ipsecdeadPeer'])
	{
		case 'On':
			$ipsec['dpd'] = 1;
			break;
		case 'Off':
			$ipsec['dpd'] = 0;
			break;
		default:
			$ipsec['dpd'] = 1;
	}

	//PFS
	switch($request['ipsecpfs'])
	{
		case 'On':
			$ipsec['pfs'] = 1;
			break;
		case 'Off':
			$ipsec['pfs'] = 0;
			break;
		default:
			$ipsec['pfs'] = 1;
	}

	//following block of values not coming from the form
	$ipsec['ka_source'] = '';
	$ipsec['ka_remote'] = '';
	$ipsec['ka_period'] = '';
	$ipsec['ka_fail_count'] = '';
	$ipsec['policy_status']  = 1;
	$ipsec['auto_initiate']  = '';
	$ipsec['direction']  = '';
	$ipsec['ike_ver'] = '';
	$ipsec['key_length'] = '';
	$ipsec['salife_type'] = '';
	$ipsec['dpd_period'] = '';
	$ipsec['dpd_failcount'] = '';
	$ipsec['x509_remote_cert'] = '';
	$ipsec['x509_local_cert'] = '';
	$ipsec['x509_local_key'] = '';

	return $ipsec_ctrl->setIpSec($ipsec);

} //END submitIPSec

?>

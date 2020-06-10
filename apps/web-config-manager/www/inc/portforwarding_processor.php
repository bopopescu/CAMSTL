<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$dbconfig = new dbconfigController();


/*
 * NOTE:
 * The port forwarding functionality requires the use of two device sub-systems: Forwarding and IPReservation.
 * The Forwarding sub-system requires the  IP and Port #; stored in pairs. Example: IP1 = 192.168.5.1 and Port1 = 55
 * The IPReservation sub-system requires the IP and MAC; stored in pairs. Example: IP1 = 192.168.5.1 and MAC1 = A4:E5:33:24:56:A9
 * The data for each Port Forwarding rule, entered using the LCM, is split between Forwarding and IPReservation using the same index #.
 *
 * The following code (add, edit, delete) is built on the assumption that only the LCM will add/edit/delete the Forwarding and IPReservation data.
 * It will keep track of the pairs and ensure that the data in Forwarding is synced, in terms of index number, with the data in IPReservation.
 * Example: If IP1 and Port1 is deleted from Forwarding, then IP1 and MAC1 is also deleted from IPReservation
 *
 * If, in the future, a separate interface is required for creating IP Reservations, then we should storing that data in the 100s index range.
 * Since IPReservation does not require consecutive entries. This will prevent any race conditions or dirty read/writes.
 */




//Check form submission
if(!empty($_REQUEST))
{
	debug('=========_REQUEST=============', $_REQUEST);	//DEBUG


	if(!empty($_REQUEST['op']))
	{
		switch($_REQUEST['op'])
		{
			case "add":
			case "edit":
				$result = savePortForwardingRule($dbconfig, trimRequest($_REQUEST));
				header("location:http://".$_SERVER['HTTP_HOST']."/network/portforwarding/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
				break;

			case "delete":
				$result = deletePortForwardingRule($dbconfig, trimRequest($_REQUEST));
				echo "http://".$_SERVER['HTTP_HOST']."/network/portforwarding/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams'];
				break;

			default:
				$result = savePortForwardingRule($dbconfig, trimRequest($_REQUEST));
				header("location:http://".$_SERVER['HTTP_HOST']."/network/portforwarding/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
				break;
		}

	}
	else
	{
		$result = savePortForwardingRule($dbconfig, trimRequest($_REQUEST));
		header("location:http://".$_SERVER['HTTP_HOST']."/network/portforwarding/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
	}
}
else
{
	header("location:http://".$_SERVER['HTTP_HOST']."/network/portforwarding/index.php");
}


/**
 * deletePortForwardingRule
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function deletePortForwardingRule($dbconfig, $request)
{
	debug('', $request);	//DEBUG
	$result = array("success" => 'false', "module" => "", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$port_forwarding_rule_deleted = $ip_reservation_rule_deleted = false;
	$rule_name = $port_forwarding_rule = $ip_reservation_rule = '';


	if(!empty($request['ruleNum']))
	{
		$ruleToDelete = $request['ruleNum'];

		//Backup the rules before attempting a delete so that the operation can be rolled back
		$port_forwarding_rule =  $dbconfig->getDbconfigData('Forwarding', 'rule'.$ruleToDelete);
		$ip_reservation_rule =  $dbconfig->getDbconfigData('IPReservation', 'rule'.$ruleToDelete);


		// unset the rule that needs to be delete
		$port_forwarding_rule_deleted = $dbconfig->unsetDbconfigData('Forwarding', 'rule'.$ruleToDelete);

		if($port_forwarding_rule_deleted)
		{
			$ip_reservation_rule_deleted = $dbconfig->unsetDbconfigData('IPReservation', 'rule'.$ruleToDelete);
		}

		if($port_forwarding_rule_deleted && $ip_reservation_rule_deleted)
		{
			$ruleIndex = $ruleToDelete;

			// Starting from the rule after the one that was deleted, move all other rules up, one index
			do{

				$ruleIndex++;

				// read the settings for the next forwarding rule
				$forwarding_rule =  $dbconfig->getDbconfigData('Forwarding', 'rule'.$ruleIndex);

				if(empty($forwarding_rule)) break; 	//stop if there are no more rules

				// read the settings for the next reservation rule
				$reservation_rule =  $dbconfig->getDbconfigData('IPReservation', 'rule'.$ruleIndex);

				$newRuleIndex = $ruleIndex-1;

				// save the forwarding rule
				$dbconfig->setDbconfigData('Forwarding', 'rule'.$newRuleIndex, $forwarding_rule);

				//save the IP reservation rule
				$dbconfig->setDbconfigData('IPReservation', 'rule'.$newRuleIndex, $reservation_rule);

			}while(true);

			// explicitly delete the last rule since there is nothing to replace it with
			if($ruleToDelete < $request['totalRules'])
			{
				$lastRuleIndex = $request['totalRules'];
				$dbconfig->unsetDbconfigData('Forwarding', 'rule'.$lastRuleIndex);
				$dbconfig->unsetDbconfigData('IPReservation', 'rule'.$lastRuleIndex);
			}
		}
	}


	if($port_forwarding_rule_deleted && $ip_reservation_rule_deleted)
	{
		$result['success'] = 'true';
		$result['codes'][] = 10;
		$result['codes'][] = 14;
	}
	else
	{
		$result['success'] = 'false';
		$result['codes'][] = 11;

		//rollback
		if(!$ip_reservation_rule_deleted && $port_forwarding_rule_deleted)
		{
			if(!empty($rule_name) && !empty($port_forwarding_rule))
			{
				$dbconfig->setDbconfigData('Forwarding', 'rule'.$ruleToDelete, $port_forwarding_rule);
			}
		}
	}

	debug('(portforwarding_processor.php|deletePortForwardingRule()) $result: ', $result); 	//DEBUG
	return $result;


} //END deletePortForwardingRule

/**
 * savePortForwardingRule
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function savePortForwardingRule($dbconfig, $request)
{
	debug('', $request);	//DEBUG

	$rule_name = $ip = $ports = $protocol = $sPorts = '';

	$result = array("success" => 'false', "module" => "", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.
	//$portfwd_result = array();	// store the success/failure state for each setting

	$new_rule_index = (isValidNumber($request['newRuleIndex']) ? $request['newRuleIndex'] : 0);

	// Backup rule being edited -- needed to revert back in case save fails
	/*if($request['op'] == "edit")
	{
		$rule = array();

		$rule['name'] = $dbconfig->getDbconfigData('Forwarding', 'Name'.$new_rule_index);
		$rule['mac'] = $dbconfig->getDbconfigData('Forwarding', 'MAC'.$new_rule_index);
		$rule['ip'] = $dbconfig->getDbconfigData('Forwarding', 'IP'.$new_rule_index);
		$rule['port'] = $dbconfig->getDbconfigData('Forwarding', 'Port'.$new_rule_index);
	}*/


	// Parse Rule Name
	if(!empty($request['ruleName']))
	{
		$rule_name = preg_replace('/[^A-Za-z0-9_-]/','-',$request['ruleName']);	//only keep allowed characters: a-z 0-9 - _
	}

	// Parse IP
	if(!empty($request['interface']) && isValidIPOctet($request['ip4']))
	{
		switch($request['interface'])
		{
			case "All":
			case "all":
				$ip = ".".$request['ip4'];
				break;
			default:
				$ip = $request['interface'].".".$request['ip4'];
				break;
		}
	}

	// Parse Port Range														/// need to implement isReservedPort
	if(isValidNumber($request['portStart']) || isValidNumber($request['portEnd']))
	{
		$port_start = $request['portStart'];
		$port_end = $request['portEnd'];

		// A range is specified
		if(isset($port_start) && $port_start != '' && isset($port_end) && $port_end != '')
		{
			debug('start and end port');	//DEBUG
			if(isValidNumber($port_start) && isValidNumber($port_end))
			{
				if($port_start <= $port_end)
				{
					$ports = $port_start."-".$port_end;
				}
			}
		}
		//Only one port is specified
		elseif(isValidNumber($port_start))
		{
			debug('only start port');	//DEBUG
			$ports = $port_start;
		}
		elseif(isValidNumber($port_end))
		{
			debug('only end port');	//DEBUG
			$ports = $port_end;
		}
	}

	// Parse source Port Range														/// need to implement isReservedPort
	if(isValidNumber($request['sPortStart']) || isValidNumber($request['sPortEnd']))
	{
		$sPort_start = $request['sPortStart'];
		$sPort_end = $request['sPortEnd'];

		// A range is specified
		if(isset($sPort_start) && $sPort_start != '' && isset($sPort_end) && $sPort_end != '')
		{
			debug('start and end source port');	//DEBUG
			if(isValidNumber($sPort_start) && isValidNumber($sPort_end))
			{
				if($sPort_start <= $sPort_end)
				{
					$sPorts = $sPort_start."-".$sPort_end;
				}
			}
		}
		//Only one port is specified
		elseif(isValidNumber($sPort_start))
		{
			debug('only start source port');	//DEBUG
			$sPorts = $sPort_start;
		}
		elseif(isValidNumber($sPort_end))
		{
			debug('only end source port');	//DEBUG
			$sPorts = $sPort_end;
		}
	}
	// Parse Protocol
	if(!empty($request['protocol']))
	{
		switch($request['protocol'])
		{
			case "All":
			case "all":
				$protocol = implode("/", $GLOBALS['trulink_protocols']);
				break;
			default:
				$protocol = $request['protocol'];
				break;
		}
	}



	// Save Rule
	if(!empty($rule_name) && !empty($ip) && isset($ports) && $ports !='' && !empty($protocol) && isset($sPorts) && $sPorts !='')
	{
		$mac = '00.00.00.00.00.00';
		//Build port forwarding rule string
		$forwarding_rule_string = $rule_name.",".$ip.",".$ports.",".$protocol.",".$mac.",".$sPorts;

		//Build IP Reservation rule string
		$reservation_rule_string = $rule_name.",".$ip.",".$mac;

		//Save Forwarding rule to db-config
		$forwarding_rule_saved = $dbconfig->setDbconfigData('Forwarding', "rule".$new_rule_index, $forwarding_rule_string);

		if($forwarding_rule_saved && $reservation_rule_saved)
		{
			$result['success'] = 'true';
			$result['codes'][] = 10;
			$result['codes'][] = 14;
		}
		else
		{
			$result['success'] = 'false';
			$result['codes'][] = 11;
		}

	}
	else
	{
		$result['success'] = 'false';
		$result['codes'][] = 12;

		// identify the failed fields
		if(empty($rule_name))
		{
			$result['fields'] .= 'ruleName,';
		}


		if(empty($ip))
		{
			$result['fields'] .= 'ip4,';
		}

		if(empty($ports))
		{
			$result['fields'] .= 'portStart,';
			//$result['codes'][] = 1100;
		}

		if(empty($protocol))
		{
			$result['fields'] .= 'protocol,';
		}


		$result['getParams'] .= '&ruleName='.$request['ruleName'];
		$result['getParams'] .= '&interface='.$request['interface'].'&ip4='.$request['ip4'];
		$result['getParams'] .= '&portStart='.$request['portStart'].'&portEnd='.$request['portEnd'];
		$result['getParams'] .= '&protocol='.$request['protocol'];

	}

	// 1) find all the keys in the $positionup_result array that have a value of false (these are the API calls that failed)
	// 2) build a string with the keys. (The key names are the same as the html element (input/radio/select) names and will be used to highlight the fields with jquery)
	$failed_results = array_keys($result, false, true);
	$portresult['fields'] = implode(',', $failed_results);

	foreach($failed_results as $field)
	{
		$portresult['getParams'] .= '&'.$field.'='.$request[$field];
	}

	if(empty($result['fields']))
	{
		$portresult['success'] = 'true';
		$portresult['codes'][] = 10;
		$portresult['codes'][] = 14;
	}
	else
	{
		$portresult['success'] = 'false';
		$portresult['codes'][] = 12;
	}
	return $portresult;
} //END savePortForwardingRule



?>

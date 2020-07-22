<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$dbconfig = new dbconfigController();


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
				$result = saveIPReservationRule($dbconfig, trimRequest($_REQUEST));
				header("location:https://".$_SERVER['HTTP_HOST']."/network/ipreservation/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
				break;

			case "delete":
				$result = deleteIPReservationRule($dbconfig, trimRequest($_REQUEST));
				echo "https://".$_SERVER['HTTP_HOST']."/network/ipreservation/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams'];
				break;

			default:
				$result = saveIPReservationRule($dbconfig, trimRequest($_REQUEST));
				header("location:https://".$_SERVER['HTTP_HOST']."/network/ipreservation/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
				break;
		}

	}
	else
	{
		$result = saveIPReservationRule($dbconfig, trimRequest($_REQUEST));
		header("location:https://".$_SERVER['HTTP_HOST']."/network/ipreservation/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
	}
}
else
{
	header("location:https://".$_SERVER['HTTP_HOST']."/network/ipreservation/index.php");
}


/**
 * deleteIPReservationRule
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function deleteIPReservationRule($dbconfig, $request)
{
	debug('', $request);	//DEBUG
	$result = array("success" => 'false', "module" => "", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$ip_reservation_rule_deleted = false;
	$rule_name = $ip_reservation_rule = '';


	if(!empty($request['ruleNum']))
	{
		$ruleToDelete = $request['ruleNum'];

		//Backup the rules before attempting a delete so that the operation can be rolled back
		//$ip_reservation_rule =  $dbconfig->getDbconfigData('IPReservation', 'rule'.$ruleToDelete);

		$ip_reservation_rule_deleted = $dbconfig->unsetDbconfigData('IPReservation', 'rule'.$ruleToDelete);


		if($ip_reservation_rule_deleted)
		{
			$ruleIndex = $ruleToDelete;

			// Starting from the rule after the one that was deleted, move all other rules up, one index
			do{

				$ruleIndex++;

				// read the settings for the next reservation rule
				$reservation_rule =  $dbconfig->getDbconfigData('IPReservation', 'rule'.$ruleIndex);

				if(empty($reservation_rule)) break; 	//stop if there are no more rules

				$newRuleIndex = $ruleIndex-1;

				//save the IP reservation rule
				$dbconfig->setDbconfigData('IPReservation', 'rule'.$newRuleIndex, $reservation_rule);

			}while(true);

			// explicitly delete the last rule since there is nothing to replace it with
			if($ruleToDelete < $request['totalRules'])
			{
				$lastRuleIndex = $request['totalRules'];
				$dbconfig->unsetDbconfigData('IPReservation', 'rule'.$lastRuleIndex);
			}
		}
	}


	if($ip_reservation_rule_deleted)
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

	debug('(ipreservation_processor.php|deleteIPReservationRule()) $result: ', $result); 	//DEBUG
	return $result;


} //END deleteIPReservationRule

/**
 * saveIPReservationRule
 * Saves the settings to dbconfig using the dbconfig wrapper
 * @param object $dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Sean Toscano (sean@absolutetrac.com)
 */
function saveIPReservationRule($dbconfig, $request)
{
	debug('', $request);	//DEBUG

	$rule_name = $mac = $ip =  '';

	$result = array("success" => 'false', "module" => "", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.
	//$portfwd_result = array();	// store the success/failure state for each setting

	$new_rule_index = (isValidNumber($request['newRuleIndex']) ? $request['newRuleIndex'] : 0);

	// Parse Rule Name
	if(!empty($request['ruleName']))
	{
		$rule_name = preg_replace('/[^A-Za-z0-9_-]/','-',$request['ruleName']);	//only keep allowed characters: a-z 0-9 - _
	}



	//Parse MAC
	if( (isset($request['mac1']) && $request['mac1'] != '') &&
			(isset($request['mac2']) && $request['mac2'] != '') &&
			(isset($request['mac3']) && $request['mac3'] != '') &&
			(isset($request['mac4']) && $request['mac4'] != '') &&
			(isset($request['mac5']) && $request['mac5'] != '') &&
			(isset($request['mac6']) && $request['mac6'] != ''))
	{
		$mac_raw = $request['mac1'].':'.$request['mac2'].':'.$request['mac3'].':'.$request['mac4'].':'.$request['mac5'].':'.$request['mac6'];
		$mac = isValidMAC($mac_raw) ? $mac_raw : '';
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


	// Save Rule
	if(!empty($rule_name) && !empty($mac) && !empty($ip))
	{
		//Build IP Reservation rule string
		$reservation_rule_string = $rule_name.",".$ip.",".$mac;

		//Save IP Reservation rule to db-config
		$reservation_rule_saved = $dbconfig->setDbconfigData('IPReservation', "rule".$new_rule_index, $reservation_rule_string);

		if($reservation_rule_saved)
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

		if(empty($mac))
		{
			$result['fields'] .= 'mac1,';
		}

		if(empty($ip))
		{
			$result['fields'] .= 'ip4,';
		}

		$result['getParams'] .= '&ruleName='.$request['ruleName'];
		$result['getParams'] .= '&mac='.$request['mac1'].':'.$request['mac2'].':'.$request['mac3'].':'.$request['mac4'].':'.$request['mac5'].':'.$request['mac6'];
		$result['getParams'] .= '&interface='.$request['interface'].'&ip4='.$request['ip4'];

	}

	debug('(ipreservation_processor.php|saveIPReservationRule()) $result: ', $result); 	//DEBUG
	return $result;

} //END saveIPReservationRule



?>

<?php
 
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dhcp_controller.inc';	//dhcp controller
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';				//contains functions for socket interaction, error message display, and logging.


if(!empty($_GET))
{
	if(!empty($_GET['codes']))
	{
		foreach(explode(",",$_GET['codes']) as $key)
		{
			translateStatusCode($key);
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
$dbconfig = new dbconfigController();
$dhcp_ctrl = new dhcpcontroller();

//VARIABLE INSTANTIATION
$rule_name = $interface = $ip4 = $iprdhcp_leases = '';
$mac = array("","","","","","");

$eip = $eip_subnet = $wip = $wip_subnet = '' ;

$rule_index = $new_rule_index = 0;
$ip_reservation_rules = array();
$operation_type = "add";


//pull the IPs for eth0 and ra0
$eip_raw = $dbconfig->getDbconfigData('system', 'eth0addr');
$eip = (isValidIP($eip_raw) ? explode(".", $eip_raw) : array(" ", " ", " ", " "));
$eip_subnet = $eip[0].".".$eip[1].".".$eip[2];

$wip_raw = $dbconfig->getDbconfigData('system', 'ra0addr');
$wip = (isValidIP($wip_raw) ? explode(".", $wip_raw) : array(" ", " ", " ", " "));
$wip_subnet = $wip[0].".".$wip[1].".".$wip[2];

// DHCP LEASES
$iprdhcp_leases = $dhcp_ctrl->getActiveDhcpLeases($eip);	//get active dhcp leases

// read the ip reservation rules
$rule_index = 200;
readReservationRules($rule_index, $ip_reservation_rules, $eip_subnet, $wip_subnet);

$new_rule_index = $rule_index;	//set the index number for the next available slot 

// read the combined port forwarding -  ip reservation rules from db-config
$rule_index = 0;
readReservationRules($rule_index, $ip_reservation_rules, $eip_subnet, $wip_subnet);


//Parse the rules and build the table
$table_rows = "";
foreach($ip_reservation_rules as $rule)
{
	$table_rows .= '<tr id="'.$rule['index'].'" class="rule '.$rule['type'].'">';
	$table_rows .= '<td class="name">'.$rule['name'].'</td>';
	$table_rows .= '<td class="mac">'.$rule['mac'].'</td>';
	$table_rows .= '<td class="ip"><ul style="list-style:none; padding: 0; margin: 0;">';
	
	foreach($rule['ip'] as $val)
	{
		$table_rows .= '<li id="'.$val['interface'].'">'.$val['ip'].'</li>';
	}
	
	
	$table_rows .= '</ul></td>';
	
	
	if($rule['type'] == 'forwarding')
	{
		$table_rows .= '<td class="edit">'. (hasSubmitAccess() ? '<a class="editRuleDisabled" href="#" title="This reservation is part of a port forwarding rule and can only be modified from the Port Forwarding page."><img src="/images/DisabledEditIcon16.png" alt="Edit" /></a>' : '&nbsp;') .'</td>';
		$table_rows .= '<td class="delete">'. (hasSubmitAccess() ? '<a class="deleteRuleDisabled" href="#" title="This reservation is part of a port forwarding rule and can only be deleted from the Port Forwarding page."><img src="/images/DisabledDeleteIcon16.png" alt="Delete" /></a>' : '&nbsp;') .'</td>';
	}
	else
	{
		$table_rows .= '<td class="edit">'. (hasSubmitAccess() ? '<a class="editRule" href="#" title="Edit rule"><img src="/images/EditIcon16.png" alt="Edit" /></a>' : '&nbsp;') .'</td>';
		$table_rows .= '<td class="delete">'. (hasSubmitAccess() ? '<a class="deleteRule" href="#" title="Delete rule"><img src="/images/DeleteIcon16.png" alt="Delete" /></a>' : '&nbsp;') .'</td>';
	}
	$table_rows .= '</tr>';
}


if(empty($table_rows))
{
	$table_rows .= '<tr><td colspan="7">No IP reservation rules have been setup</td></tr>';
}

// Display the value of any fields that failed to save. (they will be part of the url params)
// Name
if(!empty($_GET['ruleName']))
{
	$rule_name = $_GET['ruleName'];
}

// MAC
if(!empty($_GET['mac']))
{
	$mac = explode(":", $_GET['mac']);
}

// IP
if(!empty($_GET['interface']))
{
	$interface = $_GET['interface'];
}

if(!empty($_GET['ip4']))
{
	$ip4 = $_GET['ip4'];
}

// Rule index
if(!empty($_GET['newRuleIndex']))
{
	$new_rule_index = $_GET['newRuleIndex'];
}

// Operation type
if(!empty($_GET['op']))
{
	$operation_type = $_GET['op'];
}



function readReservationRules(&$rule_index, &$ip_reservation_rules, $eip_subnet, $wip_subnet)
{
	global $dbconfig;
	$rule_type = "reservation";
	
	do{
		if($rule_index < 200)
			$rule_type = "forwarding";
		
		$rule_index++;		//forwarding rules are stored in db-config starting with 1
	
		$rule_raw =  $dbconfig->getDbconfigData('IPReservation', 'rule'.$rule_index);
	
		if(empty($rule_raw)) break; 	//stop if there are no more rules
	
		// Parse the rule
		$rule = explode(",", $rule_raw);
	
		// Rule name
		$name_raw = !empty($rule[0]) ? $rule[0] : '';
	
		// IP
		if(!empty($rule[1]))
		{
			$ip_raw = array();
	
			if(stripos($rule[1], "eth0") !== FALSE)
			{
				$ip_raw[] = array("ip" => str_replace("eth0", $eip_subnet, $rule[1]), "interface" => "eth0");
			}
			elseif(stripos($rule[1], "ra0") !== FALSE)
			{
				$ip_raw[] = array("ip" => str_replace("ra0", $wip_subnet, $rule[1]), "interface" => "ra0");
			}
			else
			{
				$ip_raw[] = array("ip" => $eip_subnet.$rule[1], "interface" => "eth0");
				$ip_raw[] = array("ip" => $wip_subnet.$rule[1], "interface" => "ra0");
			}
		}
	
		// MAC
		$mac_raw = !empty($rule[2]) ? $rule[2] : '';
	
		$ip_reservation_rules[] = array("index" => $rule_index, "type" => $rule_type, "name" => $name_raw, "mac" => $mac_raw, "ip" => $ip_raw);
	
	}while(true);
}

?>

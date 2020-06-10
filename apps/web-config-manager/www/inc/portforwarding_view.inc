<?php
 
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';
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

//VARIABLE INSTANTIATION
$rule_name = $interface = $ip4 = $port_range_start = $port_range_end = $protocol = $protocol_list = '';
$sPort_range_start = $sPort_range_end = '';

$eip = $eip_subnet = $wip = $wip_subnet = '' ;

$i = $new_rule_index = 0;
$port_forwarding_rules = array();
$operation_type = "add";


//pull the IPs for eth0 and ra0
$eip_raw = $dbconfig->getDbconfigData('system', 'eth0addr');
$eip = (isValidIP($eip_raw) ? explode(".", $eip_raw) : array(" ", " ", " ", " "));
$eip_subnet = $eip[0].".".$eip[1].".".$eip[2];

$wip_raw = $dbconfig->getDbconfigData('system', 'ra0addr');
$wip = (isValidIP($wip_raw) ? explode(".", $wip_raw) : array(" ", " ", " ", " "));
$wip_subnet = $wip[0].".".$wip[1].".".$wip[2];

// read the port forwarding rules from db-config

do{
	$i++;		//forwarding rules are stored in db-config starting with 1
		
	$rule_raw =  $dbconfig->getDbconfigData('Forwarding', 'rule'.$i);
	
	if(empty($rule_raw)) break; 	//stop if there are no more rules

	// Parse the rule
	$rule = explode(",", $rule_raw);
	
	// Rule name
	$name_raw = !empty($rule[0]) ? $rule[0] : '';
	
	$ip_raw = array();
	
	// IP
	if(!empty($rule[1]))
	{	
		if(stripos($rule[1], "eth0") !== FALSE)
		{
			$ip_raw[] = array("ip" => str_replace("eth0", $eip_subnet, $rule[1]), "interface" => "eth0");
		}
		elseif(stripos($rule[1], "ra0") !== FALSE)
		{
			$ip_raw[] = array("ip" => str_replace("ra0", $wip_subnet, $rule[1]), "interface" => "ra0");
			//$ip_raw['ip'] = str_replace("ra0", $wip_subnet, $rule[1]);
			//$ip_raw['interface'] = "ra0";
		}
		else
		{
			$ip_raw[] = array("ip" => $eip_subnet.$rule[1], "interface" => "eth0");
			$ip_raw[] = array("ip" => $wip_subnet.$rule[1], "interface" => "ra0");
			//$ip_raw[] = $eip_subnet.$rule[1];
			//$ip_raw[] = $wip_subnet.$rule[1];
		}
	}
	
	// Port
	$port_raw = isset($rule[2]) ? $rule[2] : '';
	
	// Protocol
	$protocol_raw = !empty($rule[3]) ? $rule[3] : '';
	
	// sPort
	$sPort_raw = isset($rule[5]) ? $rule[5] : '80';
	
	$port_forwarding_rules[] = array("name" => $name_raw, "ip" => $ip_raw, "port" => $port_raw, "protocol" => $protocol_raw, "sPort" => $sPort_raw);
		
}while(true);


$new_rule_index = $i;

//Parse the rules and build the table
$table_rows = "";
foreach($port_forwarding_rules as $rule)
{
	$table_rows .= '<tr class="rule">';
	$table_rows .= '<td class="name">'.$rule['name'].'</td>';
	$table_rows .= '<td class="ip"><ul style="list-style:none; padding: 0; margin: 0;">';
	
	foreach($rule['ip'] as $val)
	{
		$table_rows .= '<li id="'.$val['interface'].'">'.$val['ip'].'</li>';
	}
	
	
	$table_rows .= '</ul></td>';
	$table_rows .= '<td class="port">'.$rule['port'].'</td>';
	$table_rows .= '<td class="protocol">'.$rule['protocol'].'</td>';
	$table_rows .= '<td class="edit">'. (hasSubmitAccess() ? '<a class="editRule" href="#" title="Edit rule"><img src="/images/EditIcon16.png" alt="Edit" /></a>' : '&nbsp;') .'</td>';
	$table_rows .= '<td class="delete">'. (hasSubmitAccess() ? '<a class="deleteRule" href="#" title="Delete rule"><img src="/images/DeleteIcon16.png" alt="Delete" /></a>' : '&nbsp;') .'</td>';
	$table_rows .= '<td class="sPort">'.$rule['sPort'].'</td>';
	$table_rows .= '</tr>';
}


if(empty($table_rows))
{
	$table_rows .= '<tr><td colspan="7">No port forwarding rules have been setup</td></tr>';
}

// Display the value of any fields that failed to save. (they will be part of the url params)
// Name
if(!empty($_GET['ruleName']))
{
	$rule_name = $_GET['ruleName'];
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


// sPort Range
if(isset($_GET['sPortStart']) && $_GET['sPortStart'] !== false)
{
	$sPort_range_start = $_GET['sPortStart'];
}

if(isset($_GET['sPortEnd']) && $_GET['sPortEnd'] !== false)
{
	$sPort_range_end = $_GET['sPortEnd'];
}

// Port Range
if(isset($_GET['portStart']) && $_GET['portStart'] !== false)
{
	$port_range_start = $_GET['portStart'];
}

if(isset($_GET['portEnd']) && $_GET['portEnd'] !== false)
{
	$port_range_end = $_GET['portEnd'];
}

// Protocol
if(!empty($_GET['protocol']))
{
	$protocol = $_GET['protocol'];
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


//pull protocols for select list
foreach($GLOBALS['trulink_protocols'] as $trulink_protocol)
{
	$protocol_list .= '<option value="'.$trulink_protocol.'"'. ((strcasecmp($protocol, $trulink_protocol) == 0) ? 'selected="selected"':'') .'>'.$trulink_protocol.'</option>';
}


?>

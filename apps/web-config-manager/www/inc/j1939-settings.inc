<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/dbconfig_controller.inc';

//get page variable values

$dbconfig = new dbconfigController();

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

/*
 * ATS-FIXME: All the checks for GET variables can be simplified to using
 * a single function.
*/
$templateName = '';
if(isset($_GET['templateName']) && $_GET['templateName'] !== false)
{
	$templateName = $_GET['templateName'];
	debug("templateName=", $templateName);
}

if(isset($_GET['enable']) && $_GET['enable'] !== false)
{
	$value = $_GET['enable'];
	$enable = ((strcmp($value, "On") == 0));
}
else
{
	$value = $dbconfig->getDbconfig('feature', 'can-j1939-monitor');
	$value = hex2bin(trim($value));
	debug("enable=", $value);
	$enable = ($value == 1);
}

if(isset($_GET['SourceAddress']) && $_GET['SourceAddress'] !== false)
{
	$srcAddress = $_GET['SourceAddress'];
}
else
{
	$srcAddress = trim($dbconfig->getDbconfigData('CanJ1939Monitor','sourceaddress'));
}

if(isset($_GET['CellPMRepInt']) && $_GET['CellPMRepInt'] !== false)
{
	$cellPMRepInt = $_GET['CellPMRepInt'];
}
else
{
	$cellPMRepIntRaw= trim($dbconfig->getDbconfigData('CanJ1939Monitor','periodic_seconds'));
	$cellPMRepInt = isValidNumber($cellPMRepIntRaw) ? $cellPMRepIntRaw/60 : '??';
}

if(isset($_GET['IrdPMRepInt']) && $_GET['IrdPMRepInt'] !== false)
{
	$irdPMRepInt = $_GET['IrdPMRepInt'];
}
else
{
	$irdPMRepIntRaw = trim($dbconfig->getDbconfigData('CanJ1939Monitor','periodic_overiridium_seconds'));
	$irdPMRepInt = isValidNumber($irdPMRepIntRaw) ? $irdPMRepIntRaw/60 : '??';
}

if(isset($_GET['CellEMCRepInt']) && $_GET['CellEMCRepInt'] !== false)
{
	$EMCRepInt = $_GET['CellEMCRepInt'];
}
else
{
	$EMCRepIntRaw = trim($dbconfig->getDbconfigData('CanJ1939Monitor','exceedStatTime'));
	$EMCRepInt = isValidNumber($EMCRepIntRaw) ? $EMCRepIntRaw/60 : '??';
}

if(isset($_GET['CellFMCRepInt']) && $_GET['CellFMCRepInt'] !== false)
{
	$FMCRepInt = $_GET['CellFMCRepInt'];
}
else
{
	$FMCRepIntRaw = trim($dbconfig->getDbconfigData('CanJ1939Monitor','faultStatTime'));
	$FMCRepInt = isValidNumber($FMCRepIntRaw) ? $FMCRepIntRaw/60 : '??';
}

if(isset($_GET['periodic_pri']) && $_GET['periodic_pri'] !== false)
{
	$periodic_pri = $_GET['periodic_pri'];
}
else
{
	$periodic_pri_raw = trim($dbconfig->getDbconfig('CanJ1939Monitor','overiridium_priority_periodic'));
	$periodic_pri = getSliderValue($periodic_pri_raw);
}

if(isset($_GET['exceed_pri']) && $_GET['exceed_pri'] !== false)
{
	$exceed_pri = $_GET['exceed_pri'];
}
else
{
	$exceed_pri_raw = trim($dbconfig->getDbconfig('CanJ1939Monitor','overiridium_priority_exceedance'));
	$exceed_pri = getSliderValue($exceed_pri_raw);
}

if(isset($_GET['fault_pri']) && $_GET['fault_pri'] !== false)
{
	$fault_pri = $_GET['fault_pri'];
}
else
{
	$fault_pri_raw = trim($dbconfig->getDbconfig('CanJ1939Monitor','overiridium_priority_fault'));
	$fault_pri = getSliderValue($fault_pri_raw);
}
$j1939_db_data = $dbconfig->getDbconfig('j1939-db', NULL);
$j1939_db_array = split("\n", $j1939_db_data);
$templates_array = array();
$active_template = '';
foreach ($j1939_db_array as $entry)
{
	$entry_array = split(' ', $entry);
	$key = hex2bin($entry_array[0]);

	$templateHeaderLength = strlen("template_");
	if(strncmp($key, "template_", $templateHeaderLength) == 0)
	{
		$templates_array[] = substr($key, $templateHeaderLength);
	}
	elseif(strncmp($key, "template", $templateHeaderLength - 1) == 0)
	{

		$val = hex2bin($entry_array[1]);
		$active_template = $val;
		debug('$active_template= ', $active_template);
	}
}
?>

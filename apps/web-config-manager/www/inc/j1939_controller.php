<?php
require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';	//contains functions for socket interaction, error message display, and logging.

define('_SUCCESS_SAVE_SETTINGS_CODE', '10');
define('_FAIL_SAVE_SETTINGS_CODE', '11');
define('_FAIL_SAVE_FIELDS_CODE', '12');
define('_REBOOT_MESSAGE_CODE', '14');
define('_TEMPLATE_FILE_LARGE_CODE', '1200');

define('_MAX_TEMPLATE_FILE_SIZE', '61440'); //60KB

$dbconfig = new dbconfigController();
//Check form submission
if(!empty($_REQUEST))
{
	debug('=========_REQUEST=============', $_REQUEST);

	$result = array("success" => 'false', "module" => "J1939", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	switch($_REQUEST['op'])
	{
		case "updateSettings":
			$result = updateSettings($dbconfig, trimRequest($_REQUEST));
			header("location:https://".$_SERVER['HTTP_HOST']."/device/j1939/index.php?success=".$result['success']."&module=add".$result['module']."Template&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
			break;
		case "addTemplate";
			$result = addTemplate($dbconfig, trimRequest($_REQUEST), $_FILES);
			header("location:https://".$_SERVER['HTTP_HOST']."/device/j1939/index.php?success=".$result['success']."&module=add".$result['module']."Template&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
			break;
		case "deleteTemplate":
			$result = deleteTemplate($dbconfig, trimRequest($_REQUEST));
			echo "https://".$_SERVER['HTTP_HOST']."/device/j1939/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams'];
			break;
		case "activateTemplate":
			$result = activateTemplate($dbconfig, trimRequest($_REQUEST));
			header("location:https://".$_SERVER['HTTP_HOST']."/device/j1939/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
			break;
		default:
			header("location:https://".$_SERVER['HTTP_HOST']."/device/j1939/index.php?success=".$result['success']."&module=".$result['module']."&codes=".implode(",",$result['codes'])."&fields=".$result['fields'].$result['getParams']);
	}

}
else
{
	header("location:https://".$_SERVER['HTTP_HOST']."/device/j1939/index.php");
}

/**
 * updateSettings
 * Saves the j1939 settings into dbconfig using the dbconfig wrapper
 * @param object $p_dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Tyson Pullukatt (Tyson.Pullukatt@gps1.com)
 */
function updateSettings($p_dbconfig, $request)
{
	$result = array("success" => 'false', "module" => "Modbus", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.
	$settingsSaved = false;
	$result['fields'] = '';
	$result['getParams'] = '';

	if(!empty($request['enable']))
	{
		$enable = $request['enable'];
		if(isOff($enable))
		{
			$app = 'feature';
			$key = 'can-j1939-monitor';
			$settingsSaved = $p_dbconfig->setDbconfig($app, $key, 0);
		}
		else
		{
			$j1939_results = array();
			$app = "CanJ1939Monitor";
			$j1939_results['enable'] = $p_dbconfig->setDbconfig('feature', 'can-j1939-monitor', 1);
			$j1939_results['SourceAddress'] = (((isValidNumber($request['SourceAddress']))&&
				($request['SourceAddress'] > 0) && ($request['SourceAddress'] < 0xFF))?
					$p_dbconfig->setDbconfig($app, 'sourceaddress', intval($request['SourceAddress'], 0)):false);
			$j1939_results['CellPMRepInt'] = (isValidNumber($request['CellPMRepInt']) ?
				($p_dbconfig->setDbconfig($app, 'periodic_seconds', intval($request['CellPMRepInt'])*60)): false);
			$j1939_results['IrdPMRepInt'] = ((isValidNumber($request['IrdPMRepInt']))?
				($p_dbconfig->setDbconfig($app, 'periodic_overiridium_seconds', intval($request['IrdPMRepInt'])*60)):false);
			$j1939_results['CellEMCRepInt'] = (isValidNumber($request['CellEMCRepInt']) ?
				($p_dbconfig->setDbconfig($app, 'exceedStatTime', intval($request['CellEMCRepInt'])*60)):false);
			$j1939_results['CellFMCRepInt'] = ((isValidNumber($request['CellFMCRepInt']))?
				($p_dbconfig->setDbconfig($app, 'faultStatTime', intval($request['CellFMCRepInt'])*60)):false);
			$pri = (isset($request['periodic_pri']))? getMsgPriorityFromSlider($request['periodic_pri']) : '';
			$j1939_results['periodic_pri'] = ((isValidNumber($pri))?
				($p_dbconfig->setDbconfig($app, 'overiridium_priority_periodic', $pri)):false);
			$pri = (isset($request['exceed_pri']))? getMsgPriorityFromSlider($request['exceed_pri']) : '';
			$j1939_results['exceed_pri'] = ((isValidNumber($pri))?
				($p_dbconfig->setDbconfig($app, 'overiridium_priority_exceedance', $pri)):false);
			$pri = (isset($request['fault_pri']))? getMsgPriorityFromSlider($request['fault_pri']) : '';
			$j1939_results['fault_pri'] = ((isValidNumber($pri))?
				($p_dbconfig->setDbconfig($app, 'overiridium_priority_fault', $pri)):false);
			$failed_results = array_keys($j1939_results, false, true);
			$result['fields'] = implode(',', $failed_results);
			foreach($failed_results as $field)
			{
				$result['getParams'] .= '&'.$field.'='.urlencode($request[$field]);
			}

			if(empty($result['fields']))
			{
				$settingsSaved = true;
			}
		}
	}
	if($settingsSaved)
	{
		$result['success'] = 'true';
		$result['codes'][] = _SUCCESS_SAVE_SETTINGS_CODE;
		$result['codes'][] = _REBOOT_MESSAGE_CODE;
	}
	else
	{
		$result['success'] = 'false';
		$result['codes'][] = _FAIL_SAVE_FIELDS_CODE;
	}

	return $result;
}

/**
 * addAssignemnt
 * Saves the assignment of template to slave into dbconfig using the dbconfig wrapper
 * @param object $p_dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Tyson Pullukatt (Tyson.Pullukatt@gps1.com)
 */
function activateTemplate($p_dbconfig, $request)
{
	$result = array("success" => 'false', "module" => "Modbus", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.
	$activated = false;

	if(!empty($request['template_name']))
	{
		$templateId = $request['template_name'];
		$app = 'j1939-db';
		$activated = $p_dbconfig->setDbconfig($app, 'template', $templateId);
	}

	if($activated)
	{
		$result['success'] = 'true';
		$result['codes'][] = _SUCCESS_SAVE_SETTINGS_CODE;
		$result['codes'][] = _REBOOT_MESSAGE_CODE;
	}
	else
	{
		$result['success'] = 'false';
		$result['codes'][] = _FAIL_SAVE_SETTINGS_CODE;
	}
	return $result;
}

/**
 * addTemplate
 * Saves the template to dbconfig using the dbconfig wrapper
 * @param object $p_dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @param array $file - form FILE array
 * @author Tyson Pullukatt (Tyson.Pullukatt@gps1.com)
 */
function addTemplate($p_dbconfig, $request, $file)
{
	debug('+++++_FILE++++++++++++++', $file);
	$result = array("success" => 'false', "module" => "Modbus", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.
	$templateSaved = false;
	if(!empty($file["templateFile"]))
	{
		$templateFile = $file["templateFile"];
		if($templateFile['error'] == 0)
		{
			debug("templateFile location=",$templateFile["tmp_name"]);
			debug("templateName=", $request["templateName"]);
			if($templateFile["size"] <= _MAX_TEMPLATE_FILE_SIZE)
			{
				if(!empty($templateFile["tmp_name"]) && !empty($request["templateName"]))
				{
					$templateName = $request["templateName"];
					if(isValidTemplateName($templateName))
					{
						$app = "j1939-db";
						$key = "template_".$templateName;
						$templateSaved = $p_dbconfig->setDbconfigDataFile($app, $key, $templateFile["tmp_name"]);
					}
					else
					{
						$result['fields'] = 'templateName';
					}
				}
			}
			else
			{
				$result['codes'][] = _TEMPLATE_FILE_LARGE_CODE;
				$result['fields'] = 'templateFile';
			}
		}
	}

	if($templateSaved)
	{
		$result['success'] = 'true';
		$result['codes'][] = _SUCCESS_SAVE_SETTINGS_CODE;
		$result['codes'][] = _REBOOT_MESSAGE_CODE;
	}
	else
	{
		$result['success'] = 'false';
		$result['codes'][] = _FAIL_SAVE_SETTINGS_CODE;
		$result['getParams'] = '&templateName='.urlencode($request['templateName']);
	}
	return $result;
}

/**
 * deleteTemplate
 * Delete the template from dbconfig using the dbconfig wrapper
 * @param object $p_dbconfig - wrapper class for dbconfig interaction
 * @param array $request - form REQUEST array
 * @author Tyson Pullukatt (Tyson.Pullukatt@gps1.com)
 */
function deleteTemplate($p_dbconfig, $request)
{
	$result = array("success" => 'false', "module" => "Modbus", "codes" => array(), "fields" => null, "getParams" => null);	//array for capturing result status, status code, and field names.

	$template_deleted = false;
	$deactivated_template = true;
	$templateId = $templateKey = '';
	$templateAssignments = array();
	$app = "j1939-db";
	if(!empty($request['templateId']))
	{
		$templateId =  $request['templateId'];
		$templateKey = "template_".$templateId;
		if(!empty($request['active']))
		{
			if($request['active'] == "true")
			{
				$deactivated_template = $p_dbconfig->unsetDbconfigData($app, 'template');
			}
		}
		if($deactivated_template)
		{
			$template_deleted = $p_dbconfig->unsetDbconfigData($app, $templateKey);
		}
	}

	if($assignments_deleted && $template_deleted)
	{
		$result['success'] = 'true';
		$result['codes'][] = _SUCCESS_SAVE_SETTINGS_CODE;
		$result['codes'][] = _REBOOT_MESSAGE_CODE;
	}
	else
	{
		$result['success'] = 'false';
		$result['codes'][] = _FAIL_SAVE_SETTINGS_CODE;

		//rollback
		if(!empty($templateAssignments))
		{
			foreach($templateAssignments as $slave=>$template)
			{
				$p_dbconfig->setDbconfigData($app, $slave, $template);
			}
		}
	}

	debug('(j1939_controller.php|deleteTemplate()) $result: ', $result);
	return $result;

}


?>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';			//contains functions for socket interaction, error message display, and logging.
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/db_sqlite3.inc';	//contains functions for db interaction

session_start();	//start session


//if Session info exists for the user
if(!empty($_SESSION['M2M_SESH_USERID']) && !empty($_SESSION['M2M_SESH_USERAL']))
{
	//redirect them to the url passed in the http referrer or to the index page
	//header("location:https://".$_SERVER['HTTP_HOST']."/index.php"); //for PROD
	if(hasInstallerAccess())
	{
		header("location:https://".$_SERVER['HTTP_HOST']."/device/installersettings/index.php");
	}
	else
	{
		header("location:https://".$_SERVER['HTTP_HOST']."/device/general/index.php");
	}
}

//if Session info does not exist for the user
elseif(!empty($_REQUEST['user']) && !empty($_REQUEST['pass']))
{
	//validate the user's credentials
	$dt = new db_sqlite3();
	$conn = $dt->connect_sqlite(DB);		//connect to db
	$sql_login = 'select * from user_profile where username = :user and password = :passwd';	//sql prepared statement
	$creds = array(':user'=>$_REQUEST['user'],':passwd'=>md5($_REQUEST['pass']));				//prepared statement data

	$result_login = $dt->query_sqlite($conn, $sql_login, $creds);	//execute query
	debug('(login_controller.inc) User-entered credentials lookup db result',$result_login);		//DEBUG

	//if valid credentials, redirect users to url passed in http referrer or to index page
	if(count($result_login) > 0)
	{
		session_regenerate_id();		//Regenerate session ID to prevent session fixation attacks

		$_SESSION['M2M_SESH_USERID'] = $result_login[0]['u_id'];			//set user id in Session
		$_SESSION['M2M_SESH_USERNAME'] = $result_login[0]['username'];		//set user name in Session
		$_SESSION['M2M_SESH_USERAL'] = $result_login[0]['access_level'];	//set user access level in Session

		//Write session to disc
		session_write_close();		//Write session
		setcookie('login', $_REQUEST['user']);
		//redirect
			if(!empty($_REQUEST['ref']) && !strpos($_REQUEST['ref'], "success") && validateInternalPage($_REQUEST['ref']))
		{
			header("location:".$_REQUEST['ref']);	//to ref url passed in REQUEST
		}
		else
		{
			//header("location:https://".$_SERVER['HTTP_HOST']."/index.php"); //for PROD

			if(hasInstallerAccess())
			{
				header("location:https://".$_SERVER['HTTP_HOST']."/device/installersettings/index.php");
			}
			else
			{
				header("location:https://".$_SERVER['HTTP_HOST']."/device/general/index.php");
			}
		}
	}
	//if invalid credentials, display error
	else
	{
		$html = '<div class="msgBoxLogin">';
		$html .= '<div class="failImage"></div>';
		$html .= '<div class="failMsg">You have entered an incorrect username or password.</div>';
		$html .= '</div>';

		$jscript = '<script type="text/javascript">';
		$jscript .= '$(document).ready(function(){';
		$jscript .= '$(".loginbox form#login").before(\''.$html.'\');';
		$jscript .= '});';
		$jscript .= '</script>';

		echo $jscript;
	}

	$dt->close_sqlite($conn);	//close db connection
}

function validateInternalPage($ref) {
	try {
		if($url = parse_url($ref)){
			if($url["host"] == $_SERVER['HTTP_HOST']){
				$internalPages = array("/device/general/index.php", "/device/gps/index.php", "/device/installersettings/index.php", "/device/j1939/index.php", "/device/modbus/index.php", "/device/settings/index.php", "/device/zigbee/index.php", "/diagnostics/index.php", "/index.php", "/network/cellular/index.php", "/network/ethernet/index.php", "/network/ipreservation/index.php", "/network/iridium/index.php", "/network/portforwarding/index.php", "/network/vpn/index.php", "/support/changepassword/index.php", "/support/knownissues/index.php", "/support/whatsnew/index.php", "/support/debug.php", "/network/wifi/index.php", "/login.php", "/reboot.php");
				if(in_array($url["path"], $internalPages)){
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	catch(Exception $e) {
	  return false;
	}
}
?>
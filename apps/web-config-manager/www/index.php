<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_controller.inc';

if(hasInstallerAccess())
{
	debug("(index.php) User authenticated as Installer or SuperAdmin. Redirecting to installersettings/index");
	header("location:https://".$_SERVER['HTTP_HOST']."/device/installersettings/index.php");
} 
else
{
	debug("(index.php) User authenticated as Admin or User. Redirecting to general/index");
	header("location:https://".$_SERVER['HTTP_HOST']."/device/general/index.php");
} 

exit;

?>

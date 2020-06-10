<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'inc/util.inc';				//contains functions for socket interaction, error message display, and logging.

//OBJECT INSTANTIATION
$msgpriority_ctrl = new dbconfigController();

//VARIABLE INSTANTIATION
$mp_acceleration =
$mp_accept_accel_resumed =
$mp_accept_deccel_resumed =
$mp_calamp_user_msg =
$mp_direction_change =
$mp_driver_status =
$mp_engine_off =
$mp_engine_on =
$mp_engine_param_exceed =
$mp_engine_param_normal =
$mp_engine_period_report =
$mp_engine_trouble_code =
$mp_fuel_log =
$mp_gpsfix_invalid =
$mp_hard_brake =
$mp_j1939 =
$mp_j1939_fault =
$mp_j1939_status2 =
$mp_odometer_update =
$mp_other =
$mp_power_off =
$mp_power_on =
$mp_scheduled_message =
$mp_sensor =
$mp_speed_acceptable =
$mp_speed_exceeded =
$mp_start_condition =
$mp_stop_condition =
$mp_switch_int_power =
$mp_switch_wired_power =
$mp_sensor = 
$mp_text = 20;

$mp_check_in =
$mp_check_out =
$mp_crit_batt = 
$mp_engine_param_exceed = 
$mp_heartbeat =
$mp_heartbeat =
$mp_help =
$mp_not_check_in =
$mp_ok =
$mp_ping = 2;

$mp_ignition_off =
$mp_ignition_on =
$mp_low_batt = 9;


$mp_fall_detected	=
$mp_sos = 1;
$errortxt = '--';

//if settings cannot be read from the device; display an error
if(	$mp_acceleration === false ||
		$mp_accept_accel_resumed === false ||
		$mp_accept_deccel_resumed === false ||
		$mp_calamp_user_msg === false ||
		$mp_direction_change === false ||
		$mp_driver_status === false ||
		$mp_engine_off === false ||
		$mp_engine_on === false ||
		$mp_engine_param_exceed === false ||
		$mp_engine_param_normal === false ||
		$mp_engine_period_report === false ||
		$mp_engine_trouble_code === false ||
		$mp_fuel_log === false ||
		$mp_gpsfix_invalid === false ||
		$mp_hard_brake === false ||
		$mp_j1939 === false ||
		$mp_j1939_fault === false ||
		$mp_j1939_status2 === false ||
		$mp_odometer_update === false ||
		$mp_other === false ||
		$mp_power_off === false ||
		$mp_power_on === false ||
		$mp_scheduled_message === false ||
		$mp_sensor === false ||
		$mp_speed_acceptable === false ||
		$mp_speed_exceeded === false ||
		$mp_start_condition === false ||
		$mp_stop_condition === false ||
		$mp_switch_int_power === false ||
		$mp_switch_wired_power === false ||
		$mp_text === false ||
		$mp_check_in === false ||
		$mp_check_out === false ||
		$mp_crit_batt === false || 
		$mp_engine_param_exceed === false || 
		$mp_heartbeat === false ||
		$mp_heartbeat === false ||
		$mp_help === false ||
		$mp_not_check_in === false ||
		$mp_ok === false ||
		$mp_ping === false ||
		$mp_ignition_off === false ||
		$mp_ignition_on === false ||
		$mp_sensor === false ||
		$mp_low_batt === false ||
		$mp_fall_detected=== false ||
		$mp_sos === false)
	translateStatusCode('502', $_GET['module']);


?>

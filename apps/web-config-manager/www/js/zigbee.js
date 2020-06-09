var g_zigbee_slider_values_def= Object();
function resetSliders()
{
	jQuery.each(g_zigbee_slider_values_def,function(key, value){
		$('#'+ key).slider("value", value);
	});
}
/**
 * ATS-FIXME: Need to fix mainfunctions.js:initializeFields function to
 * properly handle callback functions.  Also it needs to handle reset form
 * event. Once fixed code can be simplified by using this function.
 **/
function updateFieldState(state)
{
	$("#zigbeeSettings input:not([name=zcontrol])").attr("disabled", isOff(state));

	if(isOff(state))
	{
		disableSliders();
	}
	else
	{
		enableSliders();
	}
}

function disableSliders()
{
	$("#co_pri").slider("disable");
	$("#ci_pri").slider("disable");
	$("#statereq_pri").slider("disable");
	return true;
}

function enableSliders()
{
	$("#co_pri").slider("enable");
	$("#ci_pri").slider("enable");
	$("#statereq_pri").slider("enable");
	return true;
}

function updateAllowOverdueState(state)
{
	// the extensions stay off if the Allow Extensions is Off
	combostate = (isOff(state) || isOff($("#SLP_AllowExtensions:checked").val()) )
	if (isOff(state))
	{
		$('.SLP_AllowExtensions').attr('disabled', 'disabled');
		document.getElementById('NotificationTime').disabled = true;
		document.getElementById('HazardExtension').disabled = true;
		document.getElementById('ShiftExtension').disabled = true;
	}
	else
	{
		$('.SLP_AllowExtensions').removeAttr('disabled');
		document.getElementById('NotificationTime').disabled = false;
		document.getElementById('HazardExtension').disabled = isOff($("#SLP_AllowExtensions:checked").val());
		document.getElementById('ShiftExtension').disabled = isOff($("#SLP_AllowExtensions:checked").val());
	}
	
}

function updateAllowExtensionsState(state)
{
	document.getElementById('HazardExtension').disabled = isOff(state);
	document.getElementById('ShiftExtension').disabled = isOff(state);
}



$(document).ready(function(){
	$("input[type=range]").each( function() {
		var v_max = $(this).attr('max');
		var v_min = $(this).attr('min');
		var v_val = $(this).attr('value');
		var v_step= $(this).attr('step');
		var v_name = $(this).attr('name');
		var v_disabled = (typeof($(this).attr('disabled')) != "undefined");
		var parent = $(this).parent();
		g_zigbee_slider_values_def[v_name]=v_val;
		parent.append('<input name="'+v_name+'" type=hidden value='+v_val+' />');
		$(this).replaceWith('<div id="'+v_name+'" class="slider" ></div>');
		parent.children('.slider').slider({
			range: 'max',
			min: Number(v_min),
			max: Number(v_max),
			step: Number(v_step),
			value: Number(v_val),
			change: function(event, ui) {
				$(this).parent().children("input").val(ui.value);
			}
		});
		if(v_disabled)
		{
			parent.children('.slider').slider("disable");
		}
	});
	
	$("button[type=reset]").click(function(){
		resetSliders();
	});
	
	$("input[name=zcontrol]").change(function(){
		updateFieldState($(this).val());
	});
	
	$("#zigbeeSettings form").bind("reset", function(){
		updateFieldState($("input[name=zcontrol]").val());
		updateAllowOverdueState( $("input[name=SLP_AllowOverdue]").val() );
	});
	
	/* SLP Notification controls */
	$("input[name=SLP_AllowOverdue]").change(function(){
		updateAllowOverdueState( $(this).val() );
	});
		
	$(".SLP_AllowExtensions").change
	(
		function()
		{
			updateAllowExtensionsState($(this).val());
		}
	);

});

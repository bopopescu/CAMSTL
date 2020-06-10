function isValidNumber(p_element)
{
	var regEx = /^\d+$/;
	if( hasValidInput(regEx, p_element.val().trim()))
	{
		return true;
	}
	p_element.siblings(".errorMsg").text("Please enter a number.");
	return false;
}

function hasValidInput(p_regEx, p_value)
{
	return p_regEx.test(p_value);
}

function setOffIridiumEnable()
{
	$('input[name="IridiumEnable"]').val([0]);
	return true;
}

$(document).ready(function () {
	$IridiumEnableCtl = initializeFields('satelliteSettings', 'satellitediv',  'IridiumEnableCtl', 'CAMSIridiumSection', 'IridiumEnable',undefined, setOffIridiumEnable);
	$camsIridiumStatus = initializeFields('satelliteSettings', 'CAMSIridiumSection', 'IridiumEnable' );
	$("form#satelliteSettings input,form#satelliteSettings select").on("change keyup",function()
	{
		validateSateliteForm();
	});
});

function validateSateliteForm()
{
	$("form#satelliteSettings").find('.errorMsg').empty();
	var enable = true;
	enable &= ($("input[name='IridiumDataLimit']").is(':disabled'))? true: hasValidRange($("input[name='IridiumDataLimit']"),1024, 40960, false);
	enable &= ($("input[name='IridiumDataLimitTimeout']").is(':disabled'))? true: hasValidRange($("input[name='IridiumDataLimitTimeout']"),14400, 345600, false);
	enable &= ($("input[name='IridiumUpdateIntervalCtl']").is(':disabled'))? true: hasValidRange($("input[name='IridiumUpdateIntervalCtl']"), 0, 1440, false);
	enable &= ($("input[name='ModbusReportingIntervalCtl']").is(':disabled'))? true: hasValidRange($("input[name='ModbusReportingIntervalCtl']"), 0, 1440, false);
	enable &= ($("input[name='camsRetries']").is(':disabled'))? true: hasValidRange($("input[name='camsRetries']"), 1, 20, false);
	enable &= ($("input[name='camsIridiumTimeout']").prop('disabled'))? true: hasValidRange($("input[name='camsIridiumTimeout']"), 0, 60, false);

	$("form#satelliteSettings button[type='submit']").prop('disabled', !enable);
}

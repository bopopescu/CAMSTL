$(document).ready(function () {
	$camsStatus = initializeFields('installerSettings', 'CAMSSection', 'cams');
	$("form#installerSettings input,form#installerSettings select").on("change keyup", validateInstallerForm);
	validateInstallerForm();
	updateRSSI();

});

function validateInstallerForm()
{
	$("form#installerSettings").find(".errorMsg").empty();
	var enable = true;
	enable &= ($("input[name='positionUpdateTime']").is(':disabled'))? true : isValidNumber($("input[name='positionUpdateTime']"));
	enable &= ($("input[name='positionHeading']").is(':disabled'))? true : hasValidRange($("input[name='positionHeading']"), 5, 30, true);
	enable &= ($("input[name='apn']").is(':disabled'))? true : isNotEmpty($("input[name='apn']"));
	enable &= ($("input[name='hardwareKeepAwake']").is(':disabled'))? true : hasValidRange($("input[name='hardwareKeepAwake']"), 0, 180, false);
	enable &= ($("input[name='camsHost']").is(':disabled'))? true : isNotEmpty($("input[name='camsHost']"));
	enable &= ($("input[name='camsPort']").is(':disabled'))? true : isValidNumber($("input[name='camsPort']"));
	$("form#installerSettings button[type='submit']").prop("disabled", !enable);
}

function updateRSSI()
{
	setTimeout(function()
	{
		setInterval(function()		//update the gps every 2 seconds
		{
			$.ajax
			({
			  type: 'GET',
			  dataType: "text",
			  async: true,
			  cache: false,
			  url: 'https://'+window.location.hostname+'/inc/installer_view.php',
			  data: { op: 'RSSIupdate' },
				  
			  beforeSend:function(){},
				  
			  success:function(rssidata)
			  {
					$("#XcellRSSI").val(rssidata);
			  },
				  
			  error:function(jqXHR, textStatus, errorThrown)		// ajax request failed
			  {
					$("#XcellRSSI").val("unknown");
			  }
			});
		},10*1000); //end setInterval
	},2*1000);	//end setTimeout}
}


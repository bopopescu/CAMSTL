var g_j1939_slider_values_def = Object();
function resetSliders()
{
	jQuery.each(g_j1939_slider_values_def,function(key, value){
		$('#'+ key).slider("value", value);
	});
}

function validateJ1939Settings()
{
	$("form#j1939SettingsForm").find(".errorMsg").empty();
	var enable = true;
	enable &= ($("input[name='SourceAddress']").is(':disabled'))? true: isValidHex($("input[name='SourceAddress']"));
	enable &= ($("input[name='CellPMRepInt']").is(':disabled'))? true: isValidNumber($("input[name='CellPMRepInt']"));
	enable &= ($("input[name='IrdPMRepInt']").is(':disabled'))? true: isValidNumber($("input[name='IrdPMRepInt']"));
	enable &= ($("input[name='CellEMCRepInt']").is(':disabled'))? true: isValidNumber($("input[name='CellEMCRepInt']"));
	enable &= ($("input[name='CellFMCRepInt']").is(':disabled'))? true: isValidNumber($("input[name='CellFMCRepInt']"));

	$("form#j1939SettingsForm button[type='submit']").prop('disabled', !enable);
}

function validateAddTemplate()
{
	var templateName = $('#templateName').val();
	var templateFile = $('input:file[name=templateFile]').val();
	var status = (($("#addJ1939Template button.button2-link").attr("disabled")) == "disabled");
	if((templateName != "" ) && (templateFile != "") && status)
	{
		$("#addJ1939Template button.button2-link").attr("disabled", false);
	}
	else if((!status)&&((templateName == "") || (templateFile == "")))
	{
		$("#addJ1939Template button.button2-link").attr("disabled", true);
	}
}

function validateActivateTemplate()
{
	var template = $('#activateJ1939Template select[name="template_name"]').val();
	if(template != "*")
	{
		$("#activateJ1939Template button.button2-link").attr("disabled", false);
	}
	else
	{
		$("#activateJ1939Template button.button2-link").attr("disabled", true);
	}
}

function checkTemplateName(e)
{
	var templateName = $('#templateName').val();
	var status = (($("#addJ1939Template button.button2-link").attr("disabled")) == "disabled");
	if(status)
	{
		e.preventDefault();
		return;
	}
	var found = false;

	$.each(g_templateNames, function(key, value)
	{
		if(value == templateName)
		{
			found = true;
			return false;
		}
	});

	if(found)
	{
		e.preventDefault();
		$("#dialog").attr("title","Add Template...");
		$("#dialog").html("<p >A template exists with the same name. Do you want to overwrite this template?</p>");
		$("#dialog").dialog({
			resizable: false,
			height:"auto",
			modal: true,
			buttons: {
				"Yes": function() {
					$("#addJ1939Template").off("submit");
					displaySavingMessage();
					$("#addJ1939Template").submit();
					$(this).dialog("close");
				},
				"No": function() {
					$(this).dialog("close");
				}
			}
		});
		return;
	}
	displaySavingMessage();
	return;
}
//Array to store url codes for displaying messages. These codes match the ones defined in
//config_mang.db
var g_codes = {
	'failedToSaveSettings': '11',
};
function addTemplate(templateId)
{
	var dataStr = templateId + "&assignments="+assignments;
	// submit the data via ajax
	$.ajax({
			url: 'http://'+window.location.hostname+'/inc/j1939_controller.php',
			type: 'POST',
			data: "op=addTemplate&templateId="+dataStr,
			dataType: "text",
			beforeSend:function(){

				displaySavingMessage();

			},
			success:function(result)
			{
				window.location.href = result;
			},
			error:function(jqXHR, textStatus, errorThrown)		// ajax request failed
			{
				alert(textStatus);
				window.location.href = 'http://'+window.location.hostname+'/device/j1939/index.php?success=false&codes='+g_codes['failedtoSaveSettings'];
			},
			complete:function()
			{
				$.unblockUI;
			}
	});
}

function deleteTemplate(templateId, active)
{
	var dataStr = templateId + "&active="+active;
	// submit the data via ajax
	$.ajax({
			url: 'http://'+window.location.hostname+'/inc/j1939_controller.php',
			type: 'POST',
			data: "op=deleteTemplate&templateId="+dataStr,
			dataType: "text",
			beforeSend:function(){
				displaySavingMessage();
			},
			success:function(result)
			{
				window.location.href = result;
			},
			error:function(jqXHR, textStatus, errorThrown)		// ajax request failed
			{
				alert(textStatus);
				window.location.href = 'http://'+window.location.hostname+'/device/j1939/index.php?success=false&codes='+g_codes['failedtoSaveSettings'];
			},
			complete:function()
			{
				$.unblockUI;
			}
	});
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
		g_j1939_slider_values_def[v_name]=v_val;
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

	//Bind form onchange event to AddTemplate Form
	$("#addJ1939Template").off("submit");
	$("#addJ1939Template").submit(function(event)
	{
		checkTemplateName(event);
	});
	$("#addJ1939Template input").change(validateAddTemplate);
	$("#addJ1939Template button.button3-link").click(validateAddTemplate);

	$("#activateJ1939Template select").change(validateActivateTemplate);

	initializeFields("j1939SettingsForm", "j1939SettingsForm", "enable");

	// Delete icon operation
	$("a.deleteTemplate,a.deleteTemplateDisabled").click(function(e){
		e.preventDefault();

		if($(this).attr("class") == "deleteTemplateDisabled")
		{
			return;
		}

		var templateName = $(this).closest('tr').attr("id").substr(3);

		if(templateName == g_activeTemplate)
		{
			$("#dialog").attr("title","Delete Template...");
			$("#dialog").html("<p >This template is still active. Do you still want to delete the template?</p>");
			$("#dialog").dialog({
				resizable: false,
				height:"auto",
				modal: true,
				buttons: {
					"Delete": function() {
						deleteTemplate(templateName,true);
						$(this).dialog("close");
					},
					"Cancel": function() {
						$(this).dialog("close");
					}
				}
			});
		}
		else
		{
			deleteTemplate(templateName, false);
		}
	});

	$("a.deleteTemplateDisabled").click(function(e){
		e.preventDefault();
	});

	$('input[name=CellEMCRepInt]').on("change keyup", function()
		{
			$('input[name=IrdEMCRepInt').val($(this).val());
		});

	$('input[name=CellFMCRepInt]').on("change keyup", function()
		{
			$('input[name=IrdFMCRepInt').val($(this).val());
		});

	$("form#j1939SettingsForm input,form#satelliteSettings select").on("change keyup",function(){validateJ1939Settings();});
	validateActivateTemplate();
});
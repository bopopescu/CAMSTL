var g_j1939_slider_values_def = Object();

function resetSliders()
{
	jQuery.each(g_j1939_slider_values_def,function(key, value){
		$('#'+ key).slider("value", value);
	});
}

$(document).ready(function()
{
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

	});

	$("button[type='reset']").on("click", function()
	{
		event.preventDefault();
		$(this).closest('form').get(0).reset();
		resetSliders();
	});
});
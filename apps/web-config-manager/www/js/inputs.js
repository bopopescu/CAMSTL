var g_onslidervalue ={};
var g_offslidervalue ={};



$('select[name="gpiInputMonitor1"]').change(function(){updateInputMonitorState(1);});
$('select[name="gpiInputMonitor2"]').change(function(){updateInputMonitorState(2);});
$('select[name="gpiInputMonitor3"]').change(function(){updateInputMonitorState(3);});
$('select[name="gpiInputMonitor4"]').change(function(){updateInputMonitorState(4);});
$('select[name="gpiInputMonitor5"]').change(function(){updateInputMonitorState(5);});
$('select[name="gpiInputMonitor6"]').change(function(){updateInputMonitorState(6);});


function updateInputMonitorState(index)
{
	var theName = 'gpiInputMonitor'+index;
	
  var val = $("select[name="+theName+"]").val();
	
	if (val == 0 )
	{
		
		$("select[name='gpiInputActive"+index+"']").attr("disabled", true);
		$("select[name='gpiInputDebounce"+index+"']").attr("disabled", true);
		$("select[name='onmessage_type"+index+"']").attr("disabled", true);
		$("select[name='offmessage_type"+index+"']").attr("disabled", true);
		$('#onpri'+index).slider("disable");
		$('#offpri'+index).slider("disable");
	}
	else
	{
		$("select[name='gpiInputActive"+index+"']").attr("disabled", false);
		$("select[name='gpiInputDebounce"+index+"']").attr("disabled", false);
		$("select[name='onmessage_type"+index+"']").attr("disabled", false);
		$("select[name='offmessage_type"+index+"']").attr("disabled", false);
		$('#onpri'+index).slider("enable");
		$('#offpri'+index).slider("enable");
	}
}


function updateSlider()
{
	type = $("select[name='offmessage_type"+val+"']").val();
	if( type > 0 && type != 19 ){
		$('#'+offpri).slider("enable");
	}
}

function updateInputConfigState(state)
{
  var val = $('input[name="gpiInput"]:checked').val();
  
  if(state == false)
  {
		turnTrigger(false, val);
	}
	else 
	{
		turnTrigger(true, val);
	}
}

function switchChannel()
{	
	$('.input1,.input2,.input3,.input4,.input5,.input6').each(function(){
		$(this).attr('style', 'display:none');
	});

	var val = $('input[name="gpiInput"]:checked').val();
	$('.input'+val).each(function(){
		$(this).attr('style', 'display:block');
	});
}

function updateInputConfig()
{
  var val = $('input[name="gpiInput"]:checked').val();
  var a = "Input " + val + " Configuration";
  $("span[name=inputConfig]").text(a); 
  switchChannel();
	if( $("input[name=gpiInputMonitor"+val+"]:checked").val() == 1) {
		turnTrigger(true,val);
	}else{
		turnTrigger(false,val);
	}
}

$(document).ready(function(){
	$('input[name="gpiMonitor"]').change
	(
	function()
	{
	  var val = $('input[name="gpiMonitor"]:checked').val();

		if (val == 0)
		{
			$('#gpio_smart_tables :input').attr('disabled', true);

			for( i = 1; i <= 6; i++)
			{
				$('#onpri'+i).slider("disable");
				$('#offpri'+i).slider("disable");
			}
		}
		else
		{
			$('#gpio_smart_tables :input').removeAttr('disabled');
			updateInputMonitorState(1);
			updateInputMonitorState(2);
			updateInputMonitorState(3);
			updateInputMonitorState(4);
			updateInputMonitorState(5);
			updateInputMonitorState(6);
		}
	}
	);  // End of change
	
	//$("input[name=gpiInputMonitor1],input[name=gpiInputMonitor2],input[name=gpiInputMonitor3],input[name=gpiInputMonitor4],input[name=gpiInputMonitor5],input[name=gpiInputMonitor6]").each(function(){
		for( i = 1; i <= 6; i++)
		{
			updateInputMonitorState(i);
		}
	//});
  $("input[id='inputsslide'][type=range]").each( function() {
    var v_max = $(this).attr('max');
    var v_min = $(this).attr('min');
    var v_val = $(this).attr('value');
    var v_step= $(this).attr('step');
    var v_name = $(this).attr('name');
    var isON = true;
    var index;
    var v_disabled=false;
    if( v_name.substring(0,2) != "on" ){
      isON = false;
      index = v_name.substring(7,6);
    }else {
      index = v_name.substring(6,5);
    }

    if( isON ) {
      var type = $("select[name='onmessage_type"+index+"']").val();
      if( type == 19 || type == 0 || type == undefined || type == null ){
        v_disabled=true;
      }
    } else {
      var type = $("select[name='offmessage_type"+index+"']").val();
      if( type == 19 || type == 0 || type == undefined || type == null ){
        v_disabled=true;
      }
    }

    var parent = $(this).parent();
    parent.append('<input id="inputsslide" name="'+v_name+'" type=hidden value='+v_val+' />');
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
      parent.children('.slider').slider("value", 1);
    }
  });

  $("input[name=gpiInput]").change(function(){
    updateInputConfig();
  });

	$("input[name=gpiInputMonitor2],input[name=gpiInputMonitor3],input[name=gpiInputMonitor4],input[name=gpiInputMonitor5],input[name=gpiInputMonitor6]").change(function(){
    updateInputConfigState(($(this).val()==1)?true:false);
  });
  

  $('select[class=onmessagetype]').on('change', function(){
    var val = $('input[name="gpiInput"]:checked').val();
    var sl='onpri'+val;
    var v = $(this).val();
    var check = $('#'+sl).slider('option','disabled');
    if( v == 0 || v == undefined || v == null ) {
      $('#'+sl).slider('disable');
      $('#'+sl).slider('value', 1);
    }
    else if( v == 19 ){
      if( check == false){
        var val=$('#'+sl).slider("option", "value");
        g_onslidervalue[sl] = val;
        $('#'+sl).slider('disable');
        $('#'+sl).slider('value', 3);
      }
    } else{
      if(check == true){
        $('#'+sl).slider('enable');
        if( sl in g_onslidervalue ){
          $('#'+sl).slider('value', g_onslidervalue[sl]);
        }
      }
    }
  });

  $('select[class=offmessagetype]').on('change', function(){
    var val = $('input[name="gpiInput"]:checked').val();
    var sl='offpri'+val;
    var v = $(this).val();
    var check = $('#'+sl).slider('option','disabled');
    if( v == 0 || v == undefined || v == null ) {
      $('#'+sl).slider('disable');
      $('#'+sl).slider('value', 1);
    }
    else if( v == 19 ){
      if( check == false){
        var val=$('#'+sl).slider("option", "value");
        g_offslidervalue[sl] = val;
        $('#'+sl).slider('disable');
        $('#'+sl).slider('value', 3);
      }
    } else{
      if(check == true){
        $('#'+sl).slider('enable');
        if( sl in g_offslidervalue ){
          $('#'+sl).slider('value', g_offslidervalue[sl]);
        }
      }
    }
  });
});

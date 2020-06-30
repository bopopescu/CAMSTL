/**
 * This script performs the ajax call to connect/disconnect a vpn policy.
 * @author Sean Toscano (sean@absolutetrac.com)
 */
$(document).ready(function()
{
	$(".vpnConnect").click(function(e){
		
		e.preventDefault();
		var policyname = $(this).attr('id').substr(8);				//parse the policyname from the field id
		var ajax_data = "op=ipsecconnect&policy="+policyname;	//build the request string
		
		$this = $(this);		//retain context
		
		$vpnConnect = $.ajax({
			  type: 'GET',
			  dataType: "xml",
			  url: 'https://'+window.location.hostname+'/inc/ipsec_ajax_controls.php',
			  data: ajax_data,
			  async: true,
			  cache: false,
			  timeout: 120000,			//2 minute timeout
			  
			  beforeSend:function(){
			    $('.realtime').show();			//show the loading graphic
			    $(".msgBox").empty().hide();	//hide the success/error message box
			  },
			  
			  success:function(vpn){

			    if($(vpn).find('status').text() == 'connected')		//if policy is connected
			    {			
			    	$this.parents('td').siblings(".status").html("Connected");	//update the status column					
					$this.parent().hide();										//hide the 'Connect' link
					$this.parent().siblings('.connect.inactivelink').show();	//show the 'Connect' text
					$this.parent().siblings('.disconnect.inactivelink').hide();	//hide the 'Disconnect' text
					$this.parent().siblings('.disconnect.activelink').show();	//show the 'Disconnect' link
			    }
			    
			    $('.realtime').hide();			//hide the loading graphic

			  },
			  
			  error:function(jqXHR, textStatus, errorThrown){
				 $('.realtime').hide();			//hide the loading graphic
				 $(".msgBox").append('<div class="failImage"></div><div class="failMsg">Failed to connect vpn policy: '+ policyname +'</div><div style="clear:both;"></div>').show();
			  }
		}); //end ajax
		
	});	//end click

	$(".vpnDisconnect").click(function(e){
		
		e.preventDefault();
		var policyname = $(this).attr('id').substr(11);					//parse the policyname from the field id
		var ajax_data = "op=ipsecdisconnect&policy="+policyname;		//build the request string
		
		$this = $(this);			//retain context
		
		$vpnDisconnect = $.ajax({
			  type: 'GET',
			  dataType: "xml",
			  url: 'https://'+window.location.hostname+'/inc/ipsec_ajax_controls.php',
			  data: ajax_data,
			  async: true,
			  cache: false,
			  timeout: 120000,			//2 minute timeout
			  
			  beforeSend:function(){
			    $('.realtime').show();			//show the loading graphic
			    $(".msgBox").empty().hide();	//hide the success/error message box
			  },
			  
			  success:function(vpn){

			    if($(vpn).find('status').text() == 'disconnected')		//if policy is disconnected
			    {
			    	$this.parents("td").siblings(".status").html("Disconnected");		//update the status column				
					$this.parent().hide();												//hide the 'Disconnect' link
					$this.parent().siblings('.disconnect.inactivelink').show();			//show the 'Disconnect' text
					$this.parent().siblings('.connect.inactivelink').hide();			//hide the 'Connect' text
					$this.parent().siblings('.connect.activelink').show();				//show the 'Connect' link
			    }
			    
			    $('.realtime').hide();			//hide the loading graphic

			  },
			  
			  error:function(jqXHR, textStatus, errorThrown){
				  $('.realtime').hide();
				  $(".msgBox").append('<div class="failImage"></div><div class="failMsg">Failed to disconnect vpn policy: '+ policyname +'</div><div style="clear:both;"></div>').show();
				 
			  }
		}); //end ajax
	});	//end click
}); //end doc ready

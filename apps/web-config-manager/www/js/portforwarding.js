$(document).ready(function(){

	
	// Edit icon operation
	$("a.editRule").click(function(e){
		e.preventDefault();
		
		// Clear the error message bpx
		$('div.msgBox').empty().hide();
		
		// Remove highlighting on failed fields
		$(".highlighted-error").removeClass("highlighted-error");
		
		// The rule number to edit
		var ruleToEdit = $(this).closest("tr").index() + 1;		//indexes are zero-based
		
		// Update the signage
		$("#portForwardingForm legend span").text("Edit");
		
		// Replace the Clear button with a Cancel edit button
		$("#portForwardingForm button[type = reset]").hide();
		$("#portForwardingForm button.cancelEdit").show();
		
		// Update the rule number
		$("input[name = newRuleIndex]").val(ruleToEdit);			
		
		// Switch the op param
		$("input[name = op]").val("edit");
		
		// Parse the table row and populate the form fields
		$(this).parent().siblings().each(function(){
			
			// Name
			if($(this).attr('class') == "name")
			{
				$("#portForwardingForm input[name = ruleName]").val($(this).text());
			}
			
			// MAC
			if($(this).attr('class') == "mac")
			{
				var macAddr = $(this).text();
				var mac = macAddr.split(':');
				
				
				$("#portForwardingForm input[name = mac1]").val(mac[0]);
				$("#portForwardingForm input[name = mac2]").val(mac[1]);
				$("#portForwardingForm input[name = mac3]").val(mac[2]);
				$("#portForwardingForm input[name = mac4]").val(mac[3]);
				$("#portForwardingForm input[name = mac5]").val(mac[4]);
				$("#portForwardingForm input[name = mac6]").val(mac[5]);
				
			}
			
			// IP
			if($(this).attr('class') == "ip")
			{	
				var ipAddresses = $(this).find("li"); 
				
				$("select[name = interface] option").removeAttr("selected");
				
				if(ipAddresses.length == 1)
				{
					$("select[name = interface]").val(ipAddresses.first().attr('id'));
				}	
				else
				{
					$("select[name = interface]").val("all");
				}
				
				var ip = ipAddresses.first().text().split('.');
				
				$("#portForwardingForm input[name = ip4]").val(ip[3]);
				
			}
			
			// Port
			if($(this).attr('class') == "port")
			{
				var portRange = $(this).text();
				var port = portRange.split('-');
				
				$("#portForwardingForm input[name = portStart]").val(port[0]);
				$("#portForwardingForm input[name = portEnd]").val(port[1]);
			}

			// sPort
			if($(this).attr('class') == "sPort")
			{
				var sPortRange = $(this).text();
				var sPort = sPortRange.split('-');
				
				$("#portForwardingForm input[name = sPortStart]").val(sPort[0]);
				$("#portForwardingForm input[name = sPortEnd]").val(sPort[1]);
			}
			
			//Protocol
			if($(this).attr('class') == "protocol")
			{
				$("select[name = protocol] option").removeAttr("selected");
				var protocol = $(this).text();
				
				if(protocol.indexOf("/") > -1)
				{
					$("select[name = protocol]").val("All");
				}
				else
				{
					$("select[name = protocol]").val(protocol);
				}
			}
			
		});
		
	});
	
	
	// Cancel Edit button operation
	$("button.cancelEdit").click(function(e){
		
		// Revert signage
		$("#portForwardingForm legend span").text("Add");
		
		// Reset the form
		$("#portForwardingForm")[0].reset();
		
		// Hide Cancel Changes button
		$("#portForwardingForm button.cancelEdit").hide();
		
		// Show Clear button
		$("#portForwardingForm button[type = reset]").show();
		
		// Reset newRuleIndex field to the number of rows in the table
		$("input[name = newRuleIndex]").val($("#port-forwarding-listings tbody tr").size()+1);
		
		// Switch the op param
		$("input[name = op]").val("add");
	});
	
	
	
	// Delete icon operation
	$("a.deleteRule").click(function(e){
		e.preventDefault();
		
		var ajaxString = '';
		var ruleIndex = 0;
        var ruleIndexToDelete = 0;
        
        // find index of rule to be deleted
        ruleIndexToDelete = $(this).closest("tr").index();	//indexes are zero-based
        ruleIndexToDelete++;								//rules in db-config are one-based
		
        
        // package all the other rules that need to be re-ordered and saved
        // for each table row
		$(this).closest("tr").siblings().each(function(index){

			ruleIndex = ++index;
			
			// read each column and form a url parameter string 
			$(this).children("td").not(".delete, .edit").each(function(index){
				
				ajaxString += '&' + $(this).attr('class') + ruleIndex + '=' + $(this).text();
				
			});

		});
	
		
		// submit the data via ajax
		$.ajax({
			  url: 'https://'+window.location.hostname+'/inc/portforwarding_processor.php',
			  type: 'POST',
			  data: "op=delete&ruleNum="+ruleIndexToDelete+"&totalRules="+(ruleIndex+1)+ajaxString,
			  dataType: "text",
			  beforeSend:function(){
				  
				  displaySavingMessage();
				  
			  },
			  success:function(result)
			  {
				  //alert(result);
				  window.location.href = result;
				  
				  /*var resultString = $.parseJSON(result);
				  
				  
				  if(resultString.success == "true")
				  {
					  window.location.href = 'https://'+window.location.hostname+'/network/portforwarding/index.php?success=true&codes=10,14';
				  }
				  else if(resultString.success == 'false' || resultString.success == false || result == null)
				  {
					  window.location.href = 'https://'+window.location.hostname+'/network/portforwarding/index.php?success=false&codes=12';
				  }*/
			  },
			  error:function(jqXHR, textStatus, errorThrown)		// ajax request failed
			  {
				  alert(textStatus);
				  window.location.href = 'https://'+window.location.hostname+'/network/portforwarding/index.php?success=false&codes=11';
			  },
			  complete:function()
			  {
				  $.unblockUI;
			  }
			  
		});
	});
	
});


	
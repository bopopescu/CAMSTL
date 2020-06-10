$(document).ready(function(){
	
	
	// Edit icon operation
	$("a.editRule").click(function(e){
		e.preventDefault();
		
		// Clear the error message bpx
		$('div.msgBox').empty().hide();
		
		// Remove highlighting on failed fields
		$(".highlighted-error").removeClass("highlighted-error");
				
		// Update the signage
		$("#ipReservationForm legend span").text("Edit");
		
		// Replace the Clear button with a Cancel edit button
		$("#ipReservationForm button[type = reset]").hide();
		$("#ipReservationForm button.cancelEdit").show();
		
		// The rule number to edit
		var ruleToEdit = $(this).closest("tr").attr('id');		//indexes are zero-based

		// Update the rule number
		$("input[name = newRuleIndex]").val(ruleToEdit);			
		
		// Switch the op param
		$("input[name = op]").val("edit");
		
		// Parse the table row and populate the form fields
		$(this).parent().siblings().each(function(){
			
			// Name
			if($(this).attr('class') == "name")
			{
				$("#ipReservationForm input[name = ruleName]").val($(this).text());
			}
			
			// MAC
			if($(this).attr('class') == "mac")
			{
				var macAddr = $(this).text();
				var mac = macAddr.split(':');
				
				
				$("#ipReservationForm input[name = mac1]").val(mac[0]);
				$("#ipReservationForm input[name = mac2]").val(mac[1]);
				$("#ipReservationForm input[name = mac3]").val(mac[2]);
				$("#ipReservationForm input[name = mac4]").val(mac[3]);
				$("#ipReservationForm input[name = mac5]").val(mac[4]);
				$("#ipReservationForm input[name = mac6]").val(mac[5]);
				
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
				
				$("#ipReservationForm input[name = ip4]").val(ip[3]);
				
			}
			
		});
		
	});
	
	
	// Cancel Edit button operation
	$("button.cancelEdit").click(function(e){
		
		// Revert signage
		$("#ipReservationForm legend span").text("Add");
		
		// Reset the form
		$("#ipReservationForm")[0].reset();
		
		// Hide Cancel Changes button
		$("#ipReservationForm button.cancelEdit").hide();
		
		// Show Clear button
		$("#ipReservationForm button[type = reset]").show();
		
		// Reset newRuleIndex field to the number of rows in the table
		//$("input[name = newRuleIndex]").val($("#port-forwarding-listings tbody tr").size()+1);
		$("input[name = newRuleIndex]").val(newRuleIndex);
		
		// Switch the op param
		$("input[name = op]").val("add");
	});
	
	// Disabled Edit icon operation
	$("a.editRuleDisabled").click(function(e){
		e.preventDefault();
		alert("This reservation is part of a port forwarding rule and can only be modified from the Port Forwarding page.");
		
	});
	
	// Disabled Delete icon operation
	$("a.deleteRuleDisabled").click(function(e){
		e.preventDefault();
		alert("This reservation is part of a port forwarding rule and can only be deleted from the Port Forwarding page.");
		
	});
	
	// Delete icon operation
	$("a.deleteRule").click(function(e){
		e.preventDefault();
		
		var ajaxString = '';
		var ruleIndex = 0;
        var ruleIndexToDelete = 0;
        var totalCount = 0;
        
        var row = $(this).closest("tr");
        
        // find index of rule to be deleted
        ruleIndexToDelete = row.attr('id');	//indexes are zero-based
        
        // find total number of rules for this type
       	totalCount = $("#ip-reservation-listings tbody tr.reservation").length + 200;
        
        // package all the other rules that need to be re-ordered and saved
        // for each table row
		$(this).closest("tr").siblings().each(function(index){

			ruleIndex = ++index;
			
			// read each column and form a url parameter string 
			$(this).children("td").not(".delete, .edit").each(function(index){
				
				ajaxString += '&' + $(this).attr('class') + ruleIndex + '=' + $(this).text();
				
			});

		});
	
		//alert("rule to delete = " + ruleIndexToDelete);
		//alert("total rules = " + totalCount);
		
		// submit the data via ajax
		$.ajax({
			  url: 'http://'+window.location.hostname+'/inc/ipreservation_processor.php',
			  type: 'POST',
			  data: "op=delete&ruleNum="+ruleIndexToDelete+"&totalRules="+totalCount+ajaxString,
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
				  window.location.href = 'http://'+window.location.hostname+'/network/ipreservation/index.php?success=false&codes=11';
			  },
			  complete:function()
			  {
				  $.unblockUI;
			  }
			  
		});
	});
	
});

	
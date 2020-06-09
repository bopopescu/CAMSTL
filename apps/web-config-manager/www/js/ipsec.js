$(document).ready(function()
{
	
	//<!-- script to check for special characters -->
	$("span.scCheck > input").blur(function(){
	
		specialCharCheck($(this));
		
	});
			

	//code to activate the ipsec tab when edit is clicked on the policy summary page
	$('.editIpsec').click(function(e){
  		e.preventDefault();
  	    $(".level3tabs").tabs("option", "active", 1);
	});


	//<!-- script to switch from 4 ip octet fields, to 1 textfield for fqdn for Remote Server in IPSEC -->
	//on page load---
        	  if ($("form#ipsec select[name = 'ipsecEPtype'] option:selected").val() == 'FQDN')
              {
            	  enableRemoteFQDNField();
              }
              else
              {
            	  enableRemoteIPFields();
              }
              switchVPNMode();
        	  //on change--
        	//hide show Host FQDN/IP div on mouseclick 
            $("form#ipsec select[name = 'ipsecEPtype']").change(function(){
        		  var selection = $(this).val();
        		  if(selection == 'FQDN')
        		  {
        			 
            	  enableRemoteFQDNField();
            	  validateEmptyFields($("form#ipsec input[name = 'ipsecREMfqdn']"));
            	  

        			  
        		  }
        		  if(selection == 'IP')
        		  {
        			  enableRemoteIPFields();
                	  validateIP("ipsecREMIP");

        		  } 
            
        	  }); 
        	  
 
                //hide show Host IP/FQDN div on keyup 
                $("form#ipsec select[name = 'ipsecEPtype']").keyup(function () {

                    $("form#ipsec select[name = 'ipsecEPtype'] option:selected").each(function () {
                        var selection = $(this).val();
                        
                        if (selection == "IP") {
                         validateIP("ipsecREMIP");
               			 enableRemoteIPFields();
                       }
                       if (selection == "FQDN")
                       	{
                     	  validateEmptyFields($("form#ipsec input[name = 'ipsecREMfqdn']"));
                    	  enableRemoteFQDNField();
                       	}
                });
                   
                })
                .change();   
                
            	
        	    
        	  
        	 

	
   //<!-- script to enable/disable IPSEC Local Network Type -->
        	  if ($("form#ipsec select[name = 'ipsecLOCtype'] option:selected").val() == 'Range')
            {
              enableLocalRangeFields();
            }
            else
            {
             enableLocalSubnetFields();
            }

              $("form#ipsec select[name = 'ipsecLOCtype']").change(function(){
        		  var selection = $(this).val();
        		  if(selection == 'Range')
        		  {
        			  enableLocalRangeFields();
                if( $("form#ipsec input[name='vpnMode']:checked").val() == "GateWay" )
                {
                  validateIP("ipsecLSIP");
                  validateIP("ipsecLEIP");
                }
        		  }
        		  if(selection == 'Subnet')
        		  {
        			  enableLocalSubnetFields();
                if( $("form#ipsec input[name='vpnMode']:checked").val() == "GateWay" )
                {
                	  validateIP("ipsecLSIP");
                    validateIP("ipsecLIPSN");
                }
        		  } 
            
        	  }); 
        	 
       	    
                //hide show Host Range/Subnet div on keyup 
                $("form#ipsec select[name = 'ipsecLOCtype']").keyup(function () {

                  $("form#ipsec select[name = 'ipsecLOCtype'] option:selected").each(function () {
                    var selection = $(this).val();

                    if (selection == "Range") {
                     enableLocalRangeFields();
                     if( $("form#ipsec input[name='vpnMode']:checked").val() == "GateWay" )
                     {
                       validateIP("ipsecLSIP");
                       validateIP("ipsecLEIP");
                     }
                   }
                   if (selection == "Subnet")
                       	{
             			  enableLocalSubnetFields();
                     if( $("form#ipsec input[name='vpnMode']:checked").val() == "GateWay" )
                     {
                    	  validateIP("ipsecLSIP");
                		  validateIP("ipsecLIPSN");
                    }
                  }
                });
                   
                })
                .change(); 
        	 
        	 

        	  
        	  //<!-- script to enable/disable IPSEC Remote IP -->
        	  if ($("form#ipsec select[name = 'ipsecREMtype'] option:selected").val() == 'Range')
              {
        		  enableRemoteRangeFields(); 
  
              }
              else
              {
            	  enableRemoteSubnetFields();

              }

              $("form#ipsec select[name = 'ipsecREMtype']").change(function(){
        		  var selection = $(this).val();
        		  if(selection == 'Range')
        		  {
        			  enableRemoteRangeFields();
            		  validateIP("ipsecRSIP");
            		  validateIP("ipsecREIP");	  
        		  }
        		  if(selection == 'Subnet')
        		  {
        			  enableRemoteSubnetFields();
                	  validateIP("ipsecRSIP");
            		  validateIP("ipsecRIPSN");
        		  } 
            
        	  }); 
        	  
              $("form#ipsec input[name='vpnMode']").change(switchVPNMode);
        	  
              //hide show Host Range/Subnet div on keyup 
              $("form#ipsec select[name = 'ipsecREMtype']").keyup(function () {

                  $("form#ipsec select[name = 'ipsecREMtype'] option:selected").each(function () {
                      var selection = $(this).val();
                      
                      if (selection == "Range") {
            			  enableRemoteRangeFields();
                		  validateIP("ipsecRSIP");
                		  validateIP("ipsecREIP");	
                     }
                     if (selection == "Subnet")
                     	{
           			  enableRemoteSubnetFields();
                	  validateIP("ipsecRSIP");
            		  validateIP("ipsecRIPSN");
                     	}
              });
                 
              })
              .change(); 

              
              //<!-- enable or disable NAT if off -->
        	  if ($("form#ipsec input[name = 'ipsecnatTrav']:checked").val() == "On")
              {
        			$("form#ipsec input[name = 'ipsecNatFreq']").removeAttr('disabled');

              }
              else
              {
      	        $("form#ipsec input[name = 'ipsecNatFreq']").attr('disabled', true);

              }
        	  
              $("form#ipsec input[name = 'ipsecnatTrav']").change(function () {
        	        var selection = $(this).val();
        	        if (selection == "On") {
            			$("form#ipsec input[name = 'ipsecNatFreq']").removeAttr('disabled');
        	        } else {
              	        $("form#ipsec input[name = 'ipsecNatFreq']").attr('disabled', true);
        	        }

        	    });
        	    
        	 
              
              
        	   
  //<!-- script to switch from 4 ip octet fields, to 1 textfield for email or FQDN for Local ID -->
        	  if ($("form#ipsec select[name = 'ipsecLIDT'] option:selected").val() == 'IP')
              {
        		  enableLocalWANIPFields();

              }
              else
              {
            	  enableLocalEmailorFQDNFields();

              }

              $("form#ipsec select[name = 'ipsecLIDT']").change(function(){
        		  var selection = $(this).val();
        		  if(selection == 'IP')
        		  {
        			  enableLocalWANIPFields();
            		  validateIP("LIDIP");
        		  }
        		  if(selection == 'FQDN' || selection == 'Email')
        		  {
        			  enableLocalEmailorFQDNFields();
        		  } 
            
        	  }); 
        	  
        	  
        	  
      	   
        	   
               //hide show Host IP/FQDN/Email div on keyup 
               $("form#ipsec select[name = 'ipsecLIDT']").keyup(function () {

                   $("form#ipsec select[name = 'ipsecLIDT'] option:selected").each(function () {
                       var selection = $(this).val();
                       
                       if (selection == "IP") {
             			  enableLocalWANIPFields();
                		  validateIP("LIDIP");
                      }
                      if (selection == "FQDN"  || selection == "Email")
                      	{
            			  enableLocalEmailorFQDNFields();
                      	}
               });
                  
               })
               .change(); 
        	  
        	  

        	  //<!-- script to switch from 4 ip octet fields, to 1 textfield for email for Remote WAN IP ID -->
        	  if ($("form#ipsec select[name = 'ipsecRIT'] option:selected").val() == 'IP')
              {
            	  enableRemoteWANIPFields();
              }
              else
              {
            	  enableRemoteEmailorFQDNFields();
              }

              $("form#ipsec select[name = 'ipsecRIT']").change(function(){
        		  var selection = $(this).val();
        		  if(selection == 'IP')
        		  {
        			  enableRemoteWANIPFields();
        			  validateIP("ipsecRWANIP");
        		  }
        		  if(selection == 'FQDN')
        		  {
        			  enableRemoteEmailorFQDNFields();
        		  } 
            
    	  	}); 
        	  
    	  	
            //hide show Host IP/FQDN/Email div on keyup 
            $("form#ipsec select[name = 'ipsecRIT']").keyup(function () {

                $("form#ipsec select[name = 'ipsecRIT'] option:selected").each(function () {
                    var selection = $(this).val();
                    
                    if (selection == "IP") {
          			  enableRemoteWANIPFields();
        			  validateIP("ipsecRWANIP");
                   }
                   if (selection == "FQDN")
                   	{
                	   enableRemoteEmailorFQDNFields();
                   	}
            });
               
            })
            .change(); 
    	  	
    	  	
    	  	

    	  	        	  //<!-- script to enable/disable Preshared Key field for Authentication Method -->		  
        	  if ($("form#ipsec select[name = 'ipsecphase1am'] option:selected").val() == 'Preshared')
              {
            	  $("form#ipsec input[name = 'ipsecpskey']").removeAttr('disabled');
            	  $("form#ipsec span[name = 'errorMPK']").show();
            
              }
              else
              {
            	  $("form#ipsec input[name = 'ipsecpskey']").attr('disabled', true);
            	  $("form#ipsec span[name = 'errorMPK']").hide();
              }

              $("form#ipsec select[name = 'ipsecphase1am']").change(function(){
        		  var selection = $(this).val();
        		  if(selection == 'Preshared')
        		  {
        			  $("form#ipsec input[name = 'ipsecpskey']").removeAttr('disabled');
        			  $("form#ipsec span[name = 'errorMPK']").show();
        		  }
        		  if(selection == 'RSA' || selection == 'X509')
        		  {
        			  $("form#ipsec input[name = 'ipsecpskey']").attr('disabled', true);
        			  $("form#ipsec span[name = 'errorMPK']").hide();
        		  } 

        	   });
        	  
        	    $("form#ipsec select[name = 'ipsecphase1am']").keypress(function(){
        		  var selection = $(this).val();
        		  if(selection == 'Preshared')
        		  {
        			  $("form#ipsec input[name = 'ipsecpskey']").removeAttr('disabled');
        			  $("form#ipsec span[name = 'errorMPK']").show();
        		  }
        		  if(selection == 'RSA' || selection == 'X509')
        		  {
        			  $("form#ipsec input[name = 'ipsecpskey']").attr('disabled', true);
        			  $("form#ipsec span[name = 'errorMPK']").hide();
        		  } 

        	   });  


        	

	});// END DOC READY


    
        	  function enableRemoteIPFields()
        	  {
        		  $("form#ipsec div[name = 'ipsecRemIPD']").show();
            	  $("form#ipsec span[name = 'errorRemIP']").show();
        		  $("form#ipsec div[name = 'ipsecRemFQDND']").hide();
    			  $("form#ipsec span[name = 'errorRemFQDN']").empty();
        	  } 

        	  function  enableRemoteFQDNField()
        	  {
        		  $("form#ipsec div[name = 'ipsecRemIPD']").hide();
            	  $("form#ipsec span[name = 'errorRemIP']").empty();
        		  $("form#ipsec div[name = 'ipsecRemFQDND']").show();
    			  $("form#ipsec span[name = 'errorRemFQDN']").show();
        	 
               } 

               function switchVPNMode()
               {
                var val = $("form#ipsec input[name='vpnMode']:checked").val();
                if(val == "Client")
                {
                    ClientMode();
                }
                else
                {
                    GateWayMode();
                }
            }

            function ClientMode()
            {
                $("form#ipsec select[name = 'ipsecLOCtype']").attr('disabled', true);
                $("form#ipsec input[name ^= 'ipsecLSIP']").attr('disabled', true);
                $("form#ipsec input[name ^= 'ipsecLEIP']").attr('disabled', true);
                $("form#ipsec input[name ^= 'ipsecLIPSN']").attr('disabled', true);
                $("form#ipsec span[name = 'errorLSIP']").hide();
                $("form#ipsec span[name = 'errorLSIP']").empty();
                $("form#ipsec span[name = 'errorLEIP']").hide();
                $("form#ipsec span[name = 'errorLEIP']").empty();
                $("form#ipsec span[name = 'errorLIPSN']").hide();
                $("form#ipsec span[name = 'errorLIPSN']").empty();
                enableStatusSavebtn($("form#ipsec").attr("id"));
            }

            function GateWayMode()
            {
                $("form#ipsec select[name = 'ipsecLOCtype']").attr('disabled', false);
                $("form#ipsec input[name ^= 'ipsecLSIP']").attr('disabled', false);
                $("form#ipsec input[name ^= 'ipsecLEIP']").attr('disabled', false);
                $("form#ipsec input[name ^= 'ipsecLIPSN']").attr('disabled', false);

                if ($("form#ipsec select[name = 'ipsecLOCtype'] option:selected").val() == 'Range')
                {
                  enableLocalRangeFields();
                }
                else
                {
                  enableLocalSubnetFields();
                }
              }

            function enableLocalRangeFields()
        	  {
              if( $("form#ipsec input[name='vpnMode']:checked").val() == "GateWay" )
              {
                $("form#ipsec input[name ^= 'ipsecLIPSN']").attr('disabled', true);
                $("form#ipsec input[name ^= 'ipsecLEIP']").removeAttr('disabled');
                $("form#ipsec span[name = 'errorLSIP']").show();
                $("form#ipsec span[name = 'errorLEIP']").show();
                $("form#ipsec span[name = 'errorLIPSN']").hide();
                $("form#ipsec span[name = 'errorLIPSN']").empty();
              }
            }

            function enableLocalSubnetFields()
            {
              if( $("form#ipsec input[name='vpnMode']:checked").val() == "GateWay" )
              {
                $("form#ipsec input[name ^= 'ipsecLEIP']").attr('disabled', true);
                $("form#ipsec input[name ^= 'ipsecLIPSN']").removeAttr('disabled');
                $("form#ipsec span[name = 'errorLSIP']").show();
                $("form#ipsec span[name = 'errorLEIP']").hide();
                $("form#ipsec span[name = 'errorLEIP']").empty();
                $("form#ipsec span[name = 'errorLIPSN']").show();
              }
            }
        	  

        	  function enableRemoteRangeFields()
        	  {
        		  $("form#ipsec input[name ^= 'ipsecRIPSN']").attr('disabled', true);
        		  $("form#ipsec input[name ^= 'ipsecREIP']").removeAttr('disabled');
        		  $("form#ipsec span[name = 'errorRSIP']").show();
        		  $("form#ipsec span[name = 'errorREIP']").show();
        		  $("form#ipsec span[name = 'errorRIPSN']").hide();
        		  $("form#ipsec span[name = 'errorRIPSN']").empty();
        	  } 

        	  function enableRemoteSubnetFields()
        	  {
        		  $("form#ipsec input[name ^= 'ipsecREIP']").attr('disabled', true);
        		  $("form#ipsec input[name ^= 'ipsecRIPSN']").removeAttr('disabled');
        		  $("form#ipsec span[name = 'errorRSIP']").show();
        		  $("form#ipsec span[name = 'errorRIPSN']").show();
        		  $("form#ipsec span[name = 'errorREIP']").hide();
        		  $("form#ipsec span[name = 'errorREIP']").empty();
        	  }


        	  function enableLocalWANIPFields()
        	  {
        		  $("form#ipsec div[name = 'ipseclocalIDIP']").show();
        		  $("form#ipsec span[name = 'errorLIDIP']").show();
        		  $("form#ipsec div[name = 'ipseclocalIDFE']").hide();
        		  $("form#ipsec span[name = 'errorLIDFQDNEmail']").empty();
        	  } 

        	  function  enableLocalEmailorFQDNFields()
        	  {
        		 $("form#ipsec div[name = 'ipseclocalIDFE']").show();
        		 $("form#ipsec span[name = 'errorLIDFQDNEmail']").show();
        		 $("form#ipsec div[name = 'ipseclocalIDIP']").hide();
        		 $("form#ipsec span[name = 'errorLIDIP']").empty();
        	  }
        	  
        	  function enableRemoteWANIPFields()
        	  {
        		  $("form#ipsec div[name = 'ipsecRemIDIP']").show();
        		  $("form#ipsec span[name = 'errorRemIDIP']").show();
        		  $("form#ipsec div[name = 'ipsecRemIDF']").hide();
        		  $("form#ipsec span[name = 'errorRemIDF']").empty();
        		  
        	  } 

        	  function  enableRemoteEmailorFQDNFields()
        	  {
        		  $("form#ipsec div[name = 'ipsecRemIDIP']").hide();
        		  $("form#ipsec span[name = 'errorRemIDIP']").empty();
        		  $("form#ipsec div[name = 'ipsecRemIDF']").show();
        		  $("form#ipsec span[name = 'errorRemIDF']").show();
        	  }
        	  
        	  

              function specialCharCheck ($field)
              {
            	  var str = $("input[name = 'ipsecPolicyName']").val();

            	  if(str.match(/[^\w-]/gi))
            	  {
                        	$("form#ipsec span[name = 'errorPN']").text("Policy name can only be a-z, 0-9 , '_', or '-'");
                        	enableStatusSavebtn($("form#ipsec").attr("id"));
                        	
                   }

   
              }


$(document).ready(function () {

    //<!-- script to enable/disable CAMS settings -->
	//on page load--
    //if CAMS is turned on
    if ($("form#outputCheck input[name = 'oCAMS']:checked").val() == "0") {
    	if ($("form#outputCheck select[name = 'ocServer'] option:selected").val() == 'IP') {
   		 showdisabledCAMSIP("outputCheck");
    	}
    	if ($("form#outputCheck select[name = 'ocServer'] option:selected").val() == 'DNS') {
      		 showdisabledCAMSDNS("outputCheck");
       	}
    }
    //if CAMS is turned off
    if ($("form#outputCheck input[name = 'oCAMS']:checked").val() == "1") {
    	if ($("form#outputCheck select[name = 'ocServer'] option:selected").val() == 'IP') {
    	 validateIP("oCHostIP");
   		 showCAMSIP("outputCheck");
    	}
    	if ($("form#outputCheck select[name = 'ocServer'] option:selected").val() == 'DNS') {
       	 	validateEmptyFields($("form#outputCheck input[name = 'oCHostDNS']"));
      		 showCAMSDNS("outputCheck");
       	}

        //hide show Host IP/DNS div on mouse click
        $("form#outputCheck select[name = 'ocServer'] option").click(function () {
            var selection = $(this).val();
            if (selection == "IP") {

              	 validateIP("oCHostIP");
          		 showCAMSIP("outputCheck");
            }
            if (selection == "DNS")
            	{
           	 	validateEmptyFields($("form#outputCheck input[name = 'oCHostDNS']"));
        		 showCAMSDNS("outputCheck");
            	}
        });



        //hide show Host IP/DNS div on keyup
        $("form#outputCheck select[name = 'ocServer']").keyup(function () {
        	var selection = "";
            $("form#outputCheck select[name = 'ocServer'] option:selected").each(function () {
                selection = $(this).text() + "";

                if (selection == "IP") {

                 	validateIP("oCHostIP");
             		showCAMSIP("outputCheck");

               }
               if (selection == "DNS")
               	{
              	 validateEmptyFields($("form#outputCheck input[name = 'oCHostDNS']"));
           		showCAMSDNS("outputCheck");

               	}
        });

        })
        .change();



   }
    //on change--
    $("form#outputCheck input[name = 'oCAMS']").change(function () {
        var selection = $(this).val();
        //if CAMS is turned off
        if (selection == "0") {
        	if ($("form#outputCheck select[name = 'ocServer'] option:selected").val() == 'IP') {
          		 showdisabledCAMSIP("outputCheck");
           	}
           	if ($("form#outputCheck select[name = 'ocServer'] option:selected").val() == 'DNS') {
             		 showdisabledCAMSDNS("outputCheck");
              	}

        }
        //if CAMS is turned on
        if (selection == "1") {
        	if ($("form#outputCheck select[name = 'ocServer'] option:selected").val() == 'IP') {
           	 validateIP("oCHostIP");
          		 showCAMSIP("outputCheck");
           	}
           	if ($("form#outputCheck select[name = 'ocServer'] option:selected").val() == 'DNS') {
           	 	validateEmptyFields($("form#outputCheck input[name = 'oCHostDNS']"));
             		 showCAMSDNS("outputCheck");
              	}

            //hide show Host IP/DNS div on mouse click
            $("form#outputCheck select[name = 'ocServer'] option").click(function () {
                var selection = $(this).val();
                if (selection == "IP") {

                  	 validateIP("oCHostIP");
              		 showCAMSIP("outputCheck");

                }
                if (selection == "DNS")
                	{

               	 	validateEmptyFields($("form#outputCheck input[name = 'oCHostDNS']"));
            		 showCAMSDNS("outputCheck");

                	}
            });

            //hide show Host IP/DNS div on keyup
            $("form#outputCheck select[name = 'ocServer']").keyup(function () {
            	var selection = "";
                $("form#outputCheck select[name = 'ocServer'] option:selected").each(function () {
                    selection = $(this).text() + "";

                    if (selection == "IP") {

                     	validateIP("oCHostIP");
                 		showCAMSIP("outputCheck");

                   }
                   if (selection == "DNS")
                   	{
                  	 validateEmptyFields($("form#outputCheck input[name = 'oCHostDNS']"));
               		showCAMSDNS("outputCheck");

                   	}
            });

            })
            .change();

        }

    });


    //<!-- script to enable/disable Trakopolis settings -->
    //on page load--
    //if Trakopolis is turned off
    if ($("form#outputCheck input[name = 'oTrakopolis']:checked").val() == "0") {
    	if ($("form#outputCheck select[name = 'otServer'] option:selected").val() == 'IP') {
   		 showdisabledTrakopolisIP("outputCheck");
    	}
    	if ($("form#outputCheck select[name = 'otServer'] option:selected").val() == 'DNS') {
      		 showdisabledTrakopolisDNS("outputCheck");
       	}
   }
    //if Trakopolis is turned on
    if ($("form#outputCheck input[name = 'oTrakopolis']:checked").val() == "1") {
    	if ($("form#outputCheck select[name = 'otServer'] option:selected").val() == 'IP') {
   		 showTrakopolisIP("outputCheck");
    	}
    	if ($("form#outputCheck select[name = 'otServer'] option:selected").val() == 'DNS') {
      		 showTrakopolisDNS("outputCheck");
       	}

    	//hide show Host IP/DNS div on mouse click
        $("form#outputCheck select[name = 'otServer'] option").click(function () {
            var selection = $(this).val();
            if (selection == "IP") {
              	 validateIP("oTHostIP");
          		 showTrakopolisIP("outputCheck");
            }
            if (selection == "DNS")
            	{
           	 	validateEmptyFields($("form#outputCheck input[name = 'oTHostDNS']"));
        		 showTrakopolisDNS("outputCheck");
            	}
        });


        //hide show Host IP/DNS div on keyup
        $("form#outputCheck select[name = 'otServer']").keyup(function () {
        	var selection = "";
            $("form#outputCheck select[name = 'otServer'] option:selected").each(function () {
                selection = $(this).text() + "";

                if (selection == "IP") {

                 	 validateIP("oTHostIP");
              		 showTrakopolisIP("outputCheck");
               }
               if (selection == "DNS")
               	{
              	 	validateEmptyFields($("form#outputCheck input[name = 'oTHostDNS']"));
           		 showTrakopolisDNS("outputCheck");

               	}
        });

        })
        .change();

   }

    //on change--
    //if Trakopolis is turned off
    $("form#outputCheck input[name = 'oTrakopolis']").change(function () {
        var selection = $(this).val();
        if (selection == "0") {
        	if ($("form#outputCheck select[name = 'otServer'] option:selected").val() == 'IP') {
          		 showdisabledTrakopolisIP("outputCheck");
           	}
           	if ($("form#outputCheck select[name = 'otServer'] option:selected").val() == 'DNS') {
             		 showdisabledTrakopolisDNS("outputCheck");
              	}

        }
        //if Trakopolis is turned on
        if (selection == "1") {
        	if ($("form#outputCheck select[name = 'otServer'] option:selected").val() == 'IP') {
             	 validateIP("oTHostIP");
          		 showTrakopolisIP("outputCheck");
           	}
           	if ($("form#outputCheck select[name = 'otServer'] option:selected").val() == 'DNS') {
           	 	validateEmptyFields($("form#outputCheck input[name = 'oTHostDNS']"));
             		 showTrakopolisDNS("outputCheck");
              	}
            //hide show Host IP/DNS div on mouseclick
            $("form#outputCheck select[name = 'otServer'] option").click(function () {
                var selection = $(this).val();
                if (selection == "IP") {
                  	 validateIP("oTHostIP");
              		 showTrakopolisIP("outputCheck");
                }
                if (selection == "DNS")
                	{
               	 	validateEmptyFields($("form#outputCheck input[name = 'oTHostDNS']"));
            		 showTrakopolisDNS("outputCheck");
                	}
            });

            //hide show Host IP/DNS div on keyup
            $("form#outputCheck select[name = 'otServer']").keyup(function () {
            	var selection = "";
                $("form#outputCheck select[name = 'otServer'] option:selected").each(function () {
                    selection = $(this).text() + "";

                    if (selection == "IP") {

                     	 validateIP("oTHostIP");
                  		 showTrakopolisIP("outputCheck");
                   }
                   if (selection == "DNS")
                   	{
                  	 	validateEmptyFields($("form#outputCheck input[name = 'oTHostDNS']"));
               		 showTrakopolisDNS("outputCheck");

                   	}
            });

            })
            .change();


        }

    });


    //<!-- script to enable/disable RDS settings -->
	//on page load--
    //if RDS is turned on
    if ($("form#outputCheck input[name = 'oRDS']:checked").val() == "0") {
    	if ($("form#outputCheck select[name = 'oRDSServer'] option:selected").val() == 'IP') {
   		 showdisabledRDSIP("outputCheck");
    	}
    	if ($("form#outputCheck select[name = 'oRDSServer'] option:selected").val() == 'DNS') {
      		 showdisabledRDSDNS("outputCheck");
       	}
    }
    //if RDS is turned off
    if ($("form#outputCheck input[name = 'oRDS']:checked").val() == "1") {
    	if ($("form#outputCheck select[name = 'oRDSServer'] option:selected").val() == 'IP') {
    	 validateIP("oRDSHostIP");
   		 showRDSIP("outputCheck");
    	}
    	if ($("form#outputCheck select[name = 'oRDSServer'] option:selected").val() == 'DNS') {
       	 	validateEmptyFields($("form#outputCheck input[name = 'oRDSHostDNS']"));
      		 showRDSDNS("outputCheck");
       	}

        //hide show Host IP/DNS div on mouse click
        $("form#outputCheck select[name = 'oRDSServer'] option").click(function () {
            var selection = $(this).val();
            if (selection == "IP") {

              	 validateIP("oRDSHostIP");
          		 showRDSIP("outputCheck");
            }
            if (selection == "DNS")
            	{
           	 	validateEmptyFields($("form#outputCheck input[name = 'oRDSHostDNS']"));
        		 showRDSDNS("outputCheck");
            	}
        });

        //hide show Host IP/DNS div on keyup
        $("form#outputCheck select[name = 'oRDSServer']").keyup(function () {
        	var selection = "";
            $("form#outputCheck select[name = 'oRDSServer'] option:selected").each(function () {
                selection = $(this).text() + "";

                if (selection == "IP") {

                 	 validateIP("oRDSHostIP");
              		 showRDSIP("outputCheck");
               }
               if (selection == "DNS")
               	{
              	 	validateEmptyFields($("form#outputCheck input[name = 'oRDSHostDNS']"));
           		 showRDSDNS("outputCheck");

               	}
        });

        })
        .change();

   }


      //on change--
        //if RDS is turned off
        $("form#outputCheck input[name = 'oRDS']").change(function () {
            var selection = $(this).val();
            if (selection == "0") {
            	if ($("form#outputCheck select[name = 'oRDSServer'] option:selected").val() == 'IP') {
              		 showdisabledRDSIP("outputCheck");
               	}
               	if ($("form#outputCheck select[name = 'oRDSServer'] option:selected").val() == 'DNS') {
                 		 showdisabledRDSDNS("outputCheck");
                  	}

            }
            //if RDS is turned on
            if (selection == "1") {
            	if ($("form#outputCheck select[name = 'oRDSServer'] option:selected").val() == 'IP') {
                 	 validateIP("oRDSHostIP");
              		 showRDSIP("outputCheck");
               	}
               	if ($("form#outputCheck select[name = 'oRDSServer'] option:selected").val() == 'DNS') {
               	 	validateEmptyFields($("form#outputCheck input[name = 'oRDSHostDNS']"));
                 		 showRDSDNS("outputCheck");
                  	}
                //hide show Host IP/DNS div on mouseclick
                $("form#outputCheck select[name = 'oRDSServer'] option").click(function () {
                    var selection = $(this).val();
                    if (selection == "IP") {
                      	 validateIP("oRDSHostIP");
                  		 showRDSIP("outputCheck");
                    }
                    if (selection == "DNS")
                    	{
                   	 	validateEmptyFields($("form#outputCheck input[name = 'oRDSHostDNS']"));
                		 showRDSDNS("outputCheck");
                    	}
                });

                //hide show Host IP/DNS div on keyup
                $("form#outputCheck select[name = 'oRDSServer']").keyup(function () {
                	var selection = "";
                    $("form#outputCheck select[name = 'oRDSServer'] option:selected").each(function () {
                        selection = $(this).text() + "";

                        if (selection == "IP") {

                         	 validateIP("oTHostIP");
                      		 showRDSIP("outputCheck");
                       }
                       if (selection == "DNS")
                       	{
                      	 	validateEmptyFields($("form#outputCheck input[name = 'oRDSHostDNS']"));
                   		 showRDSDNS("outputCheck");

                       	}
                });

                })
                .change();


            }

        });



  //<!-- script to enable/disable NMEA settings -->
    if ($("form#socketsCheck input[name = 'oNMEA']:checked").val() == "0") {
        disableNMEA("socketsCheck");
        enableStatusSavebtn("socketsCheck");
    } else {
        enableNMEA("socketsCheck");
        validateEmptyFields($("form#socketsCheck input[name = 'oPort']"));
        lessThanZeroCheck($("form#socketsCheck input[name = 'oPort']"));
        enableStatusSavebtn("socketsCheck");
    }

    $("form#socketsCheck input[name = 'oNMEA']").change(function () {
        var selection = $(this).val();
        if (selection == "0") {
            disableNMEA("socketsCheck");
            enableStatusSavebtn("socketsCheck");
        } else {
            enableNMEA("socketsCheck");
            validateEmptyFields($("form#socketsCheck input[name = 'oPort']"));
            lessThanZeroCheck($("form#socketsCheck input[name = 'oPort']"));
            enableStatusSavebtn("socketsCheck");
        }

    });


});



function showdisabledCAMSIP(formId){
    $("form#" + formId + " select[name = 'ocServer']").attr('disabled', true);
    $("form#" + formId + " input[name ^= 'oCHostIP']").attr('disabled', true);
    $("form#" + formId + " input[name = 'oCPort']").attr('disabled', true);
    $("form#" + formId + " div[id = 'oCHostIPdivID']").show();
    $("form#" + formId + " div[id = 'oCHostDNSdivID']").hide();
    $("form#" + formId + " span[name = 'erroroCHostIP']").hide();
    $("form#" + formId + " span[name = 'erroroCHostIP']").empty();
    $("form#" + formId + " span[name = 'erroroCPort']").hide();
    $("form#" + formId + " span[name = 'erroroCPort']").empty();
    enableStatusSavebtn($("form#outputCheck").attr("id"));

}

function showdisabledCAMSDNS(formId){
    $("form#" + formId + " select[name = 'ocServer']").attr('disabled', true);
    $("form#" + formId + " input[name = 'oCHostDNS']").attr('disabled', true);
    $("form#" + formId + " input[name = 'oCPort']").attr('disabled', true);
    $("form#" + formId + " div[id = 'oCHostDNSdivID']").show();
    $("form#" + formId + " div[id = 'oCHostIPdivID']").hide();
    $("form#" + formId + " span[name = 'erroroCHostDNS']").hide();
    $("form#" + formId + " span[name = 'erroroCHostDNS']").empty();
    $("form#" + formId + " span[name = 'erroroCPort']").hide();
    $("form#" + formId + " span[name = 'erroroCPort']").empty();
    enableStatusSavebtn($("form#outputCheck").attr("id"));

}

function showCAMSIP(formId){
    $("form#" + formId + " select[name = 'ocServer']").removeAttr('disabled');
    $("form#" + formId + " input[name ^= 'oCHostIP']").removeAttr('disabled');
    $("form#" + formId + " input[name = 'oCPort']").removeAttr('disabled');
    $("form#" + formId + " div[id = 'oCHostIPdivID']").show();
    $("form#" + formId + " div[id = 'oCHostDNSdivID']").hide();
    $("form#" + formId + " span[name = 'erroroCHostDNS']").hide();
    $("form#" + formId + " span[name = 'erroroCHostDNS']").empty();
    $("form#" + formId + " span[name = 'erroroCHostIP']").show();
    enableStatusSavebtn($("form#outputCheck").attr("id"));

}

function showCAMSDNS(formId){
    $("form#" + formId + " select[name = 'ocServer']").removeAttr('disabled');
    $("form#" + formId + " input[name = 'oCHostDNS']").removeAttr('disabled');
    $("form#" + formId + " input[name = 'oCPort']").removeAttr('disabled');
    $("form#" + formId + " div[id = 'oCHostIPdivID']").hide();
    $("form#" + formId + " div[id = 'oCHostDNSdivID']").show();
    $("form#" + formId + " span[name = 'erroroCHostIP']").hide();
    $("form#" + formId + " span[name = 'erroroCHostIP']").empty();
    $("form#" + formId + " span[name = 'erroroCHostDNS']").show();
    enableStatusSavebtn($("form#outputCheck").attr("id"));
}


function showdisabledTrakopolisIP(formId){
    $("form#" + formId + " select[name = 'otServer']").attr('disabled', true);
    $("form#" + formId + " input[name ^= 'oTHostIP']").attr('disabled', true);
    $("form#" + formId + " input[name = 'oTPort']").attr('disabled', true);
    $("form#" + formId + " div[id = 'oTHostIPdivID']").show();
    $("form#" + formId + " div[id = 'oTHostDNSdivID']").hide();
    $("form#" + formId + " span[name = 'erroroTHostIP']").hide();
    $("form#" + formId + " span[name = 'erroroTHostIP']").empty();
    $("form#" + formId + " span[name = 'erroroTPort']").hide();
    $("form#" + formId + " span[name = 'erroroTPort']").empty();
    enableStatusSavebtn($("form#outputCheck").attr("id"));

}

function showdisabledTrakopolisDNS(formId){
    $("form#" + formId + " select[name = 'otServer']").attr('disabled', true);
    $("form#" + formId + " input[name = 'oTHostDNS']").attr('disabled', true);
    $("form#" + formId + " input[name = 'oTPort']").attr('disabled', true);
    $("form#" + formId + " div[id = 'oTHostDNSdivID']").show();
    $("form#" + formId + " div[id = 'oTHostIPdivID']").hide();
    $("form#" + formId + " span[name = 'erroroTHostDNS']").hide();
    $("form#" + formId + " span[name = 'erroroTHostDNS']").empty();
    $("form#" + formId + " span[name = 'erroroTPort']").hide();
    $("form#" + formId + " span[name = 'erroroTPort']").empty();
    enableStatusSavebtn($("form#outputCheck").attr("id"));

}

function showTrakopolisIP(formId){
    $("form#" + formId + " select[name = 'otServer']").removeAttr('disabled');
    $("form#" + formId + " input[name ^= 'oTHostIP']").removeAttr('disabled');
    $("form#" + formId + " input[name = 'oTPort']").removeAttr('disabled');
    $("form#" + formId + " div[id = 'oTHostIPdivID']").show();
    $("form#" + formId + " div[id = 'oTHostDNSdivID']").hide();
    $("form#" + formId + " span[name = 'erroroTHostDNS']").hide();
    $("form#" + formId + " span[name = 'erroroTHostDNS']").empty();
    $("form#" + formId + " span[name = 'erroroTHostIP']").show();
    $("form#" + formId + " span[name = 'erroroTPort']").show();
    enableStatusSavebtn($("form#outputCheck").attr("id"));

}

function showTrakopolisDNS(formId){
    $("form#" + formId + " select[name = 'otServer']").removeAttr('disabled');
    $("form#" + formId + " input[name = 'oTHostDNS']").removeAttr('disabled');
    $("form#" + formId + " input[name = 'oTPort']").removeAttr('disabled');
    $("form#" + formId + " div[id = 'oTHostIPdivID']").hide();
    $("form#" + formId + " div[id = 'oTHostDNSdivID']").show();
    $("form#" + formId + " span[name = 'erroroTHostIP']").hide();
    $("form#" + formId + " span[name = 'erroroTHostIP']").empty();
    $("form#" + formId + " span[name = 'erroroTHostDNS']").show();
    $("form#" + formId + " span[name = 'erroroTPort']").show();
    enableStatusSavebtn($("form#outputCheck").attr("id"));
}

function showdisabledRDSIP(formId){
    $("form#" + formId + " select[name = 'oRDSServer']").attr('disabled', true);
    $("form#" + formId + " input[name ^= 'oRDSHostIP']").attr('disabled', true);
    $("form#" + formId + " input[name = 'oRDSPort']").attr('disabled', true);
    $("form#" + formId + " div[id = 'oRDSHostIPdivID']").show();
    $("form#" + formId + " div[id = 'oRDSHostDNSdivID']").hide();
    $("form#" + formId + " span[name = 'erroroRDSHostIP']").hide();
    $("form#" + formId + " span[name = 'erroroRDSHostIP']").empty();
    $("form#" + formId + " span[name = 'erroroRDSPort']").hide();
    $("form#" + formId + " span[name = 'erroroRDSPort']").empty();
    enableStatusSavebtn($("form#outputCheck").attr("id"));

}

function showdisabledRDSDNS(formId){
    $("form#" + formId + " select[name = 'oRDSServer']").attr('disabled', true);
    $("form#" + formId + " input[name = 'oRDSHostDNS']").attr('disabled', true);
    $("form#" + formId + " input[name = 'oRDSPort']").attr('disabled', true);
    $("form#" + formId + " div[id = 'oRDSHostDNSdivID']").show();
    $("form#" + formId + " div[id = 'oRDSHostIPdivID']").hide();
    $("form#" + formId + " span[name = 'erroroRDSHostDNS']").hide();
    $("form#" + formId + " span[name = 'erroroRDSHostDNS']").empty();
    $("form#" + formId + " span[name = 'erroroRDSPort']").hide();
    $("form#" + formId + " span[name = 'erroroRDSPort']").empty();
    enableStatusSavebtn($("form#outputCheck").attr("id"));

}

function showRDSIP(formId){
    $("form#" + formId + " select[name = 'oRDSServer']").removeAttr('disabled');
    $("form#" + formId + " input[name ^= 'oRDSHostIP']").removeAttr('disabled');
    $("form#" + formId + " input[name = 'oRDSPort']").removeAttr('disabled');
    $("form#" + formId + " div[id = 'oRDSHostIPdivID']").show();
    $("form#" + formId + " div[id = 'oRDSHostDNSdivID']").hide();
    $("form#" + formId + " span[name = 'erroroRDSHostDNS']").hide();
    $("form#" + formId + " span[name = 'erroroRDSHostDNS']").empty();
    $("form#" + formId + " span[name = 'erroroRDSHostIP']").show();
    $("form#" + formId + " span[name = 'erroroRDSPort']").show();
    enableStatusSavebtn($("form#outputCheck").attr("id"));

}

function showRDSDNS(formId){
    $("form#" + formId + " select[name = 'oRDSServer']").removeAttr('disabled');
    $("form#" + formId + " input[name = 'oRDSHostDNS']").removeAttr('disabled');
    $("form#" + formId + " input[name = 'oRDSPort']").removeAttr('disabled');
    $("form#" + formId + " div[id = 'oRDSHostIPdivID']").hide();
    $("form#" + formId + " div[id = 'oRDSHostDNSdivID']").show();
    $("form#" + formId + " span[name = 'erroroRDSHostIP']").hide();
    $("form#" + formId + " span[name = 'erroroRDSHostIP']").empty();
    $("form#" + formId + " span[name = 'erroroRDSHostDNS']").show();
    $("form#" + formId + " span[name = 'erroroRDSPort']").show();
    enableStatusSavebtn($("form#outputCheck").attr("id"));
}



function disableNMEA(formId) {
    $("form#" + formId + " input[name = 'oPort']").attr('disabled', true);
    $("form#" + formId + " span[name = 'erroroPort']").hide();
    $("form#" + formId + " span[name = 'erroroPort']").empty();

}

function enableNMEA(formId) {
    $("form#" + formId + " input[name = 'oPort']").removeAttr('disabled');
    $("form#" + formId + " span[name = 'erroroPort']").show();

}







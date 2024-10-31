
jQuery(document).on("click", "#api-submit", function (e) {
  // Some event will trigger the ajax call, you can push whatever data to the server, 
  // simply passing it to the "data" object in ajax call
var api_key = jQuery("#api-key").val();
var domain = jQuery("#domain").val();
var email = jQuery("#user_email").val();
  jQuery.ajax({
    url: ajax_object.ajaxurl, // this is the object instantiated in wp_localize_script function
    type: 'POST',
    data:{ 
               'action': 'pixops_ajax_request', // this is the function in your functions.php that will be triggered
               "_ajax_nonce": ajax_object.ajax_nonce,
               'option' : 'check',
               'api_key' : api_key,
               'domain' : domain,
               'email' : email,
    },
            success: function(data){
          //Do something with the result from server
         // console.log(data);
         //alert(data);
         var result = JSON.parse(data);
            if(result == 'Success'){
               // alert(result);
               //document.getElementById("result").innerHTML = data;
               location.reload();
              }else{
                 // alert(result);
               document.getElementById("invalid").innerHTML = result;
              }
    }
  });
});
jQuery(function() {  
jQuery('#api-submit').click(function() {  
                var api_key = jQuery('#api-key');  
                if (api_key.val() != null && api_key.val() != '') {  
                    //document.getElementById("result").innerHTML = 'Entered API Key';  
                } else {  
                   document.getElementById("invalid").innerHTML = 'Enter Valid API Credentials';  
                }  
            })  
});
// Key Disconnect
jQuery('#api-disconnect').click(function()
{
    
jQuery.ajax({
    url: ajax_object.ajaxurl, // this is the object instantiated in wp_localize_script function
    type: 'POST',
    data:{ 
      _ajax_nonce: ajax_object.ajax_nonce,
         action: 'clear_log_action', // this is the function  that will be triggered  
          },
            success: function(response){
          //Do something with the result from server
          
        location.reload(true);
    }
  });

});
// 

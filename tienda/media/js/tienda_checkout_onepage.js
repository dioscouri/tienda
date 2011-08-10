window.addEvent("domready", function() {		
	$$('.tienda-collapse-processed').addEvent('click', function() { 
			var parent = this.getParent();
			if(parent.className == 'tienda-expanded')
			{
				parent.removeClass('tienda-expanded');
				parent.addClass('tienda-collapsed');
			}
			else
			{
				parent.removeClass('tienda-collapsed');
				parent.addClass('tienda-expanded');
			}		
		});
});

function tiendaGetPaymentOptions(container, form, msg)
{
	var url = 'index.php?option=com_tienda&view=checkout&task=updatePaymentOptions&format=raw';
    tiendaDoTask( url, container, form, msg, false );   
}

/**
 * Method to copy all data from Billing Addres fields to Shipping Address fields
 * @param billingprefix
 * @param shippingprefix
 * @return
 */
function copyBillingAdToShippingAd(checkbox, form)
{	
	var disable = false;
    if (checkbox.checked)
    {
    	disable = true;
    	tiendaGetShippingRates( 'onCheckoutShipping_wrapper', form );
    	tiendaGetPaymentOptions('onCheckoutPayment_wrapper', form);
    }  
}

function tiendaSaveOnepageOrder(container, errcontainer, form)
{
	var url = 'index.php?option=com_tienda&view=checkout&task=saveOrderOnePage&format=raw';	
	var str = new Array();
    for(i=0; i<form.elements.length; i++)
    {
        postvar = {
            name : form.elements[i].name,
            value : form.elements[i].value,
            checked : form.elements[i].checked,
            id : form.elements[i].id
        };
        str[i] = postvar;
    }
     
     // execute Ajax request to server
     var a=new Ajax(url,{
         method:"post",
         data:{"elements":Json.toString(str)},
         onComplete: function(response){
             var resp=Json.evaluate(response, false);                  
                        
             if (resp.error != '1') 
             {
            	 if ($(container)) { $(container).setHTML(resp.msg); }
            	 if ($('onCheckoutCart_wrapper')) { $('onCheckoutCart_wrapper').setHTML(resp.summary); }
            	 if($('tienda_btns')){ $('tienda_btns').setStyle('display', 'none'); }
            	 if($('refreshpage')){ $('refreshpage').setStyle('display', 'block'); }
            	 if($('validationmessage')){ $('validationmessage').setHTML('');}            	
            	 window.location = String(window.location).replace(/\#.*$/, "") + "#tienda-method";
             }
             else
             {
            	 if ($(errcontainer)) { $(errcontainer).setHTML(resp.msg); }
            	 if(resp.anchor){ window.location = String(window.location).replace(/\#.*$/, "") + resp.anchor;}
             } 
         }
     }).request();	
}

function tiendaGetFinalForm( container, form, msg )
{	
	var url = 'index.php?option=com_tienda&view=checkout&task=getRegisterForm&format=raw';
    tiendaDoTask( url, container, form, msg );  
    $('tienda-method-pane').setHTML($('hiddenregvalue').value);
}

function tiendaGetView(url, container, labelcont)
{		
    // execute Ajax request to server
    var a=new Ajax(url,{
        method:"post",       
        onComplete: function(response){
            var resp=Json.evaluate(response, false);                
                       
            if (resp.error != '1') 
            {
           	 if ($(container)) { $(container).setHTML(resp.msg); }
           	 if(labelcont){$(labelcont).setHTML(resp.label);}          
            }
        }
    }).request();	
}

function tiendaGetRegistrationForm( container, form, msg )
{	
	var url = 'index.php?option=com_tienda&view=checkout&task=getRegisterForm&format=raw';	
	tiendaGetView(url, container, 'tienda-method-pane'); 
}

/**
 * Simple function to check email availability
 */
function tiendaCheckEmail( container, form )
{
    var url = 'index.php?option=com_tienda&controller=checkout&task=checkEmail&format=raw';
    
    // loop through form elements and prepare an array of objects for passing to server
    var str = new Array();
    for(i=0; i<form.elements.length; i++)
    {
        postvar = {
            name : form.elements[i].name,
            value : form.elements[i].value,
            checked : form.elements[i].checked,
            id : form.elements[i].id
        };
        str[i] = postvar;
    }
    // execute Ajax request to server
    var a=new Ajax(url,{
        method:"post",
        data:{"elements":Json.toString(str)},
        onComplete: function(response){
            var resp=Json.evaluate(response, false);
            if ($(container)) { $(container).setHTML(resp.msg); }
            return true;
        }
    }).request();
   
}

/**
 * Simple function to check a password strength
 */
function tiendaCheckPassword( container, form )
{
    var url = 'index.php?option=com_tienda&controller=checkout&task=checkPassword&format=raw';
    
    // loop through form elements and prepare an array of objects for passing to server
    var str = new Array();
    for(i=0; i<form.elements.length; i++)
    {
        postvar = {
            name : form.elements[i].name,
            value : form.elements[i].value,
            checked : form.elements[i].checked,
            id : form.elements[i].id
        };
        str[i] = postvar;
    }
    // execute Ajax request to server
    tiendaPutAjaxLoader( container, '_transp' );
    var a=new Ajax(url,{
        method:"post",
        data:{"elements":Json.toString(str)},
        onComplete: function(response){
            var resp=Json.evaluate(response, false);
            if ($(container)) { $(container).setHTML(resp.msg); }
            return true;
        }
    }).request();
}

/**
 * Simple function to compare passwords
 */
function tiendaCheckPassword2( container, form )
{
    var url = 'index.php?option=com_tienda&controller=checkout&task=checkPassword2&format=raw';
    
    // loop through form elements and prepare an array of objects for passing to server
    var str = new Array();
    for(i=0; i<form.elements.length; i++)
    {
        postvar = {
            name : form.elements[i].name,
            value : form.elements[i].value,
            checked : form.elements[i].checked,
            id : form.elements[i].id
        };
        str[i] = postvar;
    }
    // execute Ajax request to server
    tiendaPutAjaxLoader( container, '_transp' );
    var a=new Ajax(url,{
        method:"post",
        data:{"elements":Json.toString(str)},
        onComplete: function(response){
            var resp=Json.evaluate(response, false);
            if ($(container)) { $(container).setHTML(resp.msg); }
            return true;
        }
    }).request();
    
}
 
 /**
  * method to hide shipping fields
  * 
  */
 function tiendaHideShippingFields( shipping_text )
 {
	 $( 'shipping_input_addressForm' ).setStyle( 'display', 'none' );
	 if( $('sameasbilling') )
	 {
		 $('sameasbilling').addEvent('change', function() {
			 var field = document.getElementById( 'shipping_input_addressForm' );
			 if (field.style.display == "none")
				field.style.display = "";
			 else
			 {
				 field.style.display = "none";
				 tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form, shipping_text );
			 }
		 });
	 }
 }

/*
 * This method toggles editation of user email in one page checkout when a user is logged in
 */
function tiendaCheckoutToogleEditEmail( container, form, check )
{
	user_email = 'email_address';
	user_email_span = 'user_email_span';
	user_email_cancel_button = 'email_address_button_cancel';
	if( $( user_email ).style.display == 'none' ) // start editation
	{
		$( container ).setHTML( '' );
		$( user_email ).setStyle( 'display','inline' );
		$( user_email_span ).setStyle( 'display','none' );
		$( user_email_cancel_button ).setStyle( 'display', 'inline' );
	}
	else // finish editation
	{
		if( check )
		{
			// send AJAX request to validate the email address against other users
		    var url = 'index.php?option=com_tienda&controller=checkout&task=checkEmail&format=raw';
		    
		    // loop through form elements and prepare an array of objects for passing to server
		    var str = new Array();
		    for(i=0; i<form.elements.length; i++)
		    {
		        postvar = {
		            name : form.elements[i].name,
		            value : form.elements[i].value,
		            checked : form.elements[i].checked,
		            id : form.elements[i].id
		        };
		        str[i] = postvar;
		    }
		    // execute Ajax request to server
		    tiendaPutAjaxLoader( container, '_transp' );
		    var a=new Ajax(url,{
		        method:"post",
		        data:{"elements":Json.toString(str)},
		        onComplete: function( response ){
		            var resp=Json.evaluate( response, false );
		            if( resp.error != '0' )
		            {
	            		$(container).setHTML(resp.msg);
		            }
		            else
	           		{
	            		$( container ).setHTML( resp.msg );
	           			$( user_email_span ).set( 'text', $( user_email ).value );
	           			$( user_email_span ).setStyle( 'display', 'inline' );
	           			$( user_email ).setStyle( 'display', 'none' );
	           			$( user_email_cancel_button ).setStyle( 'display', 'none' );
	           		}
		            return true;
		        }
		    }).request();
		}
		else
		{
    		$(container).setHTML( '' );
   			$( user_email_span ).setStyle('display','inline');
   			$( user_email ).setStyle('display','none');			
   			$( user_email_cancel_button ).setStyle( 'display', 'none' );
		}
	}
}

function tiendaPutAjaxLoader( container, suffix )
{
	var img_loader = '<img src="'+window.com_tienda.jbase+'media/com_tienda/images/ajax-loader'+suffix+'.gif'+'"/>';
	$(container).setHTML( img_loader );
}

function tiendaHideInfoCreateAccount( )
{
	$('tienda_user_additional_info').set('class', 'hidden');
	$('create_account').addEvent('change', function() {
		$('tienda_user_additional_info').toggleClass('hidden');
	});
}

function tiendaCheckoutSetBillingAddress(url, container, selected )
{
	var divContainer = document.getElementById( container );
	var divForm = document.getElementById( 'billing_input_addressForm' );
	if( selected > 0 ) // address was selected -> get shipping rates
	{
		divContainer.style.display = "";
		divForm.style.display = "none";
		tiendaDoTask( url, container, '' );
		tiendaGetCheckoutTotals();
	}
	else // user wants to create a new address
	{
		divContainer.style.display = "none";
		divForm.style.display = "";
	}
}

function tiendaCheckoutSetShippingAddress(url, container, shipping_text, form, selected )
{
	var divContainer = document.getElementById( container );
	var divForm = document.getElementById( 'shipping_input_addressForm' );
	if( selected > 0 ) // address was selected -> get shipping rates
	{
		divContainer.style.display = "";
		divForm.style.display = "none";
		tiendaDoTask( url, container, '', '', false );
		tiendaGetShippingRates( 'onCheckoutShipping_wrapper', form, shipping_text );
	}
	else // user wants to create a new address
	{
		divContainer.style.display = "none";
		divForm.style.display = "";
	}
}
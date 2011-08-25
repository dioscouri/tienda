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

function tiendaGetPaymentOptions(container, form, msg, text_payment )
{
    payment_plugin = $$( 'input[type=radio]:checked', 'onCheckoutPayment_wrapper' ).map( function(e) { return e.value; });
	var url = 'index.php?option=com_tienda&view=checkout&task=updatePaymentOptions&format=raw';
    tiendaDoTask( url, container, form, msg, false, tiendaDeletePaymentGrayDiv );
	tiendaGrayOutAjaxDiv( 'onCheckoutPayment_wrapper', text_payment );

	$ES( 'input[type=radio]', 'onCheckoutPayment_wrapper' ).each( function( e ){
		if( e.get( 'value' ) == payment_plugin )
			e.set( 'checked', true );
	} );			
    
}

/**
 * Method to copy all data from Billing Addres fields to Shipping Address fields
 * @param billingprefix
 * @param shippingprefix
 * @return
 */
function copyBillingAdToShippingAd(checkbox, form, text_shipping, text_cart, text_address, text_payment )
{	
	var disable = false;
    if (checkbox.checked)
    {
    	disable = true;
    	tiendaGrayOutAddressDiv( text_address );
    	tiendaGetShippingRates( 'onCheckoutShipping_wrapper', form, text_shipping, text_cart, tiendaDeleteAddressGrayDiv );
    	tiendaGetPaymentOptions('onCheckoutPayment_wrapper', form, text_payment );
    }
}

function tiendaSaveOnepageOrder(container, errcontainer, form, valid_text)
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
     tiendaPutAjaxLoader( errcontainer, valid_text );
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
 * Simple function to check a password strength
 */
function tiendaCheckPassword( container, form, valid_text )
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
    tiendaPutAjaxLoader( container, valid_text );
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
function tiendaCheckPassword2( container, form, valid_text )
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
    tiendaPutAjaxLoader( container, valid_text );
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
  * method to hide billing fields 
  */
function tiendaHideBillingFields() 
{
	$('billingToggle_show').set('class', 'hidden');
	
	$('field-toggle').addEvent('change', function() {
		$$('#billingDefaultAddress', '#billingToggle_show', '#billingToggle_hide').toggleClass('hidden');
	});
}

/*
 * This method checks availability of the email address
 */
function tiendaCheckoutCheckEmail( container, form, valid_text )
{
	user_email = 'email_address';
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
    tiendaPutAjaxLoader( container, valid_text );
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
       		}
            return true;
        }
    }).request();
}

function tiendaHideInfoCreateAccount( )
{
	var $tuai = $('tienda_user_additional_info'); 
	if ($tuai) {
		$tuai.set('class', 'hidden'); 
	}
	
	$('create_account').addEvent('change', function() {
		$('tienda_user_additional_info').toggleClass('hidden');
	});
}

function tiendaCheckoutSetBillingAddress(url, container, selected, text_shipping, text_cart, text_address )
{
	var divContainer = document.getElementById( container );
	var divForm = document.getElementById( 'billing_input_addressForm' );

	if( selected > 0 ) // address was selected -> get shipping rates
	{
    	values = tiendaStoreFormInputs( form );
		divContainer.style.display = "";
		divForm.style.display = "none";
		tiendaGrayOutAddressDiv( text_address );
		tiendaDoTask( url, container, '', '', false );
		if( $( 'onCheckoutShipping_wrapper' ) )
			tiendaGrayOutAjaxDiv( 'onCheckoutShipping_wrapper', text_shipping, '' );
		tiendaGrayOutAjaxDiv( 'onCheckoutCart_wrapper', text_cart, '' );

		tiendaGetCheckoutTotals( true );
    	tiendaRestoreFormInputs( form, values );
	}
	else // user wants to create a new address
	{
		divContainer.style.display = "none";
		divForm.style.display = "";
	}
}

function tiendaCheckoutSetShippingAddress(url, container, text_shipping, text_cart, text_address, form, selected )
{
	var divContainer = document.getElementById( container );
	var divForm = document.getElementById( 'shipping_input_addressForm' );
	if( selected > 0 ) // address was selected -> get shipping rates
	{
    	values = tiendaStoreFormInputs( form );
		divContainer.style.display = "";
		divForm.style.display = "none";
		tiendaGrayOutAddressDiv( text_address );
		tiendaDoTask( url, container, '', '', false );
		tiendaGrayOutAjaxDiv( 'onCheckoutShipping_wrapper', text_shipping, '' );
		tiendaGetShippingRates( 'onCheckoutShipping_wrapper', form, text_shipping, text_cart, tiendaDeleteAddressGrayDiv );
    	tiendaRestoreFormInputs( form, values );
	}
	else // user wants to create a new address
	{
		divContainer.style.display = "none";
		divForm.style.display = "";
	}
}
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
	
	if( typeof( SqueezeBox ) !== 'undefined' )
		SqueezeBox.onkeypress = function(e) {
			switch (e.key) {
			case 'esc':
				this.close();
				break;
			}
		};
});

function tiendaGetPaymentOptions(container, form, msg, callback )
{
	var payment_plugin = $E( 'input[name=payment_plugin]:checked' );
	
	if( payment_plugin )
		payment_plugin = payment_plugin.value;
	var url = 'index.php?option=com_tienda&view=checkout&task=updatePaymentOptions&format=raw';
	if( callback )
		tiendaDoTask( url, container, form, msg, false, function(){ callback(); tiendaDeletePaymentGrayDiv();} );		
	else
		tiendaDoTask( url, container, form, msg, false, tiendaDeletePaymentGrayDiv );

	tiendaGrayOutAjaxDiv( 'onCheckoutPayment_wrapper', Joomla.JText._( 'Updating Payment Methods' ) );

	if( payment_plugin )
	{
		$ES( 'input[name=payment_plugin]', 'onCheckoutPayment_wrapper' ).each( function( e ){
			if( e.get( 'value' ) == payment_plugin )
				e.set( 'checked', true );
		} );
	}
}

/**
 * Method to copy all data from Billing Address fields to Shipping Address fields
 * @param billingprefix
 * @param shippingprefix
 * @return
 */
function copyBillingAdToShippingAd(checkbox, form )
{	
	var disable = false;
    if (checkbox.checked)
    {
    	disable = true;
    	tiendaGrayOutAddressDiv();
    	tiendaGetShippingRates( 'onCheckoutShipping_wrapper', form, tiendaDeleteAddressGrayDiv );
    	tiendaGetPaymentOptions('onCheckoutPayment_wrapper', form );
    }
}

function tiendaSaveOnepageOrder(container, errcontainer, form )
{
	var url = 'index.php?option=com_tienda&view=checkout&controller=checkout&task=saveOrderOnePage&format=raw';	
    var str = tiendaGetFormInputData( form );
     
     // execute Ajax request to server
     tiendaPutAjaxLoader( errcontainer, Joomla.JText._( 'VALIDATING' ) );
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
  * method to hide billing fields 
  */
function tiendaHideBillingFields() 
{
	$('billingToggle_show').set('class', 'hidden');
	
	$('field-toggle').addEvent('change', function() {
		$$('#billingDefaultAddress', '#billingToggle_show', '#billingToggle_hide').toggleClass('hidden');
	});
}

function tiendaCheckoutSetBillingAddress(url, container, selected, form )
{
	var divContainer = document.getElementById( container );
	var divForm = document.getElementById( 'billing_input_addressForm' );

	if( selected > 0 ) // address was selected -> get shipping rates
	{
    	values = tiendaStoreFormInputs( form );
		divContainer.style.display = "";
		divForm.style.display = "none";
		tiendaGrayOutAddressDiv();
		tiendaDoTask( url, container, '', '', false );
		if( $( 'onCheckoutShipping_wrapper' ) )
			tiendaGrayOutAjaxDiv( 'onCheckoutShipping_wrapper', Joomla.JText._( 'Updating Shipping Rates' ) );
		tiendaGrayOutAjaxDiv( 'onCheckoutCart_wrapper', Joomla.JText._( 'Updating Cart' ) );

		tiendaGetCheckoutTotals( true );
    	tiendaRestoreFormInputs( form, values );
	}
	else // user wants to create a new address
	{
		divContainer.style.display = "none";
		divForm.style.display = "";
	}
}

function tiendaCheckoutSetShippingAddress(url, container, form, selected )
{
	var divContainer = document.getElementById( container );
	var divForm = document.getElementById( 'shipping_input_addressForm' );
	if( selected > 0 ) // address was selected -> get shipping rates
	{
    	values = tiendaStoreFormInputs( form );
		divContainer.style.display = "";
		divForm.style.display = "none";
		tiendaGrayOutAddressDiv();
		tiendaDoTask( url, container, '', '', false );
		tiendaGrayOutAjaxDiv( 'onCheckoutShipping_wrapper', Joomla.JText._( 'Updating Shipping Rates' ) );
		tiendaGetShippingRates( 'onCheckoutShipping_wrapper', form, tiendaDeleteAddressGrayDiv );
    	tiendaRestoreFormInputs( form, values );
	}
	else // user wants to create a new address
	{
		divContainer.style.display = "none";
		divForm.style.display = "";
	}
}
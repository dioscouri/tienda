/**
 * Based on the session contents,
 * calculates the order total
 * and returns HTML
 * 
 * @return
 */
function tiendaGetPaymentForm( element, container )
{
    var url = 'index.php?option=com_tienda&view=checkout&task=getPaymentForm&format=raw&payment_element=' + element;

   	tiendaGrayOutAjaxDiv( container, Joomla.JText._( 'COM_TIENDA_UPDATING_PAYMENT_METHODS' ) );
	tiendaDoTask( url, container, document.adminForm, '', false, tiendaDeletePaymentGrayDiv );    	
}


function tiendaGetShippingRates( container, form, callback )
{
    var url = 'index.php?option=com_tienda&view=checkout&task=updateShippingRates&format=raw';
    
	// loop through form elements and prepare an array of objects for passing to server
	var str = tiendaGetFormInputData( form );
	
   	tiendaGrayOutAjaxDiv( container, Joomla.JText._( 'COM_TIENDA_UPDATING_SHIPPING_RATES' ) );
   	
   	// execute Ajax request to server
    var a = new Request({
		url : url,
		method : "post",
		data : {
			"elements" : JSON.encode(str)
		},
		onSuccess : function(response) {
			var resp = JSON.decode(response, false);
            tiendaJQ( container ).html( resp.msg );
            if( resp.default_rate && resp.default_rate != null ) { 
                // if only one rate was found - set it as default
                tiendaSetShippingRate(resp.default_rate['name'], resp.default_rate['price'], resp.default_rate['tax'], resp.default_rate['extra'], resp.default_rate['code'], callback != null );                
            }
            
            if (typeof callback == 'function') {
                callback();
            }
            return true;
        },
        onFailure : function(response) {
            tiendaDeleteShippingGrayDiv();
        },
        onException : function(response) {
            tiendaDeleteShippingGrayDiv();
        }
    }).send();
    
    tiendaDeleteShippingGrayDiv();
}

function tiendaSetShippingRate(name, price, tax, extra, code, combined )
{
	tiendaJQ('shipping_name').value = name;
	tiendaJQ('shipping_code').value = code;
	tiendaJQ('shipping_price').value = price;
	tiendaJQ('shipping_tax').value = tax;
	tiendaJQ('shipping_extra').value = extra;

	tiendaGrayOutAjaxDiv( 'onCheckoutShipping_wrapper', Joomla.JText._( 'COM_TIENDA_UPDATING_SHIPPING_RATES' ) );
	tiendaGrayOutAjaxDiv( 'onCheckoutCart_wrapper', Joomla.JText._( 'COM_TIENDA_UPDATING_CART' ) );		
	tiendaGetCheckoutTotals( combined ); // combined = true - both shipping rates and addresses are updating at the same time
}

/**
 * Based on the session contents,
 * calculates the order total
 * and returns HTML
 * 
 * @param combined If true, both shipping rated and addresses are updating at the same time
 * @return
 */
function tiendaGetCheckoutTotals( combined )
{
    var url = 'index.php?option=com_tienda&view=checkout&task=setShippingMethod&format=raw';
//    if( typeof( combined ) == 'undefined' )
 //   	tiendaDoTask( url, 'onCheckoutCart_wrapper', document.adminForm, '', false );
    if( combined )
    	tiendaDoTask( url, 'onCheckoutCart_wrapper', document.adminForm, '', false, tiendaDeleteCombinedGrayDiv );    	
    else
    	tiendaDoTask( url, 'onCheckoutCart_wrapper', document.adminForm, '', false, tiendaDeleteShippingGrayDiv );
}

/**
 * Recalculates the currency amounts
 * @return
 */
function tiendaGetCurrencyTotals()
{
    var url = 'index.php?option=com_tienda&view=checkout&task=setCurrency&format=raw';
    tiendaDoTask( url, 'onCheckoutReview_wrapper', document.adminForm );    
}

/**
 * Based on the session contents,
 * calculates the order total
 * and returns HTML
 * 
 * @return
 */
function tiendaRefreshTotalAmountDue()
{
	if( tiendaJQ( 'payment_info' ) )
	{
		var url = 'index.php?option=com_tienda&view=checkout&task=totalAmountDue&format=raw';
		tiendaGrayOutAjaxDiv( 'payment_info', Joomla.JText._( 'COM_TIENDA_UPDATING_BILLING' ) ); 
	    tiendaDoTask( url, 'totalAmountDue', document.adminForm, '', false, tiendaDeleteTotalAmountDueGrayDiv );		
	}
}

/**
 * If Same as Billing checkbox is selected
 * this disables all the input fields in the shipping address form
 * 
 * @param checkbox
 * @return
 */
function tiendaDisableShippingAddressControls(checkbox, form)
{
    
	var disable = false;
    if (checkbox.checked){
        disable = true;
        tiendaGetShippingRates( 'onCheckoutShipping_wrapper', form );
    }
    
    var fields = "address_name;address_id;title;first_name;middle_name;last_name;company;tax_number;address_1;address_2;city;country_id;zone_id;postal_code;phone_1;phone_2;fax";
    var fieldList = fields.split(';');

//    for(var index=0;index<fieldList.length;index++){
//        shippingControl = document.getElementById('shipping_input_'+fieldList[index]);
//        if(shippingControl != null){
//            shippingControl.disabled = disable;
//        }
//    }
 
    for(var index=0;index<fieldList.length;index++){
    	billingControl = document.getElementById('billing_input_'+fieldList[index]);
        shippingControl = document.getElementById('shipping_input_'+fieldList[index]);
        if(shippingControl != null){
    		shippingControl.disabled = disable;           
            if(billingControl != null)
            {
            	if( fieldList[index] == 'zone_id' ) // special care for zones
            	{
            		if( disable )
            			tiendaDoTask( 'index.php?option=com_tienda&format=raw&controller=checkout&task=getzones&prefix=shipping_input_&disabled=1&country_id='+document.getElementById('billing_input_country_id').value+'&zone_id='+document.getElementById('billing_input_zone_id').value, 'shipping_input_zones_wrapper', '');
            		else
            			shippingControl.disabled = false;
            	}
            	else // the rest of fields is OK the way they are handled now
            		{
            			if( shippingControl.getAttribute( 'type' ) != 'hidden' )
            				shippingControl.value = disable ? billingControl.value : '';            		
            		}
            }
        }
    }
    
    tiendaDeleteGrayDivs();
}

function tiendaManageShippingRates()
{
	tiendaJQ('shipping_form_div').getElements('input[name=shipping_rate]').addEvent('click', function() {
		tiendaGetCheckoutTotals();
	}
	);
}

function tiendaDeleteAddressGrayDiv()
{
	el_billing = $$( '#billingAddress .tiendaAjaxGrayDiv' );
	if( !el_billing )
		return;
	tiendaSetColorInContainer( 'billingAddress', '' );
	el_billing.destroy();
	
	if( tiendaJQ( 'shippingAddress' ) && ( !tiendaJQ( 'sameasbilling' ) || ( tiendaJQ( 'sameasbilling' ) && !tiendaJQ( 'sameasbilling' ).checked ) ) )
	{
		tiendaSetColorInContainer( 'shippingAddress', '' );
		$$( '#shippingAddress .tiendaAjaxGrayDiv' ).destroy();		
	}
}

function tiendaDeletePaymentGrayDiv()
{
	if( tiendaJQ( 'onCheckoutPayment_wrapper' ) )
		tiendaSetColorInContainer( 'onCheckoutPayment_wrapper', '' );
}

function tiendaDeleteTotalAmountDueGrayDiv()
{
	el = $$( '#payment_info .tiendaAjaxGrayDiv' );
	if( el != '' )
		el.destroy();
	
	tiendaSetColorInContainer( 'payment_info', '' );
}

function tiendaDeleteShippingGrayDiv()
{
	if( tiendaJQ( 'onCheckoutShipping_wrapper' ) == null )
		return;

	el = $$( '#onCheckoutShipping_wrapper .tiendaAjaxGrayDiv' );
	if( el != '' )
		el.destroy();

	
	if( tiendaJQ( 'onCheckoutShipping_wrapper' ).css( 'color' ) != '' )
	{
		tiendaSetColorInContainer( 'onCheckoutShipping_wrapper', '' );
		
		// selected shipping rate has to be checked manually
		if( tiendaJQ( 'shipping_name' ) )
		{
			shipping_plugin = tiendaJQ( 'shipping_name' ).get( 'value' );
			$$( '#onCheckoutShipping_wrapper input[type=radio]' ).each( function( e ){
				if( e.get( 'rel' ) == shipping_plugin )
					e.set( 'checked', true );
			} );			
		}
	}
	tiendaDeleteCartGrayDiv();
}

function tiendaDeleteCartGrayDiv()
{
	if( tiendaJQ('onCheckoutCart_wrapper') )
		tiendaSetColorInContainer( 'onCheckoutCart_wrapper', '' );
}

function tiendaDeleteCombinedGrayDiv()
{
	tiendaDeleteAddressGrayDiv();

	if( tiendaJQ( 'onCheckoutShipping_wrapper' ) )
		tiendaDeleteShippingGrayDiv();
	else // no shipping address so delete gray div from cart
		tiendaDeleteCartGrayDiv();
}

function tiendaGrayOutAddressDiv( prefix )
{
	if( !tiendaJQ( 'shippingAddress' ) )
		return;
	values = tiendaStoreFormInputs( document.adminForm );
	tiendaGrayOutAjaxDiv( 'billingAddress', Joomla.JText._( 'COM_TIENDA_UPDATING_ADDRESS=' ), prefix );
	if( tiendaJQ( 'shippingAddress' ) && ( !tiendaJQ( 'sameasbilling' ) || ( tiendaJQ( 'sameasbilling' ) && !tiendaJQ( 'sameasbilling' ).checked ) ) )
		tiendaGrayOutAjaxDiv( 'shippingAddress', Joomla.JText._( 'COM_TIENDA_UPDATING_ADDRESS=' ), prefix );
	tiendaRestoreFormInputs( document.adminForm , values );
}

/*
 * Method to disable UI and update shipping rates
 * 
 */
function tiendaCheckoutAutomaticShippingRatesUpdate( obj_id )
{
	obj = document.getElementById( obj_id );

	// see, if you find can find payment_wrapper and update payment methods
	if( $( 'onCheckoutPayment_wrapper' ) && obj_id.substr( 0, 8 ) == 'billing_' ) // found the payment_wrapper - update payment methods && this is a billing input
	{
		if( !$( 'shippingAddress' ) ) // no shipping
		{
			tiendaGrayOutAddressDiv();
			tiendaGetPaymentOptions('onCheckoutPayment_wrapper', document.adminForm, '', tiendaDeleteAddressGrayDiv );
		}
		else
			tiendaGetPaymentOptions('onCheckoutPayment_wrapper', document.adminForm, '' );
	}

	if( !$( 'shippingAddress' ) ) {
	    // no shipping
	    return;        
	}		

	only_shipping = !tiendaJQ( 'sameasbilling' ) || !tiendaJQ( 'sameasbilling' ).get( 'checked' );
	if( only_shipping )
	{
		tiendaGrayOutAddressDiv();
		tiendaGrayOutAjaxDiv( 'onCheckoutShipping_wrapper', Joomla.JText._( 'COM_TIENDA_UPDATING_SHIPPING_RATES' ) );
		if( obj_id.substr( 0, 9 ) == 'shipping_' ) // shipping input
		{
			tiendaGetShippingRates( 'onCheckoutShipping_wrapper', document.adminForm, tiendaDeleteAddressGrayDiv );
		}
		else // billing input
		{
			tiendaGrayOutAjaxDiv( 'onCheckoutCart_wrapper', Joomla.JText._( 'COM_TIENDA_UPDATING_CART' ) );
			tiendaGetCheckoutTotals( true );
		}
	}
	else // same as billing
	{
		if( obj_id.substr( 0, 8 ) == 'billing_' ) // billing input
		{
			tiendaGrayOutAddressDiv();
			tiendaGetShippingRates( 'onCheckoutShipping_wrapper', document.adminForm, tiendaDeleteAddressGrayDiv );
		}
	}
}

/**
 * Simple function to check a password strength
 * 
 */
function tiendaCheckPassword( container, form, psw, min_length, req_num, req_alpha, req_spec )
{
    val_errors = [];
	var pass_ok = true;
		
	act_pass = tiendaJQ( psw ).get( 'value' );
	if( act_pass.length < min_length ) // password is not long enough
	{
	    str = Joomla.JText._('COM_TIENDA_PASSWORD_MIN_LENGTH');
	    str = str.replace('%s',min_length);
	    val_errors.push(str);
		pass_ok = false;
	}
	else
	{
		if( req_num ) // checks, if the password contains a number
		{
			var patt_num = /\d/;
			has_num = patt_num.test( act_pass );
			if (!has_num) {
			    str = Joomla.JText._('COM_TIENDA_PASSWORD_REQ_NUMBER');
			    val_errors.push(str);
			    pass_ok = false;
			}
		}
		
		if( pass_ok && req_alpha ) // checks, if the password contains an alphabetical character
		{
			var patt_alpha = /[a-zA-Z]/;
			has_alpha = patt_alpha.test( act_pass );
            if (!has_alpha) {
                str = Joomla.JText._('COM_TIENDA_PASSWORD_REQ_ALPHA');
                val_errors.push(str);
                pass_ok = false;
            }
		}

		if( pass_ok && req_spec ) // checks, if the password contains a special character ?!@#$%^&*{}[]()-=+.,:\\/\"<>'_;|
		{
			var patt_spec = /[\\/\|_\-\+=\.\"':;\[\]~<>!@?#$%\^&\*()]/;
			has_special = patt_spec.test( act_pass );
            if (!has_special) {
                str = Joomla.JText._('COM_TIENDA_PASSWORD_REQ_SPEC');
                val_errors.push(str);
                pass_ok = false;
            }
		}
	}

	if( pass_ok )
	{
		val_img 	= 'accept_16.png';
		val_alt	 	= Joomla.JText._( 'COM_TIENDA_SUCCESS' );
		val_text 	= Joomla.JText._( 'COM_TIENDA_PASSWORD_VALID' );
		val_class	= 'validation-success';
	}
	else
	{
		val_img 	= 'remove_16.png';
		val_alt	 	= Joomla.JText._( 'COM_TIENDA_ERROR' );
		val_errors_string = val_errors.join(".\n");
		val_text 	= Joomla.JText._( 'COM_TIENDA_PASSWORD_INVALID' );
		val_text    = val_text + ' ' + val_errors_string;
		val_class	= 'validation-fail';
	}

	content = '<div class="tienda_validation"><img src="'+window.com_tienda.jbase+'media/com_tienda/images/'+val_img+'" alt="'+val_alt+'"><span class="'+val_class+'">'+val_text+'</span></div>';
	if( $( container ) )
		$( container ).set('html',  content );
}

/**
 * Simple function to compare passwords
 */
function tiendaCheckPassword2( container, form, psw1, psw2 )
{
	if( tiendaJQ( psw1 ).get( 'value' ) == tiendaJQ( psw2 ).get( 'value' ) )
	{
		val_img 	= 'accept_16.png';
		val_alt	 	= Joomla.JText._( 'COM_TIENDA_SUCCESS' );
		val_text 	= Joomla.JText._( 'COM_TIENDA_PASSWORD_MATCH' );
		val_class	= 'validation-success';
	}
	else
	{
		val_img 	= 'remove_16.png';
		val_alt	 	= Joomla.JText._( 'COM_TIENDA_ERROR' );
		val_text 	= Joomla.JText._( 'COM_TIENDA_PASSWORD_DO_NOT_MATCH' );
		val_class	= 'validation-fail';
	}
	
	content = '<div class="tienda_validation"><img src="'+window.com_tienda.jbase+'media/com_tienda/images/'+val_img+'" alt="'+val_alt+'"><span class="'+val_class+'">'+val_text+'</span></div>';
	if( tiendaJQ( container ) )
		tiendaJQ( container ).set('html',  content );
}


/*
 * This method checks availability of the email address
 */
function tiendaCheckoutCheckEmail( container, form )
{
	user_email = 'email_address';
	// send AJAX request to validate the email address against other users
	var url = 'index.php?option=com_tienda&controller=checkout&task=checkEmail&format=raw';
		    
	// loop through form elements and prepare an array of objects for passing to server
    var str = tiendaGetFormInputData( form );
    // execute Ajax request to server
    tiendaPutAjaxLoader( container, Joomla.JText._( 'COM_TIENDA_VALIDATING' ) );
    var a = new Request({
            url: url,
            method:"post",
        data:{"elements":JSON.encode(str)},
        onSuccess: function(response){
           var resp=JSON.decode(response, false);
            if( resp.error != '0' )
            {
        		tiendaJQ(container).set('html', resp.msg);
            }
            else
       		{
        		tiendaJQ( container ).set('html',  resp.msg );
       		}
            return true;
        }
    }).send();
}

function tiendaHideInfoCreateAccount( )
{	
	tiendaJQ('create_account').addEvent('change', function() {
		tiendaJQ('tienda_user_additional_info').toggleClass('hidden');
	});
}

function tiendaGetPaymentOptions(container, form, msg, callback) {
    var payment_plugin = $$('input[name=payment_plugin]:checked');

    if (payment_plugin) {
        payment_plugin = payment_plugin.value;
    }       
        
    var str = tiendaGetFormInputData( form );
    var url = 'index.php?option=com_tienda&view=checkout&task=updatePaymentOptions&format=raw';
    
    tiendaGrayOutAjaxDiv('onCheckoutPayment_wrapper', Joomla.JText._('COM_TIENDA_UPDATING_PAYMENT_METHODS'));
    
    // execute Ajax request to server
    var a = new Request({
        url : url,
        method : "post",
        data : {
            "elements" : JSON.encode(str)
        },
        onSuccess : function(response) {
            var resp = JSON.decode(response, false);
            $( container ).set('html',  resp.msg );
            
            if (typeof callback == 'function') {
                callback();
            }
            return true;
        },
        onFailure : function(response) {
            tiendaDeletePaymentGrayDiv();
            tiendaDeleteAddressGrayDiv();
            tiendaDeleteShippingGrayDiv();
        },
        onException : function(response) {
            tiendaDeletePaymentGrayDiv();
            tiendaDeleteAddressGrayDiv();
            tiendaDeleteShippingGrayDiv();
        }
    }).send();  

    if (payment_plugin) {
        $$('#onCheckoutPayment_wrapper input[name=payment_plugin]').each(function(e) {
            if (e.get('value') == payment_plugin)
                e.set('checked', true);
        });
    }
}
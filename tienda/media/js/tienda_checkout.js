/**
 * Based on the session contents,
 * calculates the order total
 * and returns HTML
 * 
 * @return
 */
function tiendaGetPaymentForm( element, container, text )
{
    var url = 'index.php?option=com_tienda&view=checkout&task=getPaymentForm&format=raw&payment_element=' + element;

   	tiendaGrayOutAjaxDiv( container, text, '' );
    tiendaDoTask( url, container, document.adminForm, '', false, deletePaymentGrayDiv );    	
}


function tiendaGetShippingRates( container, form, text_shipping, text_cart )
{
    var url = 'index.php?option=com_tienda&view=checkout&task=updateShippingRates&format=raw';
    
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
            $( container ).setHTML( resp.msg );
            if( resp.default_rate !== null ) // if only one rate was found - set it as default
            	tiendaSetShippingRate(resp.default_rate['name'], resp.default_rate['price'], resp.default_rate['tax'], resp.default_rate['extra'], resp.default_rate['code'], text_shipping, text_cart );
            return true;
        }
    }).request();
}

function tiendaSetShippingRate(name, price, tax, extra, code, text_shipping, text_cart )
{
	$('shipping_name').value = name;
	$('shipping_code').value = code;
	$('shipping_price').value = price;
	$('shipping_tax').value = tax;
	$('shipping_extra').value = extra;

		tiendaGrayOutAjaxDiv( 'onCheckoutShipping_wrapper', text_shipping, '' );
		tiendaGrayOutAjaxDiv( 'onCheckoutCart_wrapper', text_cart, '' );		
	tiendaGetCheckoutTotals();
}

/**
 * 
 */
function tiendaAddCoupon( form, mult_enabled )
{
    var new_coupon_code = document.getElementById('new_coupon_code').value;
    
    var url = 'index.php?option=com_tienda&view=checkout&task=validateCouponCode&format=raw&coupon_code='+new_coupon_code;
    var container = 'coupon_code_message';
    
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
            if (resp.error != '1') 
            {
                if ($(container)) { $(container).setHTML(''); }
                
                // Push the code into the form
                var cc_html = $('coupon_codes').innerHTML + resp.msg;
                $('coupon_codes').setHTML( cc_html );
                
                // Clear the field
                document.getElementById('new_coupon_code').value = '';
                
                // Update the summary
                tiendaGetCheckoutTotals();
                tiendaRefreshTotalAmountDue();
                
                if (mult_enabled != 1)
                {
                    tiendaShowHideDiv('coupon_code_form');
                }                
            }
                else
            {
                if ($(container)) { $(container).setHTML(resp.msg); }
            }
        }
    }).request();
}

function deletePaymentGrayDiv()
{
	if( $( 'onCheckoutPayment_wrapper' ) )
		tiendaSetColorInContainer( 'onCheckoutPayment_wrapper', '' );
}

function deleteShippingGrayDiv()
{
	el = $ES( '.tiendaAjaxGrayDiv', 'onCheckoutShipping_wrapper' );
	if( el )
	{
		el.destroy();
		tiendaSetColorInContainer( 'onCheckoutShipping_wrapper', '' );
		
		// selected shipping rate has to be checked manually
		shipping_plugin = $( 'shipping_name' ).get( 'value' );
		$ES( 'input[type=radio]', 'onCheckoutShipping_wrapper' ).each( function( e ){
			if( e.get( 'rel' ) == shipping_plugin )
				e.set( 'checked', true );
		} );
	}
	deleteCartGrayDiv();
}

function deleteCartGrayDiv()
{
	$( 'onCheckoutCart_wrapper' ).setStyle( 'color', '' );
}

/**
 * Based on the session contents,
 * calculates the order total
 * and returns HTML
 * 
 * @return
 */
function tiendaGetCheckoutTotals()
{
    var url = 'index.php?option=com_tienda&view=checkout&task=setShippingMethod&format=raw';
    tiendaDoTask( url, 'onCheckoutCart_wrapper', document.adminForm, '', false, deleteShippingGrayDiv );    
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
	var url = 'index.php?option=com_tienda&view=checkout&task=totalAmountDue&format=raw';
    tiendaDoTask( url, 'totalAmountDue', document.adminForm, '', false );
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
    if (checkbox.checked){disable = true;tiendaGetShippingRates( 'onCheckoutShipping_wrapper', form );}  
    
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
               		shippingControl.value = disable ? billingControl.value : '';
            }
        }
    }
}

function tiendaManageShippingRates()
{
	$('shipping_form_div').getElements('input[name=shipping_rate]').addEvent('click', function() {
		tiendaGetCheckoutTotals();
	}
	);
}
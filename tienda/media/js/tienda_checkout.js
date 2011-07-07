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
    tiendaDoTask( url, container, document.adminForm );
}

function tiendaGetShippingRates( container, form, msg )
{
    var url = 'index.php?option=com_tienda&view=checkout&task=updateShippingRates&format=raw';
    tiendaDoTask( url, container, form, msg );
    tiendaGetCheckoutTotals();
}

function tiendaSetShippingRate(name, price, tax, extra, code)
{
	$('shipping_name').value = name;
	$('shipping_code').value = code;
	$('shipping_price').value = price;
	$('shipping_tax').value = tax;
	$('shipping_extra').value = extra;
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
    tiendaDoTask( url, 'onCheckoutCart_wrapper', document.adminForm, '', false );    
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
    
    var fields = "address_name;address_id;title;first_name;middle_name;last_name;company;address_1;address_2;city;country_id;zone_id;postal_code;phone_1;phone_2;fax";
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
            		tiendaDoTask( 'index.php?option=com_tienda&format=raw&controller=checkout&task=getzones&prefix=billing_input_&disabled=1&country_id='+document.getElementById('billing_input_country_id').value+'&zone_id='+document.getElementById('billing_input_zone_id').value, 'shipping_input_zones_wrapper', '');
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
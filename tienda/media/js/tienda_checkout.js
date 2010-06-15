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

function tiendaGetShippingRates( container, form )
{
    var url = 'index.php?option=com_tienda&view=checkout&task=updateShippingRates&format=raw';
    tiendaDoTask( url, container, form );
}

function tiendaSetShippingRate(name, price, tax, extra)
{
	$('shipping_name').value = name;
	$('shipping_price').value = price;
	$('shipping_tax').value = tax;
	$('shipping_extra').value = extra;
	tiendaGetCheckoutTotals();
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
    tiendaDoTask( url, 'onCheckoutCart_wrapper', document.adminForm );    
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
    
    var fields = "address_id;title;first_name;middle_name;last_name;company;address_1;address_2;city;country_id;zone_id;postal_code;phone_1;phone_2;fax";
    var fieldList = fields.split(';');

    for(var index=0;index<fieldList.length;index++){
        shippingControl = document.getElementById('shipping_input_'+fieldList[index]);
        if(shippingControl != null){
            shippingControl.disabled = disable;
        }
    }
    /*
    var selectedAddressDiv = document.getElementById('selectedShippingAddressDiv');
    if (selectedAddressDiv != null){
        if (disable){
            selectedAddressDiv.style.display = 'none';
        }
        else{
            selectedAddressDiv.style.display = 'inline';
        }           
    }*/
}

function tiendaManageShippingRates()
{
	$('shipping_form_div').getElements('input[name=shipping_rate]').addEvent('click', function() {
		tiendaGetCheckoutTotals();
	}
	);
}
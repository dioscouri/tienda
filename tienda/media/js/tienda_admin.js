function tiendaUpdateParentDefaultImage(id) {
	var url = 'index.php?option=com_tienda&view=products&task=updateDefaultImage&protocol=json&product_id=' + id;
	var form = document.adminForm;
	// default_image
	// default_image_name

	// loop through form elements and prepare an array of objects for passing to server
	var str = new Array();
	for(i=0; i<form.elements.length; i++) {
		postvar = {
			name : form.elements[i].name,
			value : form.elements[i].value,
			checked : form.elements[i].checked,
			id : form.elements[i].id
		};
		str[i] = postvar;
	}
	// execute Ajax request to server
	var a=new Ajax(url, {
		method:"post",
		data: {
			"elements":Json.toString(str)
		},
		onComplete: function(response) {
			var resp=Json.evaluate(response, false);
			$('default_image').setHTML(resp.default_image);
			$('default_image_name').setHTML(resp.default_image_name);
			return true;
		}
	}).request();
}

function tiendaSetShippingRate(name, price, tax, extra, code) {
	$('shipping_name').value = name;
	$('shipping_code').value = code;
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
function tiendaGetCheckoutTotals() {
	var url = 'index.php?option=com_tienda&view=pos&task=setShippingMethod&format=raw';
	tiendaDoTask( url, 'orderSummary', document.adminForm, '', false );
}

function tiendaGetShippingRates( container, form, msg, doModal ) {
	var url = 'index.php?option=com_tienda&view=pos&task=updateShippingRates&format=raw';
	if (doModal != false) {
		tiendaNewModal(msg);
	}
	$('validation_message').setHTML('');

	// loop through form elements and prepare an array of objects for passing to server
	var str = new Array();
	for(i=0; i<form.elements.length; i++) {
		postvar = {
			name : form.elements[i].name,
			value : form.elements[i].value,
			checked : form.elements[i].checked,
			id : form.elements[i].id
		};
		str[i] = postvar;
	}
	// execute Ajax request to server
	var a=new Ajax(url, {
		method:"post",
		data: {
			"elements":Json.toString(str)
		},
		onComplete: function(response) {
			var resp=Json.evaluate(response, false);

			if (resp.error != '1') {
				$(container).setHTML(resp.msg);
				tiendaGetCheckoutTotals();
			} else {
				$('validation_message').setHTML(resp.msg);
			}

			if (doModal != false) {
				(function() {
					document.body.removeChild($('tiendaModal'));
				}).delay(500);
			}
			return true;
		}
	}).request();
}

function tiendaGetPaymentForm( element, container ) {
	var url = 'index.php?option=com_tienda&view=pos&task=getPaymentForm&format=raw&payment_element=' + element;
	tiendaDoTask( url, container, document.adminForm );
}

/**
 *
 */
function tiendaAddCoupon( form, mult_enabled ) {
	var new_coupon_code = document.getElementById('new_coupon_code').value;

	var url = 'index.php?option=com_tienda&view=pos&task=validateCouponCode&format=raw&coupon_code='+new_coupon_code;
	var container = 'coupon_code_message';

	// loop through form elements and prepare an array of objects for passing to server
	var str = new Array();
	for(i=0; i<form.elements.length; i++) {
		postvar = {
			name : form.elements[i].name,
			value : form.elements[i].value,
			checked : form.elements[i].checked,
			id : form.elements[i].id
		};
		str[i] = postvar;
	}

	// execute Ajax request to server
	var a=new Ajax(url, {
		method:"post",
		data: {
			"elements":Json.toString(str)
		},
		onComplete: function(response) {
			var resp=Json.evaluate(response, false);
			if (resp.error != '1') {
				if ($(container)) {
					$(container).setHTML('');
				}

				// Push the code into the form
				var cc_html = $('coupon_codes').innerHTML + resp.msg;
				$('coupon_codes').setHTML( cc_html );

				// Clear the field
				document.getElementById('new_coupon_code').value = '';

				// Update the summary
				tiendaGetCheckoutTotals();

				if (mult_enabled != 1) {
					tiendaShowHideDiv('coupon_code_form');
				}
			} else {
				if ($(container)) {
					$(container).setHTML(resp.msg);
				}
			}
		}
	}).request();
}

/**
 * 
 */
function tiendaAddCredit( form )
{
    var apply_credit_amount = document.getElementById('apply_credit_amount').value;
    
    var url = 'index.php?option=com_tienda&view=pos&task=validateApplyCredit&format=raw&apply_credit_amount='+apply_credit_amount;
    var container = 'credit_message';
    
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
                $('applied_credit').setHTML( resp.msg );                
                // Clear the field
                $('apply_credit_amount').value = '';
                               
                 // Update the summary
                tiendaGetCheckoutTotals();                          
            }
                else
            {
                if ($(container)) { $(container).setHTML(resp.msg); }
            }
        }
    }).request();
}
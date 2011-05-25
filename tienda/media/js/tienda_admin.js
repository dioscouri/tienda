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

function tiendaGetShippingRates( container, form, msg ) {
	var url = 'index.php?option=com_tienda&view=pos&task=updateShippingRates&format=raw';
	tiendaDoTask( url, container, form, msg );
	tiendaGetCheckoutTotals();
}

function tiendaGetPaymentForm( element, container )
{
    var url = 'index.php?option=com_tienda&view=pos&task=getPaymentForm&format=raw&payment_element=' + element;
    tiendaDoTask( url, container, document.adminForm );
}
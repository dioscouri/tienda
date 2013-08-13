if (typeof(Tienda) === 'undefined') {
	var Tienda = {};
}

Tienda.saveConfigOnClick = function() {
    tiendaJQ('a.view-config').each(function(){
        var el = tiendaJQ(this);
        Tienda.postConfigFormAndRedirect(el);
    });
    
    tiendaJQ('a.view-shipping').each(function(){
        var el = tiendaJQ(this);
        Tienda.postConfigFormAndRedirect(el);
    });
    
    tiendaJQ('a.view-payment').each(function(){
        var el = tiendaJQ(this);
        Tienda.postConfigFormAndRedirect(el);
    });
}

Tienda.postConfigFormAndRedirect = function(el) {
    el.click(function(event){
        event.preventDefault();
        url = 'index.php?option=com_tienda&view=config&format=raw';
        
        values = tiendaJQ("#adminForm").serializeArray();
        for (index = 0; index < values.length; ++index) {
            if (values[index].name == "task") {
                values[index].value = 'save';
                break;
            }
        }
        data = jQuery.param(values);
        
        tiendaJQ.post( url, data, function(response){
            window.location = el.attr('href');
        });
    });    
}

Tienda.refreshProductGallery = function(product_id) {
    var url = 'index.php?option=com_tienda&view=products&task=refreshProductGallery&product_id=' + product_id + '&tmpl=component&format=raw';
    var request = jQuery.ajax({
        type: 'get', 
        url: url,
    }).done(function(data){
        var response = JSON.decode(data, false);
        if (response.html) {
            tiendaJQ('#form-gallery').html(response.html);
            Tienda.bindProductGalleryLinks();
        }
    }).fail(function(data){

    }).always(function(data){

    });
}

Tienda.bindProductGalleryLinks = function() {
    tiendaJQ('.delete-gallery-image').each(function(){
        el = tiendaJQ(this);
        var url = el.attr('data-href');
        var product_id = el.attr('data-product_id');
        if (url) {
            el.off('click.pg').on('click.pg', function(event){
                event.preventDefault();
                var request = jQuery.ajax({
                    type: 'get', 
                    url: url,
                }).done(function(data){
                    var response = JSON.decode(data, false);
                    Tienda.refreshProductGallery(product_id);
                }).fail(function(data){

                }).always(function(data){

                });                
            });            
        }
    });
    
    tiendaJQ('.set-default-gallery-image').each(function(){
        el = tiendaJQ(this);
        var url = el.attr('data-href');
        var image = el.attr('data-image');
        if (url) {
            el.off('click.pg').on('click.pg', function(event){
                event.preventDefault();
                var request = jQuery.ajax({
                    type: 'get', 
                    url: url,
                }).done(function(data){
                    var response = JSON.decode(data, false);
                    if (response.html) {
                        tiendaJQ('#default_image').html(response.html);
                    }
                    tiendaJQ('#product_full_image').val(image);
                }).fail(function(data){

                }).always(function(data){

                });                
            });            
        }
    });
}

Tienda.DisableShippingAddressControls = function(check){
	var s_table = tiendaJQ("table[data-type='shipping_input'] :input");
	if( check ) {
		s_table.attr('disabled', 'true');
	} else {
		s_table.removeAttr('disabled');		
	}
	
}

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
	var a = new Request({
		url : url,
		method : "post",
		data : {
			"elements" : JSON.encode(str)
		},
		onSuccess : function(response) {
			var resp = JSON.decode(response, false);
 			tiendaJQ('default_image').html ( resp.default_image );
	      	tiendaJQ('default_image_name').html( resp.default_image_name);
			return true;
		}
	}).send();
}

function tiendaSetShippingRate(name, price, tax, extra, code) {
	tiendaJQ('shipping_name').val( name );
	tiendaJQ('shipping_code').val( code );
	tiendaJQ('shipping_price').val( price );
	tiendaJQ('shipping_tax').val( tax );
	tiendaJQ('shipping_extra').val( extra );
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
		Dsc.newModal(msg)
	}
	tiendaJQ('#validation_message').html( '' );

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
	 var a = new Request({
		url : url,
		method : "post",
		data : {
			"elements" : JSON.encode(str)
		},
		onSuccess : function(response) {
			var resp = JSON.decode(response, false);

			if (resp.error != '1') {
				tiendaJQ("#"+container).html( resp.msg );
				tiendaGetCheckoutTotals();
			} else {
				tiendaJQ('#validation_message').set('html', resp.msg);
			}

			if (doModal != false) {
				if (doModal != false) { (function() { document.body.removeChild( document.getElementById('dscModal') ); }).delay(500); }
			}
			return true;
		}
	 }).send();
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
	var a = new Request({
		url : url,
		method : "post",
		data : {
			"elements" : JSON.encode(str)
		},
		onSuccess : function(response) {
			var resp = JSON.decode(response, false);
			if (resp.error != '1') {
				if ($(container)) {
					$(container).set('html', '');
				}

				// Push the code into the form
				var cc_html = $('coupon_codes').innerHTML + resp.msg;
				$('coupon_codes').set('html',  cc_html );

				// Clear the field
				document.getElementById('new_coupon_code').value = '';

				// Update the summary
				tiendaGetCheckoutTotals();

				if (mult_enabled != 1) {
					tiendaShowHideDiv('coupon_code_form');
				}
			} else {
				if ($(container)) {
					$(container).set('html', resp.msg);
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
   var a = new Request({
		url : url,
		method : "post",
		data : {
			"elements" : JSON.encode(str)
		},
		onSuccess : function(response) {
			var resp = JSON.decode(response, false);
            if (resp.error != '1') 
            {
                if ($(container)) { $(container).set('html', ''); }
                $('applied_credit').set('html',  resp.msg );                
                // Clear the field
                $('apply_credit_amount').value = '';
                               
                 // Update the summary
                tiendaGetCheckoutTotals();                          
            }
                else
            {
                if ($(container)) { $(container).set('html', resp.msg); }
            }
        }
    }).send();
}
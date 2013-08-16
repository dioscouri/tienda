if (typeof(Tienda) === 'undefined') {
    var Tienda = {};
}

Tienda.deleteWishlistItem = function(wishlistitem_id, prompt_text, callback_function) {

    if (!prompt_text) { prompt_text = "Are you sure you want to delete this item?"; }
    var r = confirm(prompt_text);
    
    if (r == true && wishlistitem_id) {
        var url = 'index.php?option=com_tienda&view=wishlists&task=deleteWishlistItem&format=raw&wishlistitem_id=' + wishlistitem_id;
        var request = jQuery.ajax({
            type: 'post', 
            url: url
        }).done(function(data){
            var response = JSON.decode(data, false);

            if (response.error) {
                alert(response.html);
            } else {
                if ( typeof callback_function === 'function') {
                    callback_function( response );
                }                                    
            } 

        }).fail(function(data){
            
        }).always(function(data){

        });        
    }
    
    return false;
}

Tienda.addToWishlist = function( form_id, container_id, callback_function ) {
    var url = 'index.php?option=com_tienda&format=raw&view=products';
    
    var form_data = tiendaJQ('#'+form_id).serializeArray();
    tiendaJQ.each(form_data, function(index, value) {
        if (value.name == 'task') {
            form_data[index].value = 'addToWishlist';
        }
    });
    
    var request = jQuery.ajax({
        type: 'post', 
        url: url,
        data: form_data
    }).done(function(data){
        var response = JSON.decode(data, false);
        if (response.html) {
            tiendaJQ('#'+container_id).html(response.html);
        }        
        if ( typeof callback_function === 'function') {
            callback_function( response );
        }                    
    }).fail(function(data){
        
    }).always(function(data){

    });
}

Tienda.privatizeWishlist = function(wishlist_id, privacy, callback_function) {

    if (wishlist_id && privacy) {
        var url = 'index.php?option=com_tienda&view=wishlists&task=privatizeWishlist&format=raw&wishlist_id='+wishlist_id+'&privacy='+privacy;
        var request = jQuery.ajax({
            type: 'post', 
            url: url
        }).done(function(data){
            var response = JSON.decode(data, false);

            if (response.error) {
                alert(response.html);
            } else {
                if ( typeof callback_function === 'function') {
                    callback_function( response );
                }                                    
            } 

        }).fail(function(data){
            
        }).always(function(data){

        });        
    }
    
    return false;
}

Tienda.deleteWishlist = function(wishlist_id, prompt_text, callback_function) {

    if (!prompt_text) { prompt_text = "Are you sure you want to delete this Wishlist?"; }
    var r = confirm(prompt_text);
    
    if (r == true && wishlist_id) {
        var url = 'index.php?option=com_tienda&view=wishlists&task=deleteWishlist&format=raw&wishlist_id=' + wishlist_id;
        var request = jQuery.ajax({
            type: 'post', 
            url: url
        }).done(function(data){
            var response = JSON.decode(data, false);

            if (response.error) {
                alert(response.html);
            } else {
                if ( typeof callback_function === 'function') {
                    callback_function( response );
                }                                    
            } 

        }).fail(function(data){
            
        }).always(function(data){

        });        
    }
    
    return false;
}

Tienda.createWishlist = function(wishlist_name, prompt_text, callback_function) {

    if (!wishlist_name) {
        if (!prompt_text) { prompt_text = "Please provide a name for this Wishlist:"; }
        var wishlist_name = prompt(prompt_text);
    };
    
    var post_data = {
            wishlist_name: wishlist_name
    }; 
    var url = 'index.php?option=com_tienda&view=wishlists&task=createWishlist&format=raw';
    
    if (wishlist_name) {
        var request = jQuery.ajax({
            type: 'post', 
            url: url,
            data: post_data
        }).done(function(data){
            var response = JSON.decode(data, false);

            if (response.error) {
                alert(response.html);
            } else {
                if ( typeof callback_function === 'function') {
                    callback_function( response );
                }                                    
            } 

        }).fail(function(data){
            
        }).always(function(data){

        });        
    }
    
    return false;
}

Tienda.renameWishlist = function(wishlist_id, prompt_text, callback_function) {

    if (!wishlist_name) {
        if (!prompt_text) { prompt_text = "Please provide a name for this Wishlist:"; }
        var wishlist_name = prompt(prompt_text);
    };
    
    var post_data = {
            wishlist_name: wishlist_name
    }; 
    var url = 'index.php?option=com_tienda&view=wishlists&task=renameWishlist&format=raw&wishlist_id=' + wishlist_id;
    
    if (wishlist_name) {
        var request = jQuery.ajax({
            type: 'post', 
            url: url,
            data: post_data
        }).done(function(data){
            var response = JSON.decode(data, false);

            if (response.error) {
                alert(response.html);
            } else {
                if ( typeof callback_function === 'function') {
                    callback_function( response );
                }                                    
            } 

        }).fail(function(data){
            
        }).always(function(data){

        });        
    }
    
    return false;
}

Tienda.addWishlistItemToWishlist = function( wishlistitem_id, wishlist_id, callback_function ) {
    var url = 'index.php?option=com_tienda&format=raw&view=wishlists&task=addWishlistItemToWishlist&wishlistitem_id='+wishlistitem_id+'&wishlist_id='+wishlist_id;
        
    var request = jQuery.ajax({
        type: 'post', 
        url: url
    }).done(function(data){
        var response = JSON.decode(data, false);
        if ( typeof callback_function === 'function') {
            callback_function( response );
        }                    
    }).fail(function(data){
        
    }).always(function(data){

    });
}

Tienda.UpdateAddToCart = function(page, container, form, working, callback) {
	var url = 'index.php?option=com_tienda&format=raw&view=products&task=updateAddToCart&page=' + page;
	if( page == 'pos' ) {
		url = 'index.php?option=com_tienda&format=raw&view=pos&task=updateAddToCart&page=' + page;
	}
	// loop through form elements and prepare an array of objects for passing to server
	var str = tiendaGetFormInputData(form);
	// execute Ajax request to server
	if (working)
		tiendaGrayOutAjaxDiv(container, Joomla.JText._('COM_TIENDA_UPDATING_ATTRIBUTES'), '');
		
    tiendaJQ.post( url, { "elements" : JSON.encode(str) }, function(response){
		var resp = JSON.decode(response, false);
		if (document.getElementById(container)) {
			document.getElementById(container).set('html', resp.msg);
		}
		document.getElementById(container).setStyle('color', '');
		
		Tienda.updateProductDetail(resp, page, container, form);
		
		if ( typeof callback === 'function')
			callback(resp);
		return true;
    });
}

/**
 * Updates a product detail page with new PAOVs
 * [Experimental]
 */
Tienda.updateProductDetail = function(resp, page, container, form) {
    var f = tiendaJQ( form );
    var changed_attr = tiendaJQ( 'input[name="changed_attr"]', f ).val();
    
    if (!resp.paov_items || !changed_attr) {
        return;
    }
    
    new_image = null;
    paov_items = resp.paov_items;
    product_id = resp.product_id;
    tiendaJQ.each(paov_items, function(index, paov){
        if (paov.productattributeoptionvalue_field == 'product_full_image' && paov.productattributeoptionvalue_operator == 'replace' && paov.productattributeoptionvalue_value) {
            new_image = paov.productattributeoptionvalue_value;
        }
    });
    
    if (new_image) {
        jqzoom = jQuery('.product-' + product_id + ' #product_image a.zoom').data('jqzoom');
        if (jqzoom) {
            jqzoom.changeimage(new_image);
        }        
    }
}

/*
 * Changes ID of currently changed attribute on form
 */
Tienda.UpdateChangedAttribute= function( form, attr_id ) {
	var f = tiendaJQ( form );
	tiendaJQ( 'input[name="changed_attr"]', f ).val( attr_id );
}

/**
 * Simple function to refresh a page.
 */
function tiendaUpdate() {
	location.reload(true);
}

/**
 * Resets the filters in a form.
 * This should be renamed to tiendaResetFormFilters
 *
 * @param form
 * @return
 */
function tiendaFormReset(form) {
	// loop through form elements
	Dsc.resetFormFilters(form);
}

/**
 *
 * @param {Object} order
 * @param {Object} dir
 * @param {Object} task
 */
function tiendaGridOrdering(order, dir, form) {
	Dsc.gridOrdering(order, dir, form);
}

/**
 *
 * @param id
 * @param change
 * @return
 */
function tiendaGridOrder(id, change, form) {
	Dsc.gridOrder(id, change, form);
}

/**
 * Sends form values to server for validation and outputs message returned.
 * Submits form if error flag is not set in response
 *
 * @param {String} url for performing validation
 * @param {String} form element name
 * @param {String} task being performed
 */
function tiendaFormValidation(url, container, task, form, doModal, msg, onCompleteFunction) {
	Dsc.formValidation(url, container, task, form, doModal, msg, onCompleteFunction);
}

/**
 * Submits form using onsubmit if present
 * @param task
 * @return
 */
function tiendaSubmitForm(task, form) {
	Dsc.submitForm(task, form);
}

/**
 * Overriding core submitbutton task to perform our onsubmit function
 * without submitting form afterwards
 *
 * @param task
 * @return
 */
function submitbutton(task) {
	if (task) {
		document.adminForm.task.value = task;
	}

	if ( typeof document.adminForm.onsubmit == "function") {
		document.adminForm.onsubmit();
	} else {
		submitform(task);
	}
}

/**
 *
 * @param {Object} divname
 * @param {Object} spanname
 * @param {Object} showtext
 * @param {Object} hidetext
 */
function tiendaDisplayDiv(divname, spanname, showtext, hidetext) {
	Dsc.displayDiv(divname, spanname, showtext, hidetext);
}

/**
 *
 * @param {Object} prefix
 * @param {Object} newSuffix
 */
function tiendaSwitchDisplayDiv(prefix, newSuffix) {
	Dsc.switchDisplayDiv(prefix, newSuffix);
}

function tiendaShowHideDiv(divname) {
	Dsc.showHideDiv(divname);
}

/**
 *
 * @param {String} url to query
 * @param {String} document element to update after execution
 * @param {String} form name (optional)
 * @param {String} msg message for the modal div (optional)
 * @param (Function) Function which is executed after the call is completed
 */
function tiendaDoTask(url, container, form, msg, doModal, onCompleteFunction) {

	Dsc.doTask(url, container, form, msg, doModal, onCompleteFunction);

}

/**
 *
 * @param {String} msg message for the modal div (optional)
 */
function tiendaNewModal(msg) {
	Dsc.newModal(msg);
}

/**
 * Gets the value of a selected radiolist item
 *
 * @param radioObj
 * @return string
 */
function tiendaGetCheckedValue(radioObj) {
	if (!radioObj) {
		return "";
	}

	var radioLength = radioObj.length;
	if (radioLength == undefined) {
		if (radioObj.checked)
			return radioObj.value;
		else
			return "";
	}

	for (var i = 0; i < radioLength; i++) {
		if (radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function tiendaVerifyZone() {
	var c = document.getElementById('country_id');
	var z = document.getElementById('zone_id');

	if (c != null && c != 'undefined' && c != '' && z != null && z != 'undefined' && z != '') {
		if (z.options[z.selectedIndex].value != "" && c.options[c.selectedIndex].value != "") {
			document.getElementById('task').value = 'addzone';
			document.adminForm.submit();
		} else {
			alert('Please select both a Country and a Zone.');
		}
	} else {
		alert('Please select both a Country and a Zone.');
	}
}

function submitTiendabutton(pressbutton, fieldname) {
	submitTiendaform(pressbutton, fieldname);
}

/**
 * Submit the admin form using a custom task field name
 */
function submitTiendaform(pressbutton, fieldname) {
	if (pressbutton) {
		document.adminForm.elements[fieldname].value = pressbutton;
	}
	if ( typeof document.adminForm.onsubmit == "function") {
		document.adminForm.onsubmit();
	}
	document.adminForm.submit();
}

/**
 * Pauses execution for the specified milliseconds
 * @param milliseconds
 * @return
 */
function tiendaPause(milliseconds) {
	var dt = new Date();
	while ((new Date()) - dt <= milliseconds) {/* Do nothing */
	}
}

/**
 *
 * @param {String} url to query
 * @param {String} document element to update after execution
 * @param {String} form name (optional)
 * @param {String} msg message for the modal div (optional)
 */
function tiendaAddToCart(url, container, form, msg) {
	var cartContainer = 'tiendaUserShoppingCart';
	var cartUrl = 'index.php?option=com_tienda&format=raw&view=carts&task=displayCart';

	// loop through form elements and prepare an array of objects for passing to server
	var str = tiendaGetFormInputData(form);

	// execute Ajax request to server
	var a = new Request({
		url : url,
		method : "post",
		data : {
			"elements" : JSON.encode(str)
		},
		onSuccess : function(response) {
			var resp = JSON.decode(response, false);
			if (resp.error == '1') {
				if (document.getElementById(container)) {
					document.getElementById(container).set('html', resp.msg);
				}
				return false;
			} else {
				tiendaPause(500);
				tiendaDoTask(cartUrl, cartContainer, '', '', false);
				return true;
			}
		}
	}).send();
}

function tiendaAddRelationship(container, msg) {
	var url = 'index.php?option=com_tienda&view=products&task=addRelationship&protocol=json';
	tiendaDoTask(url, container, document.adminForm, msg, true);
	document.adminForm.new_relationship_productid_to.value = '';
}

function tiendaRemoveRelationship(id, container, msg) {
	var url = 'index.php?option=com_tienda&view=products&task=removeRelationship&protocol=json&productrelation_id=' + id;
	tiendaDoTask(url, container, document.adminForm, msg, true);
}

function tiendaRating(id) {
	var count;
	document.getElementById('productcomment_rating').value = id;
	for ( count = 1; count <= id; count++) {
		document.getElementById('rating_'+count).getElementsByTagName("img")[0].src = window.com_tienda.jbase + "media/com_tienda/images/star_10.png";
	}

	for ( count = id + 1; count <= 5; count++) {
		document.getElementById('rating_'+count).getElementsByTagName("img")[0].src = window.com_tienda.jbase + "media/com_tienda/images/star_00.png";
	}
}

function tiendaCheckUpdateCartQuantities(form, text) {

	var quantities = form.getElements('input[name^=quantities]');
	var original_quantities = form.getElements('input[name^=original_quantities]');

	var returned = true;

	quantities.each(function(item, index) {
		if (item.value != original_quantities[index].value) {
			returned = confirm(text);
		}
	});

	return returned;

}

function tiendaPopulateAttributeOptions(select, target, opt_name, opt_id) {
	// Selected option
	var attribute_id = select.getSelected().getLast().value;

	tiendaGetAttributeOptions(attribute_id, target, opt_name, opt_id);
}

function tiendaGetAttributeOptions(attribute_id, container, opt_name, opt_id) {
	var url = 'index.php?option=com_tienda&controller=productattributeoptions&task=getProductAttributeOptions&attribute_id=' + attribute_id + '&select_name=' + opt_name + '&select_id=' + opt_id + '&format=raw';
	tiendaDoTask(url, container);
}

/**
 * Sends form values to server for validation and outputs message returned.
 * Submits form if error flag is not set in response
 * Always performs validation, regardless of task value
 *
 * @param {String} url for performing validation
 * @param {String} html container to update with validation message
 * @param {String} task to be executed if form validates
 * @param {String} form name
 * @param {Boolean} display modal overlay?
 * @param {String} Text for modal overlay
 */
function tiendaValidation(url, container, task, form, doModal, msg) {
	if (doModal == true) {
		tiendaNewModal(msg);
	}

	// loop through form elements and prepare an array of objects for passing to server
	var str = tiendaGetFormInputData(form);

	// execute Ajax request to server
	var a = new Request({
		url : url,
		method : "post",
		data : {
			"elements" : JSON.encode(str)
		},
		onSuccess : function(response) {
			var resp = JSON.decode(response, false);
			if (resp.error == '1') {
				if (document.getElementById(container)) {
					document.getElementById(container).set('html', resp.msg);
				}
			}
			if (doModal != false) { (function() { document.body.removeChild(tiendaJQ('dscModal')); }).delay(500); }
			if (resp.error != '1') {
				form.task.value = task;
				form.submit();
			}
		}
	}).send();
}

function tiendaClearInput(element, value) {
	if (element.value == value) {
		element.value = '';
	}
}

function tiendaAddProductToCompare(id, container, obj, doModal) {
	var add = 0;
	var msg = Joomla.JText._("COM_TIENDA_REMOVING_PRODUCT");
	if (obj.checked == true) {
		add = 1;
		msg = Joomla.JText._("COM_TIENDA_ADDING_PRODUCT_FOR_COMPARISON");
	}
	if (doModal == true) {
		tiendaNewModal(msg);
	}
	var url = 'index.php?option=com_tienda&view=productcompare&task=addProductToCompare&format=raw&product_id=' + id + '&add=' + add;

	// execute Ajax request to server
	var a = new Request({
		url : url,
		method : "post",

		onSuccess : function(response) {
			var resp = JSON.decode(response, false);

			if (doModal != false) { (function() { document.body.removeChild($('dscModal')); }).delay(500); }
			if (resp.error == '1') {
				if ($('validationmessage')) {
					$('validationmessage').set('html', resp.msg);
				}
			} else {
				if (document.getElementById(container)) {
					document.getElementById(container).set('html', resp.msg);
				}
			}
		}
	}).send();
}

/**
 *
 */
function tiendaAddCoupon(form, mult_enabled) {
	var new_coupon_code = document.getElementById('new_coupon_code').value;

	var url = 'index.php?option=com_tienda&view=checkout&task=validateCouponCode&format=raw&coupon_code=' + new_coupon_code;
	var container = 'coupon_code_message';

	// loop through form elements and prepare an array of objects for passing to server
	var str = tiendaGetFormInputData(form);

	tiendaGrayOutAjaxDiv('coupon_code_area', Joomla.JText._('COM_TIENDA_CHECKING_COUPON'));
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
				if (document.getElementById(container)) {
					document.getElementById(container).set('html', '');
				}

				// Push the code into the form
				var cc_html = $('coupon_codes').innerHTML + resp.msg;
				if ($('coupon_codes').set('html', cc_html)) {
				    tiendaGetPaymentOptions('onCheckoutPayment_wrapper', form, '' );
				}

				// Clear the field
				document.getElementById('new_coupon_code').value = '';

				// Update the summary
				tiendaGrayOutAjaxDiv('onCheckoutCart_wrapper', Joomla.JText._('COM_TIENDA_UPDATING_CART'));
				tiendaGetCheckoutTotals(true);
				tiendaRefreshTotalAmountDue();

				if (mult_enabled != 1) {
					tiendaShowHideDiv('coupon_code_form');
				}
				
			} else {
				if (document.getElementById(container)) {
					document.getElementById(container).set('html', resp.msg);
				}
			}

			el = $$('#coupon_code_area .tiendaAjaxGrayDiv');
			if (el != '')
				el.destroy();
			tiendaSetColorInContainer('coupon_code_area', '');
		}
	}).send();
}

/**
 *
 */
function tiendaAddCartCoupon(form, mult_enabled) {
	var new_coupon_code = document.getElementById('new_coupon_code').value;

	var url = 'index.php?option=com_tienda&view=carts&task=validateCouponCode&format=raw&coupon_code=' + new_coupon_code;
	var container = 'coupon_code_message';

	// loop through form elements and prepare an array of objects for passing to server
	var str = tiendaGetFormInputData(form);

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
				if (document.getElementById(container)) {
					document.getElementById(container).set('html', '');
				}

				// Push the code into the form
				var cc_html = $('coupon_codes').innerHTML + resp.msg;
				$('coupon_codes').set('html', cc_html);

				// Clear the field
				document.getElementById('new_coupon_code').value = '';

				// Update the summary
				tiendaGetCartCheckoutTotals();
				tiendaRefreshCartTotalAmountDue();

				if (mult_enabled != 1) {
					tiendaShowHideDiv('coupon_code_form');
				}
			} else {
				if (document.getElementById(container)) {
					document.getElementById(container).set('html', resp.msg);
				}
			}
		}
	}).send();
}

/**
 * Based on the session contents,
 * calculates the order total
 * and returns HTML
 *
 * @return
 */
function tiendaGetCartCheckoutTotals() {
	var url = 'index.php?option=com_tienda&view=carts&task=saveOrderCoupons&format=raw';
	tiendaDoTask(url, 'onCheckoutCart_wrapper', document.adminForm, '', true);
}

/**
 * Based on the session contents,
 * calculates the order total
 * and returns HTML
 *
 * @return
 */
function tiendaRefreshCartTotalAmountDue() {
	var url = 'index.php?option=com_tienda&view=carts&task=totalAmountDue&format=raw';
	tiendaDoTask(url, 'totalAmountDue', document.adminForm, '', false, function() {
	});

	//url, container, form, msg, doModal, execFunc
}

/**
 * Puts an AJAX loader gif to a div element
 * @param container ID of the div element
 * @param text Text next to ajax loading picture
 * @param suffix Suffix of the AJAX loader gif (in case it's empty '_transp' is used)
 */
function tiendaPutAjaxLoader(container, text, suffix) {
	if (!suffix || suffix == '')
		suffix = '_transp';

	text_element = '';
	if (text != null && text != '')
		text_element = '<span> ' + text + '</span>';
	var img_loader = '<img src="' + window.com_tienda.jbase + 'media/com_tienda/images/ajax-loader' + suffix + '.gif' + '"/>';
	if (document.getElementById(container)) {
	    document.getElementById(container).set('html', img_loader + text_element);
	}
}

/**
 * Puts an AJAX loader gif to a div element and gray out that div
 * @param container 	ID of the div element
 * @param text 			Text which is displayed under the image
 * @param suffix 		Suffix of the AJAX loader gif (in case it's empty '_transp' is used)
 *
 */
function tiendaGrayOutAjaxDiv(container, text, suffix) {
	if (!suffix || suffix == '')
		suffix = '_transp';

	var img_loader = '<img src="' + window.com_tienda.jbase + 'media/com_tienda/images/ajax-loader' + suffix + '.gif' + '"/>';
	document.getElementById(container).setStyle('position', 'relative');
	text_element = '';
	if (text && text.length)
		text_element = '<div class="text">' + text + '</div>';

	// make all texts in the countainer gray
	tiendaSetColorInContainer(container, '');
	document.getElementById(container).innerHTML += '<div class="tiendaAjaxGrayDiv">' + img_loader + text_element + '</div>';
}

function tiendaSetColorInContainer(container, color) {
	if (document.getElementById(container)) { document.getElementById(container).setStyle('color', color); }
	$$('#' + container + ' *' ).each(function(el) {
		el.setStyle('color', color);
	});
}

/*
 * Method to store values of all inputs on a form
 *
 * @param form Form
 *
 * @return Associative array
 */
function tiendaStoreFormInputs(form) {
	var values = new Array();
	for ( i = 0; i < form.elements.length; i++) {
		value = {
			value : form.elements[i].value,
			checked : form.elements[i].checked
		};
		values[form.elements[i].name] = value;
	}
	return values;
}

/*
 * Method to restore values of all inputs on a form
 *
 * @param form 		Form
 * @param values	Values which are being restored
 *
 * @return Associative array
 */
function tiendaRestoreFormInputs(form, values) {
	for ( i = 0; i < form.elements.length; i++) {
		if (form.elements[i].getAttribute('type') == 'checkbox')
			form.elements[i].checked = values[form.elements[i].name].checked;
		else if ($(form.elements[i].id))
			$(form.elements[i].id).set('value', values[form.elements[i].name].value);
	}
}

/*
 * Method to get value from all form inputs and put it in an array which will be passed via AJAX request
 *
 * @param form		Form with inputs
 *
 * @return Array with all data from all inputs on the form
 */
function tiendaGetFormInputData(form) {
	var str = new Array();
	for ( i = 0; i < form.elements.length; i++) {
		postvar = {
			name : form.elements[i].name,
			value : form.elements[i].value,
			checked : form.elements[i].checked,
			id : form.elements[i].id
		};
		str[i] = postvar;
	}
	return str;
}

function tiendaDeleteGrayDivs() {
    $$('.tiendaAjaxGrayDiv').each(function(el) {
        el.destroy();
    });
}
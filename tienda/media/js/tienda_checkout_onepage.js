/**
 * Method to copy all data from Billing Address fields to Shipping Address fields
 * @param billingprefix
 * @param shippingprefix
 * @return
 */
function tiendaCopyBillingAdToShippingAd(checkbox, form) {
	var disable = false;
	if (checkbox.checked) {
		disable = true;
		tiendaGrayOutAddressDiv();
		tiendaGetShippingRates('onCheckoutShipping_wrapper', form, tiendaDeleteAddressGrayDiv);
		tiendaGetPaymentOptions('onCheckoutPayment_wrapper', form);
	}
}

function tiendaSaveOnepageOrder(container, errcontainer, form) {
	var url = 'index.php?option=com_tienda&view=checkout&controller=checkout&task=saveOrderOnePage&format=raw';
	var str = tiendaGetFormInputData(form);

	// execute Ajax request to server
	tiendaPutAjaxLoader(errcontainer, Joomla.JText._('COM_TIENDA_VALIDATING'));
	var a = new Request({
		url : url,
		method : "post",
		data : {
			"elements" : JSON.encode(str)
		},
		onSuccess : function(response) {
			var resp = JSON.decode(response, false);

			if (resp.error != '1') {
			    if (resp.redirect) {
			        window.location = resp.redirect;
			        return;
			    }
			    
				if ($(container)) {
					$(container).set('html', resp.msg);
				}
				if ($('onCheckoutCart_wrapper')) {
					$('onCheckoutCart_wrapper').set('html', resp.summary);
				}
				if ($('tienda_btns')) {
					$('tienda_btns').setStyle('display', 'none');
				}
				if ($('refreshpage')) {
					$('refreshpage').setStyle('display', 'block');
				}
				if ($('validationmessage')) {
					$('validationmessage').set('html', '');
				}
				window.location = String(window.location).replace(/\#.*$/, "") + "#tienda-method";
			} else {
				if ($(errcontainer)) {
					$(errcontainer).set('html', resp.msg);
				}
				if (resp.anchor) {
					window.location = String(window.location).replace(/\#.*$/, "") + resp.anchor;
				}
			}
		}
	}).send();
}

function tiendaGetFinalForm(container, form, msg) {
	var url = 'index.php?option=com_tienda&view=checkout&task=getRegisterForm&format=raw';
	tiendaDoTask(url, container, form, msg);
	$('tienda-method-pane').set('html', $('hiddenregvalue').value);
}

function tiendaGetView(url, container, labelcont) {
	// execute Ajax request to server
	var a = new Request({
		url : url,
		method : "post",
		onSuccess : function(response) {
			var resp = JSON.decode(response, false);

			if (resp.error != '1') {
				if ($(container)) {
					$(container).set('html', resp.msg);
				}
				if (labelcont) {
					$(labelcont).set('html', resp.label);
				}
			}
		}
	}).send();
}

function tiendaGetRegistrationForm(container, form, msg) {
	var url = 'index.php?option=com_tienda&view=checkout&task=getRegisterForm&format=raw';
	tiendaGetView(url, container, 'tienda-method-pane');
}

/**
 * method to hide billing fields
 */
function tiendaHideBillingFields() {
	$('billingToggle_show').set('class', 'hidden');

	$('field-toggle').addEvent('change', function() {
		$$('#billingDefaultAddress', '#billingToggle_show', '#billingToggle_hide').toggleClass('hidden');
	});
}

function tiendaCheckoutSetBillingAddress(url, container, selected, form) {
	var divContainer = document.getElementById(container);
	var divForm = document.getElementById('billing_input_addressForm');

	if (selected > 0)// address was selected -> get shipping rates
	{
		values = tiendaStoreFormInputs(form);
		divContainer.style.display = "";
		divForm.style.display = "none";
		tiendaGrayOutAddressDiv();
		tiendaDoTask(url, container, '', '', false);
		if ($('onCheckoutShipping_wrapper'))
			tiendaGrayOutAjaxDiv('onCheckoutShipping_wrapper', Joomla.JText._('COM_TIENDA_UPDATING_SHIPPING_RATES'));
		tiendaGrayOutAjaxDiv('onCheckoutCart_wrapper', Joomla.JText._('COM_TIENDA_UPDATING_CART'));

		tiendaGetCheckoutTotals(true);
		tiendaRestoreFormInputs(form, values);
	} else// user wants to create a new address
	{
		divContainer.style.display = "none";
		divForm.style.display = "";
	}
}

function tiendaCheckoutSetShippingAddress(url, container, form, selected) {
	var divContainer = document.getElementById(container);
	var divForm = document.getElementById('shipping_input_addressForm');
	if (selected > 0)// address was selected -> get shipping rates
	{
		values = tiendaStoreFormInputs(form);
		divContainer.style.display = "";
		divForm.style.display = "none";
		tiendaGrayOutAddressDiv();
		tiendaDoTask(url, container, '', '', false);
		tiendaGrayOutAjaxDiv('onCheckoutShipping_wrapper', Joomla.JText._('COM_TIENDA_UPDATING_SHIPPING_RATES'));
		tiendaGetShippingRates('onCheckoutShipping_wrapper', form, tiendaDeleteAddressGrayDiv);
		tiendaRestoreFormInputs(form, values);
	} else// user wants to create a new address
	{
		divContainer.style.display = "none";
		divForm.style.display = "";
	}
}
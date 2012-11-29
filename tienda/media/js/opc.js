TiendaOpc = TiendaClass.extend({
    /**
     * @memberOf TiendaOpc
     */
    __construct: function() {
        this.defaults = {
            billingForm: null,
            shippingForm: null,
            guestCheckoutEnabled: 1,
            summaryElements: {
                setMethod: 'opc-checkout-method-summary',
                setBilling: 'opc-billing-summary',
                setShipping: 'opc-shipping-summary',
                setShippingMethod: 'opc-shipping-method-summary',
                setPayment: 'opc-payment-summary',
                setReview: 'opc-review-summary'
            }
        };
        
        this.method = null;
        this.billing = null;
        this.shipping = null;
        this.payment = null;
        this.syncBillingShipping = false;
        this.urls = {
            setMethod: 'index.php?option=com_tienda&view=opc&task=setMethod&tmpl=component&format=raw',
            setBilling: 'index.php?option=com_tienda&view=opc&task=setBilling&tmpl=component&format=raw',
            setShipping: 'index.php?option=com_tienda&view=opc&task=setShipping&tmpl=component&format=raw',
            setShippingMethod: 'index.php?option=com_tienda&view=opc&task=setShippingMethod&tmpl=component&format=raw',
            setPayment: 'index.php?option=com_tienda&view=opc&task=setPayment&tmpl=component&format=raw',
            setReview: 'index.php?option=com_tienda&view=opc&task=setReview&tmpl=component&format=raw',
            failure: 'index.php?option=com_tienda&view=carts'
        };
    },

    init: function (element, options, urls) {
        this.__construct();
        this.element = tiendaJQ(element);
        this.options = jQuery.extend( true, {}, this.defaults, options || {} );
        
        this.urls    = jQuery.extend( true, {}, this.urls, urls || {} );
        this.accordion = new TiendaOpcAccordion(element, this.options);
    },
    
    /**
     * 
     * @param section
     */
    gotoSection: function(section)
    {
        var sectionElement = tiendaJQ('#opc-'+section);
        sectionElement.addClass('allow');
        this.accordion.openSection('opc-'+section);
    },
    
    setMethod: function(){
        if (tiendaJQ('#checkout-method-guest') && tiendaJQ('#checkout-method-guest').attr('checked')) {
            this.method = 'guest';
            var request = jQuery.ajax({
                type: 'post', 
                url: this.urls.setMethod,
                context: this,
                data: {
                    method: 'guest'
                }
            }).done(function(data){
                var response = JSON.decode(data, false);
                response.summary.id = this.options.summaryElements.setMethod;
                this.handleSuccess(response);
            }).fail(function(data){
                this.handleFailure();
            }).always(function(data){

            });
            
            if (tiendaJQ('#register-customer-password')) {
                tiendaJQ('#register-customer-password').hide();
            }
            this.gotoSection('billing');
        }
        else if(tiendaJQ('#checkout-method-register') && (tiendaJQ('#checkout-method-register').attr('checked') || !tiendaJQ('#checkout-method-guest'))) {
            this.method = 'register';
            var request = jQuery.ajax({
                type: 'post', 
                url: this.urls.setMethod,
                context: this,
                data: {
                    method: 'register'
                }
            }).done(function(data){
                var response = JSON.decode(data, false);
                response.summary.id = this.options.summaryElements.setMethod;
                this.handleSuccess(response);
            }).fail(function(data){
                this.handleFailure();
            }).always(function(data){

            });
            
            if (tiendaJQ('#register-customer-password')) {
                tiendaJQ('#register-customer-password').show();
            }
            this.gotoSection('billing');
        }
        else {
            if (this.options.guestCheckoutEnabled) {
                alert(Joomla.JText._('COM_TIENDA_PLEASE_CHOOSE_REGISTER_OR_CHECKOUT_AS_GUEST'));
            } else {
                alert(Joomla.JText._('COM_TIENDA_PLEASE_CHOOSE_REGISTER'));
            }
            return false;
        }
    },
    
    setBilling: function() {
        var next_section = 'payment';
        if (this.shipping) {
            next_section = 'shipping';
        }        
        
        if ((tiendaJQ('#billing_input_use_for_shipping_yes')) && (tiendaJQ('#billing_input_use_for_shipping_yes').attr('checked'))) {
            if (this.shipping) {
                this.shipping.syncWithBilling();
                tiendaJQ('#opc-shipping').addClass('allow');
                tiendaJQ('#shipping_input_same_as_billing').attr('checked', true);
                next_section = 'shipping-method';
            }
        } else if ((tiendaJQ('#billing_input_use_for_shipping_no')) && (tiendaJQ('#billing_input_use_for_shipping_no').attr('checked'))) {
            tiendaJQ('#shipping_input_same_as_billing').attr('checked', false);
        } else {
            tiendaJQ('#shipping_input_same_as_billing').attr('checked', true);
        }
        
        var form_data = tiendaJQ('#opc-billing-form').serializeArray();

        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.setBilling,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            response.summary.id = this.options.summaryElements.setBilling;
            this.handleSuccess(response);
            this.gotoSection(next_section);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },
    
    setShipping: function() {
        var next_section = 'shipping-method';
        var form_data = tiendaJQ('#opc-shipping-form').serializeArray();

        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.setShipping,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            response.summary.id = this.options.summaryElements.setShipping;
            this.handleSuccess(response);
            this.gotoSection(next_section);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },

    setShippingMethod: function() {
        var next_section = 'payment';
        var form_data = tiendaJQ('#opc-shipping-method-form').serializeArray();

        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.setShippingMethod,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            response.summary.id = this.options.summaryElements.setShippingMethod;
            this.handleSuccess(response);
            this.gotoSection(next_section);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },

    setPayment: function() {
        var next_section = 'review';
        var form_data = tiendaJQ('#opc-payment-form').serializeArray();

        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.setPayment,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            response.summary.id = this.options.summaryElements.setPayment;
            this.handleSuccess(response);
            this.gotoSection(next_section);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },
    
    setReview: function() {
        var next_section = 'postpayment';
        var form_data = tiendaJQ('#opc-review-form').serializeArray();

        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.setReview,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            response.summary.id = this.options.summaryElements.setReview;
            this.handleSuccess(response);
            this.gotoSection(next_section);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },
    
    /**
     * Handles the response from a successful ajax request,
     * "successful" only in that the request didn't get a 404 or 500 error.
     * This also handles responses for failed validations... 
     */
    handleSuccess: function(response) {
        if (response.summary) {
            tiendaJQ('#'+response.summary.id).html(response.summary.html);
        }
        
        if (response.summaries) {
            tiendaJQ.each(response.summaries, function(key, summary){
                tiendaJQ('#'+summary.id).html(summary.html);
            });
        }
        
        if (response.allow_sections) {
            tiendaJQ.each(response.allow_sections, function(key, section){
                tiendaJQ('#'+section).addClass('allow');
            });
        }

        if (response.duplicateBillingInfo)
        {
            this.shipping.setSameAsBilling(true);
            this.setShipping();
        }

        if (response.goto_section) {
            this.gotoSection(response.goto_section);
            return true;
        }
        
        if (response.redirect) {
            window.location = response.redirect;
            return true;
        }
        return false;
    },
    
    /**
     * Handles a failed ajax request
     */
    handleFailure: function() {
        window.location = this.urls.failure;
    }
});

TiendaShipping = TiendaClass.extend({
    /**
     * @memberOf TiendaShipping
     */
    __construct: function() {
        this.defaults = {
            billingInputPrefix: 'billing_input_',
            shippingInputPrefix: 'shipping_input_'
        };
    },
    
    init: function (element, options) {
        this.__construct();
        this.element = tiendaJQ(element);
        this.options = jQuery.extend( true, {}, this.defaults, options || {} );
    },
    
    getFormElements: function(el) {
        var elements = el.find('*').filter(':input');
        return elements;
    },
    
    setSameAsBilling: function(flag) {
        tiendaJQ('#shipping_input_same_as_billing').attr('checked', flag);
        if (flag) {
            this.syncWithBilling();
        }
    },

    syncWithBilling: function () {
        tiendaJQ('#shipping_input_same_as_billing').attr('checked', true);
        
        if (!tiendaJQ('#billing-address-select') || !tiendaJQ('#billing-address-select').val()) {
            arrElements = this.getFormElements(this.element);

            for (i=0,len=arrElements.length; i<len; i++) {
                var targetFormElement = tiendaJQ(arrElements[i]);
                if (targetFormElement.attr('id')) {
                    var sourceField = tiendaJQ( '#' + targetFormElement.attr('id').replace(this.options.shippingInputPrefix, this.options.billingInputPrefix) );
                    if (sourceField) {
                        targetFormElement.val( sourceField.val() );
                    }
                }
            }
        } else {
            tiendaJQ('#shipping-address-select').val( tiendaJQ('#billing-address-select').val() );
        }
    }
});

TiendaPayment = TiendaClass.extend({
    /**
     * @memberOf TiendaPayment
     */
    __construct: function() {
        this.defaults = {
        };
    },
    
    init: function (element, options) {
        this.__construct();
        this.element = tiendaJQ(element);
        this.options = jQuery.extend( true, {}, this.defaults, options || {} );
    },
    
    getPluginForm: function (element, container, message) {
        var url = 'index.php?option=com_tienda&view=opc&task=getPaymentForm&format=raw&payment_element=' + element;
        Dsc.doTask( url, container, document.getElementById('opc-payment-form'), '', false );
    }
    
});
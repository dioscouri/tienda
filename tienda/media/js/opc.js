TiendaOpc = TiendaClass.extend({
    /**
     * @memberOf TiendaOpc
     */
    __construct: function() {
        this.sections = ['checkout-method', 'billing', 'shipping', 'shipping-method', 'payment', 'review'];
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
                submitOrder: 'opc-review-summary'
            },
            validationElements: {
                setMethod: 'opc-checkout-method-validation',
                setBilling: 'opc-billing-validation',
                setShipping: 'opc-shipping-validation',
                setShippingMethod: 'opc-shipping-method-validation',
                setPayment: 'opc-payment-validation',
                submitOrder: 'opc-review-validation'
            },
            urls: {
                setMethod: 'index.php?option=com_tienda&view=opc&task=setMethod&tmpl=component&format=raw',
                setBilling: 'index.php?option=com_tienda&view=opc&task=setBilling&tmpl=component&format=raw',
                setShipping: 'index.php?option=com_tienda&view=opc&task=setShipping&tmpl=component&format=raw',
                setShippingMethod: 'index.php?option=com_tienda&view=opc&task=setShippingMethod&tmpl=component&format=raw',
                setPayment: 'index.php?option=com_tienda&view=opc&task=setPayment&tmpl=component&format=raw',
                submitOrder: 'index.php?option=com_tienda&view=opc&task=submitOrder&tmpl=component&format=raw',
                failure: 'index.php?option=com_tienda&view=carts'
            }
        };
        
        this.method = null;
        this.billing = null;
        this.shipping = null;
        this.payment = null;
        this.syncBillingShipping = false;
        this.urls = {};
    },

    init: function (element, options) {
        this.__construct();
        this.element = tiendaJQ(element);
        this.options = jQuery.extend( true, {}, this.defaults, options || {} );
        
        this.urls    = this.options.urls;
        this.accordion = new TiendaOpcAccordion(element, this.options);
        
        this.setupSection('checkout-method');
    },
    
    /**
     * 
     * @param section
     */
    gotoSection: function(section)
    {
        this.setupSection(section);
        var sectionElement = tiendaJQ('#opc-'+section);
        sectionElement.addClass('allow');
        this.accordion.openSection('opc-'+section);
    },
    
    setupSection: function(section)
    {
        switch (section) 
        {
            case "checkout-method":
                this.setupMethodForm();
                break;
            case "billing":
                this.setupBillingForm();
                break;
            case "shipping":
                this.setupShippingForm();
                break;
            case "shipping-method":
                this.setupShippingMethodForm();
                break;
            case "payment":
                this.setupPaymentForm();
                break;
            case "prepare-payment":
                this.submitPreparePaymentForm();
                break;
            case "review":
                this.setupReviewForm();
                break;
        }
    },
    
    setupMethodForm: function() {
        if (tiendaJQ('#opc-checkout-method-form').length) {
            tiendaJQ('#checkout-method-guest').click(function(){
                tiendaJQ('#email-password').show(); 
                tiendaJQ('#register-password').hide();                
            });
            if (tiendaJQ('#checkout-method-guest').attr('checked')) {
                tiendaJQ('#checkout-method-guest').click();
            }
            
            tiendaJQ('#checkout-method-register').click(function(){
                tiendaJQ('#email-password').show(); 
                tiendaJQ('#register-password').show();                
            });
            if (tiendaJQ('#checkout-method-register').attr('checked')) {
                tiendaJQ('#checkout-method-register').click();
            }
        }
    },
    
    setupBillingForm: function() {
        var self = this;
        
        tiendaJQ("#existing-billing-address").change(function(){
            if (tiendaJQ(this).children(":selected").attr('id') == 'create-new-billing-address') {
                tiendaJQ('#new-billing-address').show();
            } else {
                tiendaJQ('#new-billing-address').hide();
            }
        });
        
        tiendaJQ('#opc-billing-button').on('click', function(event){
            event.preventDefault();
            self.setBilling();
        });
    },
    
    setupShippingForm: function() {
        var self = this;
        
        tiendaJQ("#existing-shipping-address").change(function(){
            if (tiendaJQ(this).children(":selected").attr('id') == 'create-new-shipping-address') {
                tiendaJQ('#new-shipping-address').show();
            } else {
                tiendaJQ('#new-shipping-address').hide();
            }
        });
        
        tiendaJQ('#opc-shipping-button').on('click', function(event){
            event.preventDefault();
            self.setShipping();
        });
    },
    
    setupShippingMethodForm: function() {
        var self = this;
        
        if (!tiendaJQ("input.shipping-plugin:checked").val()) {
            tiendaJQ('#opc-shipping-method-button').attr('disabled', 'disabled');
        }
        
        tiendaJQ('#opc-shipping-method-button').on('click', function(event){
            event.preventDefault();
            if (!tiendaJQ("input.shipping-plugin:checked").val()) {
                tiendaJQ('#opc-shipping-method-button').attr('disabled', 'disabled');
            } else {
                self.setShippingMethod();
            }
        });
        
        tiendaJQ('.shipping-plugin').on('click', function(){
            tiendaJQ('#opc-shipping-method-button').removeAttr('disabled');
        });
    },
    
    setupPaymentForm: function() {
        var self = this;
        
        if (!tiendaJQ("input.payment-plugin:checked").val()) {
            tiendaJQ('#opc-payment-button').attr('disabled', 'disabled');
        }
        
        tiendaJQ('#opc-payment-button').on('click', function(event){
            event.preventDefault();
            if (!tiendaJQ("input.payment-plugin:checked").val()) {
                tiendaJQ('#opc-payment-button').attr('disabled', 'disabled');
            } else {
                self.setPayment();
            }
        });
        
        tiendaJQ('.payment-plugin').on('click', function(){
            tiendaJQ('#opc-payment-button').removeAttr('disabled');
        });
    },
    
    setupReviewForm: function() {
        var self = this;
        tiendaJQ('#opc-review-button').removeAttr('disabled').on('click', function(event){
            event.preventDefault();
        });
        
        tiendaJQ('#opc-review-button').one('click', function(){
            self.submitOrder();
            tiendaJQ('#'+self.options.validationElements.submitOrder).empty();
            tiendaJQ(this).attr('disabled', 'disabled');
        });
    },
    
    submitPreparePaymentForm: function() {
        var self = this;
        var form = tiendaJQ("#opc-payment-prepayment form");
        if (form.length) {
            form.submit();
        }
    },
    
    setMethod: function() {
        var form_data = tiendaJQ('#opc-checkout-method-form').serializeArray();
        
        if (tiendaJQ('#checkout-method-guest').length && tiendaJQ('#checkout-method-guest').attr('checked')) {
            this.method = 'guest';
            var request = jQuery.ajax({
                type: 'post', 
                url: this.urls.setMethod,
                context: this,
                data: form_data
            }).done(function(data){
                var response = JSON.decode(data, false);
                if (!response.summary.id) {
                    response.summary.id = this.options.summaryElements.setMethod;
                }
                this.handleSuccess(response);
            }).fail(function(data){
                this.handleFailure();
            }).always(function(data){

            });
            
            if (tiendaJQ('#register-password').length) {
                tiendaJQ('#register-password').hide();
            }
            this.gotoSection('billing');
        }
        else if(tiendaJQ('#checkout-method-register').length && (tiendaJQ('#checkout-method-register').attr('checked') || !tiendaJQ('#checkout-method-guest').length)) {
            this.method = 'register';
            var request = jQuery.ajax({
                type: 'post', 
                url: this.urls.setMethod,
                context: this,
                data: form_data
            }).done(function(data){
                var response = JSON.decode(data, false);
                if (!response.summary.id) {
                    response.summary.id = this.options.summaryElements.setMethod;
                }
                this.handleSuccess(response);
            }).fail(function(data){
                this.handleFailure();
            }).always(function(data){

            });
            
            if (tiendaJQ('#register-password').length) {
                tiendaJQ('#register-password').show();
            }
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
        if ((tiendaJQ('#billing_input_use_for_shipping_yes').length) && (tiendaJQ('#billing_input_use_for_shipping_yes').attr('checked'))) {
            if (this.shipping) {
                this.shipping.syncWithBilling();
                tiendaJQ('#opc-shipping').addClass('allow');
                tiendaJQ('#shipping_input_same_as_billing').attr('checked', true).val('1');
            }
        } else if ((tiendaJQ('#billing_input_use_for_shipping_no').length) && (tiendaJQ('#billing_input_use_for_shipping_no').attr('checked'))) {
            tiendaJQ('#shipping_input_same_as_billing').attr('checked', false).val('0');
        } else {
            if (!tiendaJQ('#existing-billing-address').length || tiendaJQ('#existing-billing-address').val() == 0) {
                tiendaJQ('#shipping_input_same_as_billing').attr('checked', true).val('1');
            }            
        }
        
        var form_data = tiendaJQ('#opc-billing-form').serializeArray();

        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.setBilling,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            if (!response.summary.id) {
                response.summary.id = this.options.summaryElements.setBilling;
            }
            this.handleSuccess(response);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },
    
    setShipping: function() {
        var form_data = tiendaJQ('#opc-shipping-form').serializeArray();

        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.setShipping,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            if (!response.summary.id) {
                response.summary.id = this.options.summaryElements.setShipping;
            }
            this.handleSuccess(response);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },

    setShippingMethod: function() {
        var form_data = tiendaJQ('#opc-shipping-method-form').serializeArray();

        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.setShippingMethod,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            if (!response.summary.id) {
                response.summary.id = this.options.summaryElements.setShippingMethod;
            }
            this.handleSuccess(response);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },

    setPayment: function() {
        var form_data = tiendaJQ('#opc-payment-form').serializeArray();

        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.setPayment,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            if (!response.summary.id) {
                response.summary.id = this.options.summaryElements.setPayment;
            }
            this.handleSuccess(response);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },
    
    submitOrder: function() {
        var form_data = new Array();
        tiendaJQ.merge( form_data, tiendaJQ('#opc-checkout-method-form').serializeArray() );
        tiendaJQ.merge( form_data, tiendaJQ('#opc-billing-form').serializeArray() );
        tiendaJQ.merge( form_data, tiendaJQ('#opc-shipping-form').serializeArray() );
        tiendaJQ.merge( form_data, tiendaJQ('#opc-shipping-method-form').serializeArray() );
        tiendaJQ.merge( form_data, tiendaJQ('#opc-payment-form').serializeArray() );
        tiendaJQ.merge( form_data, tiendaJQ('#opc-review-form').serializeArray() );
        
        var request = jQuery.ajax({
            type: 'post', 
            url: this.urls.submitOrder,
            context: this,
            data: form_data
        }).done(function(data){
            var response = JSON.decode(data, false);
            if (!response.summary.id) {
                response.summary.id = this.options.validationElements.submitOrder;
            }
            this.handleSuccess(response);
        }).fail(function(data){
            this.handleFailure();
        }).always(function(data){

        });
    },
    
    /**
     * Handles the response from a successful ajax request,
     * "successful" only in that the request didn't get a 404 or 500 error, so
     * this also handles responses for failed validations... 
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
        var val = 0;
        if (flag) { val = 1; }
        tiendaJQ('#shipping_input_same_as_billing').attr('checked', flag).val(val);
        if (flag) {
            this.syncWithBilling();
        }
    },

    syncWithBilling: function () {
        tiendaJQ('#shipping_input_same_as_billing').attr('checked', true).val('1');
        
        if (!tiendaJQ('#existing-billing-address').length || tiendaJQ('#existing-billing-address').val() == 0) {
            tiendaJQ('#existing-shipping-address').val( tiendaJQ('#existing-billing-address').val() );
            arrElements = this.getFormElements(this.element);

            for (i=0,len=arrElements.length; i<len; i++) {
                var targetFormElement = tiendaJQ(arrElements[i]);
                if (targetFormElement.attr('id')) {
                    var sourceField = tiendaJQ( '#' + targetFormElement.attr('id').replace(this.options.shippingInputPrefix, this.options.billingInputPrefix) );
                    if (sourceField.length) {
                        targetFormElement.val( sourceField.val() );
                    }
                }
            }
        } else {
            tiendaJQ('#existing-shipping-address').val( tiendaJQ('#existing-billing-address').val() );
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
/**
 * 
 */
TiendaOpc = TiendaClass.extend({
    __construct: function() {
        this.defaults = {
            billingForm: null,
            shippingForm: null,
            guestCheckoutEnabled: 1,
            summaryElements: {
                setMethod: 'opc-checkout-method-summary',
                setBilling: 'opc-billing-summary',
                setShipping: 'opc-shipping-summary',
                setShippingMethod: 'opc-shipping-method-summary'
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
        this.gotoSection('review');
    },
    
    /**
     * Handles the response from a successful ajax request
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
        
        if (response.update_section) {
            /*$('checkout-'+response.update_section.name+'-load').update(response.update_section.html);*/
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
    __construct: function() {
        this.defaults = {
            clickableEntity: '.opc-section-title', 
            checkAllow: true,
            billingInputPrefix: 'billing_input_',
            shippingInputPrefix: 'shipping_input_'
        };
        this.disallowAccessToNextSections = true;
        this.currentSection = false;
    },
    
    init: function (element, options) {
        this.__construct();
        this.element = tiendaJQ(element);
        this.options = jQuery.extend( true, {}, this.defaults, options || {} );

        this.checkAllow = this.options.checkAllow;
        this.sections = tiendaJQ(element + ' .opc-section');        
        var headers = tiendaJQ(element + ' .opc-section ' + this.options.clickableEntity);

        var self = this;
        headers.each(function() {
            tiendaJQ(this).click(function(event){
                self.sectionClicked(event);
            });
        });
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
        //tiendaJQ('#billing-address-select') && this.newAddress(!tiendaJQ('billing-address-select').value);
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
    },
});
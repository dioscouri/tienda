Opc = Class.extend({
    __construct: function() {
        this.defaults = {
            billingForm: null,
            shippingForm: null,
            guestCheckoutEnabled: 1,
            summaryElements: {
                setMethod: 'opc-checkout-method-summary',
                setBilling: 'opc-billing-summary'
            }
        };
        
        this.method = ''; // str
        this.billing = ''; // obj
        this.shipping = ''; // obj
        this.payment = ''; // obj
        this.syncBillingShipping = false;
        this.urls = {
            setMethod: 'index.php?option=com_tienda&view=opc&task=setMethod&tmpl=component&format=raw',
            setBilling: 'index.php?option=com_tienda&view=opc&task=setBilling&tmpl=component&format=raw',
            failure: 'index.php?option=com_tienda&view=carts'
        };
    },
    
    init: function (element, options, urls) {
        this.__construct();
        this.element = tiendaJQ(element);
        this.options = jQuery.extend( true, {}, this.defaults, options || {} );
        
        this.urls    = jQuery.extend( true, {}, this.urls, urls || {} );
        this.accordion = new OpcAccordion(element, this.options);
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
        var next_section = 'shipping';
        
        if ((tiendaJQ('#billing_input_use_for_shipping_yes')) && (tiendaJQ('#billing_input_use_for_shipping_yes').attr('checked'))) {
            console.log('setBilling 1');
            //shipping.syncWithBilling();
            tiendaJQ('#opc-shipping').addClass('allow');
            tiendaJQ('#shipping_input_same_as_billing').attr('checked', true);
            var next_section = 'shipping-method';
        } else if ((tiendaJQ('#billing_input_use_for_shipping_no')) && (tiendaJQ('#billing_input_use_for_shipping_no').attr('checked'))) {
            tiendaJQ('#shipping_input_same_as_billing').attr('checked', false);
            console.log('setBilling 2');
        } else {
            tiendaJQ('#shipping_input_same_as_billing').attr('checked', true);
            console.log('setBilling 3');
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
        this.gotoSection('shipping_method');
    },

    setShippingMethod: function() {
        this.gotoSection('payment');
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
            //$('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }
        
        if (response.allow_sections) {
            tiendaJQ.each(response.allow_sections, function(key, section){
                tiendaJQ('#'+section).addClass('allow');
            });
        }

        if(response.duplicateBillingInfo)
        {
            //shipping.setSameAsBilling(true);
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
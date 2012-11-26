Opc = Class.extend({
    __construct: function() {
        this.defaults = {
            guestCheckoutEnabled: 1
        };
        this.method = '';
        this.urls = {
            setMethod: 'index.php?option=com_tienda&view=opc&task=setMethod',
            failure: 'index.php?option=com_tienda&view=carts'
        };
    },
    
    init: function (element, options, urls) {
        this.__construct();
        this.element = tiendaJQ(element);
        this.options = jQuery.extend( {}, this.defaults, options || {} );
        
        this.urls    = jQuery.extend( this.urls, urls || {} );
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
            }).done(function(response){
                this.handleSuccess(response);
            }).fail(function(response){
                this.handleFailure();
            }).always(function(response){

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
            }).done(function(response){
                this.handleSuccess(response);
            }).fail(function(response){
                this.handleFailure();
            }).always(function(response){

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
    
    /**
     * Handles the response from a successful ajax request
     */
    handleSuccess: function(response) {
        if (response.update_section) {
            //$('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }
        
        if (response.allow_sections) {
            response.allow_sections.each(function(e){
                //$('opc-'+e).addClassName('allow');
            });
        }

        if(response.duplicateBillingInfo)
        {
            //shipping.setSameAsBilling(true);
        }

        if (response.goto_section) {
            //this.gotoSection(response.goto_section);
            return true;
        }
        
        if (response.redirect) {
            //location.href = response.redirect;
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
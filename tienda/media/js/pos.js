TiendaPos = TiendaClass.extend({
    /**
     * @memberOf TiendaPos
     */
    __construct: function() {
        this.defaults = {
            billingInputPrefix: 'billing_input_',
            shippingInputPrefix: 'shipping_input_'
        };
        
        this.validations = {};
        this.urls = {};
        this.urls.validate_address = 'index.php?option=com_tienda&view=pos&task=validate&format=raw';
    },

    init: function (element, options) {
        this.__construct();
        this.element = tiendaJQ(element);
        this.options = jQuery.extend( true, {}, this.defaults, options || {} );
    },
    
    setupSection: function(section)
    {
        switch (section) 
        {
            case "shipping":
           	{
                this.setupShippingForm();
                break;
            }
            case "payment":
            {
                this.setupPaymentForm();
                break;
            }
        }
    },
    
    setupShippingForm: function() {
        this.validations.setAddress = new TiendaValidation('#pos-form-step3-shipping');
        var self = this;
        
        if( this.element.size() ) {
        	if( !tiendaJQ('#billing_input_address_id').size() ) {
		        tiendaJQ('#pos_continue').on('click', function(e){
		            e.preventDefault();
		            self.setAddress( this, false ); 
		        });
        	}
        }
    },

    setupPaymentForm: function() {
        this.validations.setAddress = new TiendaValidation('#pos-form-step3-payment');
        var self = this;
        
        if( this.element.size() ) {
        	if( !tiendaJQ('#billing_input_address_id').size() ) {
		        tiendaJQ('#pos_continue').on('click', function(e){
		            e.preventDefault();
		            self.setAddress( this, true );
		        });
        	}
        }
    },
    
	setAddress: function( el, onlyBilling ) {
		if( onlyBilling ) {
			if( tiendaJQ('#sameasbilling').attr('checked') == 'checked' ) {
				this.syncWithBilling();
			}
		}

		var subtask = tiendaJQ(el).data( "task" );
		if( this.validations.setAddress.validateForm() ) {
			tiendaValidation( this.urls.validate_address,
								'validation_message', 
								subtask, 
								document.adminForm, 
								true,
								 Joomla.JText._('COM_TIENDA_VALIDATING') );
		}
    },
    
    getFormElements: function(el) {
        var elements = el.find('*').filter(':input');
        return elements;
    },
    
    syncWithBilling: function () {
        tiendaJQ('#sameasbilling').attr('checked', true).val('1');
		var bill_id = tiendaJQ( '#billing_input_address_id' );
		var ship_id = tiendaJQ( '#shipping_input_address_id' );

		if(bill_id.size() == 0) {
            arrElements = this.getFormElements( tiendaJQ('#pos-form-step3-shipping') );

            for (i=0,len=arrElements.length; i<len; i++) {
                var targetFormElement = tiendaJQ(arrElements[i] );
                if (targetFormElement.attr('id')) {
                    var sourceField = tiendaJQ( '#' + targetFormElement.attr('id').replace(this.options.shippingInputPrefix, this.options.billingInputPrefix) );
                    if (sourceField.length) {
                        targetFormElement.val( sourceField.val() );
                    }
                }
            }			
		} else {
			if( ship_id.size() ) {
				ship_id.val( bill_id.val() );
			}
		}		
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
    
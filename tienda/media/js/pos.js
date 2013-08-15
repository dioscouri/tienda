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
        this.urls.validate_address = 'index.php?option=com_tienda&view=pos&task=validate&format=raw&step=step3';
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
            case "payment":
            case "shipping":
           	{
                this.setupAddressForm(section);
                break;
            }
        }
    },
    
    setupAddressForm: function(section) {
        this.validations.setAddress = new TiendaValidation('#pos-form-step3-'+section);
        var self = this;
        
        if( this.element.size() ) {
	        tiendaJQ('#pos_continue').on('click', function(e){
	            e.preventDefault();
	            self.setAddress( this, section == "payment" ); 
	        });
        }
    },

	setAddress: function( el, onlyBilling ) {
		if( !onlyBilling ) {
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
   	}
});
    
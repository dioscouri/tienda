if (typeof(Tienda) === 'undefined') {
    var Tienda = {};
}

if (typeof(Tienda.opc) === 'undefined') {
    var Tienda.opc = {};
}

Tienda.opc.prototype = {
    // private
    var defaults = {};
    
    // public
    this.method = '';
    this.urls = {
        setMethod: 'index.php?option=com_tienda&view=opc&task=setMethod'
    };
    
    init: function (element, options, urls) {
        this.element = element;
        this.options = jQuery.extend( {}, defaults, options || {} );
        this.urls    = jQuery.extend( this.urls, urls || {} );
    },
    
    gotoSection: function(section)
    {
        var sectionElement = tiendaJQ('opc-'+section);
        sectionElement.addClass('allow');
        this.openSection('opc-'+section);
    },
    
    openSection: function(section)
    {
        // do stuff to section inside this.element
    },
    
    setMethod: function(){
        if (tiendaJQ('checkout-method-guest') && tiendaJQ('checkout-method-guest').checked) {
            this.method = 'guest';
            var request = new Ajax.Request(
                this.urls.setMethod,
                {method: 'post', onFailure: this.ajaxFailure.bind(this), parameters: {method:'guest'}}
            );
            //Element.hide('register-customer-password');
            this.gotoSection('billing');
        }
        else if(tiendaJQ('checkout-method-register') && (tiendaJQ('checkout-method-register').checked || !tiendaJQ('checkout-method-guest'))) {
            this.method = 'register';
            var request = new Ajax.Request(
                this.urls.setMethod,
                {method: 'post', onFailure: this.ajaxFailure.bind(this), parameters: {method:'register'}}
            );
            //Element.show('register-customer-password');
            this.gotoSection('billing');
        }
        else {
            return false;
        }
    }    
}
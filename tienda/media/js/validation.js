TiendaValidation = TiendaClass.extend({
    /**
     * @memberOf TiendaValidation
     */
    __construct: function() {
        this.defaults = {
        };
    },
    
    init: function (element, options) {
        this.__construct();
        this.element = tiendaJQ(element);
        this.options = jQuery.extend( true, {}, this.defaults, options || {} );
        
        this.setupForm();
    },
    
    getFormElements: function(el) {
        if (el === undefined) {
            el = this.element;
        }
        var elements = el.find('*').filter(':input');
        return elements;
    },
    
    setupForm: function(el) {
        arrElements = this.getFormElements(el);
        for (i=0,len=arrElements.length; i<len; i++) {
            var formElement = tiendaJQ(arrElements[i]);
            if (formElement.hasClass("required")) {
                formElement.data("required", true);
            }
        }
    },
    
    validateForm: function (el) {
        var validations = new Array();
        arrElements = this.getFormElements(el);
        for (i=0,len=arrElements.length; i<len; i++) {
            var fieldElement = tiendaJQ(arrElements[i]);
            validations.push(this.validateField(fieldElement, el));
        }
        
        if (tiendaJQ.inArray( false, validations ) != '-1') {
            return false;
        }
        return true;
    },
    
    validateField: function (fieldElement, el) {
        if (fieldElement.data('required') && this.isFieldEmpty(fieldElement, el)) {
            fieldElement.parents(".control-group").addClass("error").removeClass('warning').removeClass('info').removeClass('success');
            return false;
        } else {
            fieldElement.parents(".control-group").removeClass('error').removeClass('warning').removeClass('info').removeClass('success');
            return true;
        }
    },
    
    isFieldEmpty: function (field, el) {
        if (el === undefined) {
            el = this.element;
        }
        var type = field.attr('type');
        switch(type) {
            case "radio":
                var val = el.find('input:radio[name='+ field.attr('name') +']:checked').val();
                if (!val) {
                    return true;
                }
                break;
            default:
                if (field.val() === null || !field.val()) {
                    return true;
                }
                break;
        }

        return false;
    }
});
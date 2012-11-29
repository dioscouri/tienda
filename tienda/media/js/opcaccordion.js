TiendaOpcAccordion = TiendaClass.extend({
    /**
     * @memberOf TiendaOpcAccordion
     */
    __construct: function() {
        this.defaults = {
            clickableEntity: '.opc-section-title', 
            checkAllow: true
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

    sectionClicked: function(event) {
        event.preventDefault();
        section_id = tiendaJQ(event.target).closest('.opc-section').attr('id');
        this.openSection(section_id);
        event.stopPropagation();
    },

    openSection: function(section) {
        var sectionObj = tiendaJQ('#'+section);

        if (this.checkAllow && !sectionObj.hasClass('allow')) {
            return;
        }

        if(sectionObj.attr('id') != this.currentSection) {
            this.closeExistingSection();
            this.currentSection = sectionObj.attr('id');
            tiendaJQ('#' + this.currentSection).addClass('active');
            var contents = tiendaJQ('.opc-section-body', sectionObj);
            contents.show();
            
            if (this.disallowAccessToNextSections) {
                var pastCurrentSection = false;
                for (var i=0; i<this.sections.length; i++) {
                    if (pastCurrentSection) {
                        tiendaJQ(this.sections[i]).removeClass('allow');
                    }
                    if (tiendaJQ(this.sections[i]).attr('id') == sectionObj.attr('id')) {
                        pastCurrentSection = true;
                    }
                }
            }
        }
    },

    closeSection: function(section) {
        var sectionObj = tiendaJQ('#'+section);
        sectionObj.removeClass('active');
        var body = tiendaJQ('.opc-section-body', sectionObj);
        body.hide();
    },

    openNextSection: function(setAllow){
        for (section in this.sections) {
            var nextIndex = parseInt(section)+1;
            if (this.sections[section].attr('id') == this.currentSection && this.sections[nextIndex]){
                if (setAllow) {
                    tiendaJQ(this.sections[nextIndex]).addClass('allow');
                }
                this.openSection(this.sections[nextIndex]);
                return;
            }
        }
    },

    openPrevSection: function(setAllow){
        for (section in this.sections) {
            var prevIndex = parseInt(section)-1;
            if (this.sections[section].attr('id') == this.currentSection && this.sections[prevIndex]){
                if (setAllow) {
                    tiendaJQ(this.sections[prevIndex]).addClass('allow');
                }
                this.openSection(this.sections[prevIndex]);
                return;
            }
        }
    },

    closeExistingSection: function() {
        if(this.currentSection) {
            this.closeSection(this.currentSection);
        }
    }
});
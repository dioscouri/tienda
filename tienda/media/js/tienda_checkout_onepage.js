window.addEvent("domready", function() {		
	$$('.tienda-collapse-processed').addEvent('click', function() { 
			var parent = this.getParent();
			if(parent.className == 'tienda-expanded')
			{
				parent.removeClass('tienda-expanded');
				parent.addClass('tienda-collapsed');
			}
			else
			{
				parent.removeClass('tienda-collapsed');
				parent.addClass('tienda-expanded');
			}		
		});
});

function tiendaGetPaymentOptions(container, form, msg)
{
	var url = 'index.php?option=com_tienda&view=checkout&task=updatePaymentOptions&format=raw';
    tiendaDoTask( url, container, form, msg );   
}

function tiendaSaveOnepageOrder(container, errcontainer, form)
{
	var url = 'index.php?option=com_tienda&view=checkout&task=saveOrderOnePage&format=raw';	
	var str = new Array();
    for(i=0; i<form.elements.length; i++)
    {
        postvar = {
            name : form.elements[i].name,
            value : form.elements[i].value,
            checked : form.elements[i].checked,
            id : form.elements[i].id
        };
        str[i] = postvar;
    }
     
     // execute Ajax request to server
     var a=new Ajax(url,{
         method:"post",
         data:{"elements":Json.toString(str)},
         onComplete: function(response){
             var resp=Json.evaluate(response, false);                  
                        
             if (resp.error != '1') 
             {
            	 if ($(container)) { $(container).setHTML(resp.msg); }
            	 if ($('onCheckoutCart_wrapper')) { $('onCheckoutCart_wrapper').setHTML(resp.summary); }
            	 if('tienda_btns'){ $('tienda_btns').setStyle('display', 'none'); }
            	 if('refreshpage'){ $('refreshpage').setStyle('display', 'block'); }
            	 if('validationmessage'){ $('validationmessage').setHTML('');}            	
            	 window.location = String(window.location).replace(/\#.*$/, "") + "#tienda-method";
             }
             else
             {
            	 if ($(errcontainer)) { $(errcontainer).setHTML(resp.msg); }
            	 if(resp.anchor){ window.location = String(window.location).replace(/\#.*$/, "") + resp.anchor;}
             } 
         }
     }).request();	
}

function tiendaGetFinalForm( container, form, msg )
{	
	var url = 'index.php?option=com_tienda&view=checkout&task=getRegisterForm&format=raw';
    tiendaDoTask( url, container, form, msg );  
    $('tienda-method-pane').setHTML($('hiddenregvalue').value);
}

function tiendaPopulateShippingAddress(checkbox, form)
{
	var populate = false;
    if (checkbox.checked)
    {
    	populate = true;
    	$('shippingDefaultAddress').setStyle('display', 'none');    	
    }
    else
    {
    	$('shippingDefaultAddress').setStyle('display', 'block');
    } 	
	
	var fields = "address_id;address_name;first_name;middle_name;last_name;company;address_1;address_2;city;country_id;zone_id;postal_code;phone_1;phone_2;fax";
	var fieldList = fields.split(';');

	for(var index=0;index<fieldList.length;index++){
		billingControl = document.getElementById('billing_input_'+fieldList[index])
		shippingControl = document.getElementById('shipping_input_'+fieldList[index]);
	    if(billingControl != null && shippingControl != null){
	    	if(populate)
	    	{
	    		shippingControl.value = billingControl.value;
	    	}else
	    	{
	    		shippingControl.value = '';
	    	}    	
	    }
	} 
	
	if(populate)
	{
		tiendaGetShippingRates( 'onCheckoutShipping_wrapper', form );
	}
}

function tiendaGetView(url, container, labelcont)
{		
    // execute Ajax request to server
    var a=new Ajax(url,{
        method:"post",       
        onComplete: function(response){
            var resp=Json.evaluate(response, false);                
                       
            if (resp.error != '1') 
            {
           	 if ($(container)) { $(container).setHTML(resp.msg); }
           	 if(labelcont){$(labelcont).setHTML(resp.label);}          
            }
        }
    }).request();	
}

function tiendaGetCustomerInfo(container)
{
	var url = 'index.php?option=com_tienda&view=checkout&task=getCustomerInfo&format=raw';
	tiendaGetView(url, container); 
	if('tiendaGuest'){$('tiendaGuest').value = '1';}  
	if('checkoutmethod-pane'){$('checkoutmethod-pane').setStyle('display', 'none');}  
}

function tiendaCheckoutMethodForm( container, form, msg )
{	
	var url = 'index.php?option=com_tienda&view=checkout&task=getCheckoutMethod&ajax=1&format=raw';
	tiendaGetView(url, container, 'tienda-method-pane'); 
}

function tiendaGetRegistrationForm( container, form, msg )
{	
	var url = 'index.php?option=com_tienda&view=checkout&task=getRegisterForm&format=raw';	
	tiendaGetView(url, container, 'tienda-method-pane'); 
}

function tiendaRegistrationValidate(obj, form, msg, doModal)
{	
	tiendaSetRegistrationTargetInput(obj.id); 
	
	if (doModal != false) { tiendaNewModal(msg); }
	
	var url = 'index.php?option=com_tienda&view=checkout&task=registerNewUserOnepage&format=raw';
	// loop through form elements and prepare an array of objects for passing to server	
	var str = new Array();
	
	for(i=0; i<form.elements.length; i++)
	{
		postvar = {
			name : form.elements[i].name,
			value : form.elements[i].value,
			checked : form.elements[i].checked,
			id : form.elements[i].id
		};
		str[i] = postvar;
	}
	
	// execute Ajax request to server
    var a=new Ajax(url,{
        method:"post",
		data:{"elements":Json.toString(str)},
        onComplete: function(response){
            var resp=Json.evaluate(response, false);      
          
            if(resp.error)
            {
            	if($(resp.target+'_msg'))
            	{
            		$(resp.target+'_msg').setHTML(resp.msg);    
            		$(resp.target+'_msg').className = 'tienda_message tienda_error';
            	}
            	else
            	{            		
            		var span = new Element('span', { 'id': resp.target+'_msg','class': 'tienda_message tienda_error' }); 
                    span.injectAfter($(resp.target));
                    span.setHTML(resp.msg);
            	}
            	$(resp.target).removeClass('success'); 
            	$(resp.target).addClass('error'); 
            	//set focus back
            	$(resp.target).focus();
            }
            else
            {            
            	if(resp.logged)
            	{            		
            		url = 'index.php?option=com_tienda&view=checkout&task=showCustomerInfo&format=raw';
            	    tiendaDoTask( url, 'checkoutmethod-pane', '', '', false ); 
            	    //call
            	}
            	else
            	{
            		if(obj.id != 'tienda_btn_register')
                	{
                		if($(obj.id+'_msg'))
                    	{
                    		$(obj.id+'_msg').setHTML(resp.msg);            		
                    		$(obj.id+'_msg').className = 'tienda_message tienda_success';	
                    		$(resp.target).removeClass('error');
                    		$(resp.target).addClass('success'); 
                    	}
                    	else
                    	{                		
                    		var span = new Element('span', { 'id': obj.id+'_msg','class': 'tienda_message tienda_success' }); 
                            span.injectAfter($(obj.id));
                            span.setHTML(resp.msg);
                            obj.className = 'inputbox';                 		            		
                    	}	             		
                	}  
            	}	     	           	
            }
                 
            if (doModal != false) { (function() { document.body.removeChild($('tiendaModal')); }).delay(200); }
         
            return true;
        }
    }).request();
}

function tiendaSetRegistrationTargetInput(id)
{		
	$('tienda_target').value = id;
}
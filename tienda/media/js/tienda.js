/**
 * Simple function to refresh a page.
 */
function tiendaUpdate()
{
    location.reload(true);
}

/**
 * Resets the filters in a form.
 * This should be renamed to tiendaResetFormFilters
 * 
 * @param form
 * @return
 */
function tiendaFormReset(form)
{
    // loop through form elements
    var str = new Array();
    for(i=0; i<form.elements.length; i++)
    {
        var string = form.elements[i].name;
        if (string && string.substring(0,6) == 'filter')
        {
            form.elements[i].value = '';
        }
    }
    form.submit();
}

/**
 * 
 * @param {Object} order
 * @param {Object} dir
 * @param {Object} task
 */
function tiendaGridOrdering( order, dir ) 
{
	var form = document.adminForm;
     
	form.filter_order.value     = order;
	form.filter_direction.value	= dir;

	form.submit();
}

/**
 * 
 * @param id
 * @param change
 * @return
 */
function tiendaGridOrder(id, change) 
{
	var form = document.adminForm;
	
	form.id.value= id;
	form.order_change.value	= change;
	form.task.value = 'order';
	
	form.submit();
}

/**
 * Sends form values to server for validation and outputs message returned.
 * Submits form if error flag is not set in response
 * 
 * @param {String} url for performing validation
 * @param {String} form element name
 * @param {String} task being performed
 */
function tiendaFormValidation( url, container, task, form ) 
{
    if (task == 'save' || task == 'apply' || task == 'savenew' || task == 'preparePayment' || task == 'review' || task == 'selectpayment' || task == 'addtocart' || task == 'addchildrentocart' ) 
    {
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
                if ($(container)) { $(container).setHTML(resp.msg); }
                if (resp.error != '1') 
                {
                    form.task.value = task;
                    form.submit();
                }
            }
        }).request();
    }
        else 
    {
        form.task.value = task;
        form.submit();
    }
}

/**
 * Submits form using onsubmit if present
 * @param task
 * @return
 */
function tiendaSubmitForm(task)
{
    document.adminForm.task.value = task;

    if (typeof document.adminForm.onsubmit == "function") 
    {
        document.adminForm.onsubmit();
    }
        else
    {
        document.adminForm.submit();
    }
}

/**
 * Overriding core submitbutton task to perform our onsubmit function
 * without submitting form afterwards
 * 
 * @param task
 * @return
 */
function submitbutton(task) 
{
    if (task) 
    {
        document.adminForm.task.value = task;
    }

    if (typeof document.adminForm.onsubmit == "function") 
    {
        document.adminForm.onsubmit();
    }
        else
    {
        submitform(task);
    }
}

/**
 * 
 * @param {Object} divname
 * @param {Object} spanname
 * @param {Object} showtext
 * @param {Object} hidetext
 */
function tiendaDisplayDiv (divname, spanname, showtext, hidetext) { 
	var div = document.getElementById(divname);
	var span = document.getElementById(spanname);

	if (div.style.display == "none")	{
		div.style.display = "";
		span.innerHTML = hidetext;
	} else {
		div.style.display = "none";
		span.innerHTML = showtext;
	}
}

/**
 * 
 * @param {Object} prefix
 * @param {Object} newSuffix
 */
function tiendaSwitchDisplayDiv( prefix, newSuffix )
{
	var newName = prefix + newSuffix;
	var currentSuffixDiv = document.getElementById('currentSuffix');
	var currentSuffix = currentSuffixDiv.innerHTML;	
	var oldName = prefix + currentSuffix;
	var newDiv = document.getElementById(newName);
	var oldDiv = document.getElementById(oldName);

	currentSuffixDiv.innerHTML = newSuffix;
	newDiv.style.display = "";
	oldDiv.style.display = "none";
}

function tiendaShowHideDiv(divname)
{
	var divObject = document.getElementById(divname);
	if (divObject == null){return;}
	if (divObject.style.display == "none"){
		divObject.style.display = "";
	}
	else{
		divObject.style.display = "none";
	}
}

/**
 * 
 * @param {String} url to query
 * @param {String} document element to update after execution
 * @param {String} form name (optional)
 * @param {String} msg message for the modal div (optional)
 */
function tiendaDoTask( url, container, form, msg, doModal ) 
{
	if (doModal != false) { tiendaNewModal(msg); }
	
	// if url is present, do validation
	if (url && form) 
	{	
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
                if ($(container)) { $(container).setHTML(resp.msg); }
                if (doModal != false) { (function() { document.body.removeChild($('tiendaModal')); }).delay(500); }
                return true;
            }
        }).request();
	}
		else if (url && !form) 
	{
		// execute Ajax request to server
        var a=new Ajax(url,{
            method:"post",
            onComplete: function(response){
                var resp=Json.evaluate(response, false);
                if ($(container)) { $(container).setHTML(resp.msg); }
                if (doModal != false) { (function() { document.body.removeChild($('tiendaModal')); }).delay(500); }
                return true;
        }
        }).request();			
	}
}

/**
 * 
 * @param {String} msg message for the modal div (optional)
 */
function tiendaNewModal (msg)
{
    if (typeof window.innerWidth != 'undefined') {
        var h = window.innerHeight;
        var w = window.innerWidth;
    } else {
        var h = document.documentElement.clientHeight;
        var w = document.documentElement.clientWidth;
    }
    var t = (h / 2) - 15;
    var l = (w / 2) - 15;
	var i = document.createElement('img');
	var src = window.com_tienda.jbase + 'media/com_tienda/images/ajax-loader.gif';
	i.src = src;
	i.style.position = 'absolute';
	i.style.top = t + 'px';
	i.style.left = l + 'px';
	i.style.backgroundColor = '#000000';
	i.style.zIndex = '100001';
	var d = document.createElement('div');
	d.id = 'tiendaModal';
	d.style.position = 'fixed';
	d.style.top = '0px';
	d.style.left = '0px';
	d.style.width = w + 'px';
	d.style.height = h + 'px';
	d.style.backgroundColor = '#000000';
	d.style.opacity = 0.5;
	d.style.filter = 'alpha(opacity=50)';
	d.style.zIndex = '100000';
	d.appendChild(i);
    if (msg != '' && msg != null) {
	    var m = document.createElement('div');
	    m.style.position = 'absolute';
	    m.style.width = '200px';
	    m.style.top = t + 50 + 'px';
	    m.style.left = (w / 2) - 100 + 'px';
	    m.style.textAlign = 'center';
	    m.style.zIndex = '100002';
	    m.style.fontSize = '1.2em';
	    m.style.color = '#ffffff';
	    m.innerHTML = msg;
	    d.appendChild(m);
	}
	document.body.appendChild(d);
}


/**
 * Gets the value of a selected radiolist item
 * 
 * @param radioObj
 * @return string
 */
function tiendaGetCheckedValue(radioObj) 
{
    if (!radioObj) { return ""; }
    
    var radioLength = radioObj.length;
    if (radioLength == undefined)
    {
        if(radioObj.checked)
            return radioObj.value;
        else
            return "";
    }
    
    for (var i = 0; i < radioLength; i++) 
    {
        if(radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";
}

function tiendaVerifyZone()
{
	var c = document.getElementById('country_id');
	var z = document.getElementById('zone_id');
	
	if (c != null && c != 'undefined' && c != '' && z != null && z != 'undefined' && z != '') {
		if (z.options[z.selectedIndex].value != "" && c.options[c.selectedIndex].value != "") {
			document.getElementById('task').value='addzone';
			document.adminForm.submit();
		} else {
			alert('Please select both a Country and a Zone.');
		}
	} else {
		alert('Please select both a Country and a Zone.');
	}
}


function submitTiendabutton(pressbutton, fieldname) {
	submitTiendaform(pressbutton, fieldname );
}


/**
* Submit the admin form using a custom task field name
*/
function submitTiendaform(pressbutton, fieldname){
	if (pressbutton) {
		document.adminForm.elements[fieldname].value=pressbutton;
	}
	if (typeof document.adminForm.onsubmit == "function") {
		document.adminForm.onsubmit();
	}
	document.adminForm.submit();
}

/**
 * Pauses execution for the specified milliseconds
 * @param milliseconds
 * @return
 */
function tiendaPause(milliseconds) 
{
    var dt = new Date();
    while ((new Date()) - dt <= milliseconds) { /* Do nothing */ }
}

/**
 * 
 * @param {String} url to query
 * @param {String} document element to update after execution
 * @param {String} form name (optional)
 * @param {String} msg message for the modal div (optional)
 */
function tiendaAddToCart( url, container, form, msg ) 
{
    var cartContainer = 'tiendaUserShoppingCart';
    var cartUrl = 'index.php?option=com_tienda&format=raw&view=carts&task=displayCart';

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
            if (resp.error == '1') 
            {
                if ($(container)) { $(container).setHTML(resp.msg); }
                return false;
            }
                else
            {
                tiendaPause(500);
                tiendaDoTask( cartUrl, cartContainer, '', '', false );
                return true;
            }
        }
    }).request();
}

function tiendaUpdateAddToCart( container, form )
{
    var url = 'index.php?option=com_tienda&format=raw&view=products&task=updateAddToCart';
    
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
            if ($(container)) { $(container).setHTML(resp.msg); }
            return true;
        }
    }).request();
}

function tiendaAddRelationship(container, msg) {
    var url = 'index.php?option=com_tienda&view=products&task=addRelationship&format=raw';
    tiendaDoTask( url, container, document.adminForm, msg, true );
    document.adminForm.new_relationship_productid_to.value = '';
}

function tiendaRemoveRelationship(id, container, msg) {
    var url = 'index.php?option=com_tienda&view=products&task=removeRelationship&format=raw&productrelation_id=' + id;
    tiendaDoTask( url, container, document.adminForm, msg, true );
}

function tiendaRating(id) 
{
    var count;  
    document.getElementById('productcomment_rating').value=id;
    for (count=1;count<=id;count++)
    {
        document.getElementById('rating_'+count).getElementsByTagName("img")[0].src=window.com_tienda.jbase + "media/com_tienda/images/star_10.png";
    }
    
    for (count=id+1;count<=5;count++)
    {
        document.getElementById('rating_'+count).getElementsByTagName("img")[0].src=window.com_tienda.jbase + "media/com_tienda/images/star_00.png";
    }
}

function tiendaCheckUpdateCartQuantities(form, text)
{
	
	var quantities = form.getElements('input[name^=quantities]');
	var original_quantities = form.getElements('input[name^=original_quantities]');
	
	var returned = true;
	
	quantities.each(function(item, index){
		if(item.value != original_quantities[index].value)
		{
			returned = confirm(text);
		}
	});
	
	return returned;

}
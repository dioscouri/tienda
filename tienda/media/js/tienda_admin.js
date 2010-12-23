
function tiendaUpdateParentDefaultImage(id) {
    var url = 'index.php?option=com_tienda&view=products&task=updateDefaultImage&protocol=json&product_id=' + id;
    var form = document.adminForm;
    // default_image
    // default_image_name
    
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
            $('default_image').setHTML(resp.default_image);
            $('default_image_name').setHTML(resp.default_image_name);
            return true;
        }
    }).request();
}
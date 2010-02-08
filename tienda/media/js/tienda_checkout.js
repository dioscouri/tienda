/**
 * Based on the session contents,
 * calculates the order total
 * and returns HTML
 * 
 * @return
 */
function tiendaGetPaymentForm( element, container )
{
    var url = 'index.php?option=com_tienda&controller=checkout&task=getPaymentForm&format=raw&payment_element=' + element;
    tiendaDoTask( url, container, document.adminForm );
}
/**
 * Based on the session contents,
 * calculates the order total
 * and returns HTML
 * 
 * @return
 */
function tiendaGetCheckoutTotals()
{
    var url = 'index.php?option=com_tienda&controller=checkout&task=setShippingMethod&format=raw';
    tiendaDoTask( url, 'onCheckoutCart_wrapper', document.adminForm );    
}
/**
 * Recalculates the currency amounts
 * @return
 */
function tiendaGetCurrencyTotals()
{
    var url = 'index.php?option=com_tienda&controller=checkout&task=setCurrency&format=raw';
    tiendaDoTask( url, 'onCheckoutReview_wrapper', document.adminForm );    
}


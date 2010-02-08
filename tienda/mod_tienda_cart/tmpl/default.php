<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// Grab the session cart
$cart = modTiendaCartHelper::getCart();
$num = count($cart);

$display_null = $params->get( 'display_null', '1' );
$null_text = $params->get( 'null_text', 'No Items in Your Cart' );

$mainframe =& JFactory::getApplication();
$ajax = $mainframe->getUserState( 'usercart.isAjax' );

$html = ($ajax) ? '' : '<div id="tiendaUserShoppingCart">';

// TODO Using the table orders object, this could easily display a subtotal for the items
	if ($num > 0)
	{
	    $qty = 0;
	    foreach ($cart as $item) 
	    {
	        $qty = $qty + $item->product_qty;
	    }
	    $html .= "<b>$qty</b> ".JText::_("Items"); 
	} 
	   elseif ($display_null == '1') 
	{
	    $text = JText::_( $null_text );
	    $html .= $text;
	}

    $html .= "
    <br />
    <a style='display: block; float:left' href='".JRoute::_("index.php?option=com_tienda&view=carts")."'>".JText::_("View Your Cart")."</a>
    <a style='display: block; float:right' href='".JRoute::_("index.php?option=com_tienda&view=checkout")."'>".JText::_("Checkout")."</a>
    <div class='reset'></div>";

	if ($ajax) 
	{
	    $mainframe->setUserState('usercart.isAjax', false);
	} 
	   else 
	{
	    $html .= '</div>';
	}
        
echo $html;
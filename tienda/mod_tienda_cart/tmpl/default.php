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

// Add CSS
$document->addStyleSheet( JURI::root(true).'/modules/mod_tienda_cart/tmpl/tienda_cart.css');

$html = ($ajax) ? '' : '<div id="tiendaUserShoppingCart">';

    $html .= '<span class="CartItems">';
    if ($num > 0)
    {
        $qty = 0;
        foreach ($items as $item) 
        {
            $qty = $qty + $item->orderitem_quantity;
        }
        $html .= '<span class="qty">'.$qty.'</span> ';
        $html .= ($qty == 1) ? JText::_('COM_TIENDA_ITEM') : JText::_('COM_TIENDA_ITEMS'); 
    } 
       elseif ($display_null == '1') 
    {
        $text = JText::_( $null_text );
        $html .= $text;
    }
    $html .= '</span>'; 
    $html .= '<span class="CartTotal">'.JText::_('COM_TIENDA_TOTAL').':<span>'.TiendaHelperBase::currency($orderTable->order_total).'</span> '.'</span> ';
    $html .= '<span class="CartView">';
    if ($params->get('display_lightbox') == '1')
    {
        $lightbox_attribs = array(); $lightbox['update'] = false; if ($lightbox_width = TiendaConfig::getInstance()->get( 'lightbox_width' )) { $lightbox_attribs['width'] = $lightbox_width; };
        $html .= Tienda::getClass("TiendaUrl", 'library.url')->popup( "index.php?option=com_tienda&view=carts&task=confirmAdd&tmpl=component", JText::_('View Your Cart'), $lightbox_attribs );
    }
        else
    {
        $html .= '<a id="cartLink" href="'.JRoute::_("index.php?option=com_tienda&view=carts").'">'.JText::_('View Your Cart').'</a>';
    }
    $html .= '</span>';
    $html .= '<span class="CartCheckout">'.'<a id="checkoutLink" href="'.JRoute::_("index.php?option=com_tienda&view=checkout").'">'.JText::_('Checkout').'</a>'.'</span>';
    $html .= '<div class="reset"></div>';

    if ($ajax)
    {
        $mainframe->setUserState('mod_usercart.isAjax', '0');
    } 
       else 
    {
        $html .= '</div>';
    }
        
echo $html;
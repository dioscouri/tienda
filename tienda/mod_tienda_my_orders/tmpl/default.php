<?php
/**
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( "TiendaHelperBase", 'helpers._base' );
$currency_helper = TiendaHelperBase::getInstance( 'Currency' );

// Add CSS
$document->addStyleSheet( JURI::root(true).'/modules/mod_tienda_my_orders/tmpl/mod_tienda_my_orders.css');

if (!empty($orders))
{ 
    $count=0;
    foreach (@$orders as $order) : ?>
        <div class="mod_tienda_my_orders_item">
            <?php if ($params->get('display_date')) { ?>
                <span class="mod_tienda_my_orders_item_date"><a href="<?php echo $order->link ?>"><?php echo JHTML::_('date', $order->created_date, TiendaConfig::getInstance()->get('date_format')); ?></a></span><br/>
            <?php } ?>
            <?php if ($params->get('display_amount')) { ?>
                <span class="mod_tienda_my_orders_item_amount"><b><?php echo JText::_("Amount"); ?>:</b> <?php echo $currency_helper->_($order->order_total); ?></span>
            <?php } ?>
            <?php if ($params->get('display_id')) { ?>
                <span class="mod_tienda_my_orders_item_id">(#<?php echo $order->order_id; ?>)</span><br/>
            <?php } ?>
            <?php if ($params->get('display_state')) { ?>
                <span class="mod_tienda_my_orders_item_status"><b><?php echo JText::_("Status"); ?>:</b> <?php echo JText::_( $order->order_state_name ); ?></span><br/>
            <?php } ?>
        </div>
        <?php  
    endforeach; 
}
    elseif ($display_null == '1') 
{
    echo JText::_( $null_text );
}
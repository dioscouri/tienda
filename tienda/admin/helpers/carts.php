<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaHelperCarts extends TiendaHelperBase
{
    /**
     * Fetches the name of the cart model to use
     * @return string
     */
    public function getSuffix()
    {
        $user =& JFactory::getUser();
        $suffix = ($user->guest) ? 'Sessioncarts' : 'Carts';
        return $suffix;
    }
    
    /**
     * Smartly updates the carts db table,
     * updating quantity if a product_id+product_attributes entry exists for the user
     * otherwise creating a new entry
     * 
     * @param array
     * @param boolean
     */
    function updateDbCart($cart = array(), $sync = false)
    {
        if ($sync) 
        {
            $mainframe =& JFactory::getApplication();
            $cart = $mainframe->getUserState( 'usercart.cart' );
        }
        
        if (!empty($cart)) 
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $table = JTable::getInstance( 'Carts', 'Table' );
            foreach ($cart as $item) 
            {
            	$item->user_id = (empty($item->user_id)) ? JFactory::getUser()->id : $item->user_id;
            	
            	$keynames = array();
            	$keynames['user_id'] = $item->user_id;
            	$keynames['product_id'] = $item->product_id;
            	$keynames['product_attributes'] = $item->product_attributes;
                if ($table->load($keynames)) 
                {
                    if ($sync) 
                    {
                        $table->product_qty = $item->product_qty;
                    } 
                        else 
                    {
                        $table->product_qty = $table->product_qty + $item->product_qty;                
                    }
                } 
                    else 
                {
                    $table->product_qty = $item->product_qty;
                    $table->product_id = $item->product_id;
                    $table->product_attributes = $item->product_attributes;
                    $table->user_id = $item->user_id;
                }
                $date = JFactory::getDate();
                $table->last_updated = $date->toMysql();
                $table->save();
            }
        }
        return true;
    }
    
    /**
     * Given an order_id, will remove the order's items from the user's cart
     * 
     * @param $order_id
     * @return unknown_type
     */
    function removeOrderItems( $order_id )
    {
        // load the order to get the user_id
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $cart = JTable::getInstance( 'Carts', 'Table' );
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem(); 
        if (!empty($order->order_id))
        {
            // foreach orderitem
	        foreach ($order->orderitems as $orderitem)
	        {
                // remove from user's cart
                $ids = array('user_id'=>$order->user_id, 'product_id'=>$orderitem->product_id, 'product_attributes'=>$orderitem->orderitem_attributes );
                $cart->delete( $ids );
	        }
        }
    }

    /**
     * Adjusts cart quantities based on availability
     * 
     * @return unknown_type
     */
    function fixQuantities()
    {
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
    	JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $product = JTable::getInstance( 'ProductQuantities', 'Table' );

        $suffix = strtolower( TiendaHelperCarts::getSuffix() );
        $model = JModel::getInstance( $suffix, 'TiendaModel' );
        
        switch ($suffix) 
        {
            case 'sessioncarts':
                $cart = $model->getList();
                foreach ($cart as $cartitem) 
                {
                	$product->load( array('product_id'=>$cartitem->product_id, 'vendor_id'=>'0', 'product_attributes'=>$cartitem->product_attributes));
                	if ($cartitem->product_qty > $product->quantity )
                	{
                		$cartitem->product_qty = $product->quantity;
                	}
                }
    
                // Set the session cart with the new values
                $mainframe =& JFactory::getApplication();
                $mainframe->setUserState( 'usercart.cart', $cart );
                break;
                
            case 'carts':
            default:
            	
                $cart = $model->getList();
                foreach ($cart as $cartitem) 
                {
	                $keynames = array();
	                $keynames['user_id'] = $cartitem->user_id;
	                $keynames['product_id'] = $cartitem->product_id;
	                $keynames['product_attributes'] = $cartitem->product_attributes;
	                
                	$table = JTable::getInstance( 'Carts', 'Table' );
                	$table->load($keynames);
                	$table->product_qty = $cartitem->product_qty;
                    $table->product_id = $cartitem->product_id;
                    $table->product_attributes = $cartitem->product_attributes;
                    $table->user_id = $cartitem->user_id;

                    $product->load( array('product_id'=>$cartitem->product_id, 'vendor_id'=>'0', 'product_attributes'=>$cartitem->product_attributes));
                    if ($cartitem->product_qty > $product->quantity )
                    {
                        $table->product_qty = $product->quantity;
                    }
                    
                    $table->save();
                }
                
                break;
        }
    }
}
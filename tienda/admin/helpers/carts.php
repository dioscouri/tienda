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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaHelperCarts extends TiendaHelperBase
{
    /**
     * Fetches the name of the cart model to use
     * @return string
     */
    public function getSuffix()
    {
        $user =& JFactory::getUser();
        $suffix = (empty($user->id)) ? 'Sessioncarts' : 'Carts';
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
            $table = JTable::getInstance( 'Carts', 'TiendaTable' );
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
                if (!$table->save())
                {
                    JError::raiseNotice('updateDbCart', $table->getError());
                }
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
        $cart = JTable::getInstance( 'Carts', 'TiendaTable' );
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
        $product = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );

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
	                
                	$table = JTable::getInstance( 'Carts', 'TiendaTable' );
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
    
	/**
	 * Briefly, this method "converts" the items in the cart to a order Object
	 * 
	 * @return array of OrderItem
	 */
	function getProductsInfo()
	{
		$suffix = strtolower(TiendaHelperCarts::getSuffix());
		JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
     	$model = JModel::getInstance($suffix, 'TiendaModel');
		$productcart = $model->getList();
		
		$productitems = array();
		foreach ($productcart as $product)
	    {
	        	unset($productModel);
	        	$productModel = JModel::getInstance('Products', 'TiendaModel');
	            $productModel->setId($product->product_id);
	            if ($productItem = $productModel->getItem())
	            {
	                $productItem->product_price = $productItem->price;
	                // at this point, ->product_price holds the default price for the product, 
                    // but the user may qualify for a discount based on volume or date, so let's get that price override 
                    $productItem->product_price_override = Tienda::getClass( "TiendaHelperProduct", 'helpers.product' )->getPrice( $productItem->product_id, $product->product_qty, '0', JFactory::getDate()->toMySQL() );
                    if (!empty($productItem->product_price_override))
                    {
                        $productItem->product_price = $productItem->product_price_override->product_price;
                    }
            
	            	// TODO Push this into the orders object->addItem() method?
		            $orderItem = JTable::getInstance('OrderItems', 'TiendaTable');
		            $orderItem->product_id             = $productItem->product_id;
		            $orderItem->orderitem_sku          = $productItem->product_sku;
		            $orderItem->orderitem_name         = $productItem->product_name;
		            $orderItem->orderitem_quantity     = $product->product_qty;
		            $orderItem->orderitem_price        = $productItem->product_price;
		            $orderItem->orderitem_attributes   = $product->product_attributes;
		            $orderItem->orderitem_attribute_names   = $product->attributes_names;
		            $orderItem->orderitem_attributes_price    = $product->orderitem_attributes_price;
		            $orderItem->orderitem_final_price         = $product->product_price * $orderItem->orderitem_quantity;
		            // TODO When do attributes for selected item get set during admin-side order creation?
		            array_push($productitems, $orderItem);		                       	
	            }
	    }

	    return $productitems;				
	}
}
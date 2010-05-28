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
     * TODO Remove this and all references to it
     * because all carts now use the one carts model
     * 
     * Fetches the name of the cart model to use
     * @return string
     */
    public function getSuffix()
    {
        return 'Carts';

        // TODO Remove this
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
    function updateCart($cart = array(), $sync = false)
    {
        $session =& JFactory::getSession();
        $user =& JFactory::getUser();
        
        if ($sync)
        {
            // get the cart based on session id
            JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
            $model = JModel::getInstance( 'Carts', 'TiendaModel' );
            $model->setState( 'filter_user', '0' );
            $model->setState( 'filter_session', $session->getId() );
            $cart = $model->getList();
            TiendaHelperCarts::cleanCart($session->getId());
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
            	if (empty($item->user_id)) 
            	{
            	    $keynames['session_id'] = $session->getId();
            	}
            	$keynames['product_id'] = $item->product_id;
            	$keynames['product_attributes'] = $item->product_attributes;
                if ($table->load($keynames)) 
                {
                    if ($sync) 
                    {
                        // if syncing, the quantity as set in the session takes precedence
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
                    $table->session_id = $session->getId();
                }
                $date = JFactory::getDate();
                $table->last_updated = $date->toMysql();
                if (!$table->save())
                {
                    JError::raiseNotice('updateCart', $table->getError());
                }
            }
        }
        return true;
    }
    
    /**
     * Given a session id, removes the entries from the carts db where user_id = 0
     * @return null
     */
    function cleanCart( $session_id )
    {
        $db = JFactory::getDBO();
        
        Tienda::load( 'TiendaQuery', 'library.query' );
        $query = new TiendaQuery();
        $query->delete();
        $query->from( "#__tienda_carts" );
        $query->where( "`session_id` = '$session_id' " );
        $query->where( "`user_id` = '0'" );
        $db->setQuery( (string) $query );
        
        // TODO Make this report errors and return boolean
        $db->query();
        
        return null;    
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
            case 'carts':
            default:
            	
                $cart = $model->getList();
                foreach ($cart as $cartitem) 
                {
	                $keynames = array();
	                $keynames['user_id'] = $cartitem->user_id;
	                if (empty($cartitem->user_id))
	                {
	                    $keynames['session_id'] = $cartitem->session_id;
	                }
	                $keynames['product_id'] = $cartitem->product_id;
	                $keynames['product_attributes'] = $cartitem->product_attributes;
	                
                	$table = JTable::getInstance( 'Carts', 'TiendaTable' );
                	$table->load($keynames);
                	$table->product_qty = $cartitem->product_qty;
                    $table->product_id = $cartitem->product_id;
                    $table->product_attributes = $cartitem->product_attributes;
                    $table->user_id = $cartitem->user_id;
                    $table->session_id = $cartitem->session_id;

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
     	
        $session =& JFactory::getSession();
        $user =& JFactory::getUser();        
        $model->setState('filter_user', $user->id );
        if (empty($user->id))
        {
            $model->setState('filter_session', $session->getId() );
        }
     	
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
		            $orderItem->product_id                    = $productItem->product_id;
		            $orderItem->orderitem_sku                 = $productItem->product_sku;
		            $orderItem->orderitem_name                = $productItem->product_name;
		            $orderItem->orderitem_quantity            = $product->product_qty;
		            $orderItem->orderitem_price               = $productItem->product_price;
		            $orderItem->orderitem_attributes          = $product->product_attributes;
		            $orderItem->orderitem_attribute_names     = $product->attributes_names;
		            $orderItem->orderitem_attributes_price    = $product->orderitem_attributes_price;
		            $orderItem->orderitem_final_price         = $product->product_price * $orderItem->orderitem_quantity;
		            // TODO When do attributes for selected item get set during admin-side order creation?
		            array_push($productitems, $orderItem);		                       	
	            }
	    }

	    return $productitems;				
	}
}
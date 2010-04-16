<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class TiendaHelperOrder extends TiendaHelperBase
{
    /**
     * This is a wrapper method for after an orderpayment has been received 
     * that performs acts such as: 
     * enabling file downloads, removing items from cart,
     * updating product quantities, etc
     * 
     * @param $order_id
     * @return unknown_type
     */
    function setOrderPaymentReceived( $order_id )
    {
        $errors = array();
        $error = false;
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $order_id );
        
        if (empty($order->order_id))
        {
            // TODO we must make sure this class is always instantiated
            $this->setError( JText::_( "Invalid Order ID" ) );
            return false;
        }
        
        // email the user
        $row = JTable::getInstance('OrderHistory', 'TiendaTable');
        $row->order_id = $order_id;
        $row->order_state_id = $order->order_state_id;
        $row->notify_customer = '1';
        $row->comments = JText::_( "Payment Received" );
        if (!$row->save())
        {
            $errors[] = $row->getError();
            $error = true;
        }
        
        // TODO Track errors with these (1,2,3)?
        
        // 1. Update quantities
        TiendaHelperOrder::updateProductQuantities( $order_id, '-' );
        
        // 2. remove items from cart
        JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );
        TiendaHelperCarts::removeOrderItems( $order_id );
        
        // TODO Should we log this as part of orderhistory?
        // 3. add productfiles to product downloads
        TiendaHelperOrder::enableProductDownloads( $order_id );
        
        if ($error)
        {
            $this->setError( implode( '<br/>', $errors ) );
            return false;
        }
        return true;
    }
    
    /*
     * This would cancel an order
     * and undo everything done by setOrderPaymentReceived()
     *
     * @param $order_id
     * @return unknown_type
     */
    function cancelOrder( $order_id )
    {
        return true;    
    }
    
    /**
     * After a checkout has been completed
     * and a payment has been received (instant)
     * run this method to enable product downloads
     * 
     * @param $order_id
     * @return unknown_type
     */
    function enableProductDownloads( $order_id )
    {
        $error = false;
        $errorMsg = "";
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $productsModel = JModel::getInstance( 'Products', 'TiendaModel' );
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        if ($order->orderitems)
        {
            foreach ($order->orderitems as $orderitem)
            {
                // if this orderItem product has productfiles that are enabled and only available when product is purchased
                $model = JModel::getInstance( 'ProductFiles', 'TiendaModel' );
                $model->setState( 'filter_product', $orderitem->product_id );
                $model->setState( 'filter_enabled', 1 );
                $model->setState( 'filter_purchaserequired', 1 );
                if (!$items = $model->getList())
                {
                    continue;
                }
                
                // then add them to the order as a productdownloads table object
                foreach ($items as $item)
                {
                    $productDownload = JTable::getInstance('ProductDownloads', 'TiendaTable');
                    $productDownload->product_id = $orderitem->product_id;
                    $productDownload->productfile_id = $item->productfile_id;
                    $productDownload->productdownload_max = '-1'; // TODO For now, infinite. In the future, add a field to productfiles that allows admins to limit downloads per file per purchase
                    $productDownload->order_id = $order->order_id;
                    $productDownload->user_id = $order->user_id;
                    if (!$productDownload->save())
                    {
                        // track error
                        $error = true;
                        $errorMsg .= $productDownload->getError();
                        // TODO What to do with this error 
                    }
                }
            }
        }
    }
    
	/**
	 * After a checkout has been completed
	 * and a payment has been received (instant) or scheduled (offline)
	 * run this method to update product quantities for the order
	 * 
	 * @param $order_id
	 * @return unknown_type
	 */
	function updateProductQuantities( $order_id, $delta='-' )
	{
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$productsModel = JModel::getInstance( 'Products', 'TiendaModel' );
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        if ($order->orderitems)
        {
        	foreach ($order->orderitems as $orderitem)
        	{
                // update quantities
                // TODO Update quantity based on vendor_id
                $product = JTable::getInstance('ProductQuantities', 'TiendaTable');
                $product->load( array('product_id'=>$orderitem->product_id, 'vendor_id'=>'0', 'product_attributes'=>$orderitem->orderitem_attributes));

                // Check if it has inventory enabled
                if (!$product->product_check_inventory)
                {
                	// do not update quantities
                	continue;
                }
                
                switch ($delta)
                {
                	case "+":
                		$new_quantity = $product->quantity + $orderitem->orderitem_quantity;
                		break;
                	case "-":
                	default:
                        $new_quantity = $product->quantity - $orderitem->orderitem_quantity;		
                		break;
                }
                
                // no product made infinite accidentally
                if ($new_quantity < 0)
                {
                	$new_quantity = 0;
                }
                
                $product->quantity = $new_quantity;
                $product->save();
        	}
        }
        
	}
	
    /**
     * Finds the prev & next items in a list of orders 
     *  
     * @param $id   product id
     * @return array( 'prev', 'next' )
     */
    function getSurrounding( $id )
    {
        $return = array();
        
        $prev = intval( JRequest::getVar( "prev" ) );
        $next = intval( JRequest::getVar( "next" ) );
        if ($prev || $next) 
        {
            $return["prev"] = $prev;
            $return["next"] = $next;
            return $return;
        }
        
        $app = JFactory::getApplication();
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');
        $state = array();
        
        $config = TiendaConfig::getInstance();
        
        $state['limit']     = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $state['limitstart'] = $app->getUserStateFromRequest($ns.'limitstart', 'limitstart', 0, 'int');
        $state['filter']    = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');               
        $state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.created_date', 'cmd');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word');
        $state['filter_orderstate']     = $app->getUserStateFromRequest($ns.'orderstate', 'filter_orderstate', '', '');
        $state['filter_user']         = $app->getUserStateFromRequest($ns.'user', 'filter_user', '', '');
        $state['filter_userid']         = $app->getUserStateFromRequest($ns.'userid', 'filter_userid', '', '');
        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_date_from'] = $app->getUserStateFromRequest($ns.'date_from', 'filter_date_from', '', '');
        $state['filter_date_to'] = $app->getUserStateFromRequest($ns.'date_to', 'filter_date_to', '', '');
        $state['filter_datetype']   = $app->getUserStateFromRequest($ns.'datetype', 'filter_datetype', '', '');
        $state['filter_total_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_total_from', '', '');
        $state['filter_total_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_total_to', '', '');
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        $rowset = $model->getList();
            
        $found = false;
        $prev_id = '';
        $next_id = '';

        for ($i=0; $i < count($rowset) && empty($found); $i++) 
        {
            $row = $rowset[$i];     
            if ($row->order_id == $id) 
            { 
                $found = true; 
                $prev_num = $i - 1;
                $next_num = $i + 1;
                if (isset($rowset[$prev_num]->order_id)) { $prev_id = $rowset[$prev_num]->order_id; }
                if (isset($rowset[$next_num]->order_id)) { $next_id = $rowset[$next_num]->order_id; }
    
            }
        }
        
        $return["prev"] = $prev_id;
        $return["next"] = $next_id; 
        return $return;
    }
    
    /**
	 * Returns a JParameter Formatted string representing the currency
	 * 
	 * @param $currency_id currency_id
	 * @return $string JParameter formatted string 
	 */
    
    function currencyToParameters($currency_id){
    	
    	if(!is_numeric($currency_id))
    		return false;
    	
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
    	$model = &JModel::getInstance('Currencies', 'TiendaModel' );
    	$table = $model->getTable(); 
		
    	// Load the currency
    	if(!$table->load($currency_id))
    		return false;
    		    	
    	// Convert this into a JParameter formatted string
    	// a bit rough, but works smoothly and is extensible (works even if you add another parameter to the curremcy table
    	$currency_parameters = $table;
    	unset($table);
    	unset($currency_parameters->currency_id);
    	unset($currency_parameters->created_date);
    	unset($currency_parameters->modified_date);
    	unset($currency_parameters->currency_enabled);
    	
    	$param = new JParameter('');
    	$param->bind($currency_parameters);
    	
    	return $param->toString();
    }
}
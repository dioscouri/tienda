<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );

Tienda::load( "TiendaHelperBase", 'helpers._base' );

class TiendaHelperSQL extends TiendaHelperBase 
{
    /**
     * Processes a new order
     * 
     * @param $order_id
     * @return unknown_type
     */
    function processOrder( $order_id ) 
    {
        // get the order
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        
        $orderTable = $model->getTable();
        $orderTable->load( $order_id );
        
        $this->_order = $orderTable;
				if( $order->user_id < Tienda::getGuestIdStart() )
					$this->_user = $order->user_id;
				else
	        $this->_user = JFactory::getUser( $order->user_id );
        $this->_date = JFactory::getDate();
        
        // find the products in the order that are integrated 
        foreach ($order->orderitems as $orderitem)
        {
            $model = JModel::getInstance( 'Products', 'TiendaModel' );
            $product = $model->getTable();
            $product->load( $orderitem->product_id );

            $this->_product = $product;
            $this->_orderitem = $orderitem;
            
            if (!empty($product->product_sql))
            {
                $this->processSQL($product->product_sql);
            }
        }
    }
    
    /**
     * This method will convert the tags in the SQL string
     * and execute it
     * 
     * @param $sql
     * @return unknown_type
     */
    function processSQL( $sql )
    {
        $regex = "#{orderitem.(.*?)}#s";
        $sql = preg_replace_callback( $regex, array($this, 'orderitem'), $sql );
        
        $regex = "#{order.(.*?)}#s";
        $sql = preg_replace_callback( $regex, array($this, 'order'), $sql );
        
        $regex = "#{user.(.*?)}#s";
        $sql = preg_replace_callback( $regex, array($this, 'user'), $sql );
        
        $regex = "#{product.(.*?)}#s";
        $sql = preg_replace_callback( $regex, array($this, 'product'), $sql );
        
        $regex = "#{date.(.*?)}#s";
        $sql = preg_replace_callback( $regex, array($this, 'date'), $sql );
        
        $regex = "#{request.(.*?)}#s";
        $sql = preg_replace_callback( $regex, array($this, 'request'), $sql );
        
        if (trim($sql)) 
        {
            $db = JFactory::getDBO();
            $db->setQuery($sql);
            if (!$db->query())
            {
                // TODO log error
                //JFactory::getApplication()->enqueueMessage($db->getErrorMsg(), 'notice');
            }            
        }
    }
    
    /**
     * Process the order object strings
     * 
     * @param $match
     * @return unknown_type
     */
    function order( $match )
    {
        // regex returns this array:
        // $match[0] = {order.order_id}
        // $match[1] = order_id       

        $key = $match[1];

        if (isset($this->_order->$key))
        {
            $return = $this->_order->$key; 
        }
            else
        {
            $return = "{order.$key}";
        }
        
        return $return;
    }
    
    /**
     * Process the user object strings
     * 
     * @param $match
     * @return unknown_type
     */
    function user( $match )
    {
        // regex returns this array:
        // $match[0] = {user.id}
        // $match[1] = id       

        $key = $match[1];

        if (isset($this->_user->$key))
        {
            $return = $this->_user->$key; 
        }
            else
        {
            $return = "{user.$key}";
        }
        
        return $return;
    }
    
    /**
     * Process the product object strings
     * 
     * @param $match
     * @return unknown_type
     */
    function product( $match )
    {
        $key = $match[1];

        if (isset($this->_product->$key))
        {
            $return = $this->_product->$key; 
        }
            else
        {
            $return = "{product.$key}";
        }
        
        return $return;
    }
    
    /**
     * Process the orderitem object strings
     * 
     * @param $match
     * @return unknown_type
     */
    function orderitem( $match )
    {
        $key = $match[1];

        if (isset($this->_orderitem->$key))
        {
            $return = $this->_orderitem->$key; 
        }
            else
        {
            $return = "{orderitem.$key}";
        }
        
        return $return;
    }
    
    /**
     * Process the date strings
     * 
     * @param $match
     * @return unknown_type
     */
    function date( $match )
    {
        $key = $match[1];

        if (strpos($key, '(') && method_exists($this->_date, substr( $key, 0, -2 ) ))
        {
            $key = substr( $key, 0, -2 );
            $return = $this->_date->$key(); 
        }
            elseif (isset($this->_date->$key))
        {
            $return = $this->_date->$key; 
        }
            else
        {
            $return = "{date.$key}";
        }
        
        return $return;
    }
    
    /**
     * Process the request strings
     * 
     * @param $match
     * @return unknown_type
     */
    function request( $match )
    {
        $key = $match[1];

        $return = JRequest::getVar( $key );
        
        return $return;
    }
}
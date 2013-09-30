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

if ( !class_exists('Tienda') ) {
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
}

Tienda::load( "TiendaHelperSql", 'helpers.sql' );

class TiendaHelperHead extends TiendaHelperSQL 
{
    public function injectIntoHead( $string )
    {
        $doc = JFactory::getDocument();
        
        if (empty($doc->_custom) || !is_array($doc->_custom))
        {
            $custom = array();
        }
        else
        {
            $custom = $doc->_custom;
        }
        
        $custom[] = $string;
        $doc->_custom = $custom; 
    }
    
    /**
     * Processes a new order
     *
     * @param $order_id
     * @return unknown_type
     */
    public function processStringForOrder( $order_id, &$string )
    {
        // get the order
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        $this->_orderFromModel = $order;
        
        $orderTable = $model->getTable();
        $orderTable->load( $order_id );
        $this->_order = $orderTable;
        
        $this->_date = JFactory::getDate();
                
        if ( $order->user_id < Tienda::getGuestIdStart() ) {
            $this->_user = $order->user_id;
        } else {
            $this->_user = JFactory::getUser( $order->user_id );
        }
        
        $this->products_model = Tienda::getClass('TiendaModelProducts', 'models.products');
        
        return $this->processString( $string );
    }
        
    /**
     * This method will convert the tags in the string
     * 
     * @param $string
     * @return unknown_type
     */
    protected function processString( &$string )
    {
        JPluginHelper::importPlugin( 'tienda' );
        $dispatcher = JDispatcher::getInstance();
        
        $dispatcher->trigger( 'onTiendaBeforeProcessHeadString', array( &$string ) );
        
        $regex = "#{confirmation.(.*?)}#s";
        $string = preg_replace_callback( $regex, array($this, 'confirmation'), $string );
        
        $regex = "#{order.(.*?)}#s";
        $string = preg_replace_callback( $regex, array($this, 'order'), $string );
        
        $regex = "#{user.(.*?)}#s";
        $string = preg_replace_callback( $regex, array($this, 'user'), $string );
        
        $regex = "#{date.(.*?)}#s";
        $string = preg_replace_callback( $regex, array($this, 'date'), $string );
        
        $regex = "#{request.(.*?)}#s";
        $string = preg_replace_callback( $regex, array($this, 'request'), $string );
        
        $dispatcher->trigger( 'onTiendaAfterProcessHeadString', array( &$string ) );
        
        return $string;
    }
    
    /**
     * Process the confirmation object strings
     *
     * @param $match
     * @return unknown_type
     */
    protected function confirmation( $match )
    {
        // regex returns this array:
        // $match[0] = {confirmation.orderitems}
        // $match[1] = orderitems
        
        $return = null;
        
        if (empty($match[1])) {
            return $return;
        }        
        
        $key = strtolower($match[1]);
        switch($key) 
        {
            case "orderitems":
                // All of the items in the order in a single string. 
                // Each item is separated by a double-pipe (||), 
                // and the properties of each orderitem are separated by a single-pipe (|). 
                // The properties are (in this order):
                // Product SKU | Product Name | Product Category | Orderitem Price | Orderitem Quantity
                $orderitems_string = null;
                if (!empty($this->_orderFromModel->orderitems)) 
                {
                    $items = array();
                    foreach ($this->_orderFromModel->orderitems as $orderitem)
                    {
                        $string = $orderitem->product_id;
                        if (!empty($orderitem->orderitem_sku)) {
                            $string .= " SKU " . $orderitem->orderitem_sku;
                        } 
                        $string .= "|";
                        
                        $string .= str_replace("|", " - ", $orderitem->orderitem_name );
                        if (!empty($orderitem->orderitem_attribute_names)) {
                            $string .= ", " . str_replace("|", " - ", $orderitem->orderitem_attribute_names );
                        }
                        $string .= "|";

                        $categories_string = null;
                        if ($categories = $this->products_model->getCategories($orderitem->product_id)) 
                        {
                            $cat_items = array();
                            foreach ($categories as $category) 
                            {
                                $cat_items[] = str_replace("|", " - ", $category->category_name);
                            }
                            $categories_string = implode(", ", $cat_items);
                        }
                        $string .= $categories_string . "|";
                        
                        $string .= ($orderitem->orderitem_price + $orderitem->orderitem_attributes_price) . "|";
                        $string .= $orderitem->orderitem_quantity;
                        
                        $items[] = $string;
                    }
                    $orderitems_string = implode("||", $items);
                }
                $return = $orderitems_string;
                break;
            case "subtotal":
                $return = (isset($this->_order->order_subtotal)) ? $this->_order->order_subtotal : null;
                break;
            case "total":
                $return = (isset($this->_order->order_total)) ? $this->_order->order_total : null;
                break;
            case "tax":
                $return = (isset($this->_order->order_tax)) ? $this->_order->order_tax : null;
                break;
            case "shipping":
                $return = (isset($this->_order->order_shipping)) ? $this->_order->order_shipping : null;
                break;
            case "ordernumber":
                $return = (isset($this->_order->order_id)) ? $this->_order->order_id : null;
                break;
        }
    
        return $return;
    }    
}
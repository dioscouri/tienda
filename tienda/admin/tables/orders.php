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
defined( '_JEXEC' ) or die( 'Restricted access' );

JLoader::import( 'com_tienda.tables._base', JPATH_ADMINISTRATOR.DS.'components' );

class TableOrders extends TiendaTable
{	
    /** @var array An array of TiendaOrderItems objects */
    protected $_items = array();

    /** @var array An array of vendor_ids */
    protected $_vendors = array();
    
    /** @var object An TiendaAddresses() object for billing */
    protected $_billing_address = null;

    /** @var object An TiendaAddresses() object for shipping */
    protected $_shipping_address = null;
    
    /** @var int tax & shipping geozone_id values */
    protected $_billing_geozone = null;
    protected $_shipping_geozone = null;
    
    /** @var object The shipping totals JObject */
    protected $_shipping_total = null;
    
    /** @var boolean Has the recurring item been added to the order? 
     * This is used exclusively during orderTotal calculation
     */
    protected $_recurringItemExists = false;
    
	/**
	 * @param $db
	 * @return unknown_type
	 */
	function TableOrders ( &$db )
	{
		$tbl_key 	= 'order_id';
		$tbl_suffix = 'orders';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}

	/**
	 * Loads the Order object with values from the DB tables
	 * (non-PHPdoc)
	 * @see tienda/admin/tables/TiendaTable#load($oid, $reset)
	 */
    function load( $oid=null, $reset=true )
    {
    	if ($return = parent::load($oid, $reset))
    	{
    		// TODO populate the protected vars with the info from the db
    	}
    	return $return;
    }
	
	/**
	 * Ensures integrity of the table object before storing to db
	 * 
	 * @return unknown_type
	 */
	function check()
	{
		$nullDate	= $this->_db->getNullDate();

		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}
		
		if (empty($this->modified_date) || $this->modified_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->modified_date = $date->toMysql();
		}
		return true;
	}

    /**
     * Saves the order to the db table
     * 
     * (non-PHPdoc)
     * @see tienda/admin/tables/TiendaTable#save()
     */	
	function save()
	{
        if ($return = parent::save())
        {
            // TODO All of the protected vars information could be saved here instead, no?	
        }
        return $return;
	}
	
    /**
     * Sets the order's billing or shipping address
     * 
     * @param $type     string      billing | shipping
     * @param $address  object      TiendaAddresses() object
     * @return object
     */
    function setAddress( $address, $type='both'  )
    {
        switch (strtolower($type))
        {
            case "billing":
                $this->_billing_address = $address;
                break;
            case "shipping":
                $this->_shipping_address = $address;
                break;
            case "both":
            default:
                $this->_shipping_address = $address;
                $this->_billing_address = $address;
                break;
        }
        $this->setGeozones();
    }
    
    /**
     * Gets the order billing address
     * @return unknown_type
     */
    function getBillingAddress()
    {
    	// TODO If $this->_billing_address is null, attempt to populate it with the orderinfo fields, or using the billing_address_id (if present)  
    	return $this->_billing_address;
    }
    
    /**
     * Gets the order shipping address
     * @return unknown_type
     */
    function getShippingAddress()
    {
        // TODO If $this->_shipping_address is null, attempt to populate it with the orderinfo fields, or using the shipping_address_id (if present)
        return $this->_shipping_address;
    }

    /**
     * Based on the object's addresses,
     * sets the shipping and billing geozones
     * 
     * @return unknown_type
     */
    function setGeozones()
    {
        JLoader::import( 'com_tienda.helpers.shipping', JPATH_ADMINISTRATOR.DS.'components' );
        if (!empty($this->_billing_address))
        {
           $this->_billing_geozone = TiendaHelperShipping::getGeoZone( $this->_billing_address->zone_id, '1' );	
        }
        if (!empty($this->_shipping_address))
        {
            $this->_shipping_geozone = TiendaHelperShipping::getGeoZone( $this->_shipping_address->zone_id, '2' );   
        }
    }
    
    /**
     * Gets the order's tax geozone
     * 
     * @return unknown_type
     */
    function getBillingGeoZone()
    {
        return $this->_billing_geozone;
    }
    
    /**
     * Gets the order's shipping geozone
     * 
     * @return unknown_type
     */
    function getShippingGeoZone()
    {
    	return $this->_shipping_geozone;
    }
    
    /**
     * Adds an item to the order object
     * $item can be a named array with a minimum of 'product_id' and 'orderitem_quantity' and 'orderitem_attributes' (as CSV of productattributeoptions_ids)
     * $item can be an object with minimum of 'product_id' and 'orderitem_quantity' and 'orderitem_attributes' properties
     * $item can be a 'product_id' string
     * 
     * $this->_items['product_id'] = TableOrderItems() object;
     * 
     * @param object    $item   TableOrderItem object
     * @return void
     */
    function addItem( $item )
    {
        $orderItem = JTable::getInstance('OrderItems', 'Table');
        if (is_array($item))
        {
            $orderItem->bind( $item );
        }
        elseif (is_object($item) && is_a($item, 'TableOrderItems'))
        {
            $orderItem = $item;
        }
        elseif (is_object($item))
        {
            $orderItem->product_id = @$item->product_id;
            $orderItem->orderitem_quantity = @$item->orderitem_quantity;
            $orderItem->vendor_id  = @$item->vendor_id;
            $orderItem->orderitem_attributes = @$item->orderitem_attributes;
        }
        else
        {
            $orderItem->product_id = $item;
            $orderItem->orderitem_quantity = '1';
            $orderItem->vendor_id  = '0';
            $orderItem->orderitem_attributes = '';
        }
        
        // check whether/not the item recurs
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Products', 'TiendaModel' );
        $model->setId( $orderItem->product_id );
        $product = $model->getItem();
        if ($product->product_recurs)
        {
        	// flag the order as recurring
            $this->order_recurs = true;
            // set the orderitem's recurring product values
            $orderItem->orderitem_recurs            = $product->product_recurs;
            $orderItem->recurring_amount            = ""; // just the product_price? 
            $orderItem->recurring_payments          = $product->recurring_payments;
            $orderItem->recurring_period_interval   = $product->recurring_period_interval;
            $orderItem->recurring_period_unit       = $product->recurring_period_unit;
            $orderItem->recurring_trial             = $product->recurring_trial;
            $orderItem->recurring_trial_period_interval = $product->recurring_trial_period_interval;
            $orderItem->recurring_trial_period_unit = $product->recurring_trial_period_unit;
            $orderItem->recurring_trial_price       = $product->recurring_trial_price;
        }
        
        // Use hash to separate items when customer is buying the same product from multiple vendors
        // and with different attribs
        $hash = intval($orderItem->product_id).".".intval($orderItem->vendor_id).".".$orderItem->orderitem_attributes;
        if (!empty($this->_items[$hash]))
        {
            // merely update quantity if item already in list
            $this->_items[$hash]->orderitem_quantity += $orderItem->orderitem_quantity;
        }
            else
        {
            $this->_items[$hash] = $orderItem; 
        }
        
        // add the vendor to the order
        $this->addVendor( $orderItem );
    }
    
    /**
     * Adds a vendor to the order based on the properties of the item being added
     * 
     * @param object    $orderItem      a TableItems object
     * @return void
     */
    function addVendor( $orderItem )
    {   
        // if this product is from a vendor other than store owner, track it
        if (!empty($orderItem->vendor_id) && empty($this->_vendors[$orderItem->vendor_id]))
        {
        	$orderVendor = JTable::getInstance('OrderVendors', 'Table');
        	$orderVendor->vendor_id = $orderItem->vendor_id;
            $this->_vendors[$orderItem->vendor_id] = $orderVendor;
        }
    	
        if (!empty($this->_vendors[$orderItem->vendor_id]))
        {
        	// TODO update the order vendor's totals?
        	// or just wait until the end, as done with the meta order's totals?
        }
    }
    
    /**
     * Gets the order items
     * 
     * @return array of TableOrderItems objects
     */
    function getItems()
    {
    	return $this->_items;
    }

    /**
     * Gets the order vendors
     * 
     * @return array of TableOrderVendors objects
     */
    function getVendors()
    {
        return $this->_vendors;
    }
    
    /**
     * Based on the items and addresses in the object, 
     * calculates the totals
     * 
     * @return void
     */
    function calculateTotals()
    {
    	$items = &$this->_items;
        $subtotal = 0.00;
        $tax = 0.00;
        $shipping_total = 0.00;
        $recurring_total = 0.00;
		
        // calculate product subtotal and taxes
        JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
        if (isset($items) && count($items) >  0)
        {
            foreach ($items as $key=>$item)
            {
            	$addItemToOrder = true;
                // calculate taxes here, based on $this->_billing_geozone
                // and update the item's product_tax value
                $product_tax_rate = 0;
                $product_tax_rate = TiendaHelperProduct::getTaxRate($item->product_id, $this->getBillingGeoZone() );
                $item->orderitem_tax = ($product_tax_rate/100) * $item->orderitem_final_price;
                
                if (empty($this->_recurringItemExists) && $item->orderitem_recurs)
                {
	                // Only one recurring item allowed per order. 
	                // If the item is recurring, 
	                // check if there already is a recurring item accounted for in the order
	                // if so, remove this one from the order but leave it in the cart and continue
	                // if not, add its properties 
	                $this->_recurringItemExists = true;
	                
		            $this->recurring_payments          = $item->recurring_payments;
		            $this->recurring_period_interval   = $item->recurring_period_interval;
		            $this->recurring_period_unit       = $item->recurring_period_unit;
		            $this->recurring_trial             = $item->recurring_trial;
		            $this->recurring_trial_period_interval = $item->recurring_trial_period_interval;
		            $this->recurring_trial_period_unit = $item->recurring_trial_period_unit;
		            $this->recurring_trial_price       = $item->recurring_trial_price;
                    $this->recurring_amount            = $item->recurring_amount; // TODO Add tax?
                }
                    elseif (!empty($this->_recurringItemExists) && $item->orderitem_recurs)
                {
                    // Only one recurring item allowed per order. 
                    // If the item is recurring, 
                    // check if there already is a recurring item accounted for in the order
                    // if so, remove this one from the order but leave it in the cart and continue
                    unset($items[$key]);
                    $addItemToOrder = false;
                }
                
                if ($addItemToOrder)
                {
	                // track the running totals
	                $subtotal += $item->orderitem_final_price;
	                $tax += $item->orderitem_tax;
                }
            }
        }

        // calculate shipping total by passing entire items array to helper
        JLoader::import( 'com_tienda.helpers.shipping', JPATH_ADMINISTRATOR.DS.'components' );
        $shipping_total = TiendaHelperShipping::getTotal( $this->shipping_method_id, $this->getShippingGeoZone(), $items );
        $this->_shipping_total = $shipping_total;    
        
        // sum totals
        $total = $subtotal + $tax + $shipping_total->shipping_rate_price + $shipping_total->shipping_rate_handling + $shipping_total->shipping_tax_total;
        
        // set object properties
        $this->order_subtotal   = $subtotal;
        $this->order_shipping   = $shipping_total->shipping_rate_price + $shipping_total->shipping_rate_handling;
        $this->order_shipping_tax = $shipping_total->shipping_tax_total;
        $this->order_tax        = $tax;
        $this->order_total      = $total;
    }
    
    /**
     * 
     * @return unknown_type
     */
    function calculateVendorTotals()
    {
    	if (empty($this->_vendors))
    	{
    		return null;
    	}
    	
        $items = $this->_items;
        $subtotal = 0.00;
        $tax = 0.00;

        // calculate product subtotal and taxes
        // calculate shipping total
        JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
        JLoader::import( 'com_tienda.helpers.shipping', JPATH_ADMINISTRATOR.DS.'components' );
        if (isset($items) && count($items) >  0)
        {
            foreach ($items as $item)
            {
            	if (!empty($item->vendor_id))
            	{
	                // orderitem calculations should have already been completed, so just sum the values for each vendor
	                $this->_vendors[$item->vendor_id]->ordervendor_total       += $item->orderitem_final_price + $item->orderitem_tax;
	                $this->_vendors[$item->vendor_id]->ordervendor_subtotal    += $item->orderitem_final_price;
	                $this->_vendors[$item->vendor_id]->ordervendor_tax         += $item->orderitem_tax;
	                // if the shipping method is NOT per-order, calculate the per-item shipping cost
	                if (!empty($this->shipping_method_id) && $this->shipping_method_id != '2')
	                {
	                    $shipping_total = TiendaHelperShipping::getTotal( $this->shipping_method_id, $this->getShippingGeoZone(), $item->product_id );
	                    $this->_vendors[$item->vendor_id]->ordervendor_shipping     += $shipping_total->shipping_rate_price + $shipping_total->shipping_rate_handling;
	                    $this->_vendors[$item->vendor_id]->ordervendor_shipping_tax += $shipping_total->shipping_tax_total;
	                }
            	}
            }
            // at this point, each vendor's TableOrderVendor object is populated
        }    	
    }
    
    /**
     * Gets the order's shipping total object
     * 
     * @return object
     */
    function getShippingTotal()
    {
    	return $this->_shipping_total;
    }
    
    /**
     * Gets the order number
     * @return unknown_type
     */
    function getOrderNumber()
    {
    	return $this->_order_number;
    }
    
    /**
     * Generates an order number based on the order's properties
     * 
     * @return unknown_type
     */
    function generateOrderNumber()
    {
        $nullDate   = $this->_db->getNullDate();
        if (empty($this->created_date) || $this->created_date == $nullDate)
        {
            $date = JFactory::getDate();
            $this->created_date = $date->toMysql();
        }
        $order_date = JHTML::_('date', $this->created_date, '%Y%m%d');
        $order_time = JHTML::_('date', $this->created_date, '%H%M%S');
        $user_id = $this->user_id;
        $this->_order_number = $order_date.'-'.$order_time.'-'.$user_id;
        return $this->_order_number;
    }
}

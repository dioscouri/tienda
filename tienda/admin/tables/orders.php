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

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableOrders extends TiendaTable
{	
    /** @var array An array of TiendaTableOrderItems objects */
    protected $_items = array();

    /** @var array An array of TiendaTableProductDownloads objects */
    protected $_downloads = array();
    
    /** @var array An array of vendor_ids */
    protected $_vendors = array();
    
    /** @var object An TiendaAddresses() object for billing */
    protected $_billing_address = null;

    /** @var object An TiendaAddresses() object for shipping */
    protected $_shipping_address = null;
    
    /** @var array      tax & shipping geozone objects */
    protected $_billing_geozones = array();
    protected $_shipping_geozones = array();
    
    /** @var array      The shipping totals JObjects */
    protected $_shipping_totals = array();
    
    /** @var boolean Has the recurring item been added to the order? 
     * This is used exclusively during orderTotal calculation
     */
    protected $_recurringItemExists = false;

    /** @var array An array of TiendaTableTaxRates objects (the unique taxrates for this order) */
    protected $_taxrates = array();
    
    /** @var array An array of tax amounts, indexed by tax_rate_id */
    protected $_taxrate_amounts = array();
    
    /** @var array An array of TiendaTableTaxRates objects (the unique taxclasses for this order) */
    protected $_taxclasses = array();
    
    /** @var array An array of tax amounts, indexed by tax_class_id */
    protected $_taxclass_amounts = array();
    
	/**
	 * @param $db
	 * @return unknown_type
	 */
	function TiendaTableOrders ( &$db )
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
            // create the order_number when the order is saved, since it is based on the auto-inc value
            $order_number_prefix = TiendaConfig::getInstance()->get('order_number_prefix');
            if (!empty($order_number_prefix) && empty($this->order_number) && !empty($this->order_id))
            {
                $this->order_number = $order_number_prefix.$this->order_id;
                $this->store();
            }
            
            // TODO All of the protected vars information could be saved here instead, no?	
        }
        return $return;
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
        $orderItem = JTable::getInstance('OrderItems', 'TiendaTable');
        if (is_array($item))
        {
            $orderItem->bind( $item );
        }
        elseif (is_object($item) && is_a($item, 'TiendaTableOrderItems'))
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
        
        // add productdownloads records to the order
        // not necessary yet 
        // $this->addDownloads( $orderItem );
    }

    /**
     * Adds product downloads records to the order 
     * based on the properties of the item being added
     * 
     * @param object    $orderItem      a TableItems object
     * @return void
     */
    function addDownloads( $orderItem )
    {
        // if this orderItem product has productfiles that are enabled and only available when product is purchased
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductFiles', 'TiendaModel' );
        $model->setState( 'filter_product', $orderItem->product_id );
        $model->setState( 'filter_enabled', 1 );
        $model->setState( 'filter_purchaserequired', 1 );
        if (!$items = $model->getList())
        {
            // TODO Is there any need to return anything here?
            return;
        }
        
        // then add them to the order as a productdownloads table object
        foreach ($items as $item)
        {
            $productDownload = JTable::getInstance('ProductDownloads', 'TiendaTable');
            $productDownload->product_id = $orderItem->product_id;
            $productDownload->productfile_id = $item->productfile_id;
            $productDownload->productdownload_max = '-1'; // TODO For now, infinite. In the future, add a field to productfiles that allows admins to limit downloads per file per purchase
            // in the order object, download is identified by the productfile_id
            $this->_downloads[$item->productfile_id] = $productDownload; 
        }
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
        	$orderVendor = JTable::getInstance('OrderVendors', 'TiendaTable');
        	$orderVendor->vendor_id = $orderItem->vendor_id;
            $this->_vendors[$orderItem->vendor_id] = $orderVendor;
        }
    	
        if (!empty($this->_vendors[$orderItem->vendor_id]))
        {
        	// TODO update the order vendor's totals?
        	// or just wait until the calculateTotals() method is executed?
        }
    }
    
    /**
     * Based on the items and addresses in the object, 
     * calculates the totals
     * 
     * @return void
     */
    function calculateTotals()
    {
    	$this->calculateProductTotals(); // aka subtotal
    	$this->calculateTaxTotals();
    	$this->calculateShippingTotals();
    	$this->calculateVendorTotals();
        
        // sum totals
        $total = 
            $this->order_subtotal 
            + $this->order_tax 
            + $this->order_shipping 
            + $this->order_shipping_tax
            ;
        
        // set object properties
        $this->order_total      = $total;
        
        // We fire just a single plugin event here and pass the entire order object
        // so the plugins can override whatever they need to
        $dispatcher    =& JDispatcher::getInstance();
        $dispatcher->trigger( "onCalculateOrderTotals", array( $this ) );
    }

    /**
     * Calculates the product total (aka subtotal) 
     * using the array of items in the order object
     * 
     * @return unknown_type
     */
    function calculateProductTotals()
    {
        $subtotal = 0.00;
        
        // TODO Must decide what we want these methods to return; for now, null
        $items = &$this->getItems();
        if (!is_array($items))
        {
            $this->order_subtotal = $subtotal;
            return;
        }
        
        // calculate product subtotal
        foreach ($items as $item)
        {
            // track the subtotal
            $subtotal += $item->orderitem_final_price;
        }

        // set object properties
        $this->order_subtotal   = $subtotal;
        
        // Allow this to be modified via plugins
        $dispatcher    =& JDispatcher::getInstance();
        $dispatcher->trigger( "onCalculateProductTotals", array( $this ) );
    }

    /**
     * Calculates the tax totals for the order
     * using the array of items in the order object
     * 
     * @return unknown_type
     */
    function calculateTaxTotals()
    {
        $tax_total = 0.00;
        
        $items =& $this->getItems();
        if (!is_array($items))
        {
            $this->order_tax = $tax_total;
            return;
        }
        
        $geozones = $this->getBillingGeoZones();
        Tienda::load( "TiendaHelperProduct", 'helpers.product' );
        foreach ($items as $key=>$item)
        {
            $orderitem_tax = 0;
            
            // For each item in $this->getBillingGeoZone, calculate the tax total
            // and update the item's tax value
            foreach ($geozones as $geozone)
            {
                $geozone_id = $geozone->geozone_id;
                $taxrate = TiendaHelperProduct::getTaxRate($item->product_id, $geozone_id, true );
                $product_tax_rate = $taxrate->tax_rate;
                
                // add this as one of the taxrates applicable to this order
                if (!empty($taxrate->tax_rate_id) && empty($this->_taxrates[$taxrate->tax_rate_id]))
                {
                    $this->_taxrates[$taxrate->tax_rate_id] = $taxrate;    
                }
                
                // track the total amount of tax applied to this order for this taxrate
                if (!empty($taxrate->tax_rate_id) && empty($this->_taxrate_amounts[$taxrate->tax_rate_id]))
                {
                    $this->_taxrate_amounts[$taxrate->tax_rate_id] = 0;    
                }
                if (!empty($taxrate->tax_rate_id))
                {
                    $this->_taxrate_amounts[$taxrate->tax_rate_id] += ($product_tax_rate/100) * $item->orderitem_final_price;    
                }                

                // add this as one of the taxclasses applicable to this order
                if (!empty($taxrate->tax_class_id) && empty($this->_taxclasses[$taxrate->tax_class_id]))
                {
                    $this->_taxclasses[$taxrate->tax_class_id] = $taxrate;    
                }
                
                // track the total amount of tax applied to this order for this taxclass
                if (!empty($taxrate->tax_class_id) && empty($this->_taxclass_amounts[$taxrate->tax_class_id]))
                {
                    $this->_taxclass_amounts[$taxrate->tax_class_id] = 0;    
                }
                
                if (!empty($taxrate->tax_class_id))
                {
                    $this->_taxclass_amounts[$taxrate->tax_class_id] += ($product_tax_rate/100) * $item->orderitem_final_price;                    
                }
                
                // track the total tax for this item
                $orderitem_tax += ($product_tax_rate/100) * $item->orderitem_final_price;
            }
            $item->orderitem_tax = $orderitem_tax;
            
            // track the running total
            $tax_total += $item->orderitem_tax;
        }        
        $this->order_tax = $tax_total;
        
        // some locations may want taxes calculated on shippingGeoZone, so
        // Allow this to be modified via plugins
        $dispatcher    =& JDispatcher::getInstance();
        $dispatcher->trigger( "onCalculateTaxTotals", array( $this ) );
    }
    
    /**
     * Calculates the shipping totals for the order 
     * using the array of items in the order object
     * 
     * @return unknown_type
     */
    function calculateShippingTotals()
    {
        $order_shipping     = 0.00;
        $order_shipping_tax = 0.00;
        
        $items =& $this->getItems();
        if (!is_array($items) || !$this->shipping)
        {
            $this->order_shipping       = $order_shipping;
            $this->order_shipping_tax   = $order_shipping_tax;
            return;
        }


        // This support multiple shipping geozones
        // For each item in $this->getShippingGeoZones, calculate the shipping total
        // and store the object for later user
        $shipping_totals = array();       
            
        /*
        $geozones = $this->getShippingGeoZones();
        foreach ($geozones as $geozone)
        {
            $geozone_id = $geozone->geozone_id;
            // calculate shipping total by passing entire items array to helper    
            Tienda::load( 'TiendaHelperShipping', 'helpers.shipping' );
            $shipping_total = TiendaHelperShipping::getTotal( $this->shipping_method_id, $geozone_id, $items );
            
			$shipping_total = new stdClass();
			$shipping_total->shipping_rate_price = 0;
			$shipping_total->shipping_rate_total = 0;
			$shipping_total->shipping_rate_handling = 0;
			$shipping_total->shipping_tax_total = 0;
            
            $order_shipping       += $shipping_total->shipping_rate_price + $shipping_total->shipping_rate_handling;
            $order_shipping_tax   += $shipping_total->shipping_tax_total;
            
            $shipping_totals[] = $shipping_total; 
        }
        */
        // store the shipping_totals objects
        //$this->_shipping_totals = $shipping_totals;
        
        // set object properties
        $this->order_shipping       = $this->shipping->shipping_price + $this->shipping->shipping_extra;
        $this->order_shipping_tax   = $this->shipping->shipping_tax;
        
        // Allow this to be modified via plugins
        $dispatcher    =& JDispatcher::getInstance();
        $dispatcher->trigger( "onCalculateShippingTotals", array( $this ) );
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

        $items =& $this->getItems();
        if (!is_array($items))
        {
            return;
        }

        $subtotal = 0.00;
        $tax = 0.00;
        
        // calculate product subtotal and taxes
        // calculate shipping total
        Tienda::load( "TiendaHelperProduct", 'helpers.product' );
        //Tienda::load( 'TiendaHelperShipping', 'helpers.shipping' );
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
                    $shipping_total = 0;//TiendaHelperShipping::getTotal( $this->shipping_method_id, $this->getShippingGeoZone(), $item->product_id );
                    $this->_vendors[$item->vendor_id]->ordervendor_shipping     += $shipping_total->shipping_rate_price + $shipping_total->shipping_rate_handling;
                    $this->_vendors[$item->vendor_id]->ordervendor_shipping_tax += $shipping_total->shipping_tax_total;
                }
            }
        }
    
        // at this point, each vendor's TableOrderVendor object is populated
        
        // Allow this to be modified via plugins
        $dispatcher    =& JDispatcher::getInstance();
        $dispatcher->trigger( "onCalculateVendorTotals", array( $this ) );
    }
    
    /**
     * Gets the order items
     * 
     * @return array of TableOrderItems objects
     */
    function getItems()
    {
        // TODO once all references use this getter, we can do fun things with this method, such as fire a plugin event
        
        // if empty($items) && !empty($this->order_id), then this is an order from the db,  
        // so we grab all the orderitems from the db  
        if (empty($this->_items) && !empty($this->order_id))
        {
            // TODO Do this?  How will this impact Site::TiendaControllerCheckout->saveOrderItems()?
            //retrieve the order's items
            $model = JModel::getInstance( 'OrderItems', 'TiendaModel' );
            $model->setState( 'filter_orderid', $this->order_id);
            $model->setState( 'order', 'tbl.orderitem_name' );
            $model->setState( 'direction', 'ASC' );
            $orderitems = $model->getList();
            foreach ($orderitems as $orderitem)
            {
                unset($table);
                $table = JTable::getInstance( 'OrderItems', 'TiendaTable' );
                $table->load( $orderitem->orderitem_id );
                $this->addItem( $table );
            }
        }
        
        $items =& $this->_items;        
        if (!is_array($items))
        {
            $items = array();
        }
        
        // ensure that the items array only has one recurring item in it
        foreach ($items as $item)
        {
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
                // TODO Set some kind of _recurring_item property, so it is easy to get the recurring item later?
            }
                elseif (!empty($this->_recurringItemExists) && $item->orderitem_recurs)
            {
                // Only one recurring item allowed per order. 
                // If the item is recurring, 
                // check if there already is a recurring item accounted for in the order
                // if so, remove this one from the order but leave it in the cart and continue
                unset($items[$key]);
            }
        }

        $this->_items = $items;
        return $this->_items;
    }

    /**
     * Gets the order downloads
     * 
     * @return array of TiendaTableProductDownloads objects
     */
    function getDownloads()
    {
        // TODO Attempt to set this property if it is empty
        return $this->_downloads;
    }
    
    /**
     * Gets the order vendors
     * 
     * @return array of TiendaTableOrderVendors objects
     */
    function getVendors()
    {
        // TODO Attempt to set this if it is empty
        return $this->_vendors;
    }
    
    /**
     * Gets the order's shipping total object
     * 
     * @return object
     */
    function getShippingTotal( $refresh=false )
    {
        // TODO If not set, should calculate it
    	return $this->_shipping_total;
    }

    /**
     * Gets one of the order's tax geozones
     * 
     * @return unknown_type
     */
    function getBillingGeoZone()
    {
        $geozone_id = 0;
        
        $geozones = $this->getBillingGeoZones();
        if (!empty($geozones))
        {
            $geozone_id = $geozones[0]->geozone_id;
        }
        
        return $geozone_id;
    }
    
    /**
     * Gets one the order's shipping geozones
     * 
     * @return unknown_type
     */
    function getShippingGeoZone()
    {
        $geozone_id = 0;
        
        $geozones = $this->getShippingGeoZones();
        if (!empty($geozones))
        {
            $geozone_id = $geozones[0]->geozone_id;
        }
        
        return $geozone_id;
    }
    
    /**
     * Gets the order's tax geozones
     * 
     * @return unknown_type
     */
    function getBillingGeoZones()
    {
        // Set this if it isn't
        if (empty($this->_billing_geozones) && !empty($this->order_id))
        {
            $orderinfo = JTable::getInstance('OrderInfo', 'TiendaTable');
            $orderinfo->load( array('order_id'=>$this->order_id) );
            $orderinfo->zone_id = $orderinfo->billing_zone_id; 
            // TODO What to do about orders that exist from pre 0.5.0 without zone_id
            $this->setAddress( $orderinfo, 'billing' );
        }
                
        return $this->_billing_geozones;
    }
    
    /**
     * Gets the order's shipping geozones
     * 
     * @return unknown_type
     */
    function getShippingGeoZones()
    {
        // Set this if it isn't
        if (empty($this->_shipping_geozones) && !empty($this->order_id))
        {
            $orderinfo = JTable::getInstance('OrderInfo', 'TiendaTable');
            $orderinfo->load( array('order_id'=>$this->order_id) );
            $orderinfo->zone_id = $orderinfo->shipping_zone_id; 
            // TODO What to do about orders that exist from pre 0.5.0 without zone_id
            $this->setAddress( $orderinfo, 'shipping' );
        }
        
        return $this->_shipping_geozones;
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
     * Generates a unique invoice number based on the order's properties
     * 
     * @return string from $order_date-$order_time-$user_id
     */
    function getInvoiceNumber( $refresh=false )
    {
        if (empty($this->_order_number) || $refresh)
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
        }

        return $this->_order_number;
    }
    
    /**
     * Gets the tax rates applicable to this order
     * and returns an array of taxrate objects 
     * 
     * @return array    An array of objects
     */
    function getTaxRates()
    {
        if (empty($this->_taxrates))
        {
            $this->calculateTaxTotals();
        }
        
        return $this->_taxrates;    
    }

    /**
     * Gets the order's tax amount for the specified tax rate
     * 
     * @return float if taxrate applies to this order, null otherwise
     */
    function getTaxRateAmount( $taxrate_id )
    {
        $amount = null;

        if (empty($this->_taxrate_amounts))
        {
            $this->calculateTaxTotals();
        }
        
        if (!empty($this->_taxrate_amounts[$taxrate_id]))
        {
            $amount = $this->_taxrate_amounts[$taxrate_id];
        }
        
        return $amount;        
    }
    
    /**
     * Gets the tax classes applicable to this order
     * and returns an array of taxclass objects 
     * 
     * @return array    An array of objects
     */
    function getTaxClasses()
    {
        if (empty($this->_taxclasses))
        {
            $this->calculateTaxTotals();
        }
        
        return $this->_taxclasses;    
    }

    /**
     * Gets the order's tax amount for the specified tax class
     * 
     * @return float if taxclass applies to this order, null otherwise
     */
    function getTaxClassAmount( $taxclass_id )
    {
        $amount = null;

        if (empty($this->_taxclass_amounts))
        {
            $this->calculateTaxTotals();
        }
        
        if (!empty($this->_taxclass_amounts[$taxclass_id]))
        {
            $amount = $this->_taxclass_amounts[$taxclass_id];
        }
        
        return $amount;        
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
     * Based on the object's addresses,
     * sets the shipping and billing geozones
     * 
     * @return unknown_type
     */
    function setGeozones()
    {
        Tienda::load( 'TiendaHelperShipping', 'helpers.shipping' );
        if (!empty($this->_billing_address))
        { 
            $this->_billing_geozones = TiendaHelperShipping::getGeoZones( $this->_billing_address->zone_id, '1' ); 
        }
        if (!empty($this->_shipping_address))
        {
            $this->_shipping_geozones = TiendaHelperShipping::getGeoZones( $this->_shipping_address->zone_id, '2' );   
        }
    }
}

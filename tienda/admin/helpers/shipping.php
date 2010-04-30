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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');

class TiendaHelperShipping extends TiendaHelperBase
{
    /**
     * Gets geozones associated with a zone id
     * 
     * @param $zone_id
     * @param $geozonetype
     * @return array
     */
    function getGeoZones( $zone_id, $geozonetype='2' )
    {
    	$return = array();
    	if (empty($zone_id))
    	{
    		return $return;
    	}
    	
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
    	$model = JModel::getInstance( 'ZoneRelations', 'TiendaModel' );
        $model->setState( 'filter_zone', $zone_id );
        $model->setState( 'filter_geozonetype', $geozonetype );
        $items = $model->getList();
        if (!empty($items))
        {
        	$return = $items;
        }
        
        return $return;
    }
	
	/**
	 * Returns the list of shipping method types
	 * @return array of objects
	 */
	public function getTypes()
	{
		static $instance;
		
		if (!is_array($instance))
		{
			$instance = array();
		}
		if (empty($instance))
		{
            $object = new JObject();
            $object->id = '0';
            $object->title = JText::_( "Per Item" );
            $instance[$object->id] = $object;

            $object = new JObject();
            $object->id = '1';
            $object->title = JText::_( "Weight Based" );
            $instance[$object->id] = $object;
            
            $object = new JObject();
            $object->id = '2';
            $object->title = JText::_( "Per Order" );
            $instance[$object->id] = $object;
		}
		
		return $instance;
	}
	
	/**
	 * Returns the requested shipping method object
	 * 
	 * @param $id
	 * @return object
	 */
	public function getType($id)
	{
		$items = TiendaHelperShipping::getTypes();
		return $items[$id];
	}
	
	/**
	 * Returns a shipping estimate, unformatted.
	 * 
     * @param int $shipping_method_id
     * @param int $geozone_id
     * @param array $orderItems     an array of TiendaTableOrderItems objects, each with ->product_id and ->orderitem_quantity
     * 
	 * @return object with ->shipping_rate_price and ->shipping_rate_handling and ->shipping_tax_total, all decimal(12,5)
	 */
	public function getTotal( $shipping_method_id, $geozone_id, $orderItems )
	{
		$return = new JObject();
		$return->shipping_rate_price      = '0.00000';
		$return->shipping_rate_handling   = '0.00000';
		$return->shipping_tax_rate        = '0.00000';
		$return->shipping_tax_total       = '0.00000';

        // cast product_id as an array
        $orderItems = (array) $orderItems;
		
		// determine the shipping method type
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables');
		$shippingmethod = JTable::getInstance( 'ShippingMethods', 'TiendaTable' );
		$shippingmethod->load( $shipping_method_id );
		if (empty($shippingmethod->shipping_method_id))
		{
			// TODO if this is an object, setError, otherwise return false, or 0.000?
			$return->setError( JText::_( "Undefined Shipping Method" ) );
			return $return;
		}
		
		switch($shippingmethod->shipping_method_type)
		{
			case "2":
				// 2 = per order
				// if any of the products in the order require shipping
				$order_ships = false;
				JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables');
				foreach ($orderItems as $item)
				{
					//$pid = $orderItems[$i]->product_id;
					$pid = $item->product_id;
		            $product = JTable::getInstance( 'Products', 'TiendaTable' );
		            $product->load( $pid );
		            if (!empty($product->product_ships))
		            {
		                $product_id = $item->product_id;
		                $order_ships = true;
		            }
				}
				if ($order_ships)
				{
	                $shippingrate = TiendaHelperShipping::getRate( $shipping_method_id, $geozone_id, $product_id );
	                $return->shipping_rate_price      = $shippingrate->shipping_rate_price;
	                $return->shipping_rate_handling   = $shippingrate->shipping_rate_handling;
				}
                break;
            case "1":
            case "0":
            	// 0 = per item
            	// 1 = weight based
            	$rates = array();
                foreach ($orderItems as $item)
                {
                    $pid = $item->product_id;
                    $qty = $item->orderitem_quantity;
                    $rates[$pid] = TiendaHelperShipping::getRate( $shipping_method_id, $geozone_id, $pid, $shippingmethod->shipping_method_type );
                    $return->shipping_rate_price      += ($rates[$pid]->shipping_rate_price * $qty);
			        $return->shipping_rate_handling   += ($rates[$pid]->shipping_rate_handling * $qty);
            	}
                break;
            default:
	            // TODO if this is an object, setError, otherwise return false, or 0.000?
	            $return->setError( JText::_( "Invalid Shipping Method Type" ) );
	            return $return;
                break;
		}

        // get the shipping tax rate and total
        $return->shipping_tax_rate    = TiendaHelperShipping::getTaxRate( $shipping_method_id, $geozone_id );
        $return->shipping_tax_total   = ($return->shipping_tax_rate/100) * ($return->shipping_rate_price + $return->shipping_rate_handling);
		
		return $return;
	}
	
	/**
	 * Returns the shipping rate for an item
	 * Going through this helper enables product-specific flat rates in the future...
	 *  
	 * @param int $shipping_method_id
	 * @param int $geozone_id
	 * @param int $product_id
	 * @return object
	 */
	public function getRate( $shipping_method_id, $geozone_id, $product_id='', $use_weight='0' )
	{
        // TODO Give this better error reporting capabilities
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance('ShippingRates', 'TiendaModel');
        $model->setState('filter_shippingmethod', $shipping_method_id);
        $model->setState('filter_geozone', $geozone_id);
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables');
        $product = JTable::getInstance( 'Products', 'TiendaTable' );
        $product->load( $product_id );
        if (empty($product->product_id))
        {
            return JTable::getInstance('ShippingRates', 'TiendaTable');           
        }
        if (empty($product->product_ships))
        {
            // product doesn't require shipping, therefore cannot impact shipping costs
            return JTable::getInstance('ShippingRates', 'TiendaTable');
        }
        
        if ($use_weight)
        {
	        $model->setState('filter_weight', $product->product_weight);
        }
        $items = $model->getList();
        if (empty($items))
        {
            return JTable::getInstance('ShippingRates', 'TiendaTable');           
        }
        
        return $items[0];
	}
	
    /**
     * Returns the tax rate for an item
     *  
     * @param int $shipping_method_id
     * @param int $geozone_id
     * @return int
     */
    public function getTaxRate( $shipping_method_id, $geozone_id )
    {
    	Tienda::load( 'TiendaQuery', 'library.query' );
            
        $taxrate = "0.00000";
        $db = JFactory::getDBO();
        
        $query = new TiendaQuery();
        $query->select( 'tbl.*' );
        $query->from('#__tienda_taxrates AS tbl');
        $query->join('LEFT', '#__tienda_shippingmethods AS shippingmethod ON shippingmethod.tax_class_id = tbl.tax_class_id');
        $query->where('shippingmethod.shipping_method_id = '.$shipping_method_id);
        $query->where('tbl.geozone_id = '.$geozone_id);
        
        $db->setQuery( (string) $query );
        if ($data = $db->loadObject())
        {
            $taxrate = $data->tax_rate;
        }
        
        return $taxrate;
    }
}
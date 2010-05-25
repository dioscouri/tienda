<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.library.plugins.shipping', JPATH_ADMINISTRATOR.DS.'components' );

class plgTiendaShipping_Example extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_example';
    
	
    /**
     * Overriding 
     * 
     * @param $options
     * @return unknown_type
     */
    function onGetShippingView( $row )
    {
        if (!$this->_isMe($row)) 
        {
            return null;
        }
        
        $html = $this->viewList();       

        return $html;
    }
    
    function onGetShippingRates($element, $values){
    	
    	// Check if this is the right plugin
    	if (!$this->_isMe($element)) 
        {
            return null;
        }
        
        $this->includeTiendaTables();
        $order = JTable::getInstance( 'Orders', 'TiendaTable' ); 
        $order->bind($values);
        
        // get the items and add them to the order
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$items = TiendaHelperCarts::getProductsInfo();
		foreach ($items as $item)
		{
			$order->addItem( $item );
		}
		
		// get the order totals
		$order->calculateTotals();
        
        $this->includeCustomModel('ShippingMethods');
        $this->includeCustomModel('ShippingRates');
        $model = $model = JModel::getInstance('ShippingMethods', 'TiendaModel');
        $model->setState( 'filter_enabled', '1' );
        $model->setState( 'filter_subtotal', $values->order_subtotal );
        
        $methods = $model->getList();
                
        $rates = array();
        
        foreach( $methods as $method )
        {
        	$rates = $this->getShippingTotal($method->shipping_method_id, $values->getShippingGeoZone(), $order->getItems() );
        }
        
        $i = 0;
        foreach( $rates as $rate )
        {
        	$vars[$i]['id'] = $rate->shipping_method_id;
        	$vars[$i]['name'] = $rate->shipping_method_name;
        	$vars[$i]['price'] = $rate->shipping_rate_price;
        	$vars[$i]['tax'] = $rate->shipping_tax_total;
        	$vars[$i]['extra'] = $rate->shipping_rate_handling;
        	$i++;
        }
        
		return $vars;
        
    }
    
 	/**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     * 
     * @param $task
     * @return html
     */
    function viewList( )
    {
        $html = "";
        
        JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.DS.'components' );
		TiendaToolBarHelper::custom( 'newMethod', 'new', 'new', JText::_('New'), false, 'shippingTask' );
		TiendaToolBarHelper::custom( 'delete', 'delete', 'delete', JText::_('Delete'), false, 'shippingTask' );
		
        $vars = new JObject();
        $vars->state = $this->_getState();        
        
        $this->includeCustomModel('ShippingMethods');

        $model = JModel::getInstance('ShippingMethods', 'TiendaModel');
        $list = $model->getList();
        
		$vars->list = $list;
		
		$id = JRequest::getInt('id', '0');
		$form = array();
		$form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
		
		$vars->form = $form;
		
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }   
    
    
	protected function getShippingTotal( $shipping_method_id, $geozone_id, $orderItems )
	{
		$return = array();
		$i = 0;

        // cast product_id as an array
        $orderItems = (array) $orderItems;
		
		// determine the shipping method type
		$this->includeCustomTables('shipping_example');
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
	                $shippingrates = $this->getRates( $shipping_method_id, $geozone_id, $product_id );
	                foreach( $shippingrates as $shippingrate)
	                {
	                	$return[$i] = new JObject();
						$return[$i]->shipping_rate_price      = '0.00000';
						$return[$i]->shipping_rate_handling   = '0.00000';
						$return[$i]->shipping_tax_rate        = '0.00000';
						$return[$i]->shipping_tax_total       = '0.00000';
	                	$return[$i]->shipping_rate_price      = $shippingrate->shipping_rate_price;
	                	$return[$i]->shipping_rate_handling   = $shippingrate->shipping_rate_handling;
	                	$i++;
	                }
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
                    $rates[$pid] = $this->getRates( $shipping_method_id, $geozone_id, $pid, $shippingmethod->shipping_method_type );
                    
                    foreach($rates[$pid] as $rate)
                    {
	                    $return[$i] = new JObject();
						$return[$i]->shipping_rate_price      = '0.00000';
						$return[$i]->shipping_rate_handling   = '0.00000';
						$return[$i]->shipping_tax_rate        = '0.00000';
						$return[$i]->shipping_tax_total       = '0.00000';
	                    $return[$i]->shipping_rate_price      += ($rate->shipping_rate_price * $qty);
				        $return[$i]->shipping_rate_handling   += ($rate->shipping_rate_handling * $qty);
                    }
            	}
                break;
            default:
	            // TODO if this is an object, setError, otherwise return false, or 0.000?
	            $return->setError( JText::_( "Invalid Shipping Method Type" ) );
	            return $return;
                break;
		}

        // get the shipping tax rate and total
        foreach( $return as $r )
        {
        	$r->shipping_tax_rate    = $this->getTaxRate( $shipping_method_id, $geozone_id );
        	$r->shipping_tax_total   = ($r->shipping_tax_rate/100) * ($r->shipping_rate_price + $r->shipping_rate_handling);
        	$r->shipping_method_id = $shipping_method_id;
        	$r->shipping_method_name = $shippingmethod->shipping_method_name;
        }
        
		return $return;
	}
	
	/**
     * Returns the tax rate for an item
     *  
     * @param int $shipping_method_id
     * @param int $geozone_id
     * @return int
     */
    protected function getTaxRate( $shipping_method_id, $geozone_id )
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
    
	/**
	 * Returns the shipping rate for an item
	 * Going through this helper enables product-specific flat rates in the future...
	 *  
	 * @param int $shipping_method_id
	 * @param int $geozone_id
	 * @param int $product_id
	 * @return object
	 */
	protected function getRates( $shipping_method_id, $geozone_id, $product_id='', $use_weight='0' )
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
        
        return $items;
	}
}

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

Tienda::load('TiendaShippingPlugin', 'library.plugins.shipping');

class plgTiendaShipping_Standard extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_standard';

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
    
    /**
     * 
     * @param $element
     * @param $values
     */
    function onGetShippingRates($element, $order)
    {
    	// Check if this is the right plugin
    	if (!$this->_isMe($element)) 
        {
            return null;
        }

        $vars = array();
       
        $this->includeTiendaTables();       
        $this->includeCustomModel('ShippingMethods');
        $this->includeCustomModel('ShippingRates');
        
        $geozones_taxes = $order->getBillingGeoZones();
        $geozones = $order->getShippingGeoZones();
        $gz_array = array();
        foreach ($geozones as $geozone)
        {
            $gz_array[] = $geozone->geozone_id;
        }

        
        
        $rates = array();
        $model = JModel::getInstance('ShippingMethods', 'TiendaModel');
        $model->setState( 'filter_enabled', '1' );
        $model->setState( 'filter_subtotal', $order->order_subtotal );
        if ($methods = $model->getList())
        {
            foreach( $methods as $method )
            {
                // filter the list of methods according to geozone
                $ratemodel = JModel::getInstance('ShippingRates', 'TiendaModel');
                $ratemodel->setState('filter_shippingmethod', $method->shipping_method_id);
                $ratemodel->setState('filter_geozones', $gz_array);
                if ($ratesexist = $ratemodel->getList())
                {
                    $total = $this->getTotal($method->shipping_method_id, $geozones, $order->getItems(), $geozones_taxes );                  
                    if ($total)
                    {
                    	$total->shipping_method_type = $method->shipping_method_type;
                        $rates[] = $total;
                    }
                }
            }
        }
        
        $i = 0;
        foreach( $rates as $rate )
        {
        	$vars[$i]['element'] = $this->_element;
        	$vars[$i]['name'] = $rate->shipping_method_name;      
        	$vars[$i]['type'] = $rate->shipping_method_type;  
        	$vars[$i]['code'] = $rate->shipping_rate_id;        	
        	$vars[$i]['price'] = $rate->shipping_rate_price;
        	$vars[$i]['tax'] = $rate->shipping_tax_total;
        	$vars[$i]['extra'] = $rate->shipping_rate_handling;
        	$vars[$i]['total'] = $rate->shipping_rate_price + $rate->shipping_rate_handling + $rate->shipping_tax_total;
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
    function viewList()
    {
        $html = "";
        
        JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.DS.'components' );
		TiendaToolBarHelper::custom( 'newMethod', 'new', 'new', JText::_('COM_TIENDA_NEW'), false, 'shippingTask' );
		TiendaToolBarHelper::custom( 'delete', 'delete', 'delete', JText::_('COM_TIENDA_DELETE'), false, 'shippingTask' );
		TiendaToolBarHelper::cancel( 'close', 'Close' );
		
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
		
		$vars->sid = $id;
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }   
    
    /**
     * 
     * Returns an object with the total cost of shipping for this method and the array of geozones
     * 
     * @param unknown_type $shipping_method_id
     * @param array $geozones
     * @param unknown_type $orderItems
     * @param unknown_type $order_id
     */
	protected function getTotal( $shipping_method_id, $geozones, $orderItems, $geozones_taxes )
	{
        $return = new JObject();
        $return->shipping_rate_id         = '0';
        $return->shipping_rate_price      = '0.00000';
        $return->shipping_rate_handling   = '0.00000';
        $return->shipping_tax_rate        = '0.00000';
        $return->shipping_tax_total       = '0.00000';
        
        $rate_exists = false;
        $geozone_rates = array();
	    
        // cast product_id as an array
        $orderItems = (array) $orderItems;
		
		// determine the shipping method type
		$this->includeCustomTables('shipping_standard');
		$shippingmethod = JTable::getInstance( 'ShippingMethods', 'TiendaTable' );
		$shippingmethod->load( $shipping_method_id );
	 
		if (empty($shippingmethod->shipping_method_id))
		{
			// TODO if this is an object, setError, otherwise return false, or 0.000?
			$return->setError( JText::_('COM_TIENDA_UNDEFINED_SHIPPING_METHOD') );
			return $return;
		}
		
		switch($shippingmethod->shipping_method_type)
		{
			case "6":
		        // 5 = per order - price based
		        // Get the total of the order, and find the rate for that
		        $total = 0;
				foreach ($orderItems as $item)
                {
                	$total += $item->orderitem_final_price;
                }		  
		 	   foreach ($geozones as $geozone)
				    {
				        unset($rate);
				        
				        $geozone_id = $geozone->geozone_id;
				        if (empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
                        {
                            $geozone_rates[$geozone_id] = array();
                        }

                        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
				        $model = JModel::getInstance('ShippingRates', 'TiendaModel');
				        $model->setState('filter_shippingmethod', $shipping_method_id);
				        $model->setState('filter_geozone', $geozone_id);
				        $model->setState('filter_weight', $total); // Use weight as total
				        
				        $items = $model->getList();
	       
				        if (empty($items))
				        {
				            return JTable::getInstance('ShippingRates', 'TiendaTable');           
				        }
				        
				        $rate = $items[0];                
                        $geozone_rates[$geozone_id]['0'] = $rate;
                        
				        // if $rate->shipping_rate_id is empty, then no real rate was found 
                        if (!empty($rate->shipping_rate_id))
                        {
                            $rate_exists = true;
                        }
                            
				        $geozone_rates[$geozone_id]['0']->qty = '1';  
				        $geozone_rates[$geozone_id]['0']->shipping_method_type = $shippingmethod->shipping_method_type;   
				    }		         
		    	break;  
		    case "5":
		        // 5 = per order - quantity based
		        // first, get the total quantity of shippable items for the entire order
		        // then, figure out the rate for this number of items (use the weight range field) + geozone
		    case "3":
			case "2":
				// 2 = per order - weight based
                // 3 = per order - flat rate
				// if any of the products in the order require shipping
				$sum_weight = 0;
				$count_shipped_items = 0;
				$order_ships = false;
				JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables');
				foreach ($orderItems as $item)
				{
				    // find out if the order ships
				    // and while looping through, sum the weight of all shippable products in the order
					$pid = $item->product_id;
		            $product = JTable::getInstance( 'Products', 'TiendaTable' );
		            $product->load( $pid );
		            if (!empty($product->product_ships))
		            {
		                $product_id = $item->product_id;
		                $order_ships = true;
		                $sum_weight += ($product->product_weight * $item->orderitem_quantity);
		                $count_shipped_items += $item->orderitem_quantity;
		            }
				}
				
				if ($order_ships)
				{
				    foreach ($geozones as $geozone)
				    {
				        unset($rate);
				        
				        $geozone_id = $geozone->geozone_id;
				        if (empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
                        {
                            $geozone_rates[$geozone_id] = array();
                        }
                        
                        switch( $shippingmethod->shipping_method_type )
                        {
                            case "3":
                                // don't use weight, just do flat rate for entire order
                                // regardless of weight and regardless of the number of items
                                $rate = $this->getRate( $shipping_method_id, $geozone_id, $product_id );
                                break;
                            case "5":
                                // get the shipping rate for the entire order using the count of all products in the order that ship
                                $rate = $this->getRate( $shipping_method_id, $geozone_id, $product_id, '1', $count_shipped_items );
                                break;
                            default:
                                // get the shipping rate for the entire order using the sum weight of all products in the order that ship
                                $rate = $this->getRate( $shipping_method_id, $geozone_id, $product_id, '1', $sum_weight );
                                break;
                        }
                        $geozone_rates[$geozone_id]['0'] = $rate;
                        
				        // if $rate->shipping_rate_id is empty, then no real rate was found 
                        if (!empty($rate->shipping_rate_id))
                        {
                            $rate_exists = true;
                        }
                            
				        $geozone_rates[$geozone_id]['0']->qty = '1';  
				        $geozone_rates[$geozone_id]['0']->shipping_method_type = $shippingmethod->shipping_method_type;   
				    }
				}
                break;
            case "4":
            case "1":
            case "0":
            	// 0 = per item - flat rate
            	// 1 = per item - weight based
                // 4 = per item - price based, a percentage of the product's price  
            	$rates = array();
                foreach ($orderItems as $item)
                {
                	$pid = $item->product_id;
                    $qty = $item->orderitem_quantity;
                    foreach ($geozones as $geozone)
                    {
                        unset($rate);
                        
                        $geozone_id = $geozone->geozone_id;
                        if (empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
                        {
                            $geozone_rates[$geozone_id] = array();
                        }
                        // $geozone_rates[$geozone_id][$pid] contains the shipping rate object for ONE product_id at this geozone.  
                        // You need to multiply by the quantity later
                        $rate = $this->getRate( $shipping_method_id, $geozone_id, $pid, $shippingmethod->shipping_method_type );
                        
                        if ($shippingmethod->shipping_method_type == '4')
                        {
                            // the rate is a percentage of the product's price
                            $rate->shipping_rate_price = ($rate->shipping_rate_price/100) * $item->orderitem_final_price;
                            
                            $geozone_rates[$geozone_id][$pid] = $rate; 
                            $geozone_rates[$geozone_id][$pid]->shipping_method_type = $shippingmethod->shipping_method_type;
                            $geozone_rates[$geozone_id][$pid]->qty = '1'; // If the method_type == 4, qty should be 1 (we don't need to multiply later, in the "calc for the entire method", since this is a percentage of the orderitem_final_price)
                        } 
                            else
                        {
                            $geozone_rates[$geozone_id][$pid] = $rate; 
                            $geozone_rates[$geozone_id][$pid]->shipping_method_type = $shippingmethod->shipping_method_type;
                            $geozone_rates[$geozone_id][$pid]->qty = $qty;    
                        }
                        
                        // if $rate->shipping_rate_id is empty, then no real rate was found 
                        if (!empty($rate->shipping_rate_id))
                        {
                            $rate_exists = true;
                        }
                    }
            	}
                break;
            default:
	            $this->setError( JText::_('COM_TIENDA_INVALID_SHIPPING_METHOD_TYPE') );
	            return false;
                break;
		}
		
		if (!$rate_exists)
		{
            $this->setError( JText::_('COM_TIENDA_NO_RATE_FOUND') );
            return false;
		}
		
		$shipping_tax_rates = array();
        $shipping_method_price = 0;
        $shipping_method_handling = 0;
        $shipping_method_tax_total = 0;
  
		// now calc for the entire method
    foreach ($geozone_rates as $geozone_id=>$geozone_rate_array)
		{		
		    foreach ($geozone_rate_array as $geozone_rate)
		    {
		    	$shipping_tax_rates[$geozone_id] = 0;
		    	foreach( $geozones_taxes as $gz_tax )
	          $shipping_tax_rates[$geozone_id] += $this->getTaxRate( $shipping_method_id, $gz_tax->geozone_id );

          $shipping_method_price += ($geozone_rate->shipping_rate_price * $geozone_rate->qty);
          $shipping_method_handling += $geozone_rate->shipping_rate_handling;
          $shipping_method_tax_total += ($shipping_tax_rates[$geozone_id]/100) * ($geozone_rate->shipping_rate_price + $geozone_rate->shipping_rate_handling); 
		    }
		}
			
        // return formatted object
		$return->shipping_rate_price    = $shipping_method_price;
		$return->shipping_rate_handling = $shipping_method_handling; 
        $return->shipping_tax_rates     = $shipping_tax_rates;
        $return->shipping_tax_total     = $shipping_method_tax_total;
        $return->shipping_method_id     = $shipping_method_id;
        $return->shipping_method_name   = $shippingmethod->shipping_method_name;

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
    public function getRate( $shipping_method_id, $geozone_id, $product_id='', $use_weight='0', $weight='0' )
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
      
        if (!empty($use_weight) && $use_weight == '1')
        {
            if(!empty($weight))
            {
                $model->setState('filter_weight', $weight);
            }
                else
            {
                $model->setState('filter_weight', $product->product_weight);
            }
        }
        $items = $model->getList();
     
        if (empty($items))
        {
            return JTable::getInstance('ShippingRates', 'TiendaTable');           
        }
        
        return $items[0];
    }
}

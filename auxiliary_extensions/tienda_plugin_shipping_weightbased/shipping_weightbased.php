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

class plgTiendaShipping_Weightbased extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element   = 'shipping_weightbased';
	
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
		$this->checkTable();
		
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
		JModel::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'shipping_weightbased'.DS.'models' );
		JTable::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'shipping_weightbased'.DS.'tables' );
		
		$this->includeTiendaTables();
		$this->includeCustomModel('ShippingMethodsWeightbased');
		$this->includeCustomModel('ShippingRatesWeightbased');

    $geozones_taxes = $order->getBillingGeoZones();
		$geozones = $order->getShippingGeoZones();
		$gz_array = array();
		foreach ($geozones as $geozone)
		{
			$gz_array[] = $geozone->geozone_id;
		}

		$rates = array();
		$model = JModel::getInstance('ShippingMethodsWeightbased', 'TiendaModel');
		$model->setState( 'filter_enabled', '1' );
		$model->setState( 'filter_price', $order->order_subtotal );
		$i = 0;
		if ($methods = $model->getList())
		{
			foreach( $methods as $method )
			{
				// filter the list of methods according to geozone
				$ratemodel = JModel::getInstance('ShippingRatesWeightbased', 'TiendaModel');
				$ratemodel->setState('filter_shippingmethod', $method->shipping_method_weightbased_id);
				$ratemodel->setState('filter_geozones', $gz_array);
				if ($ratesexist = $ratemodel->getList())
				{
					$total = $this->getTotal($method->shipping_method_weightbased_id, $geozones, $order->getItems(),$geozones_taxes );
					if ( $total )
					{
						$vars[$i]['element'] = $this->_element;
						$vars[$i]['name'] = $method->shipping_method_weightbased_name;
						$vars[$i]['code'] = $total->shipping_rate_weightbased_id;
						$vars[$i]['price'] = $total->shipping_price;
						$vars[$i]['tax'] = $total->shipping_tax_total;
						$vars[$i]['extra'] = $total->shipping_handling;
						$vars[$i]['total'] = $total->shipping_price + $total->shipping_handling + $total->shipping_tax_total;
						$i++;
					}
				}
			}
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

		JModel::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'shipping_weightbased'.DS.'models' );
		JTable::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'shipping_weightbased'.DS.'tables' );
		JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.DS.'components' );
		TiendaToolBarHelper::custom( 'newMethod', 'new', 'new', JText::_('COM_TIENDA_NEW'), false, 'shippingTask' );
		TiendaToolBarHelper::custom( 'delete', 'delete', 'delete', JText::_('COM_TIENDA_DELETE'), false, 'shippingTask' );
		TiendaToolBarHelper::cancel( 'close', 'Close' );

		$vars = new JObject();
		$vars->state = $this->_getState();

		$this->includeCustomModel('ShippingMethodsWeightbased');

		$model = JModel::getInstance('ShippingMethodsWeightbased', 'TiendaModel');
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
		$return->shipping_price					 	      = '0.00000';
		$return->shipping_handling						  = '0.00000';
		$return->shipping_tax_total       			= '0.00000';

		$rate_exists = false;
		$geozone_rates = array();

		// determine the shipping method type
		$this->includeCustomTables('shipping_weightbased');
		$shippingmethod = JTable::getInstance( 'ShippingMethodsWeightbased', 'TiendaTable' );
		$shippingmethod->load( $shipping_method_id );

		if( empty( $shippingmethod->shipping_method_weightbased_id ) )
		{
			// TODO if this is an object, setError, otherwise return false, or 0.000?
			$return->setError( JText::_('COM_TIENDA_UNDEFINED_SHIPPING_METHOD') );
			return $return;
		}

		$sum_weight = $this->calculateWeight( $orderItems );
		$order_ships = $sum_weight != 0;

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

				// get the shipping rate for the entire order using the sum weight of all products in the order that ship
				$rate = $this->getRate( $shipping_method_id, $geozone_id, $sum_weight );
	
				// if $rate->shipping_rate_id is empty, then no real rate was found
				if (!empty($rate->shipping_rate_weightbased_id))
				{
					$rate_exists = true;
					$geozone_rates[$geozone_id]['0'] = $rate;
				}
			}
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
					
				$return->shipping_rate_weightbased_id = $geozone_rate->shipping_rate_weightbased_id;
				$rate_price = $this->calculatePriceRate( $geozone_rate, $sum_weight );
		    $shipping_method_price += $rate_price;
				$shipping_method_handling += $geozone_rate->shipping_handling;
		    $shipping_method_tax_total += ($shipping_tax_rates[$geozone_id]/100) * ($rate_price + $geozone_rate->shipping_handling);
			}
		}

		// return formatted object
		$return->shipping_price    = $shipping_method_price;
		$return->shipping_handling = $shipping_method_handling;
		$return->shipping_tax_rates     = $shipping_tax_rates;
		$return->shipping_tax_total     = $shipping_method_tax_total;
		$return->shipping_method_id     = $shipping_method_id;
    $return->shipping_method_name   = $shippingmethod->shipping_method_weightbased_name;
		
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
		$query->join('LEFT', '#__tienda_shippingmethods_weightbased AS shippingmethod ON shippingmethod.tax_class_id = tbl.tax_class_id');
		$query->where('shippingmethod.shipping_method_weightbased_id = '.$shipping_method_id);
		$query->where('tbl.geozone_id = '.$geozone_id);

		$db->setQuery( (string) $query );
		if ($data = $db->loadObject())
		{
			$taxrate = $data->tax_rate;
		}

		return $taxrate;
	}

	/**
	 * Returns the shipping rate objects for an item
	 * Going through this helper enables product-specific flat rates in the future...
	 *
	 * @param int $shipping_method_id
	 * @param int $geozone_id
	 * @param int $weight
	 * @return object
	 */
	public function getRate( $shipping_method_id, $geozone_id, $weight )
	{
		// TODO Give this better error reporting capabilities
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance('ShippingRatesWeightbased', 'TiendaModel');
		$model->setState('filter_shippingmethod', $shipping_method_id);
		$model->setState('filter_geozone', $geozone_id);

		if( !empty( $weight ) )
		{
			$model->setState( 'filter_weight', $weight );
		}
		else
		{
			$model->setState( 'filter_weight', $product->product_weight );
		}
		$items = $model->getList();
		
		if ( empty( $items ) )
		{
			return JTable::getInstance('ShippingRatesWeightbased', 'TiendaTable');
		}
	
		return $items[0];
	}
	
	/*
	 * Calculates shipping costs for the specified rate and weight
	 * 
	 * @param $rate Object of the rate
	 * @param $weight Weight of the order
	 * 
	 * @return Shipping costs
	 */
	public function calculatePriceRate( $rate, $weight )
	{
		$costs = ( float )$rate->base_price;
		if( ( float ) $rate->weight_step_size )
			$steps = ( $weight - ( float )$rate->weight_start ) / ( float )$rate->weight_step_size;
		else
			$steps = 0;
		
		if( ( ( float )$weight != ( float )$rate->weight_start ) && $steps < 1 ) // at least one step is done
			$steps = 1; 
		else // we can advance only by full step
		{
			if( ( int )$steps != ( float )$steps )
				$steps = ( int )$steps + 1; // we always go for the upper step
		}
		$costs += $steps * ( float )$rate->price_step;
		return $costs;
	}
	
	/*
	 * Calculates weight of the whole order
	 * 
	 * @param $orderItems List of order items
	 * 
	 * @return Int Weight of the order
	 */
	public function calculateWeight( $orderItems )
	{
		$weight = 0;

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables');
		foreach ( $orderItems as $item )
		{
			$pid = $item->product_id;
			$product = JTable::getInstance( 'Products', 'TiendaTable' );
			$product->load( $pid );
			if( !empty( $product->product_ships ) )
				$weight += ( $product->product_weight * $item->orderitem_quantity );
		}
		
		return $weight;
	}

	/**
	 * Deletes a shipping method
	 * @see tienda/admin/library/plugins/TiendaControllerShippingPlugin::delete()
	 */
	function delete()
	{
		JModel::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'shipping_weightbased'.DS.'models' );
		JTable::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'shipping_weightbased'.DS.'tables' );
		
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';

		$model = $this->getModel('shippingmethodsweightbased');
		$row = JTable::getInstance('ShippingMethodsWeightbased', 'TiendaTable');

		$cids = JRequest::getVar('cid', array (0), 'request', 'array');
		$row->load( $cid[0] );
		$this->redirect = 'index.php?option=com_tienda&view=shipping&task=view&id='.$row->shipping_method_weightbased_id;
		if (!$row->delete($cid[0]))
		{
			$this->message .= $row->getError();
			$this->messagetype = 'notice';
			$error = true;
		}

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = JText::_('COM_TIENDA_ITEMS_DELETED');
		}

		$this->setRedirect( $this->redirect, $this->message, $this->messagetype );
	}
	
	function checkTable()
	{
		//if this has already been done, don't repeat
		if ( Tienda::getInstance()->get( 'checkWeightbasedPluginTable', '0' ) ) return true;
		$db = JFactory::getDbo();
		$q = 'CREATE TABLE IF NOT EXISTS `#__tienda_shippingrates_weightbased` (
					`shipping_rate_weightbased_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`geozone_id` INT NOT NULL ,
					`shipping_method_weightbased_id` INT NOT NULL ,
					`base_price` DECIMAL( 12, 5 ) NOT NULL ,
					`weight_start` DECIMAL( 12, 5 ) NOT NULL ,
					`weight_end` DECIMAL( 12, 5 ) NOT NULL ,
					`price_step` DECIMAL( 12, 5 ) NOT NULL ,
					`weight_step_size` DECIMAL( 12, 5 ) NOT NULL ,
					`shipping_handling` DECIMAL( 12, 5 ) NOT NULL ,
					`created_date` DATETIME NOT NULL ,
					`modified_date` DATETIME NOT NULL
					) ENGINE = InnoDB
					DEFAULT CHARACTER SET = utf8;';
		$db->setQuery( $q );
		$db->query( $q );
		
		$q = 'CREATE TABLE IF NOT EXISTS `#__tienda_shippingmethods_weightbased` (
					`shipping_method_weightbased_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`shipping_method_weightbased_name` VARCHAR( 255 ) NOT NULL ,
					`tax_class_id` INT NOT NULL ,
					`shipping_method_weightbased_enabled` TINYINT( 1 ) NOT NULL DEFAULT  \'0\',
					`shipping_method_price_start` DECIMAL( 12, 5 ) NOT NULL ,
					`shipping_method_price_end` DECIMAL( 12, 5 ) NOT NULL
					) ENGINE = InnoDB
					DEFAULT CHARACTER SET = utf8;';
		$db->setQuery( $q );
		$db->query( $q );

		// Update config to say this has been done already
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$config = JTable::getInstance( 'Config', 'TiendaTable' );
		$config->load( array( 'config_name'=>'checkWeightbasedPluginTable') );
		$config->config_name = 'checkWeightbasedPluginTable';
		$config->value = '1';
		$config->save();
	}
}

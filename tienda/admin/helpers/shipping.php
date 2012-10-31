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
	 * Returns the list of shipping method types
	 * @return array of objects
	 */
	public static function getTypes()
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
			$object->title = JText::_('COM_TIENDA_FLAT_RATE_PER_ITEM');
			$instance[$object->id] = $object;

			$object = new JObject();
			$object->id = '1';
			$object->title = JText::_('COM_TIENDA_WEIGHT_BASED_PER_ITEM');
			$instance[$object->id] = $object;

			$object = new JObject();
			$object->id = '2';
			$object->title = JText::_('COM_TIENDA_WEIGHT_BASED_PER_ORDER');
			$instance[$object->id] = $object;

			$object = new JObject();
			$object->id = '3';
			$object->title = JText::_('COM_TIENDA_FLAT_RATE_PER_ORDER');
			$instance[$object->id] = $object;

			$object = new JObject();
			$object->id = '4';
			$object->title = JText::_('COM_TIENDA_PRICE_BASED_PER_ITEM');
			$instance[$object->id] = $object;

			$object = new JObject();
			$object->id = '5';
			$object->title = JText::_('COM_TIENDA_QUANTITY_BASED_PER_ORDER');
			$instance[$object->id] = $object;

			$object = new JObject();
			$object->id = '6';
			$object->title = JText::_('COM_TIENDA_PRICE_BASED_PER_ORDER');
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
	public static function getType($id)
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
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables');
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
			case "2":
				// 2 = per order
				// if any of the products in the order require shipping
				$order_ships = false;
				JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables');
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
					//$shippingrate = TiendaHelperShipping::getRate( $shipping_method_id, $geozone_id, $product_id );
					//$return->shipping_rate_price      = $shippingrate->shipping_rate_price;
					//$return->shipping_rate_handling   = $shippingrate->shipping_rate_handling;
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
					//$rates[$pid] = TiendaHelperShipping::getRate( $shipping_method_id, $geozone_id, $pid, $shippingmethod->shipping_method_type );
					//$return->shipping_rate_price      += ($rates[$pid]->shipping_rate_price * $qty);
					//$return->shipping_rate_handling   += ($rates[$pid]->shipping_rate_handling * $qty);
				}
				break;
			default:
				// TODO if this is an object, setError, otherwise return false, or 0.000?
				$return->setError( JText::_('COM_TIENDA_INVALID_SHIPPING_METHOD_TYPE') );
				return $return;
				break;
		}

		// get the shipping tax rate and total
		$return->shipping_tax_rate    = TiendaHelperShipping::getTaxRate( $shipping_method_id, $geozone_id );
		$return->shipping_tax_total   = ($return->shipping_tax_rate/100) * ($return->shipping_rate_price + $return->shipping_rate_handling);

		return $return;
	}

	/**
	 * Gets geozones associated with a zone id and a zip code, optionally
	 *
	 * @param $zone_id
	 * @param $geozonetype
	 * @param $zip_code
	 * @param $update
	 * @return array
	 */
	public static function getGeoZones( $zone_id, $geozonetype='2', $zip_code = null, $update = false )
	{
		$return = array();
		if (empty($zone_id))
		{
			return $return;
		}
		 
		static $geozones = null; // static array for caching results
		if( $geozones === null )
			$geozones = array();
			
		if( $zip_code === null )
			$zip_code = 0;

		if( isset( $geozones[$geozonetype][$zone_id][$zip_code] ) && !$update )
			return $geozones[$geozonetype][$zone_id][$zip_code];
		 
		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$model = JModel::getInstance( 'ZoneRelations', 'TiendaModel' );
		$model->setState( 'filter_zone', $zone_id );
		$model->setState( 'filter_geozonetype', $geozonetype );

		if($zip_code)
		{
			$model->setState( 'filter_zip', $zip_code );
		}

		$items = $model->getList();

		if (!empty($items))
		{
			$return = $items;
		}
		$geozones[$geozonetype][$zone_id][$zip_code] = $return;
		return $return;
	}
	
	/**
	 * Generates hash from provided shipping values
	 * @param $values Shipping values
	 * 
	 * @return Hash as a string
	 */
	public static function generateShippingHash( $values )
	{
		static $sw = '';
		// using values shipping_type, shipping_price, shipping_name, shipping_code, shipping_tax, shipping_extra, shipping_tracking_id
		if( $sw == '' )
		{
			$config = Tienda::getInstance();
			$sw = $config->get( 'secret_word' );
		}
		if( !isset( $values['tracking_id'] ) )
			$values['tracking_id'] = '';
		
		$hash = $values['type'].$values['price'].$values['name'].
						$values['code'].$values['tax'].$values['extra'].
						$values['tracking_id'];
						
		return sha1( $hash.$sw );
	}
}
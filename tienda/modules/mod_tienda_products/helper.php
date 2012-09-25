<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.model' );

class modTiendaProductsHelper extends JObject
{
	/**
	 * Sets the modules params as a property of the object
	 * @param unknown_type $params
	 * @return unknown_type
	 */
	function __construct( $params )
	{
		$this->params = $params;
	}

	/**
	 * Sample use of the products model for getting products with certain properties
	 * See admin/models/products.php for all the filters currently built into the model
	 *
	 * @param $parameters
	 * @return unknown_type
	 */
	function getProducts()
	{
		// Check the registry to see if our Tienda class has been overridden
		if ( !class_exists('Tienda') )
		JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );

		// load the config class
		Tienda::load( 'Tienda', 'defines' );
		Tienda::load('TiendaHelperProduct', 'helpers.product');

		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		JModel::addIncludePath( JPATH_SITE.'/components/com_tienda/models' );

		// get the model
		$model = JModel::getInstance( 'Products', 'TiendaModel' );
		 
		// setting the model's state tells it what items to return
		$model->setState('filter_published', '1');
		$date = JFactory::getDate();
		$model->setState('filter_published_date', $date->toMysql() );
		$model->setState('filter_enabled', '1');

		// Set category state
		if ($this->params->get('category', '1') != '1')
		$model->setState('filter_category', $this->params->get('category', '1'));

		// Set manufacturer state
		if ($this->params->get('manufacturer', '') != '')
		$model->setState('filter_manufacturer', $this->params->get('manufacturer', ''));

		// Set id set state
		if ($this->params->get('id_set', '') != '')
		{
			$params_id_set = $this->params->get('id_set');
			$id_array = explode(',', $params_id_set);
			$id_set = "'".implode("', '", $id_array)."'";
			$model->setState('filter_id_set', $id_set);
		}

		// set the states based on the parameters
		$model->setState('limit', $this->params->get( 'max_number', '10' ));
		if($this->params->get( 'price_from', '-1' ) != '-1')
		$model->setState('filter_price_from', $this->params->get( 'price_from', '-1' ));
		if($this->params->get( 'price_to', '-1' ) != '-1')
		$model->setState('filter_price_to', $this->params->get( 'price_to', '-1' ));

		$order = $this->params->get('order');
		$direction = $this->params->get('direction', 'ASC');
		switch ($order)
		{
			case "2":
			case "name":
				$model->setState('order', 'tbl.product_name');
				break;
			case "1":
			case "created":
				$model->setState('order', 'tbl.created_date');
				break;
			case "0":
			case "ordering":
			default:
				$model->setState('order', 'tbl.ordering');
				break;
		}
		if ($this->params->get('random', '0') == '1')
				$model->setState('order', 'RAND()');
		
		$model->setState('direction', $direction);
		 
		$config = Tienda::getInstance();
		$show_tax = $config->get('display_prices_with_tax');

		$default_user_group = Tienda::getInstance()->get('default_user_group');
		$user_groups_array = $this->getUserGroups();

		$overide_price = false;
		if(count($user_groups_array) > 1 && $user_groups_array[0] != $default_user_group)
		{
			$overide_price = true;
			 
		}
		// using the set filters, get a list of products
		if ($products = $model->getList(true, false ))
		{
			if( $show_tax )
			{
				Tienda::load('TiendaHelperUser', 'helpers.user');
				$geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
				if (empty($geozones))
				{
					// use the default
					$table = JTable::getInstance('Geozones', 'TiendaTable');
					$table->load(array('geozone_id'=>Tienda::getInstance()->get('default_tax_geozone')));
					$geozones = array( $table );
				}
			}
			
			foreach ($products as $product)
			{
				if($overide_price)
				{
					$filter_group = TiendaHelperUser::getUserGroup(JFactory::getUser()->id, $product->product_id);
					$price = TiendaHelperProduct::getPrice( $product->product_id, '1', $filter_group );
					$product->price =	$price->product_price;
				}

				$product->taxtotal = 0;
				$product->tax = 0;
				if ($show_tax )
				{
					$taxtotal = TiendaHelperProduct::getTaxTotal($product->product_id, $geozones);
					$product->taxtotal = $taxtotal;
					$product->tax = $taxtotal->tax_total;
				}
				
				$product->filter_category = '';
				$categories = TiendaHelperProduct::getCategories( $product->product_id );
				if (!empty($categories))
				{
					$product->link .= "&filter_category=".$categories[0];
					$product->filter_category = $categories[0];
				}
				$itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $product->filter_category, true );
				if( empty( $itemid ) )
				{
					$product->itemid = $this->params->get( 'itemid' );
				}
				else
				{
					$product->itemid = $itemid;
				}
			}
		}
		 
		return $products;
	}

	/**
	 * Method to get if user has multiple user group
	 * @return array
	 */
	private function getUserGroups()
	{
		$user = JFactory::getUser();
		$database = JFactory::getDBO();
		Tienda::load( 'TiendaQuery', 'library.query' );
		$query = new TiendaQuery();
		$query->select( 'tbl.group_id' );
		$query->from('#__tienda_usergroupxref AS tbl');
		$query->join('INNER', '#__tienda_groups AS g ON g.group_id = tbl.group_id');
		$query->where("tbl.user_id = ".(int) $user->id);
		$query->order('g.ordering ASC');

		$database->setQuery( (string) $query );
		return $database->loadResultArray();
	}
}
?>

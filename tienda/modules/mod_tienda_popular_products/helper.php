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

class modTiendaPopularProductsHelper extends JObject
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
		Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
		Tienda::load('TiendaHelperUser', 'helpers.user');
		$helper = new TiendaHelperProduct();

		DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		DSCModel::addIncludePath( JPATH_SITE.'/components/com_tienda/models' );

		// get the model
		$model = DSCModel::getInstance( 'OrderItems', 'TiendaModel' );
		$model->setState( 'limit', $this->params->get( 'max_number', '5') );
		 
		$query = $model->getQuery();

		// group results by product ID
		$query->group('tbl.product_id');

		// select the total number of sales for each product
		$field = array();
		$field[] = " SUM(tbl.orderitem_quantity) AS total_sales ";
		$field[] = " p.product_description_short AS product_description_short ";
		$query->select( $field );

		// order results by the total sales
		$query->order('total_sales DESC');

		$model->setQuery( $query );
		 
		$show_tax = Tienda::getInstance()->get('display_prices_with_tax');

		// using the set filters, get a list of products
		if ($products = $model->getList( false, false ))
		{
			if ($show_tax)
			{
				$geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
				if (empty($geozones))
				{
					// use the default
					$table = DSCTable::getInstance('Geozones', 'TiendaTable');
					$table->load(array('geozone_id'=>Tienda::getInstance()->get('default_tax_geozone')));
					$geozones = array( $table );
				}
			}
			foreach ($products as $product)
			{
				$product->link = 'index.php?option=com_tienda&view=products&task=view&id='.$product->product_id;
				$filter_group = TiendaHelperUser::getUserGroup(JFactory::getUser()->id, $product->product_id);
				$price = $helper->getPrice( $product->product_id, '1', $filter_group );
				$product->price = $price->product_price;
				 
				//product total
				$product->taxtotal = 0;
				$product->tax = 0;
				if ($show_tax)
				{
					$taxtotal = TiendaHelperProduct::getTaxTotal($product->product_id, $geozones);
					$product->taxtotal = $taxtotal;
					$product->tax = $taxtotal->tax_total;
				}
				 
				$product->filter_category = '';
				$categories = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getCategories( $product->product_id );
				if (!empty($categories))
				{
					$product->link .= "&filter_category=".$categories[0];
					$product->filter_category = $categories[0];
				}

				$itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $product->filter_category, true );
				if (empty($itemid))
				{
					$itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->findItemid( array( 'view'=>'products' ) );
				}
				$product->itemid = $itemid;
			}
		}

		return $products;
	}
}
?>

<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaGenericExporterModelBase', 'genericexporter.models._base',  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));

class TiendaGenericExporterModelProducts extends TiendaGenericExporterModelBase
{
	public $_model = 'products';	
	public $_modelone 	= 'product';
	
	/**
	 * Method to set the model state
	 * can be override in the child class
	 * @param unknown_type $model
	 * @return array
	 */
	
	function _setModelState($model)
	{
		$app = JFactory::getApplication();		
		$ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');

		$state = array();	
		
		$filters = $this->getFilters();

		foreach($filters as $k=>$val)
		{			
			$state[$k] 	= $app->getUserStateFromRequest($ns.str_replace('filter_', '', $k), $k, '', '');
		}

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}
		return $state;
	}
	
	/**
	 * Method to get the colums of the model/table	
	 * @return array
	 * @see TiendaModelProducts->_buildQueryWhere()
	 */
	function getFilters()
	{	
		$filters = array();
		$filters['filter_id_from'] = 'Product ID From';
		$filters['filter_id_to'] = 'Product ID To';
		$filters['filter_name'] = JText::_('Product Name');	
		$filters['filter_price_from'] = 'Price From';
		$filters['filter_price_to'] = 'Price To';
		$filters['filter_quantity_from'] = 'Quantity From';
		$filters['filter_quantity_to'] = 'Quantity To';		
		$filters['filter_multicategory'] = 'Categories';
		$filters['filter_manufacturer_set'] = 'Manufacturers';				

		return $filters;
	}	
}



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

class TiendaGenericExporterModelBase extends JObject
{
	public $_model 		= '';
	public $_modelone 	= '';

	function getName()
	{
		return $this->_model;
	}

	/*
	 * Gets name of the model class suffix
	 */
	function getModelClass()
	{
		return $this->_model;
	}
	
	/**
	 * Method to retrieve an generic item name which will be usefull in XML tree
	 * @return string;
	 */
	function getSingleName()
	{
		return $this->_modelone;
	}
	
	/**	 
	 * Generic method to display the data from a model/query
	 * can be override in the child class
	 * @return array - array object
	 */
	function loadDataList()
	{	 
      	JModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_tienda/models');     
      	$model = JModel::getInstance($this->getName(),'TiendaModel');
      	$this->_setModelState($model);
    	$items = $model->getList($model);
 	
		return $items;
	}
	
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
		$state['limit']  	= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$state['limitstart'] = $app->getUserStateFromRequest($ns.'limitstart', 'limitstart', 0, 'int');
		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.'.$model->getTable()->getKeyName(), 'cmd');
		$state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'ASC', 'word');
		$state['filter']    = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
		$state['filter_enabled'] 	= $app->getUserStateFromRequest($ns.'enabled', 'filter_enabled', '', '');
		
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
	 * can be override in the child class
	 * @return array
	 */
	function getFilters()
	{		
		$filters = array();
		
		return $filters;
	}	
}

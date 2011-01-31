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

Tienda::load( 'TiendaReportPlugin', 'library.plugins.report' );

class plgTiendaReport_highestvolumeandspender extends TiendaReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = 'report_highestvolumeandspender';

	/**
	 * @var $default_model  string  Default model used by report
	 */
	var $default_model    = 'users';

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgTiendaReport_lowstock(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}

	/**
	 * Override parent::_getData() to set the direction of the product quantity
	 *
	 * @return objectlist
	 */
	function _getData()
	{
		$state = $this->_getState();
		$model = $this->_getModel();

		$query = $model->getQuery();

		$field = array();

		$queryStr = "";
        if(!empty($state['filter_date_from']))
        {
        	$queryStr .= " AND o.created_date >= '{$state['filter_date_from']}' ";
        }
        
		if(!empty($state['filter_date_to']))
        {
        	$queryStr .= " AND o.created_date <= '{$state['filter_date_to']}' ";  		
        }            	
		
		$field[] = "
            (
            SELECT 
                SUM(oi.orderitem_quantity)
            FROM
                #__tienda_orderitems AS oi
            WHERE 
            	oi.order_id IN (
            							SELECT o.order_id 
            							FROM #__tienda_orders as o 
            							WHERE 
            								o.user_id = tbl.id 
            							AND 
            								o.order_state_id = '17'
            								
										{$queryStr}
            							)     
            ) 
        AS volume ";
		$field[] = "
            (
            SELECT 
                SUM(o.order_total)
            FROM
                #__tienda_orders AS o
            WHERE 
            	o.user_id = tbl.id
            AND 
            	o.order_state_id = '17' 	
            ) 
        AS spent ";
		$query->order("spent DESC");
		$query->select( $field );
		$data = $model->getList();

		return $data;
	}

	/**
	 * Override parent::_getState() to do the filtering
	 *
	 * @return object
	 */
	function _getState()
	{
		$app = JFactory::getApplication();
		$model = $this->_getModel( 'users');
		$ns = $this->_getNamespace();

		$state = array();
		$state['filter_date_from'] = $app->getUserStateFromRequest($ns.'filter_date_from', 'filter_date_from', '', '');
		$state['filter_date_to'] = $app->getUserStateFromRequest($ns.'filter_date_to', 'filter_date_to', '', '');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		return $state;

	}
}

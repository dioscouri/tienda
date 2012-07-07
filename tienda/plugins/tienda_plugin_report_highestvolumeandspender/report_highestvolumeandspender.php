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
	var $default_model    = 'orders';

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
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, null, true);
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
		$model->setState( 'order', 'spent');
		$model->setState( 'direction', 'DESC');
		$query = $model->getQuery();
		$field = array();
		$field[] = " tbl.* ";
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
            								o.order_state_id IN ('2', '3', '5', '17' )
            							AND 
            								 o.user_id = tbl.user_id
										
            							) )
        		AS volume ";
		$field[] = "
            (
            SELECT 
                SUM(o.order_total)
            FROM
                #__tienda_orders AS o
            WHERE 
            	o.order_state_id IN ('2', '3', '5', '17' )
            AND 
            	o.user_id = tbl.user_id
            ) 
        AS spent ";		
		
		$field[] = "
            (
            SELECT 
                COUNT(*)
            FROM
                #__tienda_orders AS o
            WHERE 
            	o.order_state_id IN ('2', '3', '5', '17' )
            AND 
            	o.user_id = tbl.user_id
            ) 
        AS number_of_orders ";
		$query->select( $field );
		if (strlen($state['filter_totalpurchase_from']))
		{
			$query->having('volume >= '.(int) $state['filter_totalpurchase_from']);
		}
		if (strlen($state['filter_totalpurchase_to']))
		{
			$query->having('volume <= '.(int) $state['filter_totalpurchase_to']);
		}

		if (strlen($state['filter_totalspent_from']))
		{
			$query->having('spent >= '.(int) $state['filter_totalspent_from']);
		}
		if (strlen($state['filter_totalspent_to']))
		{
			$query->having('spent <= '.(int) $state['filter_totalspent_to']);
		}

		$query->group('tbl.user_id');
		$model->setQuery( $query );
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
		$model = $this->_getModel( 'orders');
		$ns = $this->_getNamespace();

		$state = array();
		$state['filter_date_from'] = $app->getUserStateFromRequest($ns.'filter_date_from', 'filter_date_from', '', '');
		$state['filter_date_to'] = $app->getUserStateFromRequest($ns.'filter_date_to', 'filter_date_to', '', '');
		$state['filter_totalpurchase_from'] = $app->getUserStateFromRequest($ns.'filter_totalpurchase_from', 'filter_totalpurchase_from', '', '');
		$state['filter_totalpurchase_to'] = $app->getUserStateFromRequest($ns.'filter_totalpurchase_to', 'filter_totalpurchase_to', '', '');
		$state['filter_totalspent_from'] = $app->getUserStateFromRequest($ns.'filter_totalspent_from', 'filter_totalspent_from', '', '');
		$state['filter_totalspent_to'] = $app->getUserStateFromRequest($ns.'filter_totalspent_to', 'filter_totalspent_to', '', '');
		

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		return $state;

	}
}

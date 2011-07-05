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

class plgTiendaReport_abandoned_cart extends TiendaReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = 'report_abandoned_cart';

	/**
	 * @var $default_model  string  Default model used by report
	 */
	var $default_model    = 'carts';

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
	function plgTiendaReport_abandoned_cart(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}

	/**
	 * Override parent::_getData() to sort products in users cart
	 *
	 * @return objectlist
	 */
	function _getData()
	{	
		$state = $this->_getState();
        $model = $this->_getModel();	
        $query = $model->getQuery();       
		$query->join('LEFT', '#__users AS u ON tbl.user_id = u.id');
		$query->where('tbl.user_id != 0');

		$query->select( 'u.*' );	
        $model->setQuery( $query );				
        $items = $model->getList();
		
		$data = array();
		$subtotals	 = array();
		$total_items = array();		
		foreach($items as $item) 
		{	
			if(empty($subtotals[$item->user_id])) $subtotals[$item->user_id] = 0;
			if(empty($total_items[$item->user_id])) $total_items[$item->user_id] = 0;
			$subtotals[$item->user_id] += (float) $item->product_price;	
			$total_items[$item->user_id] += (int) $item->product_qty;				
		}
			
		foreach($items as $item)
		{
			$data[$item->user_id] = $item;
			$data[$item->user_id]->subtotal = $subtotals[$item->user_id];
			$data[$item->user_id]->total_items = $total_items[$item->user_id];
		}

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
		$model = $this->_getModel();
		$ns = $this->_getNamespace();

		$state = parent::_getState(); // get the basic state values from the parent method
	

		$state['filter_name'] = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
        $state['filter_date_from'] = $app->getUserStateFromRequest($ns.'filter_date_from', 'filter_date_from', '', '');
		$state['filter_date_to'] = $app->getUserStateFromRequest($ns.'filter_date_to', 'filter_date_to', '', '');
        $state = $this->_handleRangePresets( $state );
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );
        }

        return $state;
    }

}

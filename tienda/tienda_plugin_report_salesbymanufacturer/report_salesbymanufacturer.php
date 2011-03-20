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

class plgTiendaReport_salesbymanufacturer extends TiendaReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'report_salesbymanufacturer';
    
    /**
     * @var $default_model  string  Default model used by report  
     */
    var $default_model    = 'orderitems';

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
	function plgTiendaReport_orderitems(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );	
	}
    
	/**
     * Override parent::_getData() 
     *  
     * @return unknown_type
     */
    function _getData()
    {
        $state = $this->_getState();
		$model = $this->_getModel();

        $model->setState( 'order', 'total_sales' );
        $model->setState( 'direction', 'DESC' );
        
        // filter only complete orders ( 3 - Shipped, 5 - Complete, 17 - Payment Received )        
        $order_states = array ( '3', '5', '17');
        $model->setState( 'filter_orderstates', $order_states );
        
		$query = $model->getQuery();
				
		// select the total quantity
        $field = array();
        $field[] = " SUM(tbl.orderitem_final_price) AS total_sales ";
        $field[] = " SUM(tbl.orderitem_quantity) AS sales_count ";
        $query->select( $field );
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
        $model = $this->_getModel( $this->get('default_model') );
        $ns = $this->_getNamespace();

        $state = parent::_getState(); // get the basic state values from the parent method

        // then add your own custom ones just for this report       
        $state['filter_manufacturer_name'] = $app->getUserStateFromRequest($ns.'manufacturer_name', 'filter_manufacturer_name', '', '');
        $state['filter_subscriptions_date_from'] = $app->getUserStateFromRequest($ns.'filter_subscriptions_date_from', 'filter_subscriptions_date_from', '', '');
        $state['filter_subscriptions_date_to'] = $app->getUserStateFromRequest($ns.'filter_subscriptions_date_to', 'filter_subscriptions_date_to', '', '');
        $state['filter_subscriptions_datetype'] = $app->getUserStateFromRequest($ns.'filter_subscriptions_datetype', 'filter_subscriptions_datetype', '', '');
      
        //$state = $this->_handleRangePresets( $state );
        
        // then apply the states to the model
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );
        }
        return $state;    
    }
}

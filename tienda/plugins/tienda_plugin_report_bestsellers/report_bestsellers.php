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

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );

Tienda::load( 'TiendaReportPlugin', 'library.plugins.report' );

class plgTiendaReport_bestsellers extends TiendaReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'report_bestsellers';
    
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
	function plgTiendaReport_bestsellers(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, null, true);
	}
	
    /**
     * Override parent::_getData() to insert groupBy and orderBy clauses into query
     *  setState
     * @return unknown_type
     */
    function _getData()
    {
        $state = $this->_getState();
        $model = $this->_getModel();
        
        // filter only complete orders ( 3 - Shipped, 5 - Complete, 17 - Payment Orders )
        $order_states = array ( '3', '5', '17');
        $model->setState( 'filter_orderstates', $order_states );
        
        $query = $model->getQuery();
        
        // group results by product ID
        $query->group('tbl.product_id');
        
        // select the total number of sales for each product
        $field = array();
        $field[] = " SUM(tbl.orderitem_quantity) AS total_sales ";
        $query->select( $field );
        
        // order results by the total sales
        $query->order('total_sales DESC');

        $model->setQuery( $query );
        $data = $model->getList();
                
        return $data;
    }
}

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

class plgTiendaReport_dailysales extends TiendaReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'report_dailysales';
    
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
	function plgTiendaReport_dailysales(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
    /**
     * Override parent::_getData() to insert groupBy and orderBy clauses into query
     *  
     * @return unknown_type
     */
    function _getData()
    {
        $state = $this->_getState();
        $model = $this->_getModel();
        
        // filter only complete orders ( 3 - Shipped, 5 - Complete, 17 - Payment Received )        
        $order_states = array ( '3', '5', '17');
        $model->setState( 'filter_orderstates', $order_states );
        
        $query = $model->getQuery();
        
        // order results by the total sales
        $query->order('order_total DESC');

        $model->setQuery( $query );
        $data = $model->getList();
                
        return $data;
    }
}

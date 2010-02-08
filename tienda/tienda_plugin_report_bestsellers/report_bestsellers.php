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

JLoader::import( 'com_tienda.library.plugins.report', JPATH_ADMINISTRATOR.DS.'components' );

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

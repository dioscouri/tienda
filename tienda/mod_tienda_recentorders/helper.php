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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
Tienda::load( 'TiendaQuery', 'library.query' );

class modTiendaRecentOrdersHelper extends TiendaHelperBase
{
	var $_orders = null;
	
	var $_params = null;
	
	/**
	 * Constructor to set the object's params
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	function __construct( $params )
	{
        parent::__construct();
        $this->_params = $params;	
	}
	
	/**
	 * Gets the sales statistics object, 
	 * creating it if it doesn't exist
	 * 
	 * @return unknown_type
	 */
	function getOrders()
	{
		if (empty($this->_orders))
		{
			$this->_orders = $this->_orders();
		}
		return $this->_orders;
	}
	
    /**
     * _lastfive function.
     * 
     * @access private
     * @return void
     */
    function _orders()
    {
        jimport( 'joomla.application.component.model' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setState( 'order', 'tbl.created_date' );
        $model->setState( 'direction', 'DESC' );
        $model->setState( 'limit', $this->_params->get('num_orders', '5') );
        $model->setState( 'limitstart', '0' );
        
        $csv = TiendaConfig::getInstance()->get('orderstates_csv', '2, 3, 5, 17');
        $array = explode(',', $csv);
        $this->_statesCSV = "'".implode("','", $array)."'";
        // set query for orderstate range
        $ordersQuery = $model->getQuery();
        $ordersQuery->where("tbl.order_state_id IN (".$this->_statesCSV.")");
        $model->setQuery($ordersQuery);
            
        $return = $model->getList();
        return $return;
    }
}
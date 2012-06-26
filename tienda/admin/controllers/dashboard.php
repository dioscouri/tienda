<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerDashboard extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'dashboard');
	}

	public function display($cachable=false, $urlparams = false)
	{
	    $model = $this->getModel( $this->get('suffix') );
	    $state = $model->getState();
	    $state->stats_interval = JRequest::getVar('stats_interval', 'last_thirty');
	    $model->setState('stats_interval', $state->stats_interval);

	    $cache = JFactory::getCache('com_tienda');
	    $cache->setCaching(true);
	    $cache->setLifeTime('900');
	    $orders = $cache->call(array($model, 'getOrdersChartData'), $state->stats_interval);
	    $revenue = $cache->call(array($model, 'getRevenueChartData'), $state->stats_interval);
	    $total = $cache->call(array($model, 'getSumChartData'), $orders);
	    $sum = $cache->call(array($model, 'getSumChartData'), $revenue);
	    	    
        $interval = $model->getStatIntervalValues($state->stats_interval);

	    $view = $this->getView( $this->get('suffix'), 'html' );
	    $view->assign( 'orders', $orders );
	    $view->assign( 'revenue', $revenue );
        $view->assign( 'total', $total );
        $view->assign( 'sum', $sum );
        $view->assign( 'interval', $interval );        
                
	    parent::display($cachable, $urlparams);
	}
	
	function search()
	{
	    $filter = JRequest::getVar('tienda_search_admin_keyword');
	    $filter_view = JRequest::getCmd('tienda_search_admin_view');
	    
	    $redirect = "index.php?option=com_tienda&view=" . $filter_view . "&filter=" . urlencode( $filter );
	    
	    JFactory::getApplication()->redirect( $redirect );
	}
}

?>
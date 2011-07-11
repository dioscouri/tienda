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
		$order_states = array ( '3', '5', '17');
		$filter_date_from	= $state['filter_date_from'];
        $filter_date_to		= $state['filter_date_to'];
		 if (empty($filter_date_to) and empty($filter_date_from))
		 {
			$date = JFactory::getDate();
			$today = $date->toFormat( "%Y-%m-%d 00:00:00" );
			$filter_date_to=$today;
			$database = JFactory::getDBO();
			$query = " SELECT DATE_SUB('".$today."', INTERVAL 1 MONTH) ";
			$database->setQuery( $query );
			$filter_date_from = $database->loadResult();
		 } else if (empty($filter_date_to) and !empty($filter_date_from))
		 {
			$filter_date_to=$filter_date_from;
		 }
		
		$date_tmp = date_create($filter_date_to);
		date_modify($date_tmp, '24 hour');	
		$enddate= date_format($date_tmp, 'Y-m-d H:i:s');

		$curdate=$filter_date_from;
		$database = JFactory::getDBO();
        while ($curdate < $enddate)
        {
			// set working variables
            $variables = TiendaHelperBase::setDateVariables( $curdate, $enddate, 'daily' );
            $thisdate = $variables->thisdate;
            $nextdate = $variables->nextdate;

			$query = new TiendaQuery();
			$query->select( 'COUNT(*) AS num, SUM(order_total) AS amount' );
			$query->from('#__tienda_orders AS tbl');

			$query->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$query->where("tbl.created_date >= '".$curdate."'");
			$query->where("tbl.created_date <= '".$nextdate."'");
			$database->setQuery( (string) $query );
			$return_daily_report = $database->loadObject();
			
			$date_tmp = date_create($curdate);
			$data_print= date_format($date_tmp, 'd-m-Y');


			$return_range_report->$data_print =$return_daily_report;

			// increase curdate to the next value
            $curdate = $nextdate;
		}
return $return_range_report;
/*

$database = JFactory::getDBO();
           // $model = JModel::getInstance( 'Orders', 'TiendaModel' );
           // $model->setState( 'filter_date_from', $thisdate );
           // $model->setState( 'filter_date_to', $nextdate );
            // set query for orderstate range
            $ordersQuery = $model->getQuery();
            //$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
            $model->setQuery($ordersQuery);
            $rows = $model->getList();

            $total = count( $rows );
            $model->setState('select', 'SUM(`order_total`)');
            $ordersQuery = $model->getQuery();
            //$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
            $model->setQuery($ordersQuery);
            $sum = $model->getList();

        //$sum = $database->loadObject();
			

		fb($ordersQuery,'ordersQuery');
		
		return $sum;*/

    }
	    function setStatesCSV( $csv='' )
    {
        if (empty($csv))
        {
            $csv = TiendaConfig::getInstance()->get('orderstates_csv', '2, 3, 5, 17');
        }
        
        $array = explode(',', $csv);
        $this->_statesCSV = "'".implode("','", $array)."'";
    }
    
    /**
     * Get the CSV of states to be reported on
     * @return unknown_type
     */
    function getStatesCSV()
    {
        if (empty($this->_statesCSV))
        {
            $this->setStatesCSV();
        }
        
        return $this->_statesCSV;
    }
	
	
}

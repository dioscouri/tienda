<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaViewBase', 'views._base' );
Tienda::load( 'TiendaGrid', 'library.grid' );

class TiendaViewDashboard extends TiendaViewBase
{
	/**
	 * @var string a CSV of order_state_ids that you want dashboard to report on
	 * default: '2','3','5','17' = cash in hand
	 */
	var $_statesCSV = null;

	/**
	 * Set the CSV of states to be reported on
	 * @param $csv
	 * @return unknown_type
	 */
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

	/**
	 *
	 *
	 * @access public
	 * @param mixed $tpl. (default: null)
	 * @return void
	 */
	function getLayoutVars($tpl=null)
	{
		Tienda::load( 'TiendaHelperCategory', 'helpers.category' );
		Tienda::load( 'TiendaGrid', 'library.grid' );
		Tienda::load( 'TiendaSelect', 'library.select' );
		if (empty($this->hidestats))
		{
			// properly format the statesCSV
			$this->setStatesCSV();
			 
			$model = $this->getModel();
			$state = $model->getState();
			$state->stats_interval = JRequest::getVar('stats_interval', 'last_thirty');
			 
			// set the model state
			$this->assign( 'state', $state );
			 
			$stats_interval = $state->stats_interval;
			 
			switch($stats_interval)
			{
				case 'today':
					$this->_today();
					break;
				case 'yesterday':
					$this->_yesterday();
					break;
				case 'last_seven':
					$this->_lastSeven();
					break;
				case 'ytd':
					$this->_thisYear();
					break;
				case 'years':
					$this->_theseYears();
					break;
				case 'last_thirty':
				default:
					$this->_lastThirty();
					break;
			}
		}

		// form
		$validate = JUtility::getToken();
		$form = array();
		$controller = strtolower( $this->get( '_controller', JRequest::getVar('controller', JRequest::getVar('view') ) ) );
		$view = strtolower( $this->get( '_view', JRequest::getVar('view') ) );
		$action = $this->get( '_action', "index.php?option=com_tienda&controller={$controller}&view={$view}" );
		$form['action'] = $action;
		$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
		$this->assign( 'form', $form );
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _lastThirty()
	{
		$database = JFactory::getDBO();
		$base = new TiendaHelperBase();
		$date = JFactory::getDate();
		$today = $date->toFormat( "%Y-%m-%d 23:59:59" );
		$today=TiendaHelperBase::local_to_GMT_data($today);
		$end_datetime = $today;
		$query = " SELECT DATE_SUB('".$today."', INTERVAL 1 MONTH) ";
		$database->setQuery( $query );
		$start_datetime = $database->loadResult();

		$runningtotal = 0;
		$runningsum = 0;
		$data = new stdClass();
		$num = 0;
		$result = array();
		$curdate=date_create($start_datetime);
		date_time_set($curdate, 00, 00, 00);
		$curdate= date_format($curdate, 'Y-m-d H:i:s');

		$enddate = $end_datetime;
		while ($curdate <= $enddate)
		{
			// set working variables
			$variables = TiendaHelperBase::setDateVariables( $curdate, $enddate, 'daily' );
			$thisdate = $variables->thisdate;
			$nextdate = $variables->nextdate;

			// grab all records
			$model = JModel::getInstance( 'Orders', 'TiendaModel' );
			$model->setState( 'filter_date_from', $thisdate );
			$model->setState( 'filter_date_to', $nextdate );
			// set query for orderstate range
			$ordersQuery = $model->getQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setQuery($ordersQuery);
			$rows = $model->getList();

			$total = count( $rows );
			$model->setState('select', 'SUM(`order_total`)');
			$ordersQuery = $model->getResultQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setResultQuery($ordersQuery);
			$sum = $model->getResult();

			//store the value in an array
			$result[$num]['rows']       = $rows;
			$result[$num]['datedata']   = getdate( strtotime($thisdate) );
			$result[$num]['countdata']  = $total;
			$value = TiendaHelperBase::number( $sum );
			$result[$num]['sumdata']    = empty($value) ? $sum : $value;
			$runningtotal               = $runningtotal + $total;
			$runningsum                 = $runningsum + $sum;

			// increase curdate to the next value
			$curdate = $nextdate;
			$num++;

		} // end of the while loop

		$data->rows         = $result;          // Array
		$data->total        = $runningtotal;    // Int
		$data->sum          = $runningsum;      // Int

		$this->getChartDaily( $data, 'Last Thirty Days', 'graph' );
		$this->getChartDaily( $data, 'Last Thirty Days', 'graphSum', 'sumdata', 'Line' );

		$this->assign( 'graphData', $data );
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _today()
	{
		$base = new TiendaHelperBase();
		$date = JFactory::getDate();
		$today_strg=$date->toFormat( "%Y-%m-%d" );
		$runningtotal = 0;
		$runningsum = 0;
		$data = new stdClass();
		$num = 0;
		$result = array();

		for ($curhour = 0; $curhour < 24; $curhour++)
		{
			$thishour = $curhour < 10 ? '0'.$curhour : $curhour;
				
			$start_ts=$date->toFormat( "%Y-%m-%d $thishour:00:00" );
			$end_ts=$date->toFormat( "%Y-%m-%d $thishour:59:59" );
			// grab all records
			$model = JModel::getInstance( 'Orders', 'TiendaModel' );
			$model->setState( 'filter_date_from', $start_ts );
			$model->setState( 'filter_date_to', $end_ts );
			// set query for orderstate range
			$ordersQuery = $model->getQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setQuery($ordersQuery);
			$rows = $model->getList();

			$total = count( $rows );
			$model->setState('select', 'SUM(`order_total`)');
			$ordersQuery = $model->getResultQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setResultQuery($ordersQuery);
			$sum = $model->getResult();

			//store the value in an array
			$result[$num]['rows']       = $rows;
			//$result[$num]['datedata']   = getdate( strtotime($thisdate) );
			$result[$num]['datedata']   = $thishour.':00';
			$result[$num]['countdata']  = $total;
			$value = TiendaHelperBase::number( $sum );
			$result[$num]['sumdata']    = empty($value) ? $sum : $value;
			$runningtotal               = $runningtotal + $total;
			$runningsum                 = $runningsum + $sum;

			$num++;

		} // end of the while loop

		$data->rows         = $result;          // Array
		$data->total        = $runningtotal;    // Int
		$data->sum          = $runningsum;      // Int

		$this->getChartHourly( $data,  JText::sprintf('COM_TIENDA_DASHBOARD_TODAY', $today_strg), 'graph' );
		$this->getChartHourly( $data,  JText::sprintf('COM_TIENDA_DASHBOARD_TODAY', $today_strg), 'graphSum', 'sumdata', 'Line' );


		$this->assign( 'graphData', $data );

		return;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _yesterday()
	{
		$base = new TiendaHelperBase();
		$date = JFactory::getDate();
		$date->setOffset( -24 );
		$yesterday_strg=$date->toFormat( "%Y-%m-%d" );


		$runningtotal = 0;
		$runningsum = 0;
		$data = new stdClass();
		$num = 0;
		$result = array();

		for($curhour = 0; $curhour < 24; $curhour++)
		{
			$thishour = $curhour<10?'0'.$curhour:$curhour;
			$start_ts=$date->toFormat( "%Y-%m-%d $thishour:00:00" );
			$end_ts=$date->toFormat( "%Y-%m-%d $thishour:59:59" );

			// grab all records
			$model = JModel::getInstance( 'Orders', 'TiendaModel' );
			$model->setState( 'filter_date_from', $start_ts );
			$model->setState( 'filter_date_to', $end_ts );
			// set query for orderstate range
			$ordersQuery = $model->getQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setQuery($ordersQuery);
			$rows = $model->getList();

			$total = count( $rows );
			$model->setState('select', 'SUM(`order_total`)');
			$ordersQuery = $model->getResultQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setResultQuery($ordersQuery);
			$sum = $model->getResult();

			//store the value in an array
			$result[$num]['rows']       = $rows;
			//$result[$num]['datedata']   = getdate( strtotime($thisdate) );
			$result[$num]['datedata']   = $thishour.':00';
			$result[$num]['countdata']  = $total;
			$value = TiendaHelperBase::number( $sum );
			$result[$num]['sumdata']    = empty($value) ? $sum : $value;
			$runningtotal               = $runningtotal + $total;
			$runningsum                 = $runningsum + $sum;

			$num++;

		} // end of the while loop

		$data->rows         = $result;          // Array
		$data->total        = $runningtotal;    // Int
		$data->sum          = $runningsum;      // Int

		$this->getChartHourly( $data, JText::sprintf('COM_TIENDA_DASHBOARD_YESTERDAY', $yesterday_strg), 'graph' );
		$this->getChartHourly( $data, JText::sprintf('COM_TIENDA_DASHBOARD_YESTERDAY', $yesterday_strg), 'graphSum', 'sumdata', 'Line' );

		$this->assign( 'graphData', $data );

		return;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _lastSeven()
	{
		$database = JFactory::getDBO();
		$base = new TiendaHelperBase();
		$date = JFactory::getDate();
		$today = $date->toFormat( "%Y-%m-%d 23:59:59" );
		$today=TiendaHelperBase::local_to_GMT_data($today);
		$end_datetime = $today;
		$query = " SELECT DATE_SUB('".$today."', INTERVAL 6 DAY) ";
		$database->setQuery( $query );
		$start_datetime = $database->loadResult();

		$runningtotal = 0;
		$runningsum = 0;
		$data = new stdClass();
		$num = 0;
		$result = array();
		$enddate = $end_datetime;
		$curdate=date_create($start_datetime);
		date_time_set($curdate, 00, 00, 00);
		$curdate= date_format($curdate, 'Y-m-d H:i:s');
		while ($curdate <= $enddate)
		{
			// set working variables
			$variables = TiendaHelperBase::setDateVariables( $curdate, $enddate, 'daily' );
			$thisdate = $variables->thisdate;
			$nextdate = $variables->nextdate;

			// grab all records
				
			$model = JModel::getInstance( 'Orders', 'TiendaModel' );
			$model->setState( 'filter_date_from', $thisdate );
			$model->setState( 'filter_date_to', $nextdate );
			// set query for orderstate range
			$ordersQuery = $model->getQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setQuery($ordersQuery);
			$rows = $model->getList();

			$total = count( $rows );
			$model->setState('select', 'SUM(`order_total`)');
			$ordersQuery = $model->getResultQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setResultQuery($ordersQuery);
			$sum = $model->getResult();
				
			//store the value in an array
			$result[$num]['rows']       = $rows;
			$result[$num]['datedata']   = getdate( strtotime($thisdate) );
			$result[$num]['countdata']  = $total;
			$value = TiendaHelperBase::number( $sum );
			$result[$num]['sumdata']    = empty($value) ? $sum : $value;
			$runningtotal               = $runningtotal + $total;
			$runningsum                 = $runningsum + $sum;

			// increase curdate to the next value
			$curdate = $nextdate;
			$num++;

		} // end of the while loop

		$data->rows         = $result;          // Array
		$data->total        = $runningtotal;    // Int
		$data->sum          = $runningsum;      // Int

		$this->getChartDaily( $data, JText::sprintf('COM_TIENDA_LAST_SEVEN_DAYS'), 'graph' );
		$this->getChartDaily( $data, JText::sprintf('COM_TIENDA_LAST_SEVEN_DAYS'), 'graphSum', 'sumdata', 'Line' );

		$this->assign( 'graphData', $data );
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _thisYear()
	{
		$year = gmdate('Y');
		 
		$base = new TiendaHelperBase();
		$database = JFactory::getDBO();

		$runningtotal = 0;
		$runningsum = 0;
		$data = new stdClass();
		$num = 0;
		$result = array();

		for ($curmonth = 1; $curmonth <= 12; $curmonth++)
		{
			$thismonth = $curmonth < 10 ? '0'.$curmonth : $curmonth;
			$start_ts = $year."-".$thismonth."-01 00:00:00";
			$database->setQuery("SELECT ADDDATE('".$start_ts."', INTERVAL 1 MONTH)");

			// grab all records
			$model = JModel::getInstance( 'Orders', 'TiendaModel' );
			$model->setState( 'filter_date_from', $start_ts );
			$model->setState( 'filter_date_to', $database->loadResult().' 00:00:00' );
			// set query for orderstate range
			$ordersQuery = $model->getQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setQuery($ordersQuery);
			$rows = $model->getList();

			$total = count( $rows );
			$model->setState('select', 'SUM(`order_total`)');
			$ordersQuery = $model->getResultQuery();
			$ordersQuery->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
			$model->setResultQuery($ordersQuery);
			$sum = $model->getResult();

			//store the value in an array
			$result[$num]['rows']       = $rows;
			//$result[$num]['datedata']   = getdate( strtotime($thisdate) );
			$result[$num]['datedata']   = $thismonth;
			$result[$num]['countdata']  = $total;
			$value = TiendaHelperBase::number( $sum );
			$result[$num]['sumdata']    = empty($value) ? $sum : $value;
			$runningtotal               = $runningtotal + $total;
			$runningsum                 = $runningsum + $sum;

			$num++;

		} // end of the while loop

		$data->rows         = $result;          // Array
		$data->total        = $runningtotal;    // Int
		$data->sum          = $runningsum;      // Int

		$this->getChartHourly( $data, JText::sprintf('COM_TIENDA_YEAR_TO_DATE_MONTHLY'), 'graph' );
		$this->getChartHourly( $data, JText::sprintf('COM_TIENDA_YEAR_TO_DATE_MONTHLY'), 'graphSum', 'sumdata', 'Line' );

		$this->assign( 'graphData', $data );

		return;
	}

	function _theseYears()
	{
		Tienda::load( 'TiendaHelperOrder','helpers.order' );
		$firstsale_date = TiendaHelperOrder::getDateMarginalOrder( $this->getStatesCSV(), 'ASC' );
		$lastsale_date = TiendaHelperOrder::getDateMarginalOrder( $this->getStatesCSV(), 'DESC' );
		$start_year = getdate( strtotime( $firstsale_date ) );
		$end_year = getdate( strtotime( $lastsale_date ) );

		$runningtotal = 0;
		$runningsum = 0;
		$data = new stdClass();
		$num = 0;
		$result = array();
		$database = JFactory::getDBO();
		$orderstates = explode( ',', $this->getStatesCSV() );
		
		for ($curyear_local = $start_year['year']; $curyear_local <= $end_year['year']; $curyear_local++)
		{
			$curyear_gmt = TiendaHelperBase::local_to_GMT_data( "$curyear_local-01-01 00:00:00" );
			$database->setQuery("SELECT ADDDATE('".$curyear_gmt."', INTERVAL 1 YEAR)");
			$nextyear_gmt = $database->loadResult();
				
			// grab all records
			$model = JModel::getInstance( 'Orders', 'TiendaModel' );
			$model->setState( 'filter_date_from', $curyear_gmt );
			$model->setState( 'filter_date_to', $nextyear_gmt );
			$model->setState( 'filter_orderstates', $orderstates );
			$rows = $model->getList();

			$total = count( $rows );
			$model->setState('select', 'SUM(`order_total`)');
			$sum = $model->getResult();
				
			//store the value in an array
			$result[$num]['rows']       = $rows;
			$result[$num]['datedata']   = $curyear_local;
			$result[$num]['countdata']  = $total;
			$value = TiendaHelperBase::number( $sum );
			$result[$num]['sumdata']    = empty($value) ? $sum : $value;
			$runningtotal               = $runningtotal + $total;
			$runningsum                 = $runningsum + $sum;

			$num++;
				
		} // end of the while loop

		$data->rows         = $result;          // Array
		$data->total        = $runningtotal;    // Int
		$data->sum          = $runningsum;      // Int

		$this->getChartHourly( $data, JText::sprintf('COM_TIENDA_FROM_FIRST_SALE_YEARLY'), 'graph' );
		$this->getChartHourly( $data, JText::sprintf('COM_TIENDA_FROM_FIRST_SALE_YEARLY'), 'graphSum', 'sumdata', 'Line' );

		$this->assign( 'graphData', $data );

		return;
	}

	/**
	 * getChartDaily function.
	 *
	 * @access public
	 * @param mixed $data
	 * @param mixed $chart_title
	 * @param mixed $variable_name
	 * @param string $type. (default: 'countdata')
	 * @param string $chart_type. (default: 'Column')
	 * @return void
	 */
	function getChartDaily( $data, $chart_title, $variable_name, $type='countdata', $chart_type='Column' )
	{
		 
		$args = array();

		/** Tienda Charts expect data to come as an array of objects where the objects
		 *  look like:
		 *  JObject(){
		 *     public $value;
		 *     public $label;
		 *  }
		 */
		$args['data'] = array();

		if (!empty($data)) {
			foreach ($data->rows as $r) {
				$obj = new JObject;
				$obj->value = floatval(str_replace(',', '', $r[$type]));
				$obj->label = $r['datedata']['mon'].'/'.$r['datedata']['mday'];
				$args['data'][] = $obj;
			}
		}

		$args['title'] = $chart_title;
		$args['type']  = $chart_type;

		// Try to render the chart via an installed plugin first.
		$dispatcher =& JDispatcher::getInstance();
		$results = $dispatcher->trigger('renderTiendaChart', $args);

		if (empty($results)) {
			// No Tienda Charts plugin enabled, use Google Charts.
			Tienda::load( 'TiendaCharts', 'library.charts' );
			$chart = TiendaCharts::renderGoogleChart($args['data'], $chart_title, $chart_type);
		} else {
			$chart = $results[0];
		}

		$row = new JObject();
		$row->image = $chart;
		$this->assign( "$variable_name", $row);
	}

	/**
	 *
	 * @param unknown_type $data
	 * @param unknown_type $chart_title
	 * @param unknown_type $variable_name
	 * @return unknown_type
	 */
	function getChartHourly( $data, $chart_title, $variable_name, $type='countdata', $chart_type='Column' )
	{
		$args = array();

		/** Tienda Charts expect data to come as an array of objects where the objects
		 *  look like:
		 *  JObject(){
		 *     public $value;
		 *     public $label;
		 *  }
		 */
		$args['data'] = array();

		if (!empty($data))
		{
			foreach ($data->rows as $r)
			{
				$obj = new JObject;
				$obj->value = floatval(str_replace(',', '', $r[$type]));
				$obj->label = $r['datedata'];
				//$obj->label = $r['datedata']['mon'].'/'.$r['datedata']['mday'];
				$args['data'][] = $obj;
			}
		}

		$args['title'] = $chart_title;
		$args['type']  = $chart_type;

		// Try to render the chart via an installed plugin first.
		$dispatcher =& JDispatcher::getInstance();
		$results = $dispatcher->trigger('renderTiendaChart', $args);

		if (empty($results)) {
			// No Tienda Charts plugin enabled, use Google Charts.
			Tienda::load( 'TiendaCharts', 'library.charts' );
			$chart = TiendaCharts::renderGoogleChart($args['data'], $chart_title, $chart_type);
		} else {
			$chart = $results[0];
		}

		$row = new JObject();
		$row->image = $chart;
		$this->assign( "$variable_name", $row);

		return;
	}
}
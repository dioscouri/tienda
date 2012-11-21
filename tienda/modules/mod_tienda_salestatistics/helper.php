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

class modTiendaSaleStatisticsHelper extends TiendaHelperBase
{
	var $_stats = null;
	var $_params = null;

	/**
	 * @var string a CSV of order_state_ids that you want dashboard to report on
	 * default: '2','3','5','17' = cash in hand
	 */
	var $_statesCSV = null;

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
		// properly format the statesCSV
		$this->setStatesCSV();
	}

	/**
	 * Set the CSV of states to be reported on
	 * @param $csv
	 * @return unknown_type
	 */
	function setStatesCSV( $csv='' )
	{
		if (empty($csv))
		{
			$csv = Tienda::getInstance()->get('orderstates_csv', '2, 3, 5, 17');
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
	 * Gets the sales statistics object,
	 * creating it if it doesn't exist
	 *
	 * @return unknown_type
	 */
	function getStatistics()
	{
		if (empty($this->_stats))
		{
			$this->_stats = $this->_statistics();
		}
		return $this->_stats;
	}

	/**
	 * _statistics function.
	 *
	 * @access private
	 * @return void
	 */
	function _statistics()
	{
		$stats = new JObject();
		$stats->link = "index.php?option=com_tienda&view=orders&task=list&filter_order=tbl.created_date&filter_direction=DESC";

		$stats->lifetime = $this->_lifetime();
		$stats->today = $this->_today();
		$stats->yesterday = $this->_yesterday();
		$stats->lastseven = $this->_lastSeven();
		$stats->lastmonth = $this->_lastMonth();
		$stats->thismonth = $this->_thisMonth();
		$stats->lastyear = $this->_lastYear();
		$stats->thisyear = $this->_thisYear();

		return $stats;
	}

	/**
	 * _today function.
	 *
	 * @access private
	 * @return void
	 */
	function _today()
	{		
		return $this->_getDateDb( TiendaHelperBase::getCorrectBeginDayTime( JFactory::getDate() ), '', true, false, false );
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _yesterday()
	{
		$database = JFactory::getDBO();
		$end_date = TiendaHelperBase::getCorrectBeginDayTime( JFactory::getDate() );

		$query = new TiendaQuery();
		$query = " SELECT DATE_SUB('".$end_date."', INTERVAL 1 DAY) ";
		$database->setQuery( $query );
		$start_date = $database->loadResult();
		return $this->_getDateDb( $start_date, $end_date, true, true, false );
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _lastSeven()
	{
		$database = JFactory::getDBO();
		$enddate = TiendaHelperBase::getCorrectBeginDayTime( JFactory::getDate() );

		$query = new TiendaQuery();
		$query = " SELECT DATE_SUB('".$enddate."', INTERVAL 7 DAY) ";
		$database->setQuery( $query );
		$startdate = $database->loadResult();
		$return = $this->_getDateDb( $startdate, $enddate,  true, true );
		
		$days = ($return->days_in_business > 0) ? $return->days_in_business : 1;
		$return->average_daily = $return->num / $days;
		return $return;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _thisMonth()
	{
		$database = JFactory::getDBO();

		$date = JFactory::getDate(); //get local data
		$today = $date->toFormat( "%Y-%m-%d %H:%M:%S" );
		$start_month = $date->toFormat( "%Y-%m-01 00:00:00" );
		$startdate = TiendaHelperBase::getCorrectBeginDayTime( $start_month );
		
		$return = $this->_getDateDb( $startdate, $today, true );

		$days = ($return->days_in_business > 0) ? $return->days_in_business : 1;
		$return->average_daily = $return->num / $days;
		return $return;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _lastMonth()
	{
		$database = JFactory::getDBO();

		$date = JFactory::getDate(); //get local data
		//first day of month
		$last_day = $date->toFormat( "%Y-%m-01 00:00:00" );
		$enddate = TiendaHelperBase::getCorrectBeginDayTime( $last_day );

		$query = new TiendaQuery();
		$query = " SELECT DATE_SUB('".$last_day."', INTERVAL 1 MONTH) ";
		$database->setQuery( $query );
		$first_day = $database->loadResult();				
		$startdate = TiendaHelperBase::getCorrectBeginDayTime( $first_day );
		$return = $this->_getDateDb( $startdate, $enddate, true, true );

		$days = ($return->days_in_business > 0) ? $return->days_in_business : 1;
		$return->average_daily = $return->num / $days;
		return $return;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _thisYear()
	{
		$database = JFactory::getDBO();

		$date = JFactory::getDate(); //get local data
		$today = $date->toFormat( "%Y-%m-%d %H:%M:%S" );
		$start_year = $date->toFormat( "%Y-01-01 00:00:00" );
		$startdate = TiendaHelperBase::getCorrectBeginDayTime( $start_year );

		$return = $this->_getDateDb( $startdate, $today, true );
		$days = ($return->days_in_business > 0) ? $return->days_in_business : 1;

		$return->average_daily = $return->num / $days;
		return $return;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _lastYear()
	{
		$database = JFactory::getDBO();

		$date = JFactory::getDate(); //get local data
		$today = TiendaHelperBase::getCorrectBeginDayTime( $date );

		//first day of year
		$first_day_year = $date->toFormat( "%Y-01-01 00:00:00" );
		$enddate = TiendaHelperBase::getCorrectBeginDayTime( $first_day_year );

		$query = new TiendaQuery();
		$query = " SELECT DATE_SUB('".$enddate."', INTERVAL 1 YEAR) ";
		$database->setQuery( $query );
		$startdate = $database->loadResult();

		$return = $this->_getDateDb( $startdate, $enddate, true, true );
		$days = ($return->days_in_business > 0) ? $return->days_in_business : 1;
		$return->average_daily = $return->num / $days;

		return $return;
	}

	/**
	 * _lifetime function.
	 *
	 * @access private
	 * @return void
	 */
	function _lifetime()
	{
		$database = JFactory::getDBO();
		$today = TiendaHelperBase::getToday();

		Tienda::load( 'TiendaHelperOrder','helpers.order' );
		$firstsale_date = TiendaHelperOrder::getDateMarginalOrder( $this->getStatesCSV(), 'ASC' );
		$lastsale_date = TiendaHelperOrder::getDateMarginalOrder( $this->getStatesCSV(), 'DESC' );
		
		$return = $this->_getDateDb( $firstsale_date, $lastsale_date );
		$days = ($return->days_in_business > 0) ? $return->days_in_business : 1;
		$return->average_daily = $return->num / $days;
		return $return;
	}

	function _getDateDb( $start_date, $end_date, $restrict_start = false, $restrict_end = false, $count_days = true )
	{
		$db = JFactory::getDbo();
		
		$q = new TiendaQuery();
		$q->select( 'COUNT(*) AS num' );
		$q->select( 'SUM(order_total) AS amount' );
		$q->select( 'AVG(order_total) AS average' );
		if( $count_days )
			$q->select( "DATEDIFF('{$end_date}','{$start_date}') AS days_in_business" );
		$q->from('#__tienda_orders AS tbl');
		$q->where("tbl.order_state_id IN (".$this->getStatesCSV().")");
		if( $restrict_end )
			$q->where("tbl.created_date < '$end_date'");
		if( $restrict_start )
			$q->where("tbl.created_date >= '$start_date'");
		$db->setQuery( (string) $q);
		return $db->loadObject();
	}
}
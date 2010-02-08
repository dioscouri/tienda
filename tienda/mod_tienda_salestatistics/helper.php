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

JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );
JLoader::import( 'com_tienda.library.query', JPATH_ADMINISTRATOR.DS.'components' );

class modTiendaSaleStatisticsHelper extends TiendaHelperBase
{
	var $_stats = null;
    var $_params = null;

    /**
     * @var string a CSV of order_state_ids that you want dashboard to report on
     * default: '2','3','5','17' = cash in hand
     */
    var $_statesCSV = "'2','3','5','17'";
    
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
        $database = JFactory::getDBO();
        $today = TiendaHelperBase::getToday();

        $startdate = $today;

        $query = new TiendaQuery();
        $query->select( 'COUNT(*) AS num, SUM(order_total) AS amount' );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");
        $query->where("tbl.created_date >= '$startdate'");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();

        return $return;
    }

    /**
     *
     * @return unknown_type
     */
    function _yesterday()
    {
        $database = JFactory::getDBO();
        $today = TiendaHelperBase::getToday();

        $query = new TiendaQuery();
        $query->select( 'COUNT(*) AS num, SUM(order_total) AS amount' );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");
        $query->where("tbl.created_date >= DATE_SUB('".$today."', INTERVAL 1 DAY)");
        $query->where("tbl.created_date < '$today'");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();
        return $return;
    }

    /**
     *
     * @return unknown_type
     */
    function _lastSeven()
    {
        $database = JFactory::getDBO();
        $today = TiendaHelperBase::getToday();

        $enddate = $today;
        $query = " SELECT DATE_SUB('".$today."', INTERVAL 1 DAY) ";
        $database->setQuery( $query );
        $startdate = $database->loadResult();

        $query = new TiendaQuery();
        $query->select( 'COUNT(*) AS num, SUM(order_total) AS amount' );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");
        $query->where("tbl.created_date >= DATE_SUB('".$today."', INTERVAL 6 DAY)");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();
        return $return;
    }

    /**
     *
     * @return unknown_type
     */
    function _thisMonth()
    {
        $database = JFactory::getDBO();
        $today = TiendaHelperBase::getToday();

        $start = getdate( strtotime($today) );
        // first day of month
        $startdate = date("Y-m-d", strtotime($start['year']."-".$start['mon']."-01"));

        $query = new TiendaQuery();
        $query->select( 'COUNT(*) AS num, SUM(order_total) AS amount' );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");
        $query->where("tbl.created_date >= '$startdate'");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();
        return $return;
    }

    /**
     *
     * @return unknown_type
     */
    function _lastMonth()
    {
        $database = JFactory::getDBO();
        $today = TiendaHelperBase::getToday();

        $start = getdate( strtotime($today) );
        // first day of month
        $enddate = date("Y-m-d", strtotime($start['year']."-".$start['mon']."-01"));

        $query = new TiendaQuery();
        $query->select( 'COUNT(*) AS num, SUM(order_total) AS amount' );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");
        $query->where("tbl.created_date < '$enddate'");
        $query->where("tbl.created_date >= DATE_SUB('".$enddate."', INTERVAL 1 MONTH)");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();
        return $return;
    }

    /**
     *
     * @return unknown_type
     */
    function _thisYear()
    {
        $database = JFactory::getDBO();
        $today = TiendaHelperBase::getToday();

        $start = getdate( strtotime($today) );
        // first day of year
        $startdate = date("Y-m-d", strtotime($start['year']."-01-01"));

        $query = new TiendaQuery();
        $query->select( 'COUNT(*) AS num, SUM(order_total) AS amount' );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");
        $query->where("tbl.created_date >= '$startdate'");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();
        return $return;
    }

    /**
     *
     * @return unknown_type
     */
    function _lastYear()
    {
        $database = JFactory::getDBO();
        $today = TiendaHelperBase::getToday();

        $start = getdate( strtotime($today) );
        // first day of year
        $enddate = date("Y-m-d", strtotime($start['year']."-01-01"));

        $query = new TiendaQuery();
        $query->select( 'COUNT(*) AS num, SUM(order_total) AS amount' );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");
        $query->where("tbl.created_date < '$enddate'");
        $query->where("tbl.created_date >= DATE_SUB('".$enddate."', INTERVAL 1 YEAR)");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();
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

        $firstsale = $this->_firstsale();
        $firstsale_date = empty($firstsale->date) ? '0000-00-00' : $firstsale->date;
        $lastsale = $this->_lastsale();
        $lastsale_date = empty($lastsale->date) ? '0000-00-00' : $lastsale->date; 

        $query = new TiendaQuery();
        $query->select( 'COUNT(*) AS num' );
        $query->select( 'SUM(order_total) AS amount' );
        $query->select( 'AVG(order_total) AS average' );
        $query->select( "DATEDIFF('{$lastsale_date}','{$firstsale_date}') AS days_in_business" );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();
        $days = ($return->days_in_business > 0) ? $return->days_in_business : 1;
        $return->average_daily = $return->num / $days;
        return $return;
    }

    /**
     * _firstsale function.
     * 
     * @access private
     * @return void
     */
    function _firstsale()
    {
        $database = JFactory::getDBO();
        $today = TiendaHelperBase::getToday();

        $startdate = $today;

        $query = new TiendaQuery();
        $query->select( 'tbl.created_date AS date' );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");
        $query->order("tbl.created_date ASC");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();
        return $return;
    }

    /**
     * _lastsale function.
     * 
     * @access private
     * @return void
     */
    function _lastsale()
    {
        $database = JFactory::getDBO();
        $today = TiendaHelperBase::getToday();

        $startdate = $today;

        $query = new TiendaQuery();
        $query->select( 'tbl.created_date AS date' );
        $query->from('#__tienda_orders AS tbl');
        $query->where("tbl.order_state_id IN ($this->_statesCSV)");
        $query->order("tbl.created_date DESC");

        $database->setQuery( (string) $query );
        $return = $database->loadObject();
        return $return;
    }

}
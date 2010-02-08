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
defined( '_JEXEC' ) or die( 'Restricted access' );

JLoader::import( 'com_tienda.tables._base', JPATH_ADMINISTRATOR.DS.'components' );

class TableOrderHistory extends TiendaTable 
{
	/**
	 * 
	 * 
	 * @param $db
	 * @return unknown_type
	 */
	function TableOrderHistory( &$db ) 
	{
		$tbl_key 	= 'order_history_id';
		$tbl_suffix = 'orderhistory';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
		$nullDate	= $this->_db->getNullDate();

		if (empty($this->date_added) || $this->date_added == $nullDate)
		{
			$date = JFactory::getDate();
			$this->date_added = $date->toMysql();
		}		
		return true;
	}
	
    /**
     * 
     * @param unknown_type $updateNulls
     * @return unknown_type
     */
    function store( $updateNulls=false )
    {
        if ( $return = parent::store( $updateNulls ))
        {
        	if ($this->customer_notified == '1')
        	{
        		// TODO Email the customer that the orderhistory has been updated.
        		// Should the field be changed to notify_customer?
        	}
        }
        return $return;
    }
}

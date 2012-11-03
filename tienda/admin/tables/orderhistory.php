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

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableOrderHistory extends TiendaTable 
{
	function TiendaTableOrderHistory( &$db ) 
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
        	if ($this->notify_customer == '1')
        	{
        		Tienda::load( "TiendaHelperBase", 'helpers._base' );
        		$helper = TiendaHelperBase::getInstance('Email');
        		
        		$model = Tienda::getClass("TiendaModelOrders", "models.orders");
				$model->clearCache();
        		$model->setId($this->order_id);
        		$order = $model->getItem();
        		
        		$helper->sendEmailNotices($order, 'order');
        	}
        }
        return $return;
    }
}

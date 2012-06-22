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

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableCoupons extends TiendaTable
{
	function TiendaTableCoupons( &$db )
	{
		
		$tbl_key = 'coupon_id';
		$tbl_suffix = 'coupons';
		$this->set( '_suffix', $tbl_suffix );
		$name = 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}
	
	function check( )
	{
		$db = $this->getDBO( );
		$nullDate = $db->getNullDate( );
		if ( empty( $this->created_date ) || $this->created_date == $nullDate )
		{
			$date = JFactory::getDate( );
			$this->created_date = $date->toMysql( );
		}
		if ( empty( $this->modified_date ) || $this->modified_date == $nullDate )
		{
			$date = JFactory::getDate( );
			$this->modified_date = $date->toMysql( );
		}
		$this->filterHTML( 'coupon_name' );
		if ( empty( $this->coupon_name ) )
		{
			$this->setError( JText::_('COM_TIENDA_NAME_REQUIRED') );
			return false;
		}
		$this->filterHTML( 'coupon_code' );
		if (empty($this->coupon_code) && $this->coupon_automatic != 1)
        {
            $this->setError( JText::_('COM_TIENDA_CODE_REQUIRED') );
            return false;
        }
        if($this->coupon_group == 'shipping' && $this->coupon_type != "0")
        {
        	$this->setError( JText::_('COM_TIENDA_SHIPPING_CAN_ONLY_BE_PER_ORDER') );
            return false;
        }
		return true;
	}
	
	/**
	 * Stores the object
	 * @param object
	 * @return boolean
	 */
	function store($updateNulls=false) 
	{
		$date = JFactory::getDate( );
		$this->modified_date = $date->toMysql( );
		$store = parent::store($updateNulls );
		return $store;
	}
}

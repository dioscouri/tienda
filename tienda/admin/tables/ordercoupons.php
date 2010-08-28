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

class TiendaTableOrderCoupons extends TiendaTable
{
	function TiendaTableOrderCoupons ( &$db )
	{
		$tbl_key 	= 'ordercoupon_id';
		$tbl_suffix = 'ordercoupons';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}
	
	function check()
	{
	    // TODO Check order_id and coupon_id?
		return true;
	}
	
	function save()
	{
	    if ($return = parent::save())
	    {
            $coupon = JTable::getInstance( 'Coupons', 'TiendaTable' );
            $coupon->load( array( 'coupon_id'=>$this->coupon_id ) );
            $coupon->coupon_uses = $coupon->coupon_uses + 1;
            if (!$coupon->save())
            {
                JFactory::getApplication()->enqueueMessage( $coupon->getError() );
            }
	    }
	    return $return;
	}
}
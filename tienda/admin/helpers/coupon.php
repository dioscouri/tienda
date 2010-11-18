<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class TiendaHelperCoupon extends TiendaHelperBase
{
    /**
     * Given a coupon id or code, checks if the coupon is available for use today.
     * If given a user_id, checks if the user can use the coupon
     *
     * @param $coupon string
     * @param $id_type string
     * @param $user_id
     * 
     * @return boolean if false, coupon object if true
     */
    function isValid( $coupon_id, $id_type='code', $user_id='' )
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        
        switch($id_type)
        {
            case 'id':
            case 'coupon_id':
                $coupon = JTable::getInstance( 'Coupons', 'TiendaTable' );
                $coupon->load( array('coupon_id'=>$coupon_id) );
                break;
            case 'code':
            case 'coupon_code':
            default:
                $coupon = JTable::getInstance( 'Coupons', 'TiendaTable' );
                $coupon->load( array('coupon_code'=>$coupon_id) );
                break;
        }

        // do we need individualized error reporting?
        if (empty($coupon->coupon_id))
        {
            $this->setError( JText::_( "Invalid Coupon" ) );
            return false;
        }
        
        // is the coupon enabled?
        if (empty($coupon->coupon_enabled))
        {
            $this->setError( JText::_( "Coupon Not Enabled" ) );
            return false;
        }
        
        $date = JFactory::getDate();
        if ($date->toMySQL() < $coupon->start_date)
        {
            $this->setError( JText::_( "Coupon Not Valid for Today" ) );
            return false;
        }
        
        $db = JFactory::getDBO();
        $nullDate = $db->getNullDate();
        if ($coupon->expiration_date != $nullDate && $date->toMySQL() > $coupon->expiration_date)
        {
            $this->setError( JText::_( "Coupon Has Expired" ) );
            return false;
        }
        
        if ($coupon->coupon_max_uses > '-1' && $coupon->coupon_max_uses >= $coupon->coupon_uses)
        {
            $this->setError( JText::_( "Coupon Maximum Uses Reached" ) );
            return false;
        }        
        
        if (!empty($user_id))
        {
            // Check the user's uses of this coupon
            $model = JModel::getInstance( 'OrderCoupons', 'TiendaModel' );
            $model->setState('filter_user', $user_id);
            $model->setState('filter_coupon', $coupon->coupon_id);
            $user_uses = $model->getResult();
            if ($coupon->coupon_max_uses_per_user > '-1' && $user_uses >= $coupon->coupon_max_uses_per_user)
            {
                $this->setError( JText::_( "You Have Used This Coupon the Maximum Number of Times" ) );
                return false;
            }
        }
        
        // all ok
        return $coupon;
    }  
    
    /**
     * Get total value of the coupon based on the given coupon ids
     * @param array $couponIds
     * @return decimal
     */
    function getCouponSum($couponIds)
    {    	    	
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );		
       	$model = JModel::getInstance( 'Coupons', 'TiendaModel' );
		$model->setState( 'filter_ids', $couponIds );
        $couponObjects = $model->getList();
		
        foreach($couponObjects as $couponObject)
        {
        	 if (empty($couponObject->coupon_type))
            {
                // get the value
                switch ($couponObject->coupon_value_type)
                {
                    case "1": // percentage
                        $amount = ($couponObject->coupon_value/100) * ($values['order_total']);
                        break;
                    case "0": // flat-rate
                        $amount = $couponObject->coupon_value;
                        break;
                }
            }
            
            // update the total amount of the discount
            $coupon_total += $amount;
        }
        
        return $coupon_total;
    }
}
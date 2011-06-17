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
        
        if ($coupon->coupon_max_uses > '-1' && $coupon->coupon_uses >= $coupon->coupon_max_uses)
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

	function checkByProductIds($coupon_id, $product_ids)
    {
    	if (!empty($product_ids))
        {
        	
			$ids = implode(",", $product_ids);        	

            // Check the product_id
            Tienda::load( 'TiendaQuery', 'library.query' );
	        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
	        $table = JTable::getInstance( 'ProductCoupons', 'TiendaTable' );
	        
	        $query = new TiendaQuery();
	        $query->select( "COUNT(*)" );
	        $query->from( $table->getTableName()." AS tbl" );
	        $query->where( "tbl.product_id IN (".$ids.")" );
	        $query->where( "tbl.coupon_id = ".(int) $coupon_id );
	        
	        $db = JFactory::getDBO();
	        $db->setQuery( (string) $query );
	        
	        $count = $db->loadResult();
            
	        if (!$count)
            {
                return false;
            }
            
            return true;
        }        

        return false;
    } 
	
	function getCouponProductIds($coupon_id)
    {
        Tienda::load( 'TiendaQuery', 'library.query' );
        $query = new TiendaQuery();
		$query->select('product_id');
		$query->from('#__tienda_productcouponxref');
		$query->where('coupon_id = '.(int)$coupon_id);
		
		$db = JFactory::getDBO();
		$db->setQuery($query);
		return $db->loadResultArray();  

	}
      
}
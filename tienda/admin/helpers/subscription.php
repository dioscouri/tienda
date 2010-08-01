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

class TiendaHelperSubscription extends TiendaHelperBase
{
    /**
     * Given a subscription ID, will cancel it
     * 
     * @param unknown_type $subscription_id
     * @return unknown_type
     */
    function cancel( $subscription_id )
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $subscription = JTable::getInstance('Subscriptions', 'TiendaTable');
        $subscription->subscription_id = $subscription_id;
        $subscription->status = 0;
        if (!$subscription->save())
        {
            $this->setError( $subscription->getError() );
            return false;
        }
        return true;
    }

    /**
     * Given a user_id and product_id, checks if the user has a valid subscription for it.
     * Optional date will check to see if subscription is/was/will-be valid on a certain date
     * 
     * @param $user_id
     * @param $product_id
     * @param $date (TBD) , $date=''
     * 
     * @return unknown_type
     */
    function isValid( $user_id, $product_id )
    {
        $date='';
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Subscriptions', 'TiendaModel' );
        $model->setState("filter_userid", $user_id );
        $model->setState("filter_productid", $product_id );
        $model->setState("filter_enabled", 1);
        if (!empty($date))
        {
            // TODO Enable this.  Add filters to model and set state here.
        }
        
        if ($subscriptions = $model->getList())
        {
            return true;
        }
        return false;
    }
    
    /**
     * 
     * Get's a subscription's history
     * @param $subscription_id
     * @return array
     */
    function getHistory( $subscription_id )
    {
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'SubscriptionHistory', 'TiendaModel' );
        $model->setState("filter_subscriptionid", $subscription_id );
        $model->setState("order", 'tbl.created_datetime' );
        $model->setState("direction", 'ASC');
        if ($data = $model->getList())
        {
            return $data;
        }
        return array();
    }
}
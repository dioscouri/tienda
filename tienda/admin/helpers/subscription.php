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
        $subscription->subscription_enabled = 0;
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
    
    /**
     * Checks for subscriptions that have expired,
     * sets them to expired, and sends out email notices
     *  
     * @return unknown_type
     */
    function checkExpired()
    {
        $date = JFactory::getDate();
        $today = $date->toFormat( "%Y-%m-%d 00:00:00" );
        
        // select all subs that have expired but still have status = '1';
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Subscriptions', 'TiendaModel' );
        $model->setState("filter_datetype", 'expires' );
        $model->setState("filter_date_from", $today );
        $model->setState("filter_enabled", '1' );
        if ($list = $model->getList())
        {
            foreach ($list as $item)
            {
                $this->setExpired( $item->subscription_id );
            }
        }
        
        // Update config to say this has been done
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $config = JTable::getInstance( 'Config', 'TiendaTable' );
        $config->load( array( 'config_name'=>'subscriptions_last_checked') );
        $config->config_name = 'subscriptions_last_checked';
        $config->value = $today;
        $config->save();
        
        // TODO send summary email to admins
    }
    
    /**
     * Marks a subscription as expired
     * and sends the expired email to the user
     * 
     * @param $subscription_id
     * @return unknown_type
     */
    function setExpired( $subscription_id )
    {
        // change status = '0'
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $subscription = JTable::getInstance('Subscriptions', 'TiendaTable');
        $subscription->subscription_id = $subscription_id;
        $subscription->subscription_enabled = 0;
        if (!$subscription->save())
        {
            $this->setError( $subscription->getError() );
            return false;
        }
        
        // fire plugin event onAfterExpiredSubscription        
        JPluginHelper::importPlugin( 'tienda' );
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterExpiredSubscription', array( $subscription ) );
        
        // TODO Email user that their subs expired

        return true;
    }

    /**
     * Checks for subscriptions that expire x days in future
     * and sends out email notices
     *  
     * @return unknown_type
     */
    function checkExpiring()
    {
        $config = TiendaConfig::getInstance();
        
        // select all subs that expire in x days (where expires > today + x & expires < today + x + 1)
        $subscriptions_expiring_notice_days = $config->get( 'subscriptions_expiring_notice_days', '14' );
        $subscriptions_expiring_notice_days_end = $subscriptions_expiring_notice_days + '1';
        $date = JFactory::getDate();
        $today = $date->toFormat( "%Y-%m-%d 00:00:00" );
        
        $database =& JFactory::getDBO();
        $query = " SELECT DATE_ADD('".$today."', INTERVAL %s DAY) ";
        $database->setQuery( sprintf($query, $subscriptions_expiring_notice_days ) );
        $start_date = $database->loadResult();

        $database->setQuery( sprintf($query, $subscriptions_expiring_notice_days_end ) );
        $end_date = $database->loadResult();
        
        // select all subs that expire between those dates
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Subscriptions', 'TiendaModel' );
        $model->setState("filter_datetype", 'expires' );
        $model->setState("filter_date_from", $start_date );
        $model->setState("filter_date_to", $end_date );
        $model->setState("filter_enabled", '1' );
        if ($list = $model->getList())
        {
            foreach ($list as $item)
            {
                // TODO Send expiring email for $item->subscription_id
            }
        }
    }
}
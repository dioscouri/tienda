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
    
    /**
     * Ensures a subscriber has access to files 
     * added after their subscription started
     * 
     * @param $subscriptions array of subscription objects
     * @param $files array optional array of files for the subscriptions, only sent if one kind of product is in subscriptions
     * @return unknown_type
     */
    function reconcileFiles( $subscriptions, $files=array() )
    {
        $errorMsg = '';
        $date = JFactory::getDate();
        $db = JFactory::getDBO();
        $nullDate = $db->getNullDate();
        
        // foreach of the subs
        foreach ( $subscriptions as $subscription )
        {
            $productfiles = $files;
            // if files is empty, get the product's files
            if (empty($productfiles))
            {
                JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
                $model = JModel::getInstance( 'ProductFiles', 'TiendaModel' );
                $model->setState( 'filter_product', $subscription->product_id );
                $model->setState( 'filter_enabled', 1 );
                $model->setState( 'filter_purchaserequired', 1 );
                $productfiles = $model->getList();
            }
            
            // if any of the files were created after the sub's last checked date,
            // add access for the sub's user
            foreach ($productfiles as $file)
            {
                if ($file->created_date > $subscription->checkedfiles_datetime || $file->created_date == $nullDate)
                {
                    // check: was the file added while the subscription was active?
                    if ( $subscription->subscription_enabled || 
                    (empty($subscription->subscription_enabled) && $subscription->created_datetime < $file->created_date && $file->created_date < $subscription->expires_datetime)
                    )
                    {
                        $productDownload = JTable::getInstance('ProductDownloads', 'TiendaTable');
                        $productDownload->product_id = $subscription->product_id;
                        $productDownload->productfile_id = $file->productfile_id;
                        $productDownload->productdownload_max = '-1'; // TODO For now, infinite. In the future, add a field to productfiles that allows admins to limit downloads per file per purchase
                        $productDownload->order_id = $subscription->order_id;
                        $productDownload->user_id = $subscription->user_id;
                        if (!$productDownload->save())
                        {
                            // track error
                            $error = true;
                            $errorMsg .= $productDownload->getError();
                            JFactory::getApplication()->enqueueMessage( $productDownload->getError(), 'notice' );
                            // TODO What to do with this error 
                        }                        
                    }
                }
            }
            
            $subtable = JTable::getInstance('Subscriptions', 'TiendaTable');
            $subtable->subscription_id = $subscription->subscription_id;
            $subtable->checkedfiles_datetime = $date->toMySQL();
            $subtable->save();
        }

    }
}
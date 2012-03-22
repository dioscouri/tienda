<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

Tienda::load( "TiendaHelperBase", 'helpers._base' );

class TiendaHelperJuga extends TiendaHelperBase 
{
    /**
     * Checks if Juga is installed
     * 
     * @return boolean
     */
    function isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_juga'.DS.'defines.php')) 
        {
            JLoader::register( "Juga", JPATH_ADMINISTRATOR.DS."components".DS."com_juga".DS."defines.php" );
            if (version_compare(Juga::getVersion(), '2.2.0', '>=')) 
            {
                $success = true;
            }
        }                
        return $success;
    }
    
    /**
     * Processes a new order
     * 
     * @param $order_id
     * @return unknown_type
     */
    function processOrder( $order_id ) 
    {
        if (!$this->isInstalled())
        {
            $this->setError( JText::_('COM_TIENDA_JUGA_NOT_INSTALLED') );
            return null;
        }
        
        // get the order
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        
        // find the products in the order that are integrated 
        foreach ($order->orderitems as $orderitem)
        {
            $model = JModel::getInstance( 'Products', 'TiendaModel' );
            $model->setId( $orderitem->product_id );
            $product = $model->getItem();
            
            $juga_group_csv_add = $product->product_parameters->get('juga_group_csv_add');
            $juga_group_csv_remove = $product->product_parameters->get('juga_group_csv_remove');
            
            $ids_remove = explode( ',', $juga_group_csv_remove );
            if (!empty($ids_remove))
            {
                foreach ($ids_remove as $id)
                {
                    $this->remove($order->user_id, $id);
                }
            }
            
            $ids_add = explode( ',', $juga_group_csv_add );
            if ( !empty($ids_add) )
            {
                foreach ($ids_add as $id)
                {
                    $this->add($order->user_id, $id);
                }
            }
        }
    }
    
    /**
     * 
     * Enter description here ...
     * @param $subscription     mixed  TiendaTableSubscriptions object or a subscription_id
     * @return unknown_type
     */
    function doExpiredSubscription( $subscription )
    {
        if (is_numeric($subscription))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $table = JTable::getInstance( 'Subscriptions', 'TiendaTable' );
            $table->load( array( 'subscription_id' => $subscription ) );
            $subscription = $table;
        }
        
        if (empty($subscription->subscription_id) || !is_object($subscription))
        {
            $this->setError( JText::_('COM_TIENDA_JUGA_INVALID_SUBSCRIPTION') );
            return false;
        }
        

        if (!empty($subscription->product_id))
        {
            JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
            $model = JModel::getInstance( 'Products', 'TiendaModel' );
            $model->setId( $subscription->product_id );
            $product = $model->getItem();
            
            $juga_group_csv_add = $product->product_parameters->get('juga_group_csv_add_expiration');
            $juga_group_csv_remove = $product->product_parameters->get('juga_group_csv_remove_expiration');
            
            $ids_remove = explode( ',', $juga_group_csv_remove );
            if (!empty($ids_remove))
            {
                foreach ($ids_remove as $id)
                {
                    $this->remove($subscription->user_id, $id);
                }
            }
            
            $ids_add = explode( ',', $juga_group_csv_add );
            if ( !empty($ids_add) )
            {
                foreach ($ids_add as $id)
                {
                    $this->add($subscription->user_id, $id);
                }
            }
        }
        
        return true;        
    }
    
    /**
     * Checks if user is in a group
     * 
     * @param $userid
     * @param $groupid
     * @return unknown_type
     */
    function already( $userid, $groupid ) 
    {
        $success = false;
        $database = JFactory::getDBO();
        
        // query the db to see if the user is already a member of group
        $database->setQuery("
            SELECT 
                `user_id` 
            FROM 
                #__juga_u2g
            WHERE 
                `group_id` = '{$groupid}'
            AND 
                `user_id` = '{$userid}' 
        ");
        
        $success = $database->loadResult();

        return $success;
    }
    
    /**
     * Adds User to a Group if not already in it
     * 
     * @param $userid
     * @param $groupid
     * @return unknown_type
     */
    function add( $userid, $groupid )
    {
        $success = false;
        $database = JFactory::getDBO();
        
        $already = $this->already( $userid, $groupid );
        
        // if they aren't already a member of the group, add them to the group
        if (($already != $userid)) 
        {
            $database->setQuery("
                INSERT INTO 
                    #__juga_u2g
                SET
                    `user_id` = '{$userid}',
                    `group_id` = '{$groupid}'
            ");
            
            if ($database->query()) {
                $success = true; 
            }
        } 
        
        return $success;
    }
    
    /**
     * Remove User from a Group 
     * 
     * @param $userid
     * @param $groupid
     * @return unknown_type
     */
    function remove( $userid, $groupid )
    {
        $success = false;
        $database = JFactory::getDBO();
        
        $database->setQuery("
            DELETE FROM 
                #__juga_u2g
            WHERE
                `user_id` = '{$userid}'
            AND
                `group_id` = '{$groupid}'
        ");
        
        if ($database->query()) {
            $success = true; 
        }
    
        return $success;
    }

}
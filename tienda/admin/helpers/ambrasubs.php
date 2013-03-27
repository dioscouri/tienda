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
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );

Tienda::load( "TiendaHelperBase", 'helpers._base' );

class TiendaHelperAmbrasubs extends TiendaHelperBase 
{
    /**
     * Checks if extension is installed
     * 
     * @return boolean
     */
    function isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_ambrasubs/defines.php')) 
        {
            JLoader::register( "Ambrasubs", JPATH_ADMINISTRATOR."/components/com_ambrasubs/defines.php" );
            if (version_compare(Ambrasubs::getVersion(), '3.4.0', '>=')) 
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
            $this->setError( JText::_('COM_TIENDA_AMBRASUBS_NOT_INSTALLED') );
            return null;
        }
        
        // get the order
        $model = DSCModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        $this->order = $order;
        
        // find the products in the order that are integrated 
        foreach ($order->orderitems as $orderitem)
        {
            $model = DSCModel::getInstance( 'Products', 'TiendaModel' );
            $model->setId( $orderitem->product_id );
            $product = $model->getItem();
            
            $ambrasubs_type_id = $product->product_parameters->get('ambrasubs_type_id');
            if ( !empty($ambrasubs_type_id) )
            {
                $this->add($order->user_id, $ambrasubs_type_id);
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
            DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
            $table = DSCTable::getInstance( 'Subscriptions', 'TiendaTable' );
            $table->load( array( 'subscription_id' => $subscription ) );
            $subscription = $table;
        }
        
        if (empty($subscription->subscription_id) || !is_object($subscription))
        {
            $this->setError( JText::_('COM_TIENDA_AMBRASUBS_INVALID_SUBSCRIPTION') );
            return false;
        }
        

        if (!empty($subscription->product_id))
        {
            DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
            $model = DSCModel::getInstance( 'Products', 'TiendaModel' );
            $model->setId( $subscription->product_id );
            $product = $model->getItem();
            
            $ambrasubs_type_id = $product->product_parameters->get('ambrasubs_type_id');
            if ( !empty($ambrasubs_type_id) )
            {
                $this->remove($subscription->user_id, $ambrasubs_type_id);
            }
        }
        
        return true;        
    }
    
    /**
     * Adds User to a Subscription Type
     * 
     * @param $user_id
     * @param $sub_id
     * @return unknown_type
     */
    function add( $user_id, $sub_id )
    {
        $success = false;
        
        Tienda::load( "AmbrasubsHelperPayment", 'helpers.payment', array( 'site'=>'admin', 'type'=>'components', 'ext'=>'com_ambrasubs' ) );        
        $payment = new AmbrasubsHelperPayment();
        
        // Set the following fields, using $this->order
        
        // payment fields
        $payment->payment_type = 'com_tienda';          // unique identifier, should be the plugin name
        $payment->created_by = $this->order->user_id;          // user creating the payment record
        $payment->created_datetime = $this->order->created_date;   // always in GMT
        $payment->payment_id = $this->order->orderpayments[0]->orderpayment_id;           // transaction id from payment method
        $payment->payment_status = '1';       // status of the PAYMENT
        $payment->payment_amount = $this->order->orderpayments[0]->orderpayment_amount;       // payment amount
        $payment->payment_details = $this->order->orderpayments[0]->transaction_details;     // text - any info about payment
        $payment->payment_datetime = $this->order->orderpayments[0]->created_date;   // always in GMT
        
        // subscription fields
        $payment->userid = $this->order->user_id;              // user to be associated with the subscription
        $payment->typeid = $sub_id;                      // id value for subscription type
        $payment->status = '1';                  // status of the SUBSCRIPTION
        $payment->paymentid = '';                               // will be set by save process
        $payment->expires_datetime = '';       // if not set, will be set to the correct day in the future by save process - only set this (=NOW) if the payment was invalid
        
        // set plugin data for further processing by the AS email system
        $payment->payment_plugin_data = '';
        
        if ( ! ($already = AmbrasubsHelperPayment::getInstance( $payment->payment_id, $payment->payment_type, '1', 'payment_id' )) ) 
        { 
            if ( ! $payment->save()) {
                $paymentError = JText::_('COM_TEINDA_AMBRASUBS_MESSAGE_PAYMENT_SAVE_FAILED');
            }
        } 
            else
        {
            // simply create a subscription using $already->id
            
            // type info
            $table_type = $payment->getTable( 'Type' );
            $table_type->load( $payment->typeid );
            
            // baased on payment_datetime, find expiration date 'period' days in future
            $database = JFactory::getDBO();
            $query = " SELECT DATE_ADD('{$payment->payment_datetime}', INTERVAL {$table_type->period} DAY ) ";
            $database->setQuery( $query );
            $expires_datetime = $database->loadResult();

            // store the association between user, subscription type, and payment
            $table_u2t = $payment->getTable( 'Users2types' );
            $table_u2t->bind( $payment->getProperties() );
            $table_u2t->paymentid = $already->id;
            $table_u2t->expires_datetime = empty($payment->expires_datetime) ? $expires_datetime : $payment->expires_datetime;
            
            if ( !$result_u2t = $table_u2t->store() ) 
            {
                $paymentError = $table_u2t->getError();
            }
        }
        
        return $success;
    }
    
    /**
     * Removes a user from an AS subscription
     * 
     * @param $user_ir
     * @param $sub_id
     * @return unknown_type
     */
    function remove( $user_id, $sub_id )
    {
        $date = JFactory::getDate();
        $today = $date->toFormat( '%Y-%m-%d %H:%M:%S' );
        
        $database = JFactory::getDBO();
        
        $query = "
            UPDATE
                #__ambrasubs_users2types
            SET 
                `status` = '0'
            WHERE
                `expires_datetime` <= '{$today}'
            AND
                `status` = '1'
            AND
                `userid` = '{$user_id}'
            AND
                `typeid` = '{$sub_id}'    
                
        ";
        $database->setQuery( $query );
        $database->query();
    }

    /**
     * Gets a select list of Ambrasubs Subscription Types
     * 
     * @param $selected
     * @param $name
     * @param $attribs
     * @param $idtag
     * @param $allowAny
     * @param $allowNone
     * @param $title
     * @param $title_none
     * @param $enabled
     * @return unknown_type
     */
    function selectTypes($selected, $name = 'ambrasubs_type_id', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select Subscription Type', $title_none = 'No Subscription Type', $enabled = null )
    {
        Tienda::load( "AmbrasubsHelperSubscription", 'helpers.subscription', array( 'site'=>'admin', 'type'=>'components', 'ext'=>'com_ambrasubs' ) );        
        $list = AmbrasubsHelperSubscription::getSelectListTypes( '', 0 );
        return JHTML::_('select.genericlist', $list, $name, $attribs, 'value', 'text', $selected);
    }
    
    /**
     * Event is triggered after product is saved in Tienda
     * and updates the AS subscription type's record
     * 
     * @param $product
     * @return unknown_type
     */
    function onAfterSaveProducts( $product )
    {
        $model = DSCModel::getInstance( 'Products', 'TiendaModel' );
        $model->setId( $product->product_id );
        $product = $model->getItem();

        $ambrasubs_type_id = $product->product_parameters->get('ambrasubs_type_id');
        if ( !empty($ambrasubs_type_id) )
        {
            $db = JFactory::getDBO();
            $db->setQuery( "SELECT * FROM #__ambrasubs_types WHERE `id` = '{$ambrasubs_type_id}';" );
            $type = $db->loadObject();
            
            $params = new DSCParameter( trim($type->params) );
            $params->set( 'tienda_product_id', $product->product_id );
            
            $type_params = $db->getEscaped( trim( $params->toString() ) );
            $db->setQuery( "UPDATE #__ambrasubs_types SET `params` = '$type_params' WHERE `id` = '{$ambrasubs_type_id}';" );
            $db->query();
        }
        
 
    }

}
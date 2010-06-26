<?php
/**
 * @version 1.5
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

class TiendaHelperAmigos extends TiendaHelperBase 
{
    /**
     * Checks if Amigos is installed
     * 
     * @return boolean
     */
    function isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_amigos'.DS.'helpers'.DS.'_base.php')) 
        {
            $success = true;
        }                
        return $success;
    }
    
    /**
     * Gets a user's referral status
     * and returns boolean
     * 
     * @param int $userid
     * @return boolean if false, object if user is a referral
     */
    function getReferralStatus( $userid )
    {
        $return = false;
        
        Tienda::load( 'TiendaQuery', 'library.query' );
        $query = new TiendaQuery();
        $query->select( 'tbl.*' );
        $query->from( '#__amigos_logs AS tbl' );
        $query->where( "tbl.userid = '".(int) $userid."'" );
        
        $db =& JFactory::getDBO();
        $db->setQuery( (string) $query );
        $referral = $db->loadObject();
        
        if (!empty($referral->accountid))
        {
            $return = $referral;
        }
        
        return $return;
    }
    
    /**
     * Creates a commission record for an order 
     * if Amigos is installed and the user is a referral
     * 
     * @param int $order_id An order number
     * @return array
     */
    function getCommissions( $order_id )
    {
        $return = array();
        Tienda::load( 'TiendaQuery', 'library.query' );
        $query = new TiendaQuery();
        $query->select( 'tbl.*' );
        $query->from( '#__amigos_commissions AS tbl' );
        $query->where( "tbl.orderid = '".(int) $order_id."'" );
        $query->where( "tbl.order_type = 'com_tienda'" );
        
        $db =& JFactory::getDBO();
        $db->setQuery( (string) $query );
        $commissions = $db->loadObjectList();
        
        if (!empty($commissions))
        {
            $return = $commissions;
        }
        
        return $return;
    }
    
    /**
     * Creates a commission record for an order 
     * if Amigos is installed and the user is a referral
     * 
     * @param int $order_id An order number
     * @return boolean
     */
    function createCommission( $order_id ) 
    {
        if (!$this->isInstalled())
        {
            $this->serError( JText::_( "Amigos Not Installed" ) );
            return null;
        }
        
        // get the order
        $order = JTable::getInstance( 'Orders', 'TiendaTable' );
        $order->load( array( 'order_id'=>$order_id ) );
        
        $referral = $this->getReferralStatus($order->user_id);
        if (empty($order->user_id) || empty($referral))
        {
            $this->serError( JText::_( "User Not a Referral" ) );
            return null;            
        }
        
        // If here, create a commissions record
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_amigos'.DS.'tables' );
        if (!class_exists('AmigosConfig'))
        {
            JLoader::import( 'com_amigos.defines', JPATH_ADMINISTRATOR.DS.'components' );    
        }        
        Tienda::load( 'AmigosHelperCommission', 'helpers.commission', array( 'site'=>'admin', 'type'=>'components', 'ext'=>'com_amigos' ) );
        Tienda::load( 'AmigosHelperCommission', 'helpers.commission', array( 'site'=>'admin', 'type'=>'components', 'ext'=>'com_amigos' ) );
        
        if (!empty($referral->accountid))
        {
            $config =& AmigosConfig::getInstance();
            $date =& JFactory::getDate();
            // get the account
            $account = JTable::getInstance('Accounts', 'Table');
            $account->load( $referral->accountid );
            // get payouttype
            $payout = JTable::getInstance('Payouts', 'Table');
            $payout->load( $account->payoutid );
            
            // create commission record
            $commission = JTable::getInstance('Commissions', 'Table');
            $commission->accountid          = $account->id;
            $commission->orderid            = $order_id;
            $commission->order_type         = 'com_tienda';
            $commission->order_userid       = $order->user_id;
            $commission->order_value        = $order->order_total;
            $commission->created_datetime   = $date->toMysql();
            $commission->refer_url          = $referral->refer_url;
            $commission->amigosid           = $referral->amigosid;
            $commission->payouttype         = $payout->payouttype ? $payout->payouttype : $config->get('default_payouttype', 'PPSP');
            $commission->payout_value       = $payout->value ? $payout->value : $config->get('default_payout_value', '10%');
            $commission->value              = AmigosHelperCommission::calculate( $commission->payouttype, $commission->payout_value, $commission->order_value );
            
            if (!$commission->save())
            {
                JError::raiseNotice("createCommission01", "createCommission :: ".$commission->getError() );
                return false;
            }
            return true;
        }
        
        return null;
    }
    
}
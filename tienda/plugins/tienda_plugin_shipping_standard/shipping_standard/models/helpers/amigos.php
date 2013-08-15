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
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );

Tienda::load( "TiendaHelperBase", 'helpers._base' );

class TiendaHelperAmigos extends TiendaHelperBase 
{
    protected $commissions = array();
    
    /**
     * Checks if Amigos is installed
     * 
     * @return boolean
     */
    function isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
     
		if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_amigos/defines.php')) 
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
        
        $db = JFactory::getDBO();
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
        if (!isset($this->commissions[$order_id]))
        {
            $return = array();
            Tienda::load( 'TiendaQuery', 'library.query' );
            $query = new TiendaQuery();
            $query->select( 'tbl.*' );
            $query->from( '#__amigos_commissions AS tbl' );
            $query->where( "tbl.orderid = '".(int) $order_id."'" );
            $query->where( "tbl.order_type = 'com_tienda'" );
            
            $db = JFactory::getDBO();
            $db->setQuery( (string) $query );
            $this->commissions[$order_id] = $db->loadObjectList();
        }

        return $this->commissions[$order_id];
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
            $this->setError( JText::_('COM_TIENDA_AMIGOS_NOT_INSTALLED') );
            return null;
        }
        
        // get the order
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        
        $referral = $this->getReferralStatus($order->user_id);
        if (empty($order->user_id) || empty($referral))
        {
            $this->setError( JText::_('COM_TIENDA_AMIGOS_USER_NOT_A_REFERRAL') );
            return null;            
        }
        
        // If here, create a commissions record
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_amigos/tables' );
        if (!class_exists('Amigos'))
        {
            JLoader::import( 'com_amigos.defines', JPATH_ADMINISTRATOR.'/components' );    
        }        
        Tienda::load( 'AmigosHelperCommission', 'helpers.commission', array( 'site'=>'admin', 'type'=>'components', 'ext'=>'com_amigos' ) );
        
        if (!empty($referral->accountid))
        {
            $config = Amigos::getInstance();
            $date = JFactory::getDate();
            
            if (version_compare(Amigos::getInstance()->getVersion(), '1.2.1', '<')) 
            {
                $account = JTable::getInstance('Accounts', 'Table');
            } 
                else
            {
                $account = JTable::getInstance('Accounts', 'AmigosTable');
            }
            
            $account->load( $referral->accountid );
            
            // get payout type and value
            if (version_compare(Amigos::getInstance()->getVersion(), '1.2.1', '<')) 
            {
                $payout = JTable::getInstance('Payouts', 'Table');
            } 
                else
            {
                $payout = JTable::getInstance('Payouts', 'AmigosTable');
            }
            
            $payout->load( $account->payoutid );
            $payout_type = $payout->payouttype ? $payout->payouttype : $config->get('default_payouttype', 'PPSP');
            $payout_value = $payout->value ? $payout->value : $config->get('default_payout_value', '10%');
            
            // determine the commission value based on each product's commission rate override
            $commission_value = 0;
            foreach ($order->orderitems as $orderitem)
            {
                $model = JModel::getInstance( 'Products', 'TiendaModel' );
                $model->setId( $orderitem->product_id );
                $product = $model->getItem();
                
                // does this product have a override for the commission rate?
                if ($product->product_parameters->get('amigos_commission_override') === '')
                {
                    $product_commission_value = AmigosHelperCommission::calculate( $payout_type, $payout_value, $orderitem->orderitem_final_price );
                }
                    else
                {
                    $product_payout_value = $product->product_parameters->get('amigos_commission_override');
                    $product_commission_value = AmigosHelperCommission::calculate( $payout_type, $product_payout_value, $orderitem->orderitem_final_price );
                }
                
                $commission_value += $product_commission_value;
            }
            
            // create commission record
            if (version_compare(Amigos::getInstance()->getVersion(), '1.2.1', '<')) 
            {
                $commission = JTable::getInstance('Commissions', 'Table');
            } 
                else
            {
                $commission = JTable::getInstance('Commissions', 'AmigosTable');
            }
            
            $commission->accountid          = $account->id;
            $commission->orderid            = $order_id;
            $commission->order_type         = 'com_tienda';
            $commission->order_userid       = $order->user_id;
            $commission->order_value        = $order->order_total;
            $commission->created_datetime   = $date->toMysql();
            $commission->refer_url          = $referral->refer_url;
            $commission->amigosid           = $referral->amigosid;
            $commission->payouttype         = $payout_type;
            $commission->payout_value       = $payout_value;
            $commission->value              = $commission_value;
            
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
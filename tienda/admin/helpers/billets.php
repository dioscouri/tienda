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

class TiendaHelperBillets extends TiendaHelperBase 
{
    /**
     * Checks if Billets is installed
     * 
     * @return boolean
     */
    function isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_billets'.DS.'defines.php')) 
        {
            JLoader::register( "Billets", JPATH_ADMINISTRATOR.DS."components".DS."com_billets".DS."defines.php" );
            JLoader::register( "BilletsConfig", JPATH_ADMINISTRATOR.DS."components".DS."com_billets".DS."defines.php" );
            if (version_compare(Billets::getVersion(), '4.2.0', '>=')) 
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
            $this->setError( JText::_( "Billets Not Installed" ) );
            return null;
        }
        
        // get the order
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        
        // find the products in the order that impact billets ticket limit 
        foreach ($order->orderitems as $orderitem)
        {
            $model = JModel::getInstance( 'Products', 'TiendaModel' );
            $model->setId( $orderitem->product_id );
            $product = $model->getItem();
            
            $billets_ticket_limit_increase = $product->product_parameters->get('billets_ticket_limit_increase');
            $billets_ticket_limit_exclusion = $product->product_parameters->get('billets_ticket_limit_exclusion');
            
            // does this product impact ticket limits?
            if ( $billets_ticket_limit_increase > '0' || $billets_ticket_limit_exclusion == '1' )
            {
                // update userdata
                JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_billets'.DS.'tables' );
                $userdata = JTable::getInstance('Userdata', 'BilletsTable');
                $userdata->load( array('user_id'=>$order->user_id) );
                $userdata->user_id = $order->user_id;
                $userdata->ticket_max = $userdata->ticket_max + $billets_ticket_limit_increase;
                if ($billets_ticket_limit_exclusion == '1')
                {
                    $userdata->limit_tickets_exclusion = $billets_ticket_limit_exclusion; 
                }
                $userdata->save();
            }
        }
    }
}
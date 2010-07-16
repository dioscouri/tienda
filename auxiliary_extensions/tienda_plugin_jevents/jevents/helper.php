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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaHelperLightspeed extends TiendaHelperBase 
{
    /**
     * 
     * Enter description here ...
     */
    function getDBO()
    {
        $db = $this->getDBOConnection();
        if (!empty($db->_errorNum))
        {
            $this->setError( $db->_errorMsg );
            return false;
        }
        return $db;
    }
    
    /**
     * 
     * Enter description here ...
     */
    function getDBOCredentials()
    {
        $fields = array( 
            'lightspeed_host',
            'lightspeed_user',
            'lightspeed_password',
            'lightspeed_database',
            'lightspeed_prefix',
            'lightspeed_driver'
        );
        
        // 'lightspeed_port' is not required
        
        $config = TiendaConfig::getInstance();
        $option = array();
        
        $option['driver']   = $config->get( 'lightspeed_driver' );            // Database driver name
        $option['host']     = $config->get( 'lightspeed_host' );            // Database host name
        $option['user']     = $config->get( 'lightspeed_user' );          // User for database authentication
        $option['password'] = $config->get( 'lightspeed_password' );     // Password for database authentication
        $option['database'] = $config->get( 'lightspeed_database' );         // Database name
        $option['prefix']   = $config->get( 'lightspeed_prefix' );                // Database prefix (may be empty)
        
        $port = $config->get( 'lightspeed_port' );
        if (!empty($port) && $port != '3306') { $option['host'] .= ":".$port; } // alternative ports
        
        foreach ($option as $key=>$value)
        {
            if (empty($value))
            {
                $this->setError( JText::_( "Incomplete Lightspeed Credentials" ) );
                return false;
            }
        }
        
        return $option;
    }
    
    /**
     * Gets a connection to the Lightspeed DB
     * Enter description here ...
     * @param $refresh
     */
    function getDBOConnection( $refresh = false )
    {
        static $instance;
        
        if (!is_object($instance) || $instance->_errorNum ) 
        {
            if (!$credentials = $this->getDBOCredentials()) 
            {
                $instance = new pseudoJDatabase();
                $instance->_errorNum = '-212';
                $instance->_errorMsg = $this->getError();
            } 
                else 
            {
                // verify connection
                $database = JDatabase::getInstance( $credentials );

                if ( method_exists( $database, 'test' ) )
                {
                    // success
                    $instance = $database;
                }
                    else
                {
                    $instance = new pseudoJDatabase();
                    $instance->_errorNum = '-213';
                    $instance->_errorMsg = JText::_( 'Could not connect to this DB.' );
                }
            }
        }
        
        return $instance;
    }
    
    /**
     * Given an id number and a field identifier
     * will return the corresponding xref id 
     * 
     * @param $id int 
     * @param $field string
     * @return int
     */
    function getXrefID( $id, $id_type )
    {
        JTable::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'lightspeed'.DS.'tables' );

        switch (strtolower( $id_type ))
        {
            case "customer_id":
                // returns user_id
                $table = JTable::getInstance( 'LSCustomers', 'TiendaTable' );
                $return_field = 'user_id';
                break;
            case "user_id":
                // returns customer_id
                $table = JTable::getInstance( 'LSCustomers', 'TiendaTable' );
                $return_field = 'customer_id';
                break;
            case "cart_id":
                // returns order_id
                $table = JTable::getInstance( 'LSOrders', 'TiendaTable' );
                $return_field = 'order_id';
                break;
            case "order_id":
                // returns cart_id
                $table = JTable::getInstance( 'LSOrders', 'TiendaTable' );
                $return_field = 'cart_id';
                break;
            case "product_id":
                // returns ls_product_id
                $table = JTable::getInstance( 'LSProducts', 'TiendaTable' );
                $return_field = 'ls_product_id';
                break;
            case "ls_product_id":
                // returns product_id
                $table = JTable::getInstance( 'LSProducts', 'TiendaTable' );
                $return_field = 'product_id';
                break;
            case "category_id":
                // returns ls_category_id
                $table = JTable::getInstance( 'LSCategories', 'TiendaTable' );
                $return_field = 'ls_category_id';
                break;
            case "ls_category_id":
                // returns category_id
                $table = JTable::getInstance( 'LSCategories', 'TiendaTable' );
                $return_field = 'category_id';
                break;
            default:
                return false;
                break;
        }

        $table->load( array( $id_type=>$id ) );
        if (!empty($table->$return_field))
        {
            return $table->$return_field;
        }
        return null;
    }
}

class pseudoJDatabase extends JObject
{
    var $_errorNum;
    var $_errorMsg;
    
    function getErrorNum(){ return $this->_errorNum; }
    function getErrorMsg(){ return $this->_errorMsg; }
}
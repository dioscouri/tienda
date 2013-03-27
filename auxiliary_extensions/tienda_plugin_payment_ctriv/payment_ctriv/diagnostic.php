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

Tienda::load( 'TiendaHelperDiagnostics', 'helpers.diagnostics' );

class TiendaHelperDiagnosticsLightspeed extends TiendaHelperDiagnostics 
{
    /**
     * Performs basic checks on your installation to ensure it is OK
     * @return unknown_type
     */
    function checkInstallation() 
    {
        if (!$this->checkTableLSCustomersXref()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC checkTableLSCustomersXref FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkTableLSOrdersXref()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC checkTableLSOrdersXref FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkTableLSProductsXref()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC checkTableLSProductsXref FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkTableLSCategoriesXref()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC checkTableLSCategoriesXref FAILED') .' :: '. $this->getError(), 'error' );
        }
    }    
    
    /**
     * Creates a table if it doesn't exist
     * 
     * @param $table
     * @param $definition
     */
    function createTable( $table, $definition )
    {
        if (!$this->tableExists( $table ))
        {
            $db = JFactory::getDBO();
            $db->setQuery( $definition );
            if (!$db->query())
            {
                $this->setError( $db->getErrorMsg() );
                return false;
            }
        }
        return true;
    }
    
    /**
     * Checks if a table exists
     * 
     * @param $table
     */
    function tableExists( $table )
    {
        $db = JFactory::getDBO();
        
        // Manually replace the Joomla Tables prefix. Automatically it fails
        // because the table name is between single-quotes
        $db->setQuery(str_replace('#__', $db->_table_prefix, "SHOW TABLES LIKE '$table'"));
        $result = $db->loadObject();
        
        if ($result === null) return false;
        else return true;
    }
    
    /**
     * Confirms existence of the DB table 
     * for associating Tienda users with LS customers
     * 
     */
    function checkTableLSCustomersXref()
    {
        // if this has already been done, don't repeat
        if (Tienda::getInstance()->get('checkTableLSCustomersXref', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_ls_customers_xref';
        $definition = "
            CREATE TABLE IF NOT EXISTS `#__tienda_ls_customers_xref` (
              `rowid` int(11) NOT NULL COMMENT 'LS Customer ID',
              `user_id` int(11) NOT NULL COMMENT 'Tienda User ID',
              UNIQUE KEY `customer_id` (`rowid`,`user_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        
        if ($this->createTable( $table, $definition ))
        {
            // Update config to say this has been done already
            DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
            $config = DSCTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkTableLSCustomersXref') );
            $config->config_name = 'checkTableLSCustomersXref';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Confirms existence of the DB table 
     * for associating Tienda orders with LS carts
     * 
     */
    function checkTableLSOrdersXref()
    {
        // if this has already been done, don't repeat
        if (Tienda::getInstance()->get('checkTableLSOrdersXref', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_ls_orders_xref';
        $definition = "
            CREATE TABLE IF NOT EXISTS `#__tienda_ls_orders_xref` (
              `rowid` int(11) NOT NULL COMMENT 'LS Cart ID',
              `order_id` int(11) NOT NULL COMMENT 'Tienda Order ID',
              UNIQUE KEY `cart_id` (`rowid`,`order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        
        if ($this->createTable( $table, $definition ))
        {
            // Update config to say this has been done already
            DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
            $config = DSCTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkTableLSOrdersXref') );
            $config->config_name = 'checkTableLSOrdersXref';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Confirms existence of the DB table 
     * for associating Tienda products with LS products
     * 
     */
    function checkTableLSProductsXref()
    {
        // if this has already been done, don't repeat
        if (Tienda::getInstance()->get('checkTableLSProductsXref', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_ls_products_xref';
        $definition = "
            CREATE TABLE IF NOT EXISTS `#__tienda_ls_products_xref` (
              `rowid` int(11) NOT NULL COMMENT 'LS Product ID',
              `product_id` int(11) NOT NULL COMMENT 'Tienda Product ID',
              UNIQUE KEY `ls_product_id` (`rowid`,`product_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        
        if ($this->createTable( $table, $definition ))
        {
            // Update config to say this has been done already
            DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
            $config = DSCTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkTableLSProductsXref') );
            $config->config_name = 'checkTableLSProductsXref';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Confirms existence of the DB table 
     * for associating Tienda categories with LS categories
     * 
     */
    function checkTableLSCategoriesXref()
    {
        // if this has already been done, don't repeat
        if (Tienda::getInstance()->get('checkTableLSCategoriesXref', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_ls_categories_xref';
        $definition = "
            CREATE TABLE IF NOT EXISTS `#__tienda_ls_categories_xref` (
              `rowid` int(11) NOT NULL COMMENT 'LS Category ID',
              `category_id` int(11) NOT NULL COMMENT 'Tienda Category ID',
              UNIQUE KEY `ls_category_id` (`rowid`,`category_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        
        if ($this->createTable( $table, $definition ))
        {
            // Update config to say this has been done already
            DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
            $config = DSCTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkTableLSCategoriesXref') );
            $config->config_name = 'checkTableLSCategoriesXref';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
}
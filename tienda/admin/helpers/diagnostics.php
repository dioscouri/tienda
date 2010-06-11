<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaHelperDiagnostics extends TiendaHelperBase 
{
    /**
     * Redirects with message
     * 
     * @param object $message [optional]    Message to display
     * @param object $type [optional]       Message type
     */
    function redirect($message = '', $type = '')
    {
        $mainframe = JFactory::getApplication();
        
        if ($message) 
        {
            $mainframe->enqueueMessage($message, $type);
        }
        
        JRequest::setVar('controller', 'dashboard');
        JRequest::setVar('view', 'dashboard');
        JRequest::setVar('task', '');
        return;
    }    

    /**
     * Performs basic checks on your installation to ensure it is OK
     * @return unknown_type
     */
    function checkInstallation() 
    {
        // Check default currency
        if (!$this->checkDefaultCurrency()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKDEFAULTCURRENCY FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        // check the productfiles table 
        // deprecate this check eventually, b/c it is only needed it the admin installed 0.2.0
        if (!$this->checkProductFiles()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKPRODUCTFILES FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        // check the orders table 
        if (!$this->checkOrdersOrderCurrency()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKORDERSORDERCURRENCY FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        // check the category root 
        if (!$this->checkCategoriesRootDesc()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKCATEGORIESROOTDESC FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        // check the products table 
        if (!$this->checkProductsParamsLayout()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKPRODUCTSPARAMSLAYOUT FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        // check the categories table 
        if (!$this->checkCategoriesParamsLayout()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKCATEGORIESPARAMSLAYOUT FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        // check the countries table 
        if (!$this->checkCountriesEnabled()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKCOUNTRIESENABLED FAILED') .' :: '. $this->getError(), 'error' );
        }
        if (!$this->checkCountriesOrdering()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKCOUNTRIESORDERING FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        // Check order history table
        if (!$this->checkOrderHistory()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKORDERHISTORY FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkProductsShortDesc()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKPRODUCTSSHORTDESC FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkTaxclassesOrdering()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKTAXCLASSESORDERING FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkOrdersOrderNumber()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKORDERSORDERNUMBER FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkOrderInfoZones()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKORDERINFOZONES FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkCurrenciesExchange()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKCURRENCIESEXCHANGE FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkCartsSessionId()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKCARTSSESSIONID FAILED') .' :: '. $this->getError(), 'error' );
        }
        
        if (!$this->checkOrderZoneAndUser()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKCARTSSESSIONID FAILED') .' :: '. $this->getError(), 'error' );
        }
        
   	    if (!$this->checkOrderPaymentReceived()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKCARTSSESSIONID FAILED') .' :: '. $this->getError(), 'error' );
        }
        

    }
    
    /**
     * Inserts fields into a table
     * 
     * @param string $table
     * @param array $fields
     * @param array $definitions
     * @return boolean
     */
    function insertTableFields($table, $fields, $definitions)
    {
        $database = JFactory::getDBO();
        $fields = (array) $fields;
        $errors = array();
        
        foreach ($fields as $field)
        {
            $query = " SHOW COLUMNS FROM {$table} LIKE '{$field}' ";
            $database->setQuery( $query );
            $rows = $database->loadObjectList();
            if (!$rows && !$database->getErrorNum()) 
            {       
                $query = "ALTER TABLE `{$table}` ADD `{$field}` {$definitions[$field]}; ";
                $database->setQuery( $query );
                if (!$database->query())
                {
                    $errors[] = $database->getErrorMsg();
                }
            }
        }
        
        if (!empty($errors))
        {
            $this->setError( implode('<br/>', $errors) );
            return false;
        }
        return true;
    }
    
    /**
     * Changes fields in a table
     * 
     * @param string $table
     * @param array $fields
     * @param array $definitions
     * @param array $newnames
     * @return boolean
     */
    function changeTableFields($table, $fields, $definitions, $newnames)
    {
        $database = JFactory::getDBO();
        $fields = (array) $fields;
        $errors = array();
        
        foreach ($fields as $field)
        {
            $query = " SHOW COLUMNS FROM {$table} LIKE '{$field}' ";
            $database->setQuery( $query );
            $rows = $database->loadObjectList();
            if ($rows && !$database->getErrorNum()) 
            {       
                $query = "ALTER TABLE `{$table}` CHANGE `{$field}` `{$newnames[$field]}` {$definitions[$field]}; ";
                $database->setQuery( $query );
                if (!$database->query())
                {
                    $errors[] = $database->getErrorMsg();
                }
            }
        }
        
        if (!empty($errors))
        {
            $this->setError( implode('<br/>', $errors) );
            return false;
        }
        return true;
    }
    
	/**
	 * Check if a default currencies has been selected,
	 * and if the selected currency really exists
	 * @return boolean
	 */
	function checkDefaultCurrency() 
	{
        $default_currencyid = TiendaConfig::getInstance()->get('default_currencyid', '-1');
        if ($default_currencyid == '-1')
        {
            JError::raiseNotice( 'checkDefaultCurrency', JText::_("No Default Currency Selected") );
            // do not return false here to enable users to actually change the default currency
            return true;
        } 
            else
        {
            // Check if the currency exists
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $table = JTable::getInstance('Currencies', 'TiendaTable');
            if ( !$table->load($default_currencyid) )
            {
                JError::raiseNotice( 'checkDefaultCurrency', JText::_("Currency does not exists") );
                // do not return false here to enable users to actually change the default currency
                return true;
            }
        }
        return true;
	}
	
    /**
     * Check if the _productfiles table is correct
     * This is only necessary if 0.2.0 was ever installed
     * 
     * @return boolean
     */
    function checkProductFiles() 
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkProductFiles', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_productfiles';
        $definitions = array();
        $fields = array();
        
        $fields[] = "file_id";
            $newnames["file_id"] = "productfile_id";
            $definitions["file_id"] = "int(11) NOT NULL AUTO_INCREMENT";

        $fields[] = "file_name";
            $newnames["file_name"] = "productfile_name";
            $definitions["file_name"] = "varchar(128) NOT NULL DEFAULT ''";

        $fields[] = "file_path";
            $newnames["file_path"] = "productfile_path";
            $definitions["file_path"] = "varchar(255) NOT NULL";
            
        $fields[] = "file_description";
            $newnames["file_description"] = "productfile_description";
            $definitions["file_description"] = "mediumtext NOT NULL";
            
        $fields[] = "file_extension";
            $newnames["file_extension"] = "productfile_extension";
            $definitions["file_extension"] = "varchar(6) NOT NULL DEFAULT ''";
            
        $fields[] = "file_mimetype";
            $newnames["file_mimetype"] = "productfile_mimetype";
            $definitions["file_mimetype"] = "varchar(64) NOT NULL DEFAULT ''";
            
        $fields[] = "file_url";
            $newnames["file_url"] = "productfile_url";
            $definitions["file_url"] = "varchar(255) NOT NULL DEFAULT ''";
            
        $fields[] = "file_enabled";
            $newnames["file_enabled"] = "productfile_enabled";
            $definitions["file_enabled"] = "tinyint(1) NOT NULL DEFAULT '0'";
        
        if ($this->changeTableFields( $table, $fields, $definitions, $newnames ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkProductFiles') );
            $config->config_name = 'checkProductFiles';
            $config->value = '1';
            $config->save();
            return true;
        }

        return false;        
    }
    
    /**
     * Checks the products table to confirm it has the params and layout fields
     * 
     * return boolean
     */
    function checkProductsInventory()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkProductsInventory', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_products';
        $definitions = array();
        $fields = array();
        
        $fields[] = "product_check_inventory";
            $definitions["product_check_inventory"] = "tinyint(1) DEFAULT '1' COMMENT 'Check Product Inventory?'";
            
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkProductsInventory') );
            $config->config_name = 'checkProductsInventory';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Checks the orders table to confirm it has the order_currency field
     * 
     * return boolean
     */
    function checkOrdersOrderCurrency()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkOrdersOrderCurrency', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_orders';
        $definitions = array();
        $fields = array();
        
        $fields[] = "order_currency";
            $newnames["order_currency"] = "order_currency";
            $definitions["order_currency"] = "TEXT NOT NULL COMMENT 'Stores a JParameter formatted version of the current currency. Used to maintain the order integrity'";
            
        if ($this->changeTableFields( $table, $fields, $definitions, $newnames ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkOrdersOrderCurrency') );
            $config->config_name = 'checkOrdersOrderCurrency';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Confirm that the category root is properly named and doesn't yell at users
     * return boolean
     */
    function checkCategoriesRootDesc()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkCategoriesRootDesc', '0'))
        {
            return true;
        }
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $category = JTable::getInstance( 'Categories', 'TiendaTable' );
        $root = $category->getRoot();
        
        if ($root->category_name == "ROOT" || $root->category_description == "root" || !empty($root->category_description))
        {
            $category->load( $root->category_id );
            $category->category_name = "All Categories";
            $category->category_description = "";
            if (!$category->save())
            {
                return false;
            }
        }

        // Update config to say this has been done already
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $config = JTable::getInstance( 'Config', 'TiendaTable' );
        $config->load( array( 'config_name'=>'checkCategoriesRootDesc') );
        $config->config_name = 'checkCategoriesRootDesc';
        $config->value = '1';
        $config->save();
        return true;        
    }
    
    /**
     * Checks the products table to confirm it has the params and layout fields
     * 
     * return boolean
     */
    function checkProductsParamsLayout()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkProductsParamsLayout', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_products';
        $definitions = array();
        $fields = array();
        
        $fields[] = "product_params";
            $definitions["product_params"] = "text";

        $fields[] = "product_layout";
            $definitions["product_layout"] = "varchar(255) DEFAULT '' COMMENT 'The layout file for this product'";
            
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkProductsParamsLayout') );
            $config->config_name = 'checkProductsParamsLayout';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Checks the categories table to confirm it has the layout field
     * 
     * return boolean
     */
    function checkCategoriesParamsLayout()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkCategoriesParamsLayout', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_categories';
        $definitions = array();
        $fields = array();
        
        $fields[] = "category_params";
            $definitions["category_params"] = "text";

        $fields[] = "category_layout";
            $definitions["category_layout"] = "varchar(255) DEFAULT '' COMMENT 'The layout file for this category'";

        $fields[] = "categoryproducts_layout";
            $definitions["categoryproducts_layout"] = "varchar(255) DEFAULT '' COMMENT 'The layout file for all products in this category'";
            
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkCategoriesParamsLayout') );
            $config->config_name = 'checkCategoriesParamsLayout';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Checks the countries table to confirm it has the enabled field
     * 
     * return boolean
     */
    function checkCountriesEnabled()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkCountriesEnabled', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_countries';
        $definitions = array();
        $fields = array();
        
        $fields[] = "country_enabled";
            $definitions["country_enabled"] = "TINYINT(1) NOT NULL DEFAULT '1'";
            
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkCountriesEnabled') );
            $config->config_name = 'checkCountriesEnabled';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Checks the countries table to confirm it has the ordering field
     * 
     * return boolean
     */
    function checkCountriesOrdering()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkCountriesOrdering', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_countries';
        $definitions = array();
        $fields = array();
        
        $fields[] = "ordering";
            $definitions["ordering"] = "int(11) NOT NULL";
            
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            
            // also fix the ordering values for the countries
            $country = JTable::getInstance( 'Countries', 'TiendaTable' );
            $country->reorder();
            
            // Update config to say this has been done already
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkCountriesOrdering') );
            $config->config_name = 'checkCountriesOrdering';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
	/**
     * Checks the orderhistory table to confirm it has the notify_customer field change
     * 
     * return boolean
     */
    function checkOrderHistory()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkOrderHistory', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_orderhistory';
        $definitions = array();
        $fields = array();
        
        $fields[] = "customer_notified";
            $newnames["customer_notified"] = "notify_customer";
            $definitions["customer_notified"] = "TINYINT(1) NOT NULL DEFAULT '1'";
            
        if ($this->changeTableFields( $table, $fields, $definitions, $newnames ))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            
            // Update config to say this has been done already
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkOrderHistory') );
            $config->config_name = 'checkOrderHistory';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * 
     * 
     * return boolean
     */
    function checkProductsShortDesc()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkProductsShortDesc', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_products';
        $definitions = array();
        $fields = array();
        
        $fields[] = "product_description_short";
            $definitions["product_description_short"] = "text";
            
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            
            // Update config to say this has been done already
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkProductsShortDesc') );
            $config->config_name = 'checkProductsShortDesc';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Checks the countries table to confirm it has the ordering field
     * As of v0.5.0
     * 
     * return boolean
     */
    function checkTaxclassesOrdering()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkTaxclassesOrdering', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_taxclasses';
        $definitions = array();
        $fields = array();
        
        $fields[] = "ordering";
            $definitions["ordering"] = "int(11) NOT NULL";
            
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            
            // also fix the ordering values
            $country = JTable::getInstance( 'Taxclasses', 'TiendaTable' );
            $country->reorder();
            
            // Update config to say this has been done already
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkTaxclassesOrdering') );
            $config->config_name = 'checkTaxclassesOrdering';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Checks the orders table for the order_number field
     * As of v0.5.0
     * 
     * return boolean
     */
    function checkOrdersOrderNumber()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkOrdersOrderNumber', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_orders';
        $definitions = array();
        $fields = array();
        
        $fields[] = "order_number";
            $definitions["order_number"] = "varchar(255) DEFAULT '' COMMENT 'The Invoice Number that Can be Set by Admins'";

        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkOrdersOrderNumber') );
            $config->config_name = 'checkOrdersOrderNumber';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Checks the orderinfo table for the zones fields
     * As of v0.5.0
     * 
     * return boolean
     */
    function checkOrderInfoZones()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkOrderInfoZones', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_orderinfo';
        $definitions = array();
        $fields = array();
        
        $fields[] = "billing_zone_id";
            $definitions["billing_zone_id"] = "int(11) NOT NULL DEFAULT '0'";
            
        $fields[] = "billing_country_id";
            $definitions["billing_country_id"] = "int(11) NOT NULL DEFAULT '0'";
            
        $fields[] = "shipping_zone_id";
            $definitions["shipping_zone_id"] = "int(11) NOT NULL DEFAULT '0'";
            
        $fields[] = "shipping_country_id";
            $definitions["shipping_country_id"] = "int(11) NOT NULL DEFAULT '0'";

        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkOrderInfoZones') );
            $config->config_name = 'checkOrderInfoZones';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Checks the currencies table for the exchange fields
     * As of v0.5.0
     * 
     * return boolean
     */
    function checkCurrenciesExchange()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkCurrenciesExchange', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_currencies';
        $definitions = array();
        $fields = array();
        
        $fields[] = "updated_date";
            $definitions["updated_date"] = "datetime NOT NULL COMMENT 'The last time the currency was updated'";
            
        $fields[] = "exchange_rate";
            $definitions["exchange_rate"] = "DECIMAL(15,8) NOT NULL DEFAULT '0.00000000' COMMENT 'Value of currency in USD'";
            
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkCurrenciesExchange') );
            $config->config_name = 'checkCurrenciesExchange';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    /**
     * Checks the carts table for missing fields
     * As of v0.5.0
     * 
     * return boolean
     */
    function checkCartsSessionId()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkCartsSessionId', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_carts';
        $definitions = array();
        $fields = array();
        
        $fields[] = "session_id";
            $definitions["session_id"] = "VARCHAR(200) NOT NULL";
            
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkCartsSessionId') );
            $config->config_name = 'checkCartsSessionId';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    
     /**
     * Checks the orderinfo table for the zone id and the user id 
     * As of v0.5.0
     * 
     * return boolean
     */
    function checkOrderZoneAndUser()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkOrderZoneAndUser', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_orderinfo';
        $definitions = array();
        $fields = array();
        
        $fields[] = "zone_id";
            $definitions["zone_id"] = "int(11) NULL DEFAULT '0'";
     
        $fields[] = "user_id";
            $definitions["user_id"] = "int(11)NULL DEFAULT '0'";
       

        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkOrderZoneAndUser') );
            $config->config_name = 'checkOrderZoneAndUser';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
    
    
   /**
     * Checks the orders table for the payment_received
     * As of v0.5.0
     * 
     * return boolean
     */
    function checkOrderPaymentReceived()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkOrderPaymentReceived', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_orders';
        $definitions = array();
        $fields = array();
        
        $fields[] = "payment_received";
            $definitions["payment_received"] = "TINYINT(1) NOT NULL DEFAULT '0'";
     
       
        if ($this->insertTableFields( $table, $fields, $definitions ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkOrderPaymentReceived') );
            $config->config_name = 'checkOrderPaymentReceived';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
}
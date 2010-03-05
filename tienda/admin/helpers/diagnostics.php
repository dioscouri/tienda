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

JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );

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
        // TODO deprecate this check eventually, b/c it is only needed it the admin installed 0.2.0
        if (!$this->checkProductFiles()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC CHECKPRODUCTFILES FAILED') .' :: '. $this->getError(), 'error' );
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
            $this->setError(JText::_("No Default Currency Selected"));
            return false;
        } 
            else
        {
            // Check if the currency exists
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $table = JTable::getInstance('Currencies', 'TiendaTable');
            if ( !$table->load($default_currencyid) )
            {
                $this->setError(JText::_("Currency does not exists"));
                return false;   
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
            $table = JTable::getInstance( 'Config', 'TiendaTable' );
            $table->load( 'checkProductFiles' );
            $table->config_name = 'checkProductFiles';
            $table->value = '1';
            $table->save();
            return true;
        }

        return false;        
    }

}
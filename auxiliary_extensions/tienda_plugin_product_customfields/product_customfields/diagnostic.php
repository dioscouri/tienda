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

class TiendaHelperDiagnosticsProductCustomFields extends TiendaHelperDiagnostics 
{
    /**
     * Performs basic checks on your installation to ensure it is OK
     * @return unknown_type
     */
    function checkInstallation() 
    {
		$prod_cf_config = 'checkTableProductsForCustomFieldsColumn';
    	if (!$this->checkTableColumn($prod_cf_config, '#__tienda_products', 'product_customfields', 'text NOT NULL'))
    	{
    		return $this->redirect( JText::_('DIAGNOSTIC '.$prod_cf_config_name.' FAILED') .' :: '. $this->getError(), 'error' );
    	}
    	
    	$carts_cf_config = 'checkTableCartsForCustomFieldsIDColumn';
        if (!$this->checkTableColumn($carts_cf_config, '#__tienda_carts', 'cartitem_customfields_id', 'INT(11) NOT NULL'))
    	{
    		return $this->redirect( JText::_('DIAGNOSTIC '.$carts_cf_config_name.' FAILED') .' :: '. $this->getError(), 'error' );
    	}

    	$carts_cf_config = 'checkTableCartsForCustomFieldsColumn';
        if (!$this->checkTableColumn($carts_cf_config, '#__tienda_carts', 'cartitem_customfields', 'text NOT NULL'))
    	{
    		return $this->redirect( JText::_('DIAGNOSTIC '.$carts_cf_config_name.' FAILED') .' :: '. $this->getError(), 'error' );
    	}
    	
    	$orderitems_cf_config = 'checkTableOrderItemsForCustomFieldsIDColumn';
        if (!$this->checkTableColumn($orderitems_cf_config, '#__tienda_orderitems', 'orderitem_customfields_id', 'INT(11) NOT NULL'))
    	{
    		return $this->redirect( JText::_('DIAGNOSTIC '.$orderitems_cf_config.' FAILED') .' :: '. $this->getError(), 'error' );
    	}    	

    	$orderitems_cf_config = 'checkTableOrderItemsForCustomFieldsColumn';
        if (!$this->checkTableColumn($orderitems_cf_config, '#__tienda_orderitems', 'orderitem_customfields', 'text NOT NULL'))
    	{
    		return $this->redirect( JText::_('DIAGNOSTIC '.$orderitems_cf_config.' FAILED') .' :: '. $this->getError(), 'error' );
    	}    	
    	
    }

    function checkTableColumn( $config_name, $table, $column, $attributes)
    {    	
        if ($this->getConfig( $config_name )) return true;
        
        if ($this->createColumn( $table, $column, $attributes ))
        {
        	$this->setConfig( $config_name );
            return true;
        }
        return false;
    }

    function createColumn( $table, $column, $attributes )
    {
        if (!$this->columnExists( $table, $column ))
        {
            $db =& JFactory::getDBO();
            $query = "ALTER TABLE `".$table."` ADD `".$column."` ".$attributes."";
            $db->setQuery(str_replace('#__', $db->_table_prefix, $query));
            if (!$db->query())
            {
                $this->setError( $db->getErrorMsg() );
                return false;
            }
        }
        return true;        
    }  
      
    function columnExists( $table, $column )
    {
        $db =& JFactory::getDBO();
        
        // Manually replace the Joomla Tables prefix. Automatically it fails
        // because the table name is between single-quotes
        $db->setQuery(str_replace('#__', $db->_table_prefix, "SHOW COLUMNS FROM $table"));
        $columns = $db->loadObjectList();
        
        if ($columns === null) return false;
        
        $exists = false;
		foreach ($columns as $column) {
        	if ($column->Field == $column) {
            	$exists = true;
            	break;
         	}
      	}        
        return $exists;    	
    }
    
    function getConfig( $config_name )
    {
		return Tienda::getInstance()->get($config_name, '0');    	
    }
    
    function setConfig( $config_name )
    {
		// Update config to say this has been done already
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $config = JTable::getInstance( 'Config', 'TiendaTable' );
        $config->load( array( 'config_name'=>$config_name) );
        $config->config_name = $config_name;
        $config->value = '1';
        $config->save();
    }
}
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

class TiendaHelperDiagnosticsJEvents extends TiendaHelperDiagnostics 
{
    /**
     * Performs basic checks on your installation to ensure it is OK
     * @return unknown_type
     */
    function checkInstallation() 
    {
        if (!$this->checkTableJEventEventsProducts()) 
        {
            return $this->redirect( JText::_('DIAGNOSTIC checkTableJEventEventsProducts FAILED') .' :: '. $this->getError(), 'error' );
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
            $db =& JFactory::getDBO();
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
        $db =& JFactory::getDBO();
        
        // Manually replace the Joomla Tables prefix. Automatically it fails
        // because the table name is between single-quotes
        $db->setQuery(str_replace('#__', $db->_table_prefix, "SHOW TABLES LIKE '$table'"));
        $result = $db->loadObject();
        
        if ($result === null) return false;
        else return true;
    }
    
    /**
     * Confirms existence of the DB table 
     * for associating Tienda products with the JEvent
     * 
     */
    function checkTableJEventEventsProducts()
    {
        // if this has already been done, don't repeat
        if (TiendaConfig::getInstance()->get('checkTableJEventEventsProducts', '0'))
        {
            return true;
        }
        
        $table = '#__tienda_jeventseventsproducts';
        $definition = "
        CREATE TABLE IF NOT EXISTS `#__tienda_jeventseventsproducts` (
		  `product_id` int(11) NOT NULL COMMENT 'Tienda product id',
		  `event_id` int(11) NOT NULL COMMENT 'JEvents event id',
		  UNIQUE KEY `jeventseventproduct` (`product_id`,`event_id`)
		) ENGINE=MyISAM ";
        
        if ($this->createTable( $table, $definition ))
        {
            // Update config to say this has been done already
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $config = JTable::getInstance( 'Config', 'TiendaTable' );
            $config->load( array( 'config_name'=>'checkTableJEventEventsProducts') );
            $config->config_name = 'checkTableJEventEventsProducts';
            $config->value = '1';
            $config->save();
            return true;
        }
        return false;        
    }
    
  
   
}
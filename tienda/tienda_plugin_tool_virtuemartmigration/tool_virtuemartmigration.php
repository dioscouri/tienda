<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Daniele Rosario
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaToolPlugin', 'library.plugins.tool' );

class plgTiendaTool_VirtueMartMigration extends TiendaToolPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'tool_virtuemartmigration';
    
    /**
     * @var $_tablename  string  A required tablename to use when verifying the provided prefix  
     */    
    var $_tablename = 'vm_product';
    
	function plgTiendaTool_VirtueMartMigration(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
    /**
     * Overriding 
     * 
     * @param $options
     * @return unknown_type
     */
    function onGetToolView( $row )
    {
        if (!$this->_isMe($row)) 
        {
            return null;
        }
        
        // go to a "process suffix" method
        // which will first validate data submitted,
        // and if OK, will return the html?
        $suffix = $this->_getTokenSuffix();
        $html = $this->_processSuffix( $suffix );

        return $html;
    }
    
    /**
     * Validates the data submitted based on the suffix provided
     * 
     * @param $suffix
     * @return html
     */
    function _processSuffix( $suffix='' )
    {
        $html = "";
        
        switch($suffix)
        {
            case"2":
                if (!$verify = $this->_verifyDB())
                {
                    JError::raiseNotice('_verifyDB', $this->getError());
                    $html .= $this->_renderForm( '1' );
                }
                    else
                {
                    // migrate the data and output the results
                    $html .= $this->_doMigration();
                }
                break;
            case"1":
                if (!$verify = $this->_verifyDB())
                {
                    JError::raiseNotice('_verifyDB', $this->getError());
                    $html .= $this->_renderForm( '1' );
                }
                    else
                {
                    $suffix++;
                    // display a 'connection verified' message
                    // and request confirmation before migrating data
                    $html .= $this->_renderForm( $suffix );
                    $html .= $this->_renderView( $suffix );                    
                }
                break;
            default:
                $html .= $this->_renderForm( '1' );
                break;
        }
        
        return $html;
    }
	
    /**
     * Prepares the 'view' tmpl layout
     *  
     * @return unknown_type
     */
    function _renderView( $suffix='' )
    {
        $vars = new JObject();
        $layout = 'view_'.$suffix;
        $html = $this->_getLayout($layout, $vars);
        
        return $html;
    }
    
    /**
     * Prepares variables for the form
     * 
     * @return unknown_type
     */
    function _renderForm( $suffix='' )
    {
        $vars = new JObject();
        $vars->token = $this->_getToken( $suffix );
        $vars->state = $this->_getState();
        
        $layout = 'form_'.$suffix;
        $html = $this->_getLayout($layout, $vars);
        
        return $html;
    }

    /**
     * Gets the appropriate values from the request
     * 
     * @return unknown_type
     */
    function _getState()
    {
        $state = new JObject();
        $state->host = '';
        $state->user = '';
        $state->password = '';
        $state->database = '';
        $state->prefix = '';
        $state->vm_prefix = '';
        $state->driver = 'mysql';
        $state->port = '3306';
        
        foreach ($state->getProperties() as $key => $value)
        {
            $new_value = JRequest::getVar( $key );
            $value_exists = array_key_exists( $key, $_POST );
            if ( $value_exists && !empty($key) )
            {
                $state->$key = $new_value;
            }
        }
        return $state;
    }

    /**
     * Perform the data migration
     * 
     * @return html
     */
    function _doMigration()
    {
        $html = "";
        $vars = new JObject();
        
        // perform the data migration
        // grab all the data and insert it into the tienda tables
        // if the host or database names are diff from the joomla one
        $state = $this->_getState();
        $conf =& JFactory::getConfig();
        $jHost       = $conf->getValue('config.host');
        $jDatabase   = $conf->getValue('config.db');
        
        if (($state->database != $jDatabase) && ($state->host != $jHost))
        {
            // then we can do an insert select
            $results = $this->_migrateInternal($state->prefix, $state->vm_prefix);                        
        }
            else
        {
            // cannot do an insert select
            $results = $this->_migrateExternal($state->prefix, $state->vm_prefix);            
        }
        $vars->results = $results;
        
        $suffix = $this->_getTokenSuffix();
        $suffix++;
        $layout = 'view_'.$suffix;
                
        $html = $this->_getLayout($layout, $vars);
        return $html;
    }
    
    /**
     * Do the migration
     * where the target and source db is the same 
     * 
     * @return array
     */
    function _migrateInternal($prefix = 'jos_', $vm_prefix = 'vm_')
    {
        $queries = array();
        
        $p = $prefix.$vm_prefix;
        
        $queries[0]->title = "CATEGORIES";
        $queries[0] = "
            INSERT IGNORE INTO #__tienda_categories ( category_id, parent_id, category_name, category_description, category_enabled )
            SELECT c.category_id, cx.category_parent_id, c.category_name, c.category_description, IF(c.category_publish = 'Y', 1, 0) AS category_enabled
            FROM {$p}category as c, {$p}category_xref as cx WHERE c.category_id = cx.category_child_id;
        ";
        
        $queries[1]->title = "PRODUCTS";
        $queries[1] = "
            INSERT IGNORE INTO #__tienda_products ( product_id, product_sku, product_name, product_weight, product_description, product_width, product_length, product_height, product_enabled )
            SELECT p.product_id, p.product_sku, p.product_name, p.product_weight, p.product_desc, p.product_width, p.product_length, p.product_height, IF(p.product_publish = 'Y', 1, 0) AS product_enabled
            FROM {$p}product as p;
        ";
        
        $queries[2]->title = "QUANTITIES";
        $queries[2] = "            
            INSERT IGNORE INTO #__tienda_productquantities ( quantity, product_id )
            SELECT p.product_in_stock, p.product_id
            FROM {$p}product as p;
        ";
        
        $queries[3]->title = "PRICES";
        $queries[3] = "
            INSERT IGNORE INTO #__tienda_productprices ( product_id, product_price, price_quantity_start, price_quantity_end )
            SELECT p.product_id, p.product_price, p.price_quantity_start, p.price_quantity_end
            FROM {$p}product_price as p;
        ";
        
        $queries[4]->title = "PRODUCT CATEGORIES XREF";
        $queries[4] = "
            INSERT IGNORE INTO #__tienda_productcategoryxref ( category_id, product_id )
            SELECT p.category_id, p.product_id
            FROM {$p}product_category_xref as p;
        ";
        
        
        $results = array();
        $db = JFactory::getDBO();
        $n=0;
        foreach ($queries as $query)
        {
            $db->setQuery($query);
            $results[$n]->title = $query->title;
            $results[$n]->query = $db->getQuery();
            $results[$n]->error = '';
            if (!$db->query())
            {
                $results[$n]->error = $db->getErrorMsg();
            }
            $results[$n]->affectedRows = $db->getAffectedRows();
            $n++; 
        }
        
        return $results;
    }

    /**
     * Do the migration
     * where the target and source db are not the same 
     * 
     * @return array
     */
    function _migrateExternal($prefix = 'jos_', $vm_prefix = 'vm_')
    {
        $queries = array();
        
        $p = $prefix.$vm_prefix;
        
        // migrate categories
         $queries[0]->title = "CATEGORIES";
        $queries[0]->select = "
            SELECT c.category_id, cx.category_parent_id, c.category_name, c.category_description, IF(c.category_publish = 'Y', 1, 0) AS category_enabled
            FROM {$p}category as c, {$p}category_xref as cx WHERE c.category_id = cx.category_child_id;
        ";
        $queries[0]->insert = "
            INSERT IGNORE INTO #__tienda_categories ( category_id, parent_id, category_name, category_description, category_enabled )
            VALUES ( %s )
        ";

        // migrate products
        $queries[1]->title = "PRODUCTS";
        $queries[1]->select = "
            SELECT p.product_id, p.product_sku, p.product_name, p.product_weight, p.product_desc, p.product_width, p.product_length, p.product_height, IF(p.product_publish = 'Y', 1, 0) AS product_enabled
            FROM {$p}product as p;
        ";
        $queries[1]->insert = "
            INSERT IGNORE INTO #__tienda_products ( product_id, product_sku, product_name, product_weight, product_description, product_width, product_length, product_height, product_enabled )
            VALUES ( %s )
        ";
        
        // migrate product quantities
        $queries[2]->title = "QUANTITIES";
        $queries[2]->select = "            
            SELECT p.product_in_stock, p.product_id
            FROM {$p}product as p;
        ";
        $queries[2]->insert = "            
            INSERT IGNORE INTO #__tienda_productquantities ( quantity, product_id )
            VALUES ( %s )
        ";
        
        // migrate product prices
        $queries[3]->title = "PRICES";
        $queries[3]->select = "
            SELECT p.product_id, p.product_price, p.price_quantity_start, p.price_quantity_end
            FROM {$p}product_price as p;
        ";
        $queries[3]->insert = "
            INSERT IGNORE INTO #__tienda_productprices ( product_id, product_price, price_quantity_start, price_quantity_end )
            VALUES ( %s )
        ";
        
        // migrate product categories xref
		$queries[4]->title = "PRODUCT CATEGORIES XREF";
        $queries[4]->select = "
            SELECT p.category_id, p.product_id
            FROM {$p}product_category_xref as p;
        ";
        $queries[4]->insert = "
            INSERT IGNORE INTO #__tienda_productcategoryxref ( category_id, product_id )
            VALUES ( %s )
        ";
        
        $results = array();
        $jDBO = JFactory::getDBO();
        $sourceDB = $this->_verifyDB();        
        $n=0;
        foreach ($queries as $query)
        {
            $errors = array();
            $sourceDB->setQuery($query->select);
            
            if ($rows = $sourceDB->loadObjectList())
            {                
                foreach ($rows as $row)
                {
                    $values = array();
                    foreach (get_object_vars($row) as $key => $value)
                    {
                        $values[] = $jDBO->Quote( $value );
                    }
                    $string = implode( ",", $values );
                    $insert_query = sprintf( $query->insert, $string );

                    $jDBO->setQuery( $insert_query );
                    if (!$jDBO->query())
                    {
                        $errors[] = $jDBO->getErrorMsg();
                    }
                }
            }
            $results[$n]->title = $query->title;
            $results[$n]->query = $query->insert;
            $results[$n]->error = implode('\n', $errors);
            $results[$n]->affectedRows = count( $rows );
            $n++; 
        }
        
        return $results;
    }
   
}

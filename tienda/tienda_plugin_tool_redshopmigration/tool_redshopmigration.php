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

class plgTiendaTool_RedShopMigration extends TiendaToolPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'tool_redshopmigration';
    
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
        $state->prefix = 'jos_';
        $state->vm_prefix = 'vm_';
        $state->driver = 'mysql';
        $state->port = '3306';
        $state->external_site_url = '';
        
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
}

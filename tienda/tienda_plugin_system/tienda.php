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

/** Import library dependencies */
jimport('joomla.plugin.plugin');

class plgSystemTienda extends JPlugin 
{
    /**
     * This holds the html output by our override controller
     * @var unknown_type
     */
    var $_html = null;
    
    function plgSystemTienda(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }
    
    /**
     * Loads overrides if they exist
     * otherwise just keeps quiet
     * 
     * We should execute here so we run before the core component does
     * (until we can figure out how to completely stop the core component
     * from loading at all)
     *  
     * @return unknown_type
     */
    function onAfterInitialise() 
    {
        $success = null;

        // Should check that Tienda is installed first before executing
        if (!$this->_isInstalled())
        {
            return $success;
        }
        
        // get the option variable
        // and get rid of the com_
        $option = JRequest::getVar( 'option' );
        $name = str_replace("com_", "", $option);

        // does an override exist for this component?
        // if so, include it!  Hooray!  Drinks for everyone!
        if (!$this->overrideExists( $name )) 
        {
            // if not, quietly exit stage left
            return $success;
        }
        // hee hee, this method returns the same thing no matter what :-)
        return $success;
    }
    
    /**
     * Checks if a component-specific override exists 
     * 
     * @return boolean
     */
    function overrideExists( $name )
    {
        $success = false;

        $app = JFactory::getApplication();
        $site = 'site';
        if ( $app->isAdmin() ) 
        {
            $site = 'admin';
        }
        
        jimport('joomla.filesystem.file');
        $file = JPATH_SITE.DS."plugins".DS."system".DS."tienda".DS."components".DS.$name.DS.$site.DS."{$name}.php";
        // Enable each override to be disabled by a param in the xml file
        if (JFile::exists( $file ) && $this->params->get( "{$site}_override_{$name}", '0' ) ) 
        {
            // this includes the override for the entrypoint file of the component
            // which starts the entire override
            // enjoy the ride!
            ob_start();
            
            if ($disable_error_reporting = $this->params->get( "disable_error_reporting", '0' ))
            {
                // disable error reporting if this is a live site
                ini_set('display_errors', 0);
                ini_set('error_reporting', 0);                
            }
            
            require_once( $file );
            $this->_html = ob_get_contents(); 
            ob_end_clean();
                        
            $success = true;
        }
        return $success;
    }
    
    /**
     * This sets the document buffer to whatever came out of our overrides
     */
    function onAfterDispatch()
    {
        $success = null;

        // Should check that Tienda is installed first before executing
        if (!$this->_isInstalled())
        {
            return $success;
        }
        
        if (!empty($this->_html))
        {
            $document =& JFactory::getDocument();
            $document->setBuffer( $this->_html, 'component' );
        }
    }
    
    /**
     * Checks the extension is installed
     * 
     * @return boolean
     */
    function _isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php')) 
        {
            $success = true;
            // Check the registry to see if our Tienda class has been overridden
            if ( !class_exists('Tienda') ) { 
                JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
                JLoader::register( "TiendaConfig", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
            }
        }
        return $success;
    }
}

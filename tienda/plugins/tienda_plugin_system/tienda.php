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
    
    function __construct(& $subject, $config)
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
    function onAfterRoute() 
    {
        $success = null;

        // Should check that Tienda is installed first before executing
        if (!$this->_isInstalled())
        {
            return $success;
        }
        
        // clean expired session carts
        $this->deleteExpiredSessionCarts();
        
        // clean expired session products compared
        $this->deleteExpiredSessionProductsCompared();
        
        // get the option variable
        // and get rid of the com_
        $option = JRequest::getCmd( 'option', '', 'get' );
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
        $file = JPATH_SITE."/plugins/system/tienda/tienda/components/".$name."/".$site."/{$name}.php";
    
        // Enable each override to be disabled by a param in the xml file
        if (JFile::exists( $file ) && $this->_activeOverride( $name, $site ) ) 
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
						echo $this->_html;
                        
            $success = true;
        }
        return $success;
    }
    
    /**
     * This method determines, if the override is active
     */         
    function _activeOverride( $name, $site )
    {
      $aliases = array( 'users' => 'user' );
      
      $param = "{$site}_override_{$name}";
      if( isset($aliases[$name] ) )
        $param = "{$site}_override_{$aliases[$name]}";   
    
      return $this->params->get( $param, '0' );
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
            $document = JFactory::getDocument();
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
        if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_tienda/defines.php')) 
        {
            $success = true;
            // Check the registry to see if our Tienda class has been overridden
            if ( !class_exists('Tienda') ) { 
                JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
            
            }
        }
        return $success;
    }
    
    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
    function deleteExpiredSessionCarts()
    {
        $config = Tienda::getInstance();
        $last_run = $config->get('last_deleted_expired_sessioncarts');
        
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $helper = new TiendaHelperBase();
        
        $date = JFactory::getDate();
        $now = $date->toMySQL();
        
        $three_hours_ago = $helper->getOffsetDate($now, '-3');
        
        // when was this last run?
        // if it was run more than 3 hours ago, run again
        if ($last_run < $three_hours_ago)
        {
            // run it
            jimport( 'joomla.application.component.model' );
            DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
            $model = DSCModel::getInstance( 'Carts', 'TiendaModel');
            $model->deleteExpiredSessionCarts();
        } 

        return;        
    }
    
	/**
     * Method to delete expired session compared products 
     * @return void
     */
    function deleteExpiredSessionProductsCompared()
    {
        $config = Tienda::getInstance();
        $last_run = $config->get('last_deleted_expired_sessionproductscompared');
        
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $helper = new TiendaHelperBase();
        
        $date = JFactory::getDate();
        $now = $date->toMySQL();
        
        $three_hours_ago = $helper->getOffsetDate($now, '-3');
        
        // when was this last run?
        // if it was run more than 3 hours ago, run again
        if ($last_run < $three_hours_ago)
        {
            // run it
            jimport( 'joomla.application.component.model' );
            DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
            $model = DSCModel::getInstance( 'ProductCompare', 'TiendaModel');
            $model->deleteExpiredSessionProductCompared();
        } 

        return;        
    }
}

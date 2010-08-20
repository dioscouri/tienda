<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport( 'joomla.application.component.model' );

class plgSystemTienda_Subscriptions extends JPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'tienda_subscriptions';
    
	function plgSystemTienda_Subscriptions(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
    /**
     * Checks the extension is installed
     * 
     * @return boolean
     */
    function isInstalled()
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
	
    /**
     * 
     * @return unknown_type
     */
    function onAfterInitialise() 
    {
        $success = null;
        if (!$this->isInstalled()) {
            return $success;    
        }
        
        if (!$this->canRun()) {
            return $success;    
        }

        Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
        $helper = new TiendaHelperSubscription();
        $helper->checkExpired();
        $helper->checkExpiring();
        
        return $success;
    }
    
    /**
     * Checks params and lastchecked to see if function should run again today
     * 
     * @return unknown_type
     */
    function canRun() 
    {
        $success = false;
        
        // Use config to store & retrieve lastchecked from the __config table
        $config = TiendaConfig::getInstance();
        $lastchecked = $config->get( 'subscriptions_last_checked' );
        $date = JFactory::getDate();
        $today = $date->toFormat( "%Y-%m-%d 00:00:00" );
        
        if ($lastchecked < $today) 
        {
            if (JFactory::getApplication()->isAdmin() && !empty(JFactory::getUser()->id) )
            {
                JError::raiseNotice('plgSystemTienda_Subscriptions::canRun', sprintf(JTEXT::_("TIENDA MSG SENDING SUBSCRIPTION EMAIL NOTICES" ), $lastchecked, $today));     
            }
            $success = true;
        }
                
        return $success;    
    }
}

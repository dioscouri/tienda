<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.plugin.plugin');

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

class plgUserTienda extends JPlugin
{
    function plgUserTienda(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage( '', JPATH_ADMINISTRATOR );
    }

    /**
     * When the user logs in, their session cart should override their db-stored cart.
     * Current actions take precedence
     * 
     * @param $user
     * @param $options
     * @return unknown_type
     */
    function onLoginUser($user, $options)
    {	
    	$session =& JFactory::getSession();
    	$old_sessionId = $session->get( 'old_sessionId' );
    	   	
    	// Should check that Tienda is installed first before executing
        if (!$this->_isInstalled())
        {
            return;
        }
        
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        if (!empty($old_sessionId))
        {
            TiendaHelperCarts::updateCart('', true, $old_sessionId);    
        }
            else
        {
            TiendaHelperCarts::updateCart( '', true );
        }
        
        TiendaHelperCarts::cleanCart();
        
        return true;
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
        }
        return $success;
    }
}
?>

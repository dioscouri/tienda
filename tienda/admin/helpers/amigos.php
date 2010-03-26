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

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

Tienda::load( "TiendaHelperBase", 'helpers._base' );

class TiendaHelperAmigos extends TiendaHelperBase 
{
    /**
     * Checks if Amigos is installed
     * 
     * @return boolean
     */
    function isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_amigos'.DS.'helpers'.DS.'_base.php')) 
        {
            $success = true;
        }                
        return $success;
    }
    
    /**
     * Gets a user's referral status
     * and returns boolean
     * 
     * @param int $userid
     * @return boolean
     */
    function getReferralStatus( $userid )
    {
        $return = false;
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_amigos'.DS.'tables' );
        unset($logByUser);
        $logByUser = JTable::getInstance('Logs', 'Table');
        $logByUser->load( $userid, 'userid' );
        if (!empty($logByUser->accountid))
        {
            $return = $logByUser;
        }
        
        return $return;
    }
}
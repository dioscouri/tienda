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
    	$old_sessionid = $session->get( 'old_sessionid' );

    	$user['id'] = intval(JUserHelper::getUserId($user['username']));
    	
    	// Should check that Tienda is installed first before executing
        if (!$this->_isInstalled())
        {
            return;
        }

        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $helper = new TiendaHelperCarts();
        if (!empty($old_sessionid))
        {
            $helper->mergeSessionCartWithUserCart( $old_sessionid, $user['id'] );
        }
            else
        {
            $helper->updateUserCartItemsSessionId( $user['id'], $session->getId() );
        }
        
        $this->checkUserGroup();

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
    
    /**
     * check whether user belongs to a group or not 
     * in case not then add them to the default group
     *
     * @return unknown type
     */
    function checkUserGroup()
    {
        $user = JFactory::getUser();
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $user_groups = JTable::getInstance('UserGroups', 'TiendaTable');
        $user_groups->load(array('user_id'=>$user->id));
        
        if (empty($user_groups->groupid))
        {
            $user_groups->group_id = TiendaConfig::getInstance()->get('default_user_group', '1'); ; // If there is no user selected then it will consider as default user group 
            $user_groups->user_id = $user->id;
            if (!$user_groups->save())
            {
            	// TODO if data does not save in the mapping table, what to do? 
            }
        }
    }
}
?>
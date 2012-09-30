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
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );

class plgUserTienda extends JPlugin
{
	
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $language = JFactory::getLanguage();
		$language -> load('plg_user_tienda', JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_user_tienda', JPATH_ADMINISTRATOR, null, true);
    }

    /**
     * When the user logs in, their session cart should override their db-stored cart.
     * Current actions take precedence
     * For Joomla 1.5     
     *
     * @param $user
     * @param $options
     * @return unknown_type
     */
    public function onLoginUser($user, $options = array())
    {
      return $this->LoginUserEvent($user, $options);
    }

    /**
     * When the user logs in, their session cart should override their db-stored cart.
     * Current actions take precedence
     *
     * @param $user
     * @param $options
     * @return unknown_type
     */
    public function onUserLogin($user, $options = array())
    {
      return $this->LoginUserEvent($user, $options);
    }
    
    /**
     * When the user logs in, their session cart should override their db-stored cart.
     * Current actions take precedence
     *
     * @param $user
     * @param $options
     * @return unknown_type
     */
    function LoginUserEvent( $user, $options = array() )
    {
    	$session = JFactory::getSession();
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
            
            JModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
		        $wishlist_model = JModel::getInstance( 'Wishlists', 'TiendaModel' );
            $wishlist_model->setUserForSessionItems( $old_sessionid, $user['id'] );
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
        if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_tienda/defines.php'))
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
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $user_groups = JTable::getInstance('UserGroups', 'TiendaTable');
        $user_groups->load(array('user_id'=>$user->id));
        
        if (empty($user_groups->group_id))
        {
            $user_groups->group_id = Tienda::getInstance()->get('default_user_group', '1'); ; // If there is no user selected then it will consider as default user group 
            $user_groups->user_id = $user->id;
            if (!$user_groups->save())
            {
            	// TODO if data does not save in the mapping table, what to do? 
            }
        }
    }
}
?>
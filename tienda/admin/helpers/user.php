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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaHelperUser extends TiendaHelperBase
{
    /**
     * Gets a users basic information
     * 
     * @param int $userid
     * @return obj TiendaAddresses if found, false otherwise
     */
    function getBasicInfo( $userid )
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $row = JTable::getInstance('UserInfo', 'TiendaTable');
        $row->load( array( 'user_id' => $userid ) );
        return $row;
    }
    
    /**
     * Gets a users primary address, if possible
     * 
     * @param int $userid
     * @return obj TiendaAddresses if found, false otherwise
     */
    function getPrimaryAddress( $userid, $type='billing' )
    {
        $return = false;
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Addresses', 'TiendaModel' );
        switch($type)
        {
            case "shipping":
                $model->setState('filter_isdefaultshipping', '1');
                break;
            default:
                $model->setState('filter_isdefaultbilling', '1');
                break;
        }
        $model->setState('filter_userid', (int) $userid);
        $items = $model->getList();
        if (empty($items))
        {
            $model = JModel::getInstance( 'Addresses', 'TiendaModel' );
            $model->setState('filter_userid', (int) $userid);
            $items = $model->getList();
        }
        
        if (!empty($items))
        {
            $return = $items[0];
        }       
        
        return $return;
    }
    
    /**
     * Gets a user's geozone
     * @param int $userid
     * @return unknown_type
     */
    function getGeoZones( $userid )
    {
        Tienda::load( 'TiendaHelperShipping', 'helpers.shipping' );
        
        $address = TiendaHelperUser::getPrimaryAddress( $userid, 'billing' );
        if (empty($address->zone_id))
        {
            return array();
        }
        
        $geozones = TiendaHelperShipping::getGeoZones( $address->zone_id, '1', $address->postal_code );
        return $geozones;
    }
    
    /**
     * 
     * @param $string
     * @return unknown_type
     */
    function usernameExists( $string ) 
    {
        // TODO Make this use ->load()
        
        $success = false;
        $database = JFactory::getDBO();
        $string = $database->getEscaped($string);
        $query = "
            SELECT 
                *
            FROM 
                #__users
            WHERE 1
            AND 
                `username` = '{$string}'
            LIMIT 1
        ";
        $database->setQuery($query);
        $result = $database->loadObject();
        if ($result) {
            $success = true;
        }
        return $success;
    }

    /**
     * 
     * @param $string
     * @return unknown_type
     */
    function emailExists( $string, $table='users'  ) {
        switch($table)
        {
            case 'accounts' :
                $table = '#__tienda_accounts';
                break;

            case  'users':
            default     :
                $table = '#__users';
        }
        
        $success = false;
        $database = JFactory::getDBO();
        $string = $database->getEscaped($string);
        $query = "
            SELECT 
                *
            FROM 
                $table
            WHERE 1
            AND 
                `email` = '{$string}'
            LIMIT 1
        ";
        $database->setQuery($query);
        $result = $database->loadObject();
        if ($result) {
            $success = true;
        }       
        return $result;     
    }
    

    /**
     * Returns yes/no
     * @param mixed Boolean
     * @param mixed Boolean
     * @return array
     */
    function createNewUser( $details, $guest=false ) 
    {
        $success = false;
        // Get required system objects
        $user       = clone(JFactory::getUser());
        $config     =& JFactory::getConfig();
        $authorize  =& JFactory::getACL();

        $usersConfig = &JComponentHelper::getParams( 'com_users' );

        // Initialize new usertype setting
        $newUsertype = $usersConfig->get( 'new_usertype' );
        if (!$newUsertype) { $newUsertype = 'Registered'; }

        // Bind the post array to the user object
        if (!$user->bind( $details )) 
        {
            $this->setError( $user->getError() );
            return false;
        }
        
        if (empty($user->password))
        {
            jimport('joomla.user.helper');
            $user->password = JUserHelper::genRandomPassword();    
        }
        
        // Set some initial user values
        $user->set('id', 0);
        $user->set('usertype', '');
        $user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));

        $date =& JFactory::getDate();
        $user->set('registerDate', $date->toMySQL());
    
        // we disable useractivation for auto-created users
        $useractivation = '0';
        if ($useractivation == '1') {
            jimport('joomla.user.helper');
            $user->set('activation', md5( JUserHelper::genRandomPassword() ) );
            $user->set('block', '0');
        }

        // If there was an error with registration, set the message and display form
        if ( !$user->save() ) {
            $msg->message = $user->getError();
            return $success;
        }

	if(!TiendaConfig::getInstance()->get('disable_guest_signup_email'))
	{
        	// Send registration confirmation mail
        	TiendaHelperUser::_sendMail( $user, $details, $useractivation, $guest );
	}

        return $user;
    }

    /**
     * Returns yes/no
     * @param array [username] & [password]
     * @param mixed Boolean
     * 
     * @return array
     */ 
    function login( $credentials, $remember='', $return='' ) {
        global $mainframe;

        if (strpos( $return, 'http' ) !== false && strpos( $return, JURI::base() ) !== 0) {
            $return = '';
        }

        // $credentials = array();
        // $credentials['username'] = JRequest::getVar('username', '', 'method', 'username');
        // $credentials['password'] = JRequest::getString('passwd', '', 'post', JREQUEST_ALLOWRAW);
        
        $options = array();
        $options['remember'] = $remember;
        $options['return'] = $return;

        //preform the login action
        $success = $mainframe->login($credentials, $options);

        if ( $return ) {
            $mainframe->redirect( $return );
        }
        
        return $success;
    }

    /**
     * Returns yes/no
     * @param mixed Boolean
     * @return array
     */
    function logout( $return='' ) {
        global $mainframe;

        //preform the logout action//check to see if user has a joomla account
        //if so register with joomla userid
        //else create joomla account
        $success = $mainframe->logout();

        if (strpos( $return, 'http' ) !== false && strpos( $return, JURI::base() ) !== 0) {
            $return = '';
        }

        if ( $return ) {
            $mainframe->redirect( $return );
        }
        
        return $success;        
    }
    
    /**
     * Unblocks a user
     * 
     * @param int $user_id
     * @param int $unblock
     * @return boolean
     */
    function unblockUser($user_id, $unblock = 1)
    {
        $user =& JFactory::getUser( (int)$user_id );
        
        if ($user->get('id')) {
            $user->set('block', !$unblock);
        
            if (  ! $user->save()) {
                return false;
            }
            
            return true;
        }
        else {
            return false;   
        }
    }

    /**
     * Returns yes/no
     * @param object
     * @param mixed Boolean
     * @return array
     */
    function _sendMail( &$user, $details, $useractivation, $guest=false ) 
    {
        $lang = &JFactory::getLanguage();
        $lang->load('com_tienda', JPATH_ADMINISTRATOR);
        
        $mainframe = JFactory::getApplication();

        $db     =& JFactory::getDBO();

        $name       = $user->get('name');
        $email      = $user->get('email');
        $username   = $user->get('username');
        $activation = $user->get('activation');
        $password   = $details['password2']; // using the original generated pword for the email
        
        $usersConfig    = &JComponentHelper::getParams( 'com_users' );
        // $useractivation = $usersConfig->get( 'useractivation' );
        $sitename       = $mainframe->getCfg( 'sitename' );
        $mailfrom       = $mainframe->getCfg( 'mailfrom' );
        $fromname       = $mainframe->getCfg( 'fromname' );
        $siteURL        = JURI::base();

        $subject    = sprintf ( JText::_( 'Account details for' ), $name, $sitename);
        $subject    = html_entity_decode($subject, ENT_QUOTES);

        if ( $useractivation == 1 ) 
        {
            $message = sprintf ( JText::_( 'EMAIL_MESSAGE_ACTIVATION' ), $sitename, $siteURL, $username, $password, $activation );
        } 
            else 
        {
            $message = sprintf ( JText::_( 'EMAIL_MESSAGE' ), $sitename, $siteURL, $username, $password );
        }
        
        if ($guest)
        {
            $message = sprintf ( JText::_( 'EMAIL_MESSAGE_GUEST' ), $sitename, $siteURL, $username, $password );
        }

        $message = html_entity_decode($message, ENT_QUOTES);

        //get all super administrator
        $query = 'SELECT name, email, sendEmail' .
                ' FROM #__users' .
                ' WHERE LOWER( usertype ) = "super administrator"';
        $db->setQuery( $query );
        $rows = $db->loadObjectList();

        // Send email to user
        if ( ! $mailfrom  || ! $fromname ) {
            $fromname = $rows[0]->name;
            $mailfrom = $rows[0]->email;
        }

        $success = TiendaHelperUser::_doMail($mailfrom, $fromname, $email, $subject, $message);
        
        return $success;
    }
    
    /**
     * 
     * @return unknown_type
     */
    function _doMail( $from, $fromname, $recipient, $subject, $body, $actions=NULL, $mode=NULL, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL ) 
    {
        $success = false;

        $message =& JFactory::getMailer();
        $message->addRecipient( $recipient );
        $message->setSubject( $subject );
        
        // check user mail format type, default html
        $message->IsHTML(true);
        $body = htmlspecialchars_decode( $body );
        $message->setBody( nl2br( $body ) );
        
        $sender = array( $from, $fromname );
        $message->setSender($sender);
       
        $sent = $message->send();
        if ($sent == '1') {
            $success = true;
        }
        return $success;
    
    }
    
    /**
     * Updates the core __users table
     * setting the email address = $email 
     */
    function updateUserEmail( $userid, $email )
    {
        $user =& JFactory::getUser( $userid );
        $user->set('email', $email);
        
        if ( !$user->save() ) 
        {
            $this->setError( $user->getError() );
            return false;
        }
        return true; 
    }
    
    /**
     * Gets the next auto-inc id in the __users table
     */
    function getLastUserId()
    {
        $database = &JFactory::getDBO();
        $query = "
            SELECT 
                MAX(id) as id
            FROM 
                #__users
            ";
        $database->setQuery($query);
        $result = $database->loadObject();
        
        return $result->id;
    }
    
    function getACLSelectList( $default='', $fieldname='core_user_new_gid' )
    {
        $object = new JObject();
        $object->value = '';
        $object->text = JText::_( "No Change" );
        $gtree = JFactory::getACL()->get_group_children_tree( null, 'USERS', false );
        foreach ($gtree as $key=>$item)
        {
            if ($item->value == '29' || $item->value == '30')
            {
                unset($gtree[$key]);
            }
        }
        array_unshift($gtree, $object );
        return JHTML::_('select.genericlist', $gtree, $fieldname, 'size="1"', 'value', 'text', $default );
    }
    
    /**
     * Processes a new order
     * 
     * @param $order_id
     * @return unknown_type
     */
    function processOrder( $order_id ) 
    {
        // get the order
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        
        // find the products in the order that are integrated 
        foreach ($order->orderitems as $orderitem)
        {
            $model = JModel::getInstance( 'Products', 'TiendaModel' );
            $model->setId( $orderitem->product_id );
            $product = $model->getItem();
            
            $core_user_change_gid = $product->product_parameters->get('core_user_change_gid');
            $core_user_new_gid = $product->product_parameters->get('core_user_new_gid');
            if (!empty($core_user_change_gid))
            {
                $user = new JUser();
                $user->load( $order->user_id );
                $user->gid = $core_user_new_gid;
                $user->save();
            }
        }
    }
    
    /**
     * Gets a user's user group used for pricing
     * 
     * @param $user_id
     * @return mixed
     */
    function getUserGroup( $user_id='', $product_id='')
    {
        // $sets[$user_id][$product_id]
        static $sets, $groups;
        if (!is_array($sets)) { $sets = array(); }
        if (!is_array($groups)) { $groups = array(); }
    
        $user_id = (int) $user_id;
        $product_id = (int) $product_id;
        
    	if (!empty($user_id) && !empty($product_id))
    	{
    	    if (!isset($sets[$user_id][$product_id]))
    	    {
    	        if (!isset($groups[$user_id]))
    	        {   
                    JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );        
                    $model = JModel::getInstance('UserGroups', 'TiendaModel');
                    $model->setState( 'filter_user', $user_id );
                    //order to get the upper group
                    $model->setState('order', 'g.ordering');
                    $model->setState( 'direction', 'ASC' );
                    $groups[$user_id] = $model->getList();
    	        }
    	        $items = $groups[$user_id];
    	 
    	        // using the helper to cut down on queries
                $product_helper = TiendaHelperBase::getInstance( 'Product' );
                $prices = $product_helper->getPrices( $product_id );                
               
                $groupIds = array();
                foreach($prices as $price):
                    $groupIds[]=$price->group_id;       
                endforeach;
     	
                foreach($items as $item):
                    if (in_array($item->group_id, $groupIds)):
                        $sets[$user_id][$product_id] = $item->group_id;                      
                       // return $sets[$user_id][$product_id]; // i dont understand why the return here doesnt work and and the still continues
                        break;
                    endif;
                endforeach;    	        
    	    }
    	}
    	else 
    	{
    		$sets[$user_id][$product_id] = TiendaConfig::getInstance()->get('default_user_group', '1');
    	}
    	  	
		return $sets[$user_id][$product_id];
    }
    /**
     * 
     * Get Avatar based on the installed community component
     * @param int $id - userid
     * @return object
     */
    function getAvatar($id)
    {
    	$avatar = '';    	  	
    	$found = false;    	
    	Tienda::load( 'TiendaHelperAmbra', 'helpers.ambra' ); 
    	
    	//check if ambra installed
    	if(TiendaHelperAmbra::isInstalled() && !$found) 
    	{
	    	    if ( !class_exists('Ambra') )
	            { 
	                JLoader::register( "Ambra", JPATH_ADMINISTRATOR.DS."components".DS."com_ambra".DS."defines.php" );
	            }
	            //Get Ambra Avatar
	          if($image = Ambra::get( "AmbraHelperUser", 'helpers.user' )->getAvatar( $id ))
	          {
	          		$link = JRoute::_( JURI::root().'index.php?option=com_ambra&view=users&id='.$id, false ); 
	          		$avatar .= "<a href='{$link}' target='_blank'>";
	          		$avatar .= "<img src='{$image}' style='max-width:80px; border:1px solid #ccccce;' />";
	          		$avatar .= "</a>";
	          }    		
    		$found = true;
    	}
    	//check if jomsocial installed
    	if(JComponentHelper::getComponent( 'com_community', true)->enabled && !$found)
    	{ 
    		//Get JomSocial Avatar
    		$database = JFactory::getDBO();
    		$query = " 
			SELECT 
				*
			FROM
				#__community_users
			WHERE
				`userid` = '".$id."'
			";
    		$database->setQuery( $query );
			$result = $database->loadObject();
	    	if (isset($result->thumb )) 
	    	{
				$image = JURI::root().$result->thumb;
			}
    		$link = JRoute::_( JURI::root().'index.php?option=com_community&view=profile&userid='.$id, false ); 
	        $avatar .= "<a href='{$link}' target='_blank'>";
	        $avatar .= "<img src='{$image}' style='max-width:80px; border:1px solid #ccccce;' />";
	        $avatar .= "</a>";
    		$found = true;
    	}
    	
    	//check if community builder is installed
   		if(JComponentHelper::getComponent( 'com_comprofiler', true)->enabled && !$found)
    	{
    		//Get JomSocial Avatar
    		$database = JFactory::getDBO();
    		$query = " 
			SELECT 
				*
			FROM
				#__comprofiler
			WHERE
				`id` = '".$id."'
			";
	    	$database->setQuery( $query );
			$result = $database->loadObject();
			if (isset($result->avatar)) 
			{
				$image = JURI::root().'images/comprofiler/'.$result->avatar;
			}
			else 
			{
				$image = JRoute::_( JURI::root().'components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png');
			}
    		$link = JRoute::_( JURI::root().'index.php?option=com_comprofiler&userid='.$id, false ); 
	        $avatar .= "<a href='{$link}' target='_blank'>";
	        $avatar .= "<img src='{$image}' style='max-width:80px; border:1px solid #ccccce;' />";
	        $avatar .= "</a>";	        
    		$found = true;
    	}
    	
    	return $avatar;
    	
    }
}

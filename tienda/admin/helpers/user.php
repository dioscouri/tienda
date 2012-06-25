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

Tienda::load('TiendaHelperBase', 'helpers._base');
class TiendaHelperUser extends DSCHelperUser
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
		$model->setState( 'filter_deleted', 0 );
		$items = $model->getList();
		if (empty($items))
		{
			$model = JModel::getInstance( 'Addresses', 'TiendaModel' );
			$model->setState('filter_userid', (int) $userid);
			$model->setState( 'filter_deleted', 0 );
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
	public static function emailExists( $string, $table='users'  ) {
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
/*	function createNewUser( $details, $guest=false )
	{
		$success = false;
		// Get required system objects
		$user       = clone(JFactory::getUser());
		$config     = JFactory::getConfig();
		$authorize  = JFactory::getACL();

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
		$user->set('usertype', $newUsertype);
		$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));

		$date = JFactory::getDate();
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

		if(!Tienda::getInstance()->get('disable_guest_signup_email'))
		{
			// Send registration confirmation mail
			TiendaHelperUser::_sendMail( $user, $details, $useractivation, $guest );
		}

		return $user;
	}*/

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

		$db     = JFactory::getDBO();

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

		$subject    = sprintf ( JText::_('COM_TIENDA_ACCOUNT_DETAILS_FOR'), $name, $sitename);
		$subject    = html_entity_decode($subject, ENT_QUOTES);

		if ( $useractivation == 1 )
		{
			$message = sprintf ( JText::_('COM_TIENDA_EMAIL_MESSAGE_ACTIVATION'), $sitename, $siteURL, $username, $password, $activation );
		}
		else
		{
			$message = sprintf ( JText::_('COM_TIENDA_EMAIL_MESSAGE'), $sitename, $siteURL, $username, $password );
		}

		if ($guest)
		{
			$message = sprintf ( JText::_('COM_TIENDA_EMAIL_MESSAGE_GUEST'), $sitename, $siteURL, $username, $password );
		}

		$message = html_entity_decode($message, ENT_QUOTES);

		//get all super administrator
		/*$query = 'SELECT name, email, sendEmail' .
                ' FROM #__users' .
                ' WHERE LOWER( usertype ) = "super administrator"';
		$db->setQuery( $query );
		$rows = $db->loadObjectList();*/
		
		$rows = DSCAcl::getAdminList();

		// Send email to user
		if ( ! $mailfrom  || ! $fromname ) {
			$fromname = $rows[0]->name;
			$mailfrom = $rows[0]->email;
		}

		$success = TiendaHelperUser::_doMail($mailfrom, $fromname, $email, $subject, $message);

		return $success;
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
		if( $order->user_id < Tienda::getGuestIdStart() )
			return;
		
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
				$product_helper =  TiendaHelperBase::getInstance( 'Product' );
			
				$prices = $product_helper->getPrices( $product_id );
				 
				$groupIds = array();
				foreach ($prices as $price)
				{
					$groupIds[] = $price->group_id;
				}

				foreach ($items as $item)
				{
					if (in_array($item->group_id, $groupIds))
					{
						$sets[$user_id][$product_id] = $item->group_id;
						// return $sets[$user_id][$product_id]; // i dont understand why the return here doesnt work and and the still continues
						break;
					}
				}
			}
		}

		if (!isset($sets[$user_id][$product_id]))
		{
			$sets[$user_id][$product_id] = Tienda::getInstance()->get('default_user_group', '1');
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

	/*
	 * Gets user subscription number
	 *
	 * @param $id User ID
	 *
	 * @return User subscription number
	 */
	function getSubNumber( $id )
	{
		$db = JFactory::getDbo();
		$q = ' SELECT `sub_number` FROM `#__tienda_userinfo` WHERE `user_id` = '.$id;
		$db->setQuery( $q );
		return $db->loadResult();
	}

	/**
	 * Verifies that the string is in a proper e-mail address format.
	 *
	 * @static
	 * @param string $email String to be verified.
	 * @return boolean True if string has the correct format; false otherwise.
	 * @since 1.5
	 */
	public static  function isEmailAddress($email)
	{
		// Split the email into a local and domain
		$atIndex    = strrpos($email, "@");
    if($atIndex === false) // no "@" => false
      return false;
		$domain     = substr($email, $atIndex+1);
		$local      = substr($email, 0, $atIndex);

		// Check Length of domain
		$domainLen  = strlen($domain);
		if ($domainLen < 1 || $domainLen > 255) {
			return false;
		}

		// Check the local address
		// We're a bit more conservative about what constitutes a "legal" address, that is, A-Za-z0-9!#$%&\'*+/=?^_`{|}~-
		$allowed    = 'A-Za-z0-9!#&*+=?_-';
		$regex      = "/^[$allowed][\.$allowed]{0,63}$/";
		if ( !preg_match($regex, $local) ) {die(Tienda::dump('foo'));
		return false;
		}

		// No problem if the domain looks like an IP address, ish
		$regex      = '/^[0-9\.]+$/';
		if ( preg_match($regex, $domain)) {
			return true;
		}

		// Check Lengths
		$localLen   = strlen($local);
		if ($localLen < 1 || $localLen > 64) {
			return false;
		}

		// Check the domain
		$domain_array   = explode(".", rtrim( $domain, '.' ));
		if (count($domain_array) == 1)
		{
			return false;
		}

		$regex      = '/^[A-Za-z0-9-]{0,63}$/';
		foreach ($domain_array as $domain ) {

			// Must be something
			if ( ! $domain ) {
				return false;
			}

			// Check for invalid characters
			if ( ! preg_match($regex, $domain) ) {
				return false;
			}

			// Check for a dash at the beginning of the domain
			if ( strpos($domain, '-' ) === 0 ) {
				return false;
			}

			// Check for a dash at the end of the domain
			$length = strlen($domain) -1;
			if ( strpos($domain, '-', $length ) === $length ) {
				return false;
			}

		}

		return true;
	}

	/**
	 * Method which returns the next guest user account ID in the system
	 * (starts off with -11 => reserve 0 ... -10 for later use)
	 * 
	 * @return Guest user account ID
	 */
	function getNextGuestUserId()
	{
		$db = JFactory::getDbo();
		Tienda::load( 'TiendaQuery', 'library.query' );
		$q = new TiendaQuery();
		$start_id = Tienda::getGuestIdStart();
		
		$q->select( 'min( tbl.user_id)' );
		$q->from( '#__tienda_userinfo tbl' );
		$q->where( 'tbl.user_id < '.$start_id );
		$db->setQuery( ( string )$q );
		$res = $db->loadResult();
		if( $res === null ) // no guest account in system
			return $start_id-1; // start off with -11
		else
			return $res - 1; // the last guest account id -1
	}

	/**
	 * 
	 * Method to validate a user password via PHP
	 * @param $pass								Password for validation
	 * @param $force_validation		Can forces this method to validate the password, even thought PHP validation is turned off
	 * 
	 * @return	Array with result of password validation (position 0) and list of requirements which the password does not fullfil (position 1)
	 */
	function validateUserPassword( $pass, $force_validation = '' )
	{
		$errors = array();
		$result = true;
		
		$validate_php = $force_validation ||  Tienda::getInstance()->get( 'password_php_validate', 1 );
		if( !$validate_php )
			return array( $result, $errors );

		$min_length = Tienda::getInstance()->get( 'password_min_length', 5 );
		$req_num = Tienda::getInstance()->get( 'password_req_num', 1 );
		$req_alpha = Tienda::getInstance()->get( 'password_req_alpha', 1 );
		$req_spec = Tienda::getInstance()->get( 'password_req_spec', 1 );
		
		if( strlen( $pass ) < $min_length )
		{
			$result = false;
			$errors []= 'length';
		}

		if( $req_num && !preg_match( '/[0-9]/', $pass ) )
		{
			$result = false;
			$errors []= 'number';
		}
		
		if( $req_alpha && !preg_match( '/[a-zA-Z]/', $pass ) )
		{
			$result = false;
			$errors []= 'alpha';
		}
		
		if( $req_spec && !preg_match( '/[\\/\|_\-\+=\."\':;\[\]~<>!@?#$%\^&\*()]/', $pass ) )
		{
			$result = false;
			$errors []= 'spec';
		}
		
		return array( $result, $errors );
	}
}

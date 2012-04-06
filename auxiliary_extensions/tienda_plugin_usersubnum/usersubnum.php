<?php
/**
 * @version    $Id: usersubnum.php $
 * @package    Joomla
 * @subpackage Tienda
 * @copyright
 * @license    GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') )
	JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

class plgUserUserSubNum extends JPlugin {

	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = 'usersubnum';

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param   array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgUserUserSubNum(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}

	/**
	 * Tienda User store user method
	 *
	 * Method is called after user data is stored in the database
	 * The sort order of this plugin must be set to be after the User - Tienda plugin, because
	 * that creates an entry in userinfo, and we can't act without that.
	 *
	 * @param   array    holds the new user data
	 * @param   boolean     true if a new user is stored
	 * @param   boolean     true if user was succesfully stored in the database
	 * @param   string      message
	 */
	function onAfterStoreUser($user, $isnew, $success, $msg)
	{
		$create_address = $this->params->get( 'create_address', 0 );
		if ($isnew)
		{
			// load the config class
			Tienda::load( 'TiendaConfig', 'defines' );
			$notify = $this->params->get( 'notify_person', 1 );
			JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
			Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
			$component = JRequest::getCmd( 'option' );

			$tokens = explode( ' ', $user['name'] );
			$last_name = $first_name = '';
			if( count( $tokens ) > 1 )
			{
				$last_name = $tokens[count( $tokens )-1];
				$first_name = str_replace( $last_name, '', $user['name'] );
			} else {
				$last_name = $user['name'];
			}

			//save the subscription number
			$tblUser = JTable::getInstance( 'UserInfo', 'TiendaTable' );
			$tblUser->load( array( 'user_id' => $user['id'] ) );
			$tblUser->user_id = $user['id'];
			$tblUser->sub_number = TiendaHelperSubscription::getNextSubNum();
			// one-page checkout + other registrations that are not done via tienda -> we need to add data manually
			if( $component != 'com_tienda' || ( $component == 'com_tienda' && TiendaConfig::getInstance()->get( 'one_page_checkout', 0 ) ) )
			{				
				$tblUser->last_name = $last_name;
				$tblUser->first_name = $first_name;
				$tblUser->email = $user['email'];
			}

			if( !$tblUser->save() )
			{
				$this->sendNotification( JText::_('Error during saving subscription number'), $user['id'] );
				JError::raiseError(500, JText::_('Error during saving subscription number') );
				return;
			}

 			// we want to create a default address and are not registering via tienda
 			// (tienda takes care about it automatically)
			if( $create_address && $component != 'com_tienda' )
			{
				$tblAddress = JTable::getInstance( 'Addresses', 'TiendaTable' );				
				$tblAddress->user_id = $user['id'];
				$tblAddress->addresstype_id = 1;
				$tblAddress->address_name = $this->params->get( 'address_title', JText::_('Main Address Default') );
				$tblAddress->country_id = $this->params->get( 'default_country',0 );
				$tblAddress->zone_id = $this->params->get( 'default_zone',0 );
				$tblAddress->is_default_billing	 = 1;
				$tblAddress->is_default_shipping = 1;
				$tblAddress->last_name = $last_name;
				$tblAddress->first_name = $first_name;

				if( !$tblAddress->store() )
				{
					$this->sendNotification( JText::_('Error during creating an empty address'), $user['id'] );
					JError::raiseError(500, JText::_('Error during creating an empty address') );
					return;
				}
			}
		}
	}

	function sendNotification( $msg, $user_id )
	{
		$config     = TiendaConfig::getInstance();
		$mainframe = JFactory::getApplication();
		$mailfrom   = $config->get( 'shop_email', '' );
		if( !strlen( $mailfrom ) )
			$mailfrom = $mainframe->getCfg('mailfrom');

		$fromname   = $config->get( 'shop_email_from_name', '' );
		if( !strlen( $fromname ) )
			$fromname = $mainframe->getCfg('fromname');

		$toemail = $this->params->get( 'person_email', '' );
		if( !strlen( $toemail) )
			return;

		$mailer = JFactory::getMailer();
		$mailer->addRecipient( $toemail );
		$mailer->setSubject( sprintf( $this->params->get( 'email_subject' ), $user_id ) );
		$mailer->setBody( htmlspecialchars_decode( $msg ).' - '.JText::_('COM_TIENDA_USER').'='.$user_id );
		$sender = array( $from, $fromname );
		$mailer->setSender($sender);
		$mailer->send();
	}
}

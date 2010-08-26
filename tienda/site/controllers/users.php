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
defined( '_JEXEC' ) or die( 'Restricted access' );


class TiendaControllerUsers extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->set('suffix', 'users');
	}

	/**
	 * Checks if an item is checked out, and if so, redirects to layout for viewing item
	 * Otherwise, displays a form for editing item
	 *
	 * @return void
	 */

	function edit()
	{
		$model  = $this->getModel( $this->get('suffix') );
		$row = $model->getTable();
		//$row->load( array( 'user_id' => JFactory::getUser()->id ) );

		// JRequest::setVar('id', $row->user_info_id );
		JRequest::setVar('users', 'accounts');
		JRequest::setVar('layout', 'form');
		parent::display();
	}

	function save(){
		$values = JRequest::get('post');
		Tienda::load( 'TiendaHelperUser', 'helpers.user' );
		$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');

		$details = array(
					'email' => $values['email'],
					'name' => $values['name'],
					'username' => $values['username'],
					'password'=> $values['password'], 
					'password2'=> $values['password2']		
		);


		// create the new user
		$msg = $this->getError();
		$user = $userHelper->createNewUser($details, true);

		if (empty($user->id))
		{

			// TODO what to do if creating new user failed?
		}

		// save the real user's info in the userinfo table
		//			JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		//			$userinfo = JTable::getInstance('UserInfo', 'TiendaTable');
		//			$userinfo->load( array('user_id'=>$user->id) );
		//			$userinfo->user_id = $user->id;
		//			$userinfo->email = $values['email_address'];
		//			$userinfo->save();
		//		   if (empty($userinfo->id))
		//			{
		//				// TODO what to do if creating new $userinfo failed?
		//			}

		// login the user
		$userHelper->login(
		array('username' => $user->username, 'password' => $details['password'])
		);

		$redirect = "index.php?option=com_tienda&view=checkout";
		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );

	}
	/*
	 * validate the entered fields
	 */

	function verifyFields()
	{

		$response = array();
		$response['msg'] = "";
		$response['error'] = "";
		$msg = new stdClass();
		$msg->message = "";
		$msg->error = "";

		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		// get elements from post
		$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

		// Test if elements are empty
		// Return proper message to user
		if (empty($elements))
		{
			// do form validation
			// if it fails check, return message
			$response['error'] = '1';
			$response['msg'] .= $helper->generateMessage(JText::_("Error while validating the parameters"));
			echo ( json_encode( $response ) );
			return;
		}

		// convert elements to array that can be binded
			
		$submitted_values = $helper->elementsToArray( $elements );

		// verify that fields are present
		if (!$submitted_values['email'] && !$submitted_values['name'] && !$submitted_values['username'] && !$submitted_values['password'] && !$submitted_values['password2'] )
		{
			$response['error'] = '1';
			$response['msg'] .= $helper->generateMessage(JText::_("All Fields are Mandatory"));
			echo ( json_encode( $response ) );
			return;
		}
			


		Tienda::load( 'TiendaHelperUser', 'helpers.user' );
		$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');

		jimport('joomla.mail.helper');
		if(!JMailHelper::isEmailAddress($submitted_values['email'])){
			$response['msg'] = $helper->generateMessage( JText::_('Please insert a correct email address') );
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return;
		}
		if ($userHelper->emailExists($submitted_values['email']))
		{
			$response['error'] = '1';
			$response['msg'] .= $helper->generateMessage(JText::_("Email Already Exist"));
			echo ( json_encode( $response ) );
			return;
			// TODO user already exists

		}


		if ($userHelper->usernameExists($submitted_values['username']))
		{
			$response['error'] = '1';
			$response['msg'] .= $helper->generateMessage(JText::_("User Name Already exist"));
			echo ( json_encode( $response ) );
			return;
			// TODO user already exists

		}

		if ($submitted_values['password'] != $submitted_values['password2'] )
		{
			$response['error'] = '1';
			$response['msg'] .= $helper->generateMessage(JText::_("Passwords are not matching"));
			echo ( json_encode( $response ) );
			return;
			// TODO user already exists

		}

		return ;
	}


}
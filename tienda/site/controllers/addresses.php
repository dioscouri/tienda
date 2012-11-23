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

class TiendaControllerAddresses extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct()
	{
		if (empty(JFactory::getUser()->id))
		{
			$url = JRoute::_( "index.php?option=com_tienda&view=addresses" );
			Tienda::load( "TiendaHelperUser", 'helpers.user' );
			$redirect = JRoute::_( TiendaHelperUser::getUserLoginUrl( $url ), false );
			JFactory::getApplication()->redirect( $redirect );
			return;
		}

		parent::__construct();
		$this->set('suffix', 'addresses');
		$this->registerTask( 'flag_billing', 'flag' );
		$this->registerTask( 'flag_shipping', 'flag' );
		$this->registerTask( 'flag_deleted', 'flag' );
	}

	function _setModelState()
	{
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		$state['filter_userid']     = JFactory::getUser()->id;
		$state['filter_deleted']    = '0';

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}
		return $state;
	}

	/**
	 * @return void
	 */
	function edit()
	{
		$redirect = "index.php?option=com_tienda&view=addresses";
		$redirect = JRoute::_( $redirect, false );

		$user = JFactory::getUser();
		$model  = $this->getModel( $this->get('suffix') );
		$row = $model->getTable();
		$row->load( $model->getId() );

		// if id is present then user is editing, check if user can edit this item
		if (!empty($row->address_id) && $row->user_id != JFactory::getUser()->id)
		{
			$this->message = JText::_('COM_TIENDA_CANNOT_EDIT_ADDRESS_NOTICE');
			$this->messagetype = 'notice';
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		// else creating new item
		JRequest::setVar( 'hidemainmenu', '1' );
		JRequest::setVar( 'view', $this->get('suffix') );
		JRequest::setVar( 'layout', 'form' );

		$view  = $this->getView( 'addresses', 'html' );
		$view->assign('form_inner', $this->getInnerAddressForm($row->address_id));
		parent::display();
	}

	/**
	 *
	 * @param $address_id
	 */
	function getInnerAddressForm($address_id)
	{
		$html = '';
		$model = JModel::getInstance( 'Addresses', 'TiendaModel' );
		$address_id = JRequest::getVar('address_id');
		$model->setId($address_id);
		$item = $model->getItem();

		$view   = $this->getView( 'addresses', 'html' );
		$view->set( '_controller', 'addresses' );
		$view->set( '_view', 'addresses' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->setLayout( 'form_inner' );
		$view->set('row', $item);

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Saves a submitted form
	 *
	 */
	function save()
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$model = $this->getModel( $this->get('suffix') );
		$row = $model->getTable();
		$row->load( $model->getId() );
		$row->bind( $_POST );
		$row->_isNew = empty($row->address_id);

		$redirect = "index.php?option=com_tienda&view=addresses";
		if (JRequest::getVar('tmpl') == 'component')
		{
			$redirect .= "&tmpl=component";
		}
		$redirect = JRoute::_( $redirect, false );

		if ($row->_isNew)
		{
			$row->user_id = JFactory::getUser()->id;
		}
		elseif ($row->user_id != JFactory::getUser()->id)
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_NOT_AUTHORIZED_TO_EDIT_ITEM');
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}

		if ( $row->save() )
		{
		    $model->clearCache();
			$model->setId( $row->address_id );
			$this->messagetype  = 'message';
			$this->message      = JText::_('COM_TIENDA_SAVED');

			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Returns a selectlist of zones
	 * Called via Ajax
	 *
	 * @return unknown_type
	 */
	function getZones()
	{
		Tienda::load( 'TiendaSelect', 'library.select' );
		$html = '';
		$text = '';
		 
		$country_id = JRequest::getVar('country_id');
		$prefix = JRequest::getVar('prefix');
		$html = TiendaSelect::zone( '', $prefix.'zone_id', $country_id );
		 
		$response = array();
		$response['msg'] = $html;
		$response['error'] = '';

		// encode and echo (need to echo to send back to browser)
		echo ( json_encode($response) );

		return;
	}

	/**
	 * Flags an address
	 * @return unknown_type
	 */
	function flag()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';
		$redirect = 'index.php?option=com_tienda&view=addresses';
		if (JRequest::getVar('tmpl') == 'component')
		{
			$redirect .= "&tmpl=component";
		}

		$model = $this->getModel($this->get('suffix'));
		$row = $model->getTable();

		$task = JRequest::getVar( 'task' );
		$actions = explode( '_', $task );
		if (!is_array($actions))
		{
			$this->message = JText::_('COM_TIENDA_INVALID_TASK');
			$this->messagetype = 'notice';
			$redirect = JRoute::_( $redirect, false );
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		$act = $actions['1'];
		$errors = array();

		$cids = JRequest::getVar('cid', array (0), 'post', 'array');
		foreach (@$cids as $cid)
		{
			switch($act)
			{
				case "billing":
					$flag = "is_default_billing"; $value = "1";
					break;
				case "shipping":
					$flag = "is_default_shipping"; $value = "1";
					break;
				case "deleted":
					$flag = "is_deleted"; $value = "1";
					break;
				default:
					$this->message = JText::_('COM_TIENDA_INVALID_ACT');
					$this->messagetype = 'notice';
					$redirect = JRoute::_( $redirect, false );
					$this->setRedirect( $redirect, $this->message, $this->messagetype );
					return;
					break;
			}
			$row->load($cid);
			if ($row->address_id && $row->user_id == JFactory::getUser()->id)
			{
				$row->$flag = $value;
				if (!$row->save())
				{
					$errors[] = $cid;
					$this->messagetype = 'notice';
					$error = true;
				}
			}
			else
			{
				$errors[] = $cid;
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_UNABLE_TO_CHANGE').": ".implode(", ", $errors);
		}
		else
		{
			$this->message = "";
		}

		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
		return;
	}

	/**
	 * getAddress function.
	 *
	 * @access public
	 * @return void
	 */
	function getAddress()
	{
		$html = '';
		$model = JModel::getInstance( 'Addresses', 'TiendaModel' );
		$address_id = JRequest::getVar('address_id');
		$model->setId($address_id);
		if ($item = $model->getItem())
		{
			$view   = $this->getView( 'addresses', 'html' );
			$view->set( '_controller', 'addresses' );
			$view->set( '_view', 'addresses' );
			$view->set( '_doTask', true);
			$view->set( 'hidemenu', true);
			$view->setModel( $model, true );
			$view->setLayout( 'view_inner' );
			$view->set('row', $item);
			 
			ob_start();
			$view->display();
			$html = ob_get_contents();
			ob_end_clean();
		}

		$response = array();
		$response['msg'] = $html;
		$response['error'] = '';

		// encode and echo (need to echo to send back to browser)
		echo ( json_encode($response) );

		return;

	}

}
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

class TiendaControllerPOS extends TiendaController
{
	/**
	 * Default redirect URL
	 */
	var $redirect = 'index.php?option=com_tienda&view=orders';
	var $validation_url = 'index.php?option=com_tienda&view=pos&task=validate&format=raw';

	function display($cachable=false)
	{				
		$step = JRequest::getVar('nextstep', 'step1');
		if(empty($step))
		{
			$step = 'step1';
		}

		JModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tienda/models');
		$elementUserModel = JModel::getInstance('ElementUser', 'TiendaModel');
		$session = JFactory::getSession();

		$view = $this->getView('pos', 'html');
		$view->assign('session', $session);
		$view->assign('step', $step);
		$view->assign('validation_url', $this->validation_url);
		$view->setModel($elementUserModel);

		$method_name = 'do' . $step;
		if(method_exists($this, $method_name))
		{
			$this->$method_name();
		}

		parent::display();
	}

	/**
	 *
	 * Enter description here ...
	 * @return unknown_type
	 */
	function doStep2()
	{
		$view = $this->getView('pos', 'html');
		$view->assign('step1_inactive', $this->step1Inactive());
		$view->assign('cart', $this->getCartView());
	}

	/**
	 * Method to set values in the session from the post
	 * @return unknown_type
	 */
	function saveStep1()
	{
		$post = JRequest::get('post');
		$user_id = $post['user_id'];

		if($post['user_type'] == 'new' || $post['user_type'] == 'anonymous')
		{
			$email = '';
			// Create user
			Tienda::load('TiendaHelperUser', 'helpers.user');
			$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');
			if($post['user_type'] == 'new')
			{
				$text = JText::_('Username');
				$email = $post['new_email'];
				$username = $post['new_username_create'] && $post['new_username'] != $text ? $post['new_username'] : $post['new_email'];
				$details = array('email' => $post['new_email'],
				'name' => $post['new_name'],
				'username' => $username);
				// create password here?
				jimport('joomla.user.helper');
				$details['password'] = JUserHelper::genRandomPassword();
				$details['password2'] = $details['password'];

				$user = $userHelper->createNewUser($details);
			}
			elseif($post['user_type'] == 'anonymous')
			{
				$email = $post['anon_email'];
				// create a guest email address to be stored in the __users table
				//get the domain from the uri
				$uri = JURI::getInstance();
				$domain = $uri->gethost();

				$guestId = time();
				// send the guest user credentials to the user's real email address
				$details = array('email' => $post['anon_email'],
				'name' => "guest_" . $guestId,
				'username' => "guest_" . $guestId);

				// use a random password, and send password2 for the email
				jimport('joomla.user.helper');
				$details['password'] = JUserHelper::genRandomPassword();
				$details['password2'] = $details['password'];

				$user = $userHelper->createNewUser($details, true);

				// but don't save the user's real email in the __users db table
				if(TiendaConfig::getInstance()->get('obfuscate_guest_email', '0'))
				{
					$lastUserId = $userHelper->getLastUserId();
					$guestId = $lastUserId + 1;
					// format: guest_[id]@domain.com
					$guest_email = "guest_" . $guestId . "@" . $domain;
					$userEmailUpdate = $userHelper->updateUserEmail($user->id, $guest_email);
				}
			}

			// save the real user's info in the userinfo table
			JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables');
			$userinfo = JTable::getInstance('UserInfo', 'TiendaTable');
			$userinfo->load( array('user_id' => $user->id));
			$userinfo->user_id = $user->id;
			$userinfo->email = $email;
			$userinfo->save();

			// overide the userid in the post
			$user_id = $user->id;
		}

		// store the values in the session
		$session = JFactory::getSession();
		$session->set('user_type', $post['user_type'], 'tienda_pos');
		$session->set('user_id', $user_id, 'tienda_pos');
		$session->set('new_email', $post['new_email'], 'tienda_pos');
		$session->set('new_name', $post['new_name'], 'tienda_pos');
		$session->set('new_username_create', !empty($post['new_username_create']), 'tienda_pos');
		$session->set('new_username', $post['new_username'], 'tienda_pos');
		$session->set('anon_emails', !empty($post['anon_emails']), 'tienda_pos');
		$session->set('anon_email', $post['anon_email'], 'tienda_pos');
		$session->set('subtask', 'shipping', 'tienda_pos');

		$this->setRedirect("index.php?option=com_tienda&view=pos&nextstep=step2");
	}

	function doStep3()
	{	
		// track if we are in shipping or payment	
		$session = JFactory::getSession();
		$subtask = $session->get('subtask', 'shipping', 'tienda_pos');	
				
		$order =& $this->populateOrder();
		$view = $this->getView('pos', 'html');
		$view->assign('step1_inactive', $this->step1Inactive());
		
		// determin if have item/s that need shippings
		Tienda::load("TiendaHelperBase", 'helpers._base');
		$product_helper = &TiendaHelperBase::getInstance('Product');

		$items = $order->getItems();
		
		$showShipping = false;
		foreach($items as $item)
		{
			$shipping = $product_helper->isShippingEnabled($item->product_id);
			if($shipping)
			{
				$showShipping = true;
				break;
			}
		}
		$view->assign('showShipping', $showShipping);

		
		if(!$showShipping)
			$subtask = 'payment';
		// go directly to payment if we dont no shipping required

		$billingAddress = $order->getBillingAddress();
		if(!empty($billingAddress))
		{
			$view->assign('billingAddress', $billingAddress);
		}
		else
		{
			$view->assign('billingForm', $this->getAddressForm('billing_input_'));
		}
		
		if($showShipping)
		{
			$shippingAddress = $order->getShippingAddress();
			if(!empty($shippingAddress))
			{
				$view->assign('shippingAddress', $shippingAddress);
			}
			else
			{
				$view->assign('shippingForm', $this->getAddressForm('shipping_input_', $showShipping, true));
			}
		}
		switch($subtask)
		{
			case 'shipping' :
				$view->assign('shippingRates', $this->getShippingHtml($order));
				break;
			case 'payment' :				
				$order->shipping = new JObject();
				$order->shipping->shipping_price = $session->get('shipping_price', '', 'tienda_pos');
				$order->shipping->shipping_extra = $session->get('shipping_extra', '', 'tienda_pos');
				$order->shipping->shipping_name = $session->get('shipping_name', '', 'tienda_pos');
				$order->shipping->shipping_tax = $session->get('shipping_tax', '', 'tienda_pos');
				
				//calculate the order totals as we already have the shipping
				$order->calculateTotals();
				
				$view->assign('paymentOptions', $this->getPaymentOptionsHtml($order));
			default :
				break;
		}
		
		$view->assign('orderSummary', $this->getOrderSummary($order));
		$view->assign('subtask', $subtask);
		
	}

	function doStep4()
	{
		$session = JFactory::getSession();
		$view = $this->getView('pos', 'html');
		$view->assign('step1_inactive', $this->step1Inactive());
		$order =& $this->populateOrder();
		$order->shipping = new JObject();
		$order->shipping->shipping_price = $session->get('shipping_price', '', 'tienda_pos');
		$order->shipping->shipping_extra = $session->get('shipping_extra', '', 'tienda_pos');
		$order->shipping->shipping_name = $session->get('shipping_name', '', 'tienda_pos');
		$order->shipping->shipping_tax = $session->get('shipping_tax', '', 'tienda_pos');
				
		//calculate the order totals as we already have the shipping
		$order->calculateTotals();
		$view->assign('orderSummary', $this->getOrderSummary($order));		
	}

	function saveStep2()
	{
		// merely a redirect
		$this->setRedirect("index.php?option=com_tienda&view=pos&nextstep=step3");
	}
	
	function saveStep3()
	{
		// merely a redirect
		$this->setRedirect("index.php?option=com_tienda&view=pos&nextstep=step4");		
		
	}

	function step1Inactive()
	{
		$session = JFactory::getSession();
		switch ($session->get( 'user_type', '', 'tienda_pos' ))
		{
			case "existing" :
				$user = JFactory::getUser($session->get('user_id', '', 'tienda_pos'));
				$step1_inactive = JText::_("Existing user") . ": " . $user->name . " - " . $user->email . " [" . $user->id . "]";
				break;
			case "new" :
				$step1_inactive = JText::_("New User") . ": " . $session->get('new_name', '', 'tienda_pos') . " - " . $session->get('new_email', '', 'tienda_pos');
				break;
			case "anonymous" :
				$step1_inactive = JText::_("Anonymous user");
				break;
			default :
				$step1_inactive = JText::_("Name and email of user");
				break;
		}

		return $step1_inactive;
	}
	
	function saveShipping()
	{
		$post = JRequest::get('post');
		$session = JFactory::getSession();
		$session->set('shipping_price', $post['shipping_price'], 'tienda_pos');
		$session->set('shipping_tax', $post['shipping_tax'], 'tienda_pos');
		$session->set('shipping_name', $post['shipping_name'], 'tienda_pos');
		$session->set('shipping_code', $post['shipping_code'], 'tienda_pos');
		$session->set('shipping_extra', $post['shipping_extra'], 'tienda_pos');
		$session->set('shipping_set', true, 'tienda_pos');
		$session->set('subtask', 'payment', 'tienda_pos');
		$this->setRedirect("index.php?option=com_tienda&view=pos&nextstep=step3&subtask=payment");
	}

	/**
	 * (non-PHPdoc)
	 * @see tienda/site/TiendaController::validate()
	 */
	function validate()
	{
		Tienda::load('TiendaHelperBase', 'helpers._base');
		$helper = new TiendaHelperBase();

		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		// get elements from post
		$elements = json_decode(preg_replace('/[\n\r]+/', '\n', JRequest::getVar('elements', '', 'post', 'string')));

		// validate it using table's ->check() method
		if(empty($elements))
		{
			// if it fails check, return message
			$response['error'] = '1';
			$response['msg'] = $helper->generateMessage(JText::_("Could not process form"));
			echo( json_encode($response));
			return ;
		}

		// convert elements to array that can be binded
		$values = $helper->elementsToArray($elements);

		// override the step if we are in shipping and then going to payment
		if(!empty($values['subtask']))
		{
			$values['step'] = 'step3';
		}

		// validate it based on the step
		switch ( $values['step'] )
		{
			case "step1" :
				$response = $this->validateStep1($values);
				break;
			case "step2" :
				$response = $this->validateStep2($values);
				break;
			case "step3" :
				$response = $this->validateStep3($values);
				break;
			case "step4" :
				break;
		}

		echo( json_encode($response));
		return ;
	}

	/**
	 *
	 * Enter description here ...
	 * @param $values
	 * @return unknown_type
	 */
	function validateStep1($values)
	{
		Tienda::load('TiendaHelperBase', 'helpers._base');
		$helper = new TiendaHelperBase();

		$msg = array();
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		switch ( $values['_checked']['user_type'] )
		{
			case "existing" :
				if(empty($values['user_id']))
				{
					$response['error'] = '1';
					$msg[] = JText::_("Please Select a User");

				}
				break;
			case "new" :
				if(empty($values['new_email']) || $values['new_email'] == JText::_('Email'))
				{
					$response['error'] = '1';
					$msg[] = JText::_("Please provide an email");
				}

				if(empty($values['new_name']) || $values['new_name'] == JText::_('Full Name'))
				{
					$response['error'] = '1';
					$msg[] = JText::_("Please provide a name");
				}

				if(empty($values['_checked']['new_username_create']) && (empty($values['new_username']) || $values['new_username'] == JText::_('Username')))
				{
					$response['error'] = '1';
					$msg[] = JText::_("Please provide a username");
				}

				$userhelper = $helper->getInstance('User');

				// Is this email already used?
				if($userhelper->emailExists($values['new_email']))
				{
					$response['error'] = '1';
					$msg[] = JText::_("This email already exists");
				}

				// Is this username already used?
				if(empty($values['_checked']['new_username_create']) && $userhelper->usernameExists($values['new_username']))
				{
					$response['error'] = '1';
					$msg[] = JText::_("This username already exists");
				}
				break;
			case "anonymous" :
				if(!empty($values['_checked']['anon_emails']) && (empty($values['anon_email']) || $values['anon_email'] == JText::_("Email")))
				{
					$response['error'] = '1';
					$msg[] = JText::_("Please provide an email");
				}
				break;
		}

		$response['msg'] = $helper->generateMessage("<li>" . implode("</li><li>", $msg) . "</li>", false);
		return $response;
	}

	/**
	 * Method to validate the cart before proceeding to the next step
	 * @param $values - array
	 * @return array - json response
	 */
	function validateStep2($values)
	{
		Tienda::load('TiendaHelperBase', 'helpers._base');
		$helper = new TiendaHelperBase();

		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		//check if we have empty cart
		if(!isset($values['cid']))
		{
			$response['error'] = '1';
			$response['msg'] = $helper->generateMessage(JText::_("No Items in Cart. Please add item/s to cart to continue."), false);
		}

		return $response;
	}
	
	
	function validateStep3($values)
	{
		Tienda::load('TiendaHelperBase', 'helpers._base');
		$helper = new TiendaHelperBase();

		$response = array();
		$response['msg'] = '';
		$response['error'] = '';
		
		$session = JFactory::getSession();
		$subtask = $session->get('subtask', 'shipping', 'tienda_pos');	
		
		switch($subtask)
		{
			case 'shipping':
				$response = $this->validateShipping($values);
				break;
			case 'payment':
			default:
				$response = $this->validatePayment($values);
				break;	
			
		}		

		return $response;
	}
	
	function validateShipping($values)
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';
		
		return $response;
	}
	
	function validatePayment($values)
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';
		
		return $response;
	}

	/**
	 * Method to show the listing of products
	 */
	function addProducts()
	{
		$this->set('suffix', 'products');
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel($this->get('suffix'));
		$ns = $this->getNamespace();

		foreach(@$state as $key => $value)
		{
			$model->setState($key, $value);
		}

		$view = $this->getView('pos', 'html');
		$view->setModel($model, true);
		$view->assign('state', $model->getState());
		$view->assign('items', $model->getList());
		$view->setLayout('addproduct');
		$view->display();
	}

	/**
	 * Method to show the view a product to be added to cart
	 * @return unknown_type
	 */
	function viewProduct()
	{
		$model = $this->getModel('Products');
		$model->setId($model->getId());
		$row = $model->getItem();
		$session = JFactory::getSession();
		$view = $this->getView('pos', 'html');
		$view->setModel($model, true);
		$view->assign('product', $row);
		$view->setLayout('viewproduct');
		$view->display();
	}

	/**
	 *
	 * Enter description here ...
	 * @param $product_id
	 * @param $values
	 * @return unknown_type
	 */
	function getAddToCart($product_id, $values = array())
	{
		$html = '';
		JModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tienda/models');
		$model = JModel::getInstance('Products', 'TiendaModel');
		$model->setId($product_id);

		Tienda::load('TiendaHelperUser', 'helpers.user');
		$session = JFactory::getSession();
		$user_id = $session->get('user_id', '', 'tienda_pos');
		$filter_group = TiendaHelperUser::getUserGroup($user_id, $product_id);
		$model->setState('filter_group', $filter_group);

		$row = $model->getItem(false);

		$view = $this->getView('pos', 'html');
		$view->setModel($model, true);
		$view->assign('product', $row);
		$view->setLayout('viewproduct');

		$dispatcher = &JDispatcher::getInstance();

		ob_start();
		$dispatcher->trigger('onDisplayProductAttributeOptions', array($row->product_id));
		$view->assign('onDisplayProductAttributeOptions', ob_get_contents());
		ob_end_clean();

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Method to item to cart
	 */
	function addToCart()
	{
		$post = JRequest::get('post');
		$files = JRequest::get('files');
		$product_id = $post['product_id'];

		// get attributes
		$attributes = array();
		foreach($post as $key => $value)
		{
			if(substr($key, 0, 10) == 'attribute_')
				$attributes[] = $value;
		}
		sort($attributes);
		$attributes_csv = implode(',', $attributes);

		$product_qty = $post['quantity'];
		// Integrity checks on quantity being added
		if($product_qty < 0)
			$product_qty = '1';

		// check product if available
		$availableQuantity = Tienda::getClass('TiendaHelperProduct', 'helpers.product')->getAvailableQuantity($product_id, $attributes_csv);
		if($availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity)
		{
			$messagetype = 'notice';
			$message = JText::_(JText::sprintf('NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $product_qty));
			$this->setRedirect('index.php?option=com_tienda&view=pos&task=addproducts&tmpl=component', $message, $messagetype);
			return ;
		}

		// chec if product for sale
		$product = JTable::getInstance('Products', 'TiendaTable');
		$product->load( array('product_id' => $product_id));

		// if product notforsale, fail
		if($product->product_notforsale)
		{
			$messagetype = 'notice';
			$message = JText::_("Product Not For Sale");
			$this->setRedirect('index.php?option=com_tienda&view=pos&task=addproducts&tmpl=component', $message, $messagetype);
			return ;
		}

		$session = JFactory::getSession();
		$cart_id = $session->get('user_id', '', 'tienda_pos');
		// userid from session
		$id_type = "user_id";

		// create cart object out of item properties
		$item = new JObject;
		$item->user_id = $cart_id;
		//TODO: need to determine what user
		$item->product_id = ( int )$product_id;
		$item->product_qty = ( int )$product_qty;
		$item->product_attributes = $attributes_csv;
		$item->vendor_id = '0';
		// vendors only in enterprise version

		$canAddToCart = Tienda::getClass('TiendaHelperCarts', 'helpers.carts')->canAddItem($item, $cart_id, $id_type);

		// onAfterCreateItemForAddToCart: plugin can add values to the item before it is being validated /added
		// once the extra field(s) have been set, they will get automatically saved
		$dispatcher = &JDispatcher::getInstance();
		$results = $dispatcher->trigger("onAfterCreateItemForAddToCart", array($item,
		$post,
		$files));
		foreach($results as $result)
		{
			foreach($result as $key => $value)
			{
				$item->set($key, $value);
			}
		}

		// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
		$results = array();
		$dispatcher = &JDispatcher::getInstance();
		$results = $dispatcher->trigger("onBeforeAddToCart", array($item,
		$post));

		for($i = 0; $i < count($results); $i++)
		{
			$result = $results[$i];
			if(!empty($result->error))
			{
				$messagetype = 'notice';
				$message = JText::_(JText::sprintf('NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $product_qty));
				$this->setRedirect('index.php?option=com_tienda&view=pos&task=addproducts&tmpl=component', $result->message, 'notice');
				return ;
			}
		}

		JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables');
		$table = JTable::getInstance('Carts', 'TiendaTable');

		// first, determine if this product+attribute+vendor(+additonal_keys) exists in the cart
		// if so, update quantity
		// otherwise, add as new item
		// return the cart object with cart_id (to be used by plugins, etc)

		$keynames = array();
		$keynames['user_id'] = $item->user_id;
		if(empty($item->user_id))
		{
			$keynames['session_id'] = $session->getId();
		}
		$keynames['product_id'] = $item->product_id;
		$keynames['product_attributes'] = $item->product_attributes;

		// fire plugin event: onGetAdditionalCartKeyValues
		// this event allows plugins to extend the multiple-column primary key of the carts table
		$additionalKeyValues = TiendaHelperCarts::getAdditionalKeyValues($item, null, null);
		if(!empty($additionalKeyValues))
		{
			$keynames = array_merge($keynames, $additionalKeyValues);
		}

		if($table->load($keynames))
		{
			$table->product_qty = $table->product_qty + $item->product_qty;
		}
		else
		{
			foreach($item as $key => $value)
			{
				if(property_exists($table, $key))
				{
					$table->set($key, $value);
				}
			}
		}

		// Now for Eavs!!
		$eavs = TiendaHelperEav::getAttributes('products', $item->product_id);

		if(count($eavs))
		{
			foreach($eavs as $eav)
			{
				// Search for user edtable fields & user submitted value
				if($eav->editable_by == 2 && array_key_exists($eav->eavattribute_alias, $item))
				{
					$key = $eav->eavattribute_alias;
					$table->set($key, $item->$key);
				}
			}
		}

		$date = JFactory::getDate();
		$table->last_updated = $date->toMysql();
		$table->session_id = $session->getId();

		if(!$table->save())
		{
			JError::raiseNotice('updateCart', $table->getError());
		}
		else
		{
			$this->fixQuantities($item->user_id);
		}
		$this->setRedirect('index.php?option=com_tienda&view=pos&task=addproducts&added=1&tmpl=component', JText::_('Successfullty Added Item to Cart'), 'success');
	}

	function getCartView()
	{
		$session = JFactory::getSession();
		$user_id = $session->get('user_id', '', 'tienda_pos');
		$model = $this->getModel('Carts');
		$model->setState('filter_user', $user_id);
		$items = &$model->getList();

		if(!empty($items))
		{
			//trigger the onDisplayCartItem for each cartitem
			$dispatcher = &JDispatcher::getInstance();

			$i = 0;
			$onDisplayCartItem = array();
			foreach($items as $item)
			{
				ob_start();
				$dispatcher->trigger('onDisplayCartItem', array($i,
				$item));
				$cartItemContents = ob_get_contents();
				ob_end_clean();
				if(!empty($cartItemContents))
				{
					$onDisplayCartItem[$i] = $cartItemContents;
				}
				$i++;
			}
			$view = $this->getView('pos', 'html');
			$view->assign('onDisplayCartItem', $onDisplayCartItem);
			$view->assign('items', $items);
			$view->setModel($model, true);
			$view->setLayout('cart');

			ob_start();
			$view->display();
			$html = ob_get_contents();
			ob_end_clean();
		}
		else
		{
			$html = JText::_('No Items in Cart');
		}

		return $html;
	}

	/**
	 * Method to remove items or updated the quantities to a cart
	 */
	function update()
	{
		$model = $this->getModel('carts');
		$post = JRequest::get('post');
		$cids = JRequest::getVar('cid', array(0), '', 'ARRAY');
		$product_attributes = JRequest::getVar('product_attributes', array(0), '', 'ARRAY');
		$quantities = JRequest::getVar('quantities', array(0), '', 'ARRAY');

		$session = &JFactory::getSession();
		$user_id = $session->get('user_id', '', 'tienda_pos');

		if(isset($post['remove']))
		{
			foreach($cids as $cart_id => $product_id)
			{
				$row = $model->getTable();
				$ids = array('user_id' => $user_id,
				'cart_id' => $cart_id);

				if($return = $row->delete( array('cart_id' => $cart_id)))
				{
					$item = new JObject;
					$item->product_id = $product_id;
					$item->product_attributes = $product_attributes[$cart_id];
					$item->vendor_id = '0';
					// vendors only in enterprise version

					// fire plugin event
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger('onRemoveFromCart', array($item));
				}
			}
		}
		elseif($post['update'])
		{
			foreach($quantities as $cart_id => $value)
			{
				$carts = JTable::getInstance('Carts', 'TiendaTable');
				$carts->load( array('cart_id' => $cart_id));
				$product_id = $carts->product_id;
				$value = (int)$value;

				$vals = array();
				$vals['user_id'] = $user_id->id;
				$vals['session_id'] = $session->getId();
				$vals['product_id'] = $product_id;

				$availableQuantity = Tienda::getClass('TiendaHelperProduct', 'helpers.product')->getAvailableQuantity($product_id, $product_attributes[$cart_id]);
				if($availableQuantity->product_check_inventory && $value > $availableQuantity->quantity)
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $value));
					continue ;
				}

				if($value > 1)
				{
					$product = JTable::getInstance('Products', 'TiendaTable');
					$product->load( array('product_id' => $product_id));
					if($product->quantity_restriction)
					{
						$min = $product->quantity_min;
						$max = $product->quantity_max;

						if($max)
						{
							if($value > $max)
							{
								$msg = JText::_('You have reached the maximum quantity for this object: ') . $max;
								$value = $max;
							}
						}
						if($min)
						{
							if($value < $min)
							{
								$msg = JText::_('You have reached the minimum quantity for this object: ') . $min;
								$value = $min;
							}
						}
					}
					if($product->product_recurs)
					{
						$value = 1;
					}
				}

				$row = $model->getTable();
				$vals['product_attributes'] = $product_attributes[$cart_id];
				$vals['product_qty'] = $value;
				if(empty($vals['product_qty']) || $vals['product_qty'] < 1)
				{
					// remove it
					if($return = $row->delete($cart_id))
					{
						$item = new JObject;
						$item->product_id = $product_id;
						$item->product_attributes = $product_attributes[$cart_id];
						$item->vendor_id = '0';
						// vendors only in enterprise version

						// fire plugin event
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger('onRemoveFromCart', array($item));
					}
				}
				else
				{
					$row->load($cart_id);
					$row->product_qty = $vals['product_qty'];
					$row->save();
				}

			}
		}
		$this->setRedirect("index.php?option=com_tienda&view=pos&nextstep=step2");
	}

	/**
	 * Adjusts cart quantities based on availability
	 *
	 * @see tienda/admin/TiendaHelperCarts::fixQuantities()
	 */
	function fixQuantities($user_id)
	{
		JModel::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models');
		JModel::addIncludePath(JPATH_SITE . DS . 'components' . DS . 'com_tienda' . DS . 'models');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables');
		$product = JTable::getInstance('ProductQuantities', 'TiendaTable');
		$tableProduct = JTable::getInstance('Products', 'TiendaTable');

		$suffix = strtolower(TiendaHelperCarts::getSuffix());
		$model = &JModel::getInstance('Carts', 'TiendaModel');

		switch ($suffix)
		{
			case 'sessioncarts' :

			case 'carts' :

			default :
				$model->setState('filter_user', $user_id);

				$cart = $model->getList();
				if(!empty($cart))
				{
					foreach($cart as $cartitem)
					{
						$keynames = array();
						$keynames['user_id'] = $cartitem->user_id;
						if(empty($cartitem->user_id))
						{
							$keynames['session_id'] = $cartitem->session_id;
						}
						$keynames['product_id'] = $cartitem->product_id;
						$keynames['product_attributes'] = $cartitem->product_attributes;

						$tableProduct->load($cartitem->product_id);
						if($tableProduct->quantity_restriction)
						{
							$quantity = $cartitem->product_qty;
							$min = $tableProduct->quantity_min;
							$max = $tableProduct->quantity_max;

							if($max)
							{
								if($cartitem->product_qty > $max)
								{
									$quantity = $max;
								}
							}
							if($min)
							{
								if($cartitem->product_qty < $min)
								{
									$quantity = $min;
								}
							}
							// load table to adjust quantity in cart
							$table = JTable::getInstance('Carts', 'TiendaTable');
							//$table->load($keynames);
							$table->load( array('cart_id' => $cartitem->cart_id));
							$table->product_id = $cartitem->product_id;
							$table->product_attributes = $cartitem->product_attributes;
							$table->user_id = $cartitem->user_id;
							$table->session_id = $cartitem->session_id;
							// adjust the cart quantity
							$table->product_qty = $quantity;
							$table->save();
						}

						if(empty($tableProduct->product_check_inventory))
						{
							// if this item doesn't check inventory, skip it
							continue ;
						}

						$product->load( array('product_id' => $cartitem->product_id,
						'vendor_id' => '0',
						'product_attributes' => $cartitem->product_attributes));
						if($cartitem->product_qty > $product->quantity)
						{
							// enqueu a system message
							JFactory::getApplication()->enqueueMessage(JText::sprintf('NOT_AVAILABLE_QUANTITY', $cartitem->product_name, $cartitem->product_qty));

							// load table to adjust quantity in cart
							$table = JTable::getInstance('Carts', 'TiendaTable');
							$table->load($keynames);
							$table->product_id = $cartitem->product_id;
							$table->product_attributes = $cartitem->product_attributes;
							$table->user_id = $cartitem->user_id;
							$table->session_id = $cartitem->session_id;
							// adjust the cart quantity
							$table->product_qty = $product->quantity;
							$table->save();
						}
					}
				}

				break;
		}
	}

	/**
	 * Method to populate the order with the items from cart
	 * @return object - order object
	 */
	function populateOrder()
	{
		$session = JFactory::getSession();
		$order = JTable::getInstance('Orders', 'TiendaTable');
		$order->currency_id = TiendaConfig::getInstance()->get('default_currencyid', '1');
		// USD is default if no currency selected
		// set the shipping method
		$order->shipping_method_id = TiendaConfig::getInstance()->get('defaultShippingMethod', '2');

		// set the order's addresses based on the form inputs
		// set to user defaults
		Tienda::load("TiendaHelperBase", 'helpers._base');
		$user_helper = &TiendaHelperBase::getInstance('User');
		$user_id = $session->get('user_id', '', 'tienda_pos');
		$billingAddress = $user_helper->getPrimaryAddress($user_id, 'billing');
		$shippingAddress = $user_helper->getPrimaryAddress($user_id, 'shipping');

		$order->setAddress($billingAddress, 'billing');
		$order->setAddress($shippingAddress, 'shipping');

		// get the items and add them to the order		
		$items = $this->getProductsInfo();

		foreach($items as $item)
		{
			$order->addItem($item);
		}

		// get the order totals
		$order->calculateTotals();
		return $order;
	}

	function getOrderSummary(&$order)
	{
		$model = $this->getModel('carts');
		$view = $this->getView( 'pos', 'html' );	
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );	
		
		$config = TiendaConfig::getInstance();
        $show_tax = $config->get('display_prices_with_tax');
        $view->assign( 'show_tax', $show_tax );
        $view->assign( 'using_default_geozone', false );
            
        $view->assign( 'order', $order );
		
		if($show_tax)
        {
	        $geozones = $order->getBillingGeoZones();
	        if (empty($geozones))
	        {
	            // use the default
	            $view->assign( 'using_default_geozone', true );
	            $table = JTable::getInstance('Geozones', 'TiendaTable');
	            $table->load(array('geozone_id'=>$config->get('default_tax_geozone')));
	            $geozones = array( $table );
	        }        
        }
		
		$orderitems = $order->getItems();
		
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $product_helper = &TiendaHelperBase::getInstance( 'Product' );
        $order_helper = &TiendaHelperBase::getInstance( 'Order' );
		
		$tax_sum = 0;
        foreach ($orderitems as &$item)
        {
            $item->price = $item->orderitem_price + floatval( $item->orderitem_attributes_price );            
            $tax = 0;
            if ($show_tax)
            {            	
		        foreach($geozones as $geozone)
		        {
		        	  $taxrate = $product_helper->getTaxRate($item->product_id, $geozone->geozone_id, true );
				      $product_tax_rate = $taxrate->tax_rate;	
				      $tax += ($product_tax_rate/100) * ($item->orderitem_price + floatval( $item->orderitem_attributes_price ));
		        }       	
            
            	$item->price = $item->orderitem_price + floatval( $item->orderitem_attributes_price ) + $tax;
                $item->orderitem_final_price = $item->price * $item->orderitem_quantity;
               
                $order->order_subtotal += ($tax * $item->orderitem_quantity);    
            }
            $tax_sum += ($tax * $item->orderitem_quantity);
        }
     
        if (empty($order->user_id))
        {
            //$order->order_total += $tax_sum;
            $order->order_tax += $tax_sum;
        }

        $view->assign( 'orderitems', $orderitems );
		
		// Checking whether shipping is required
		$showShipping = false;		
		if ($isShippingEnabled = $model->getShippingIsEnabled())
		{
			$showShipping = true;
			$view->assign( 'shipping_total', $order->getShippingTotal() );
		}
		$view->assign( 'showShipping', $showShipping );
		
        //START onDisplayOrderItem: trigger plugins for extra orderitem information
        if (!empty($orderitems))
        {
        	$onDisplayOrderItem = $order_helper->onDisplayOrderItems($orderitems);        	
	        $view->assign( 'onDisplayOrderItem', $onDisplayOrderItem );
        }
        //END onDisplayOrderItem
        
		$view->setLayout( 'ordersummary' );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Gets an address form for display
	 *
	 * @param string $prefix
	 * @return string html
	 */
	function getAddressForm($prefix, $showShipping=false, $forShipping=false)
	{
		$html = '';
		$model = $this->getModel('Addresses', 'TiendaModel');
		$view = $this->getView('pos', 'html');
		$view->set('form_prefix', $prefix);
		$view->setModel($model, true);
		$view->setLayout('form_address');

		$view->assign('showShipping', $showShipping);
		$view->assign('forShipping', $forShipping);

		JModel::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models');
		$countries_model = JModel::getInstance('Countries', 'TiendaModel');
		$default_country = $countries_model->getDefault();
		$default_country_id = $default_country->country_id;

		Tienda::load('TiendaSelect', 'library.select');
		$zones = TiendaSelect::zone('', $prefix . 'zone_id', $default_country_id);

		$view->assign('default_country_id', $default_country_id);
		$view->assign('zones', $zones);

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 *
	 * @param $address_id
	 * @return unknown_type
	 */
	function retrieveAddressIntoArray( $address_id )
	{
		$model = JModel::getInstance( 'Addresses', 'TiendaModel' );
		$model->setId($address_id);
		$item = $model->getItem();
		if (is_object($item))
		{
			return get_object_vars( $item );
		}
		return array();
	}
	/**
	 *
	 * @param $values
	 * @para boolean - save the addresses
	 * @return unknown_type
	 */
	function setAddresses(&$order, $values, $saved = false )
	{		
		// Get the currency from the configuration
		$currency_id			= TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected
		$billing_address_id     = (!empty($values['billing_address_id'])) ? $values['billing_address_id'] : 0;
		$shipping_address_id    = (!empty($values['shipping_address_id'])) ? $values['shipping_address_id'] : 0;
		//$shipping_method_id     = $values['shipping_method_id'];
		$same_as_billing        = (!empty($values['sameasbilling'])) ? true : false;		
		$billing_input_prefix   = 'billing_input_';
		$shipping_input_prefix  = 'shipping_input_';
		
		$session = JFactory::getSession();
		$user_id = $session->get('user_id', '', 'tienda_pos');	

		$billing_zone_id = 0;
	
		$billingAddressArray = $this->retrieveAddressIntoArray($billing_address_id);
		if (array_key_exists('zone_id', $billingAddressArray))
		{
			$billing_zone_id = $billingAddressArray['zone_id'];
		}

		//SHIPPING ADDRESS: get shipping address from dropdown or form (depending on selection)
		$shipping_zone_id = 0;
		if ($same_as_billing)
		{
			$shippingAddressArray = $billingAddressArray;
		}
		else
		{
			$shippingAddressArray = $this->retrieveAddressIntoArray($shipping_address_id);
			//$shippingAddressArray = $this->getAddress($shipping_address_id, $shipping_input_prefix, $values);
		}

		if (array_key_exists('zone_id', $shippingAddressArray))
		{
			$shipping_zone_id = $shippingAddressArray['zone_id'];
		}

		// keep the array for binding during the save process
		//$this->_orderinfoBillingAddressArray = $this->filterArrayUsingPrefix($billingAddressArray, '', 'billing_', true);
		//$this->_orderinfoShippingAddressArray = $this->filterArrayUsingPrefix($shippingAddressArray, '', 'shipping_', true);
		//$this->_billingAddressArray = $billingAddressArray;
		//$this->_shippingAddressArray = $shippingAddressArray;

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$billingAddress = JTable::getInstance('Addresses', 'TiendaTable');
		$shippingAddress = JTable::getInstance('Addresses', 'TiendaTable');

		// set the order billing address
		$billingAddress->bind( $billingAddressArray );
		$billingAddress->user_id = $user_id;
		if($saved) $billingAddress->save();
		
		$order->setAddress( $billingAddress);

		// set the order shipping address
		$shippingAddress->bind( $shippingAddressArray );
		$shippingAddress->user_id = $user_id;
		if($saved) $shippingAddress->save();
		
		$order->setAddress( $shippingAddress, 'shipping' );

		return;
	}
	
	
	function getShippingHtml($order=null)
	{
		$html = '';
		$model = $this->getModel('Checkout', 'TiendaModel');
		$view = $this->getView('pos', 'html');
		$view->setModel($model, true);
		$view->setLayout('shipping');

		$rates = $this->getShippingRates($order);
		$default_rate = array();
		if(count($rates) == 1)
		{
			$default_rate = $rates[0];
		}
		$view->assign('rates', $rates);
		$view->assign('default_rate', $default_rate);

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	function getShippingRates($order=null)
	{
		// get all the enabled shipping plugins
		Tienda::load('TiendaHelperPlugin', 'helpers.plugin');
		//$plugins = TiendaHelperPlugin::getPluginsWithEvent( 'onGetShippingPlugins' );
		$model = JModel::getInstance('Shipping', 'TiendaModel');
		$model->setState('filter_enabled', '1');
		$plugins = $model->getList();

		$dispatcher = &JDispatcher::getInstance();

		$rates = array();

		if($plugins)
		{
			foreach($plugins as $plugin)
			{

				$shippingOptions = $dispatcher->trigger("onGetShippingOptions", array($plugin->element,
				$order));

				if(in_array(true, $shippingOptions, true))
				{
					$results = $dispatcher->trigger("onGetShippingRates", array($plugin->element,
					$order));
					foreach($results as $result)
					{
						if(is_array($result))
						{
							foreach($result as $r)
							{
								$extra = 0;
								// here is where a global handling rate would be added
								if($global_handling = TiendaConfig::getInstance()->get('global_handling'))
								{
									$extra = $global_handling;
								}
								$r['extra'] += $extra;
								$r['total'] += $extra;
								$rates[] = $r;
							}
						}
					}
				}
			}
		}

		return $rates;
	}

	/**
	 * Sets the selected shipping method
	 *
	 * @return unknown_type
	 */
	function setShippingMethod()
	{
		$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		$values = $helper->elementsToArray( $elements );

		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		// get the order object so we can populate it
		$order = JTable::getInstance('Orders', 'TiendaTable');
	
		// bind what you can from the post
		$order->bind( $values );

		// set the currency
		$order->currency_id = TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected

		// set the shipping method
		$order->shipping = new JObject();
		$order->shipping->shipping_price      = @$values['shipping_price'];
		$order->shipping->shipping_extra      = @$values['shipping_extra'];
		$order->shipping->shipping_name       = @$values['shipping_name'];
		$order->shipping->shipping_tax        = @$values['shipping_tax'];

		// set the addresses
		$this->setAddresses( $order, $values );
		
		$items = $this->getProductsInfo();
		foreach ($items as $item)
		{
			$order->addItem( $item );
		}

	    // get all coupons and add them to the order
        if (!empty($values['coupons']))
        {
            foreach ($values['coupons'] as $coupon_id)
            {
                $coupon = JTable::getInstance('Coupons', 'TiendaTable');
                $coupon->load(array('coupon_id'=>$coupon_id));
                $order->addCoupon( $coupon );
            }
        }
		
		// get the order totals
		$order->calculateTotals();

		// now get the summary
		$html = $this->getOrderSummary($order);

		$response = array();
		$response['msg'] = $html;	
		$response['error'] = '';

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);

		return;
	}

	function updateShippingRates()
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

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
			$response['msg'] = $helper->generateMessage(JText::_("Error while validating the parameters"));
			echo ( json_encode( $response ) );
			return;
		}
		
		$order = &$this->populateOrder();
		// set response array
		$response = array();
		$response['msg'] = $this->getShippingHtml($order);

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);

		return;
	}
	
	function getPaymentOptionsHtml(&$order)
	{
		$html = '';
		$model = $this->getModel( 'Checkout', 'TiendaModel' );
		$view   = $this->getView( 'pos', 'html' );		
		$view->setModel( $model, true );
		$view->setLayout( 'payment_options' ); 

        $payment_plugins = $this->getPaymentOptions($order);		
        $view->assign( 'payment_plugins',  $payment_plugins);
        
        if (count($payment_plugins) == 1)
        {
        	$payment_plugins[0]->checked = true;
        	$dispatcher    =& JDispatcher::getInstance();
			$results = $dispatcher->trigger( "onGetPaymentForm", array( $payment_plugins[0]->element, '' ) );
	
			$text = '';
			for ($i=0; $i<count($results); $i++)
			{				
				$text .= $results[$i];
			}
	     
            $view->assign( 'payment_form_div', $text );                                               
        }  
		
		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	function getPaymentOptions(&$order)
	{
		$options = array();
		
		if(is_null($order)) return $options;
		
		//get payment plugins
		// get all the enabled payment plugins
		Tienda::load( 'TiendaHelperPlugin', 'helpers.plugin' );
		$plugins = TiendaHelperPlugin::getPluginsWithEvent( 'onGetPaymentPlugins' );
	   
	    if ($plugins)
	    {
	    	$dispatcher =& JDispatcher::getInstance();
	    	foreach ($plugins as $plugin)
	        {
	            $results = $dispatcher->trigger( "onGetPaymentOptions", array( $plugin->element, $order ) );
	            if (in_array(true, $results, true))
	            {
	            	$options[] = $plugin;
	        	}
	        }
        }
        
        return $options;
	}
	
	/**
	 * Fires selected tienda payment plugin and captures output
	 * Returns via json_encode
	 *
	 * @return unknown_type
	 */
	function getPaymentForm( $element='' )
	{
		// Use AJAX to show plugins that are available
		JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );
		$values = JRequest::get('post');
		$html = '';
		$text = "";
		$user = JFactory::getUser();
		if (empty($element)) { $element = JRequest::getVar( 'payment_element' ); }
		$results = array();
		$dispatcher    =& JDispatcher::getInstance();
		$results = $dispatcher->trigger( "onGetPaymentForm", array( $element, $values ) );

		for ($i=0; $i<count($results); $i++)
		{
			$result = $results[$i];
			$text .= $result;
		}

		$html = $text;

		// set response array
		$response = array();
		$response['msg'] = $html;

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);

		return;
	}
	
	// TODO: transfer it to the helper?
	function getProductsInfo()
	{
	    Tienda::load( "TiendaHelperProduct", 'helpers.product' );
	    $product_helper = TiendaHelperBase::getInstance( 'Product' );
	    
		JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$model = JModel::getInstance( 'Carts', 'TiendaModel');

		$session =& JFactory::getSession();
		$user_id = $session->get('user_id', '', 'tienda_pos');
		$model->setState('filter_user', $user_id );		
		
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
		Tienda::load( "TiendaHelperCarts", 'helpers.carts' );
        $user_helper = &TiendaHelperBase::getInstance( 'User' );
        $filter_group = $user_helper->getUserGroup($user_id);
        $model->setState('filter_group', $filter_group );

		$cartitems = $model->getList();

		$productitems = array();
		foreach ($cartitems as $cartitem)
		{
		    //echo Tienda::dump($cartitem);
			unset($productModel);
			$productModel = JModel::getInstance('Products', 'TiendaModel');			
        	$filter_group = $user_helper->getUserGroup($user_id, $cartitem->product_id);
        	$productModel->setState('filter_group', $filter_group );
			$productModel->setId($cartitem->product_id);
			if ($productItem = $productModel->getItem(false))
			{								
				$productItem->price = $productItem->product_price = !$cartitem->product_price_override->override ? $cartitem->product_price : $productItem->price;	
				
				//we are not overriding the price if its a recurring && price				
				if(!$productItem->product_recurs && $cartitem->product_price_override->override)
				{
					// at this point, ->product_price holds the default price for the product,
					// but the user may qualify for a discount based on volume or date, so let's get that price override
					// TODO Shouldn't we remove this?  Is it necessary?  $cartitem has already done this in the carts model!
					$productItem->product_price_override = $product_helper->getPrice( $productItem->product_id, $cartitem->product_qty, $filter_group, JFactory::getDate()->toMySQL() );
					if (!empty($productItem->product_price_override))
					{
						$productItem->product_price = $productItem->product_price_override->product_price;
					}
				}

				if($productItem->product_check_inventory)
				{
					// using a helper file,To determine the product's information related to inventory
					$availableQuantity = $product_helper->getAvailableQuantity( $productItem->product_id, $cartitem->product_attributes );
					if( $availableQuantity->product_check_inventory && $cartitem->product_qty >$availableQuantity->quantity && $availableQuantity->quantity >=1) {
						JFactory::getApplication()->enqueueMessage(JText::sprintf( 'CART_QUANTITY_ADJUSTED',$productItem->product_name, $cartitem->product_qty, $availableQuantity-> quantity ));
						$cartitem->product_qty = $availableQuantity->quantity;
					}

					// removing the product from the cart if it's not available
					if ($availableQuantity->quantity == 0)
					{
						if (empty($cartitem->user_id))
						{
							TiendaHelperCarts::removeCartItem( $session_id, $cartitem->user_id, $cartitem->product_id );
						}
    						else
						{
							TiendaHelperCarts::removeCartItem( $cartitem->session_id, $cartitem->user_id, $cartitem->product_id );
						}
						JFactory::getApplication()->enqueueMessage( JText::sprintf( 'Not available') . " " .$productItem->product_name );
						continue;
					}
                }
                
    			// TODO Push this into the orders object->addItem() method?
    			$orderItem = JTable::getInstance('OrderItems', 'TiendaTable');
    			$orderItem->product_id                    = $productItem->product_id;
    			$orderItem->orderitem_sku                 = $cartitem->product_sku;
    			$orderItem->orderitem_name                = $productItem->product_name;
    			$orderItem->orderitem_quantity            = $cartitem->product_qty;
    			$orderItem->orderitem_price               = $productItem->product_price;
    			$orderItem->orderitem_attributes          = $cartitem->product_attributes;
    			$orderItem->orderitem_attribute_names     = $cartitem->attributes_names;
    			$orderItem->orderitem_attributes_price    = $cartitem->orderitem_attributes_price;
    			$orderItem->orderitem_final_price         = ($orderItem->orderitem_price + $orderItem->orderitem_attributes_price) * $orderItem->orderitem_quantity;
 		
    			$dispatcher =& JDispatcher::getInstance();
		        $results = $dispatcher->trigger( "onGetAdditionalOrderitemKeyValues", array( $cartitem ) );
		        foreach ($results as $result)
		        {
		            foreach($result as $key=>$value)
		            {
		            	$orderItem->set($key,$value);
		            }
		        }	    			
		        
    			// TODO When do attributes for selected item get set during admin-side order creation?
    			array_push($productitems, $orderItem);
            }
	   }	
	   return $productitems;
    }

}
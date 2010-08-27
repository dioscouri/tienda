<?php
/**
 * @version 1.5
 * @link  http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author  Dioscouri Design
 * @package Tienda
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerCheckout extends TiendaController
{
	var $_order                = null; // a TableOrders() object
	var $initial_order_state   = 15; // pre-payment/orphan set in costructor
	var $billing_input_prefix  = 'billing_input_';
	var $shipping_input_prefix = 'shipping_input_';
	var $defaultShippingMethod = null; // set in constructor
	var $steps 				   = array(); // set in constructor
	var $current_step 		   = 0;

	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();
		if (!TiendaConfig::getInstance()->get('shop_enabled', '1'))
		{
			JFactory::getApplication()->redirect( JRoute::_( 'index.php?option=com_tienda&view=products' ), JText::_( "Checkout Disabled" ) );
			return;
		}

		// get the items and add them to the order
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$items = TiendaHelperCarts::getProductsInfo();
		$task = JRequest::getVar('task');
		if (empty($items) && $task != 'confirmPayment' )
		{
			JFactory::getApplication()->redirect( JRoute::_( 'index.php?option=com_tienda&view=products' ), JText::_( "Your Cart is Empty" ) );
			return;
		}

		$uri = JFactory::getURI();
		if (TiendaConfig::getInstance()->get('force_ssl_checkout') && $uri->isSSL() == false )
		{
			$post = JRequest::get('post');
			if (is_array($post) && !empty($post))
			{
				// Don't redirect if this is POST
			}
			else
			{
				$uri->setScheme('https');
				JFactory::getApplication()->redirect( $uri->toString() );
				return;
			}
		}

		$this->set('suffix', 'checkout');
		// create the order object
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$this->_order = JTable::getInstance('Orders', 'TiendaTable');
		$this->defaultShippingMethod = TiendaConfig::getInstance()->get('defaultShippingMethod', '2');
		$this->initial_order_state = TiendaConfig::getInstance()->get('initial_order_state', '15');
		// Default Steps
		$this->steps = array(
            'STEP_SELECTSHIPPINGMETHOD',
            'STEP_SELECTPAYMENTMETHOD',
            'STEP_REVIEWORDER',
            'STEP_CHECKOUTRESULTS'
            );
            $this->current_step = 0;
	}

	/**
	 * Gets this view's unique namespace for request & session variables
	 * (non-PHPdoc)
	 *
	 * @see tienda/site/TiendaController#getNamespace()
	 * @return unknown
	 */
	function getNamespace()
	{
		$app = JFactory::getApplication();
		$ns = $app->getName().'::'.'com.tienda.model.checkout';
		return $ns;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see tienda/site/TiendaController#view()
	 */
	function display()
	{
		$user = JFactory::getUser();
		JRequest::setVar( 'view', $this->get('suffix') );
		$guest = JRequest::getVar( 'guest', '0' );
		if($guest == '1')
		$guest = true;
		else
		$guest = false;

		$register =JRequest::getVar( 'register', '0' );

		if($register == '1')
		$register = true;
		else
		$register = false;
		// determine layout based on login status
		// Login / Register / Checkout as a guest
		if (empty($user->id) && !($guest || $register))
		{
			// Display a form for selecting either to register or to login
			JRequest::setVar('layout', 'form');
			Tienda::load( "TiendaHelperRoute", 'helpers.route' );
			$helper = new TiendaHelperRoute();
			$view = $this->getView( 'checkout', 'html' );
			$view->assign('checkout_itemid', $helper->findItemid( array('view'=>'checkout') ) );
			parent::display();
			return;
		}
		elseif (($guest && TiendaConfig::getInstance()->get('guest_checkout_enabled')) || $register)
		{
			// Checkout as a Guest
			$order =& $this->_order;
			$order = $this->populateOrder(true);

			// now that the order object is set, get the orderSummary html
			$html = $this->getOrderSummary();

			// Get the current step
			$progress = $this->getProgress();

			// get address forms
			$billing_address_form = $this->getAddressForm( $this->billing_input_prefix, true );
			$shipping_address_form = $this->getAddressForm( $this->shipping_input_prefix, true, true );

			// get all the enabled shipping plugins
			Tienda::load( 'TiendaHelperPlugin', 'helpers.plugin' );
			$plugins = TiendaHelperPlugin::getPluginsWithEvent( 'onGetShippingPlugins' );

			$dispatcher =& JDispatcher::getInstance();

			$rates = array();
			if ($plugins)
			{
				foreach ($plugins as $plugin)
				{
					$results = $dispatcher->trigger( "onGetShippingRates", array( $plugin->element, $order ) );

					foreach ($results as $result)
					{
						if(is_array($result))
						{
							foreach( $result as $r )
							{
								$rates[] = $r;
							}
						}
					}// endforeach results

				} // endforeach plugins
			} // endif plugins


			// now display the entire checkout page
			$view = $this->getView( 'checkout', 'html' );
			$view->set( 'hidemenu', false);
			$view->assign( 'order', $order );
			if($register){
				$view->assign( 'register', $register );
			}
			$view->assign( 'billing_address_form', $billing_address_form );
			$view->assign( 'shipping_address_form', $shipping_address_form );
			$view->assign( 'orderSummary', $html );
			$view->assign( 'progress', $progress );
			//$view->assign( 'default_billing_address', $default_billing_address );
			//$view->assign( 'default_shipping_address', $default_shipping_address );
			$view->assign( 'rates', $rates );

			// Checking whether shipping is required
			$showShipping = false;
			$shipping_layout = "shipping_no";

			$cartsModel = $this->getModel('carts');
			if ($isShippingEnabled = $cartsModel->getShippingIsEnabled())
			{
				$showShipping = true;
			}

			if ($showShipping)
			{
				$shipping_layout = "shipping_yes";
				if (empty( $shippingAddress ))
				{
					$shipping_layout = "shipping_calculate";
				}
			}
			$shipping_method_form = $this->getShippingHtml( $shipping_layout );
			$view->assign( 'showShipping', $showShipping );
			$view->assign( 'shipping_method_form', $shipping_method_form );

			JRequest::setVar('layout', 'guest');
		}
		else
		{
			// Already Logged in, a traditional checkout
			$order =& $this->_order;
			$order = $this->populateOrder(false);

			// now that the order object is set, get the orderSummary html
			$html = $this->getOrderSummary();

			// Get the current step
			$progress = $this->getProgress();

			JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
			$model = JModel::getInstance( 'addresses', 'TiendaModel' );
			$model->setState("filter_userid", JFactory::getUser()->id);
			$model->setState("filter_deleted", 0);
			$addresses = $model->getList();

			$billingAddress = $order->getBillingAddress();
			$shippingAddress = $order->getShippingAddress();

			// get address forms
			$billing_address_form = $this->getAddressForm( $this->billing_input_prefix );
			$shipping_address_form = $this->getAddressForm( $this->shipping_input_prefix, false ,true );

			// get the default shipping and billing addresses, if possible
			$default_billing_address = $this->getAddressHtml( @$billingAddress->address_id );
			$default_shipping_address = $this->getAddressHtml( @$shippingAddress->address_id );

			// now display the entire checkout page
			$view = $this->getView( 'checkout', 'html' );
			$view->set( 'hidemenu', false);
			$view->assign( 'order', $order );
			$view->assign( 'addresses', $addresses );
			$view->assign( 'billing_address', $billingAddress);
			$view->assign( 'shipping_address', $shippingAddress );
			$view->assign( 'billing_address_form', $billing_address_form );
			$view->assign( 'shipping_address_form', $shipping_address_form );
			$view->assign( 'orderSummary', $html );
			$view->assign( 'progress', $progress );
			$view->assign( 'default_billing_address', $default_billing_address );
			$view->assign( 'default_shipping_address', $default_shipping_address );

			// Check whether shipping is required
			$showShipping = false;
			$shipping_layout = "shipping_no";

			$cartsModel = $this->getModel('carts');
			if ($isShippingEnabled = $cartsModel->getShippingIsEnabled())
			{
				$showShipping = true;
			}

			if ($showShipping)
			{
				$shipping_layout = "shipping_yes";
				if (empty( $shippingAddress ))
				{
					$shipping_layout = "shipping_calculate";
				}

			}
			$shipping_method_form = $this->getShippingHtml( $shipping_layout );
			$view->assign( 'showShipping', $showShipping );
			$view->assign( 'shipping_method_form', $shipping_method_form );

			JRequest::setVar('layout', 'default');
		}

		$dispatcher =& JDispatcher::getInstance();

		ob_start();
		$dispatcher->trigger( 'onBeforeDisplaySelectShipping', array( $order ) );
		$view->assign( 'onBeforeDisplaySelectShipping', ob_get_contents() );
		ob_end_clean();

		ob_start();
		$dispatcher->trigger( 'onAfterDisplaySelectShipping', array( $order ) );
		$view->assign( 'onAfterDisplaySelectShipping', ob_get_contents() );
		ob_end_clean();

		parent::display();
	}

	/**
	 * Populate the order object with items and addresses, and calculate the order Totals
	 * @param $guest	guest mode?
	 * @return $order 	the populated order
	 */
	function populateOrder($guest = false)
	{
		$order =& $this->_order;
		// set the currency
		$order->currency_id = TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected
		// set the shipping method
		$order->shipping_method_id = $this->defaultShippingMethod;

		if (!$guest)
		{
			// set the order's addresses based on the form inputs
			// set to user defaults
			Tienda::load( 'TiendaHelperUser', 'helpers.user' );
			$billingAddress = TiendaHelperUser::getPrimaryAddress( JFactory::getUser()->id );
			$shippingAddress = TiendaHelperUser::getPrimaryAddress( JFactory::getUser()->id, 'shipping' );
			$order->setAddress( $billingAddress, 'billing' );
			$order->setAddress( $shippingAddress, 'shipping' );
		}

		// get the items and add them to the order
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$items = TiendaHelperCarts::getProductsInfo();

		foreach ($items as $item)
		{
			$order->addItem( $item );
		}

		// get the order totals
		$order->calculateTotals();

		return $order;
	}

	/**
	 * Get the progress bar
	 */
	function getProgress()
	{
		$view = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->assign( 'steps', $this->steps );
		$view->assign( 'current_step', $this->current_step );
		$view->setLayout( 'progress' );

		// Get and Set Model
		$model = $this->getModel('checkout');
		$view->setModel( $model, true );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Prepares data for and returns the html of the order summary layout.
	 * This assumes that $this->_order has already had its properties set
	 *
	 * @return unknown_type
	 */
	function getOrderSummary()
	{
		// get the order object
		$order =& $this->_order; // a TableOrders object (see constructor)

		$model = $this->getModel('carts');
		$view = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'order', $order );
		$view->assign( 'orderitems', $order->getItems() );

		// Checking whether shipping is required
		$showShipping = false;
		$cartsModel = $this->getModel('carts');
		if ($isShippingEnabled = $cartsModel->getShippingIsEnabled())
		{
			$showShipping = true;
			$view->assign( 'shipping_total', $order->getShippingTotal() );
		}
		$view->assign( 'showShipping', $showShipping );

		$view->setLayout( 'cart' );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * (non-PHPdoc)
	 * @see tienda/site/TiendaController#validate()
	 */
	function validate()
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

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		$submitted_values = $helper->elementsToArray( $elements );

		$step = (!empty($submitted_values['step'])) ? strtolower($submitted_values['step']) : '';
		switch ($step)
		{
			case "selectshipping": {
				// Validate the email address if it is a guest checkout!
				if((TiendaConfig::getInstance()->get('guest_checkout_enabled', '1')) && !empty($submitted_values['guest']) )
				{
					jimport('joomla.mail.helper');
					if(!JMailHelper::isEmailAddress($submitted_values['email_address'])){
						$response['msg'] = $helper->generateMessage( JText::_('Please insert a correct email address') );
						$response['error'] = '1';
						echo ( json_encode( $response ) );
						return;
					}
					Tienda::load( 'TiendaHelperUser', 'helpers.user' );
					if(TiendaHelperUser::emailExists($submitted_values['email_address'])){
						$response['msg'] = $helper->generateMessage( JText::_('This email address is already registered! Login to checkout as a user!') );
						$response['error'] = '1';
						echo ( json_encode( $response ) );
						return;
					}
				}
				// checking for the registartion
				if(!empty($submitted_values['register']) )
				{
					// verify that fields are present
					if (empty($submitted_values['email_address']) || empty($submitted_values['name']) || empty($submitted_values['username']) || empty($submitted_values['password'] ) || empty ($submitted_values['password2']) )
					{
						$response['error'] = '1';
						$response['msg'] .= $helper->generateMessage(JText::_("All Fields of registreation sections are Mandatory"));
						echo ( json_encode( $response ) );
						return;
					}

					jimport('joomla.mail.helper');
					if(!JMailHelper::isEmailAddress($submitted_values['email_address'])){
						$response['msg'] = $helper->generateMessage( JText::_('Please insert a correct email address') );
						$response['error'] = '1';
						echo ( json_encode( $response ) );
						return;
					}
					Tienda::load( 'TiendaHelperUser', 'helpers.user' );
					if(TiendaHelperUser::emailExists($submitted_values['email_address'])){
						$response['msg'] = $helper->generateMessage( JText::_('This email address is already registered! Login to checkout as a user!') );
						$response['error'] = '1';
						echo ( json_encode( $response ) );
						return;
					}
					if (TiendaHelperUser::usernameExists($submitted_values['username']))
					{
						$response['error'] = '1';
						$response['msg'] .= $helper->generateMessage(JText::_("User Name Already exist"));
						echo ( json_encode( $response ) );
						return;
						// TODO user already exists

					}
					if (strcmp($submitted_values['password'],$submitted_values['password2'] ) )
					{
						$response['error'] = '1';
						$response['msg'] .= $helper->generateMessage(JText::_("Passwords are not matching"));
						echo ( json_encode( $response ) );
						return;
						// TODO user already exists

					}

				}
				$this->validateSelectShipping( $submitted_values );

				if(!empty($submitted_values['register']) )
				{
					$this->registerNewUser($submitted_values);
				}
				break; }
			case "selectpayment":
				$this->validateSelectPayment( $submitted_values );
				break;
			default:
				$response['error'] = '1';
				$response['msg'] = $helper->generateMessage(JText::_("INVALID STEP IN CHECKOUT PROCESS"));
				echo ( json_encode( $response ) );
				break;
		}
		return;
	}

	/**
	 * Validates the select shipping method form
	 */
	function validateSelectShipping( $submitted_values )
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();

		// fail if no shipping method selected

		if ($submitted_values['shippingrequired'])
		{
			if (empty($submitted_values['_checked']['shipping_plugin']))
			{
					
				$response['msg'] = $helper->generateMessage( JText::_('Please select shipping method') );
				$response['error'] = '1';
				echo ( json_encode( $response ) );
				return;
			}
		}

		// fail if billing address is invalid
		if (!$this->validateAddress( $submitted_values, $this->billing_input_prefix , @$submitted_values['billing_address_id'] ))
		{
			$response['msg'] = $helper->generateMessage( JText::_( "BILLING ADDRESS ERROR" )." :: ".$this->getError() );
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return;
		}

		// fail if shipping address is invalid
		// if we're checking shipping and the sameasbilling is checked, then this is good
		if($submitted_values['shippingrequired'])
		{
			$sameasbilling = (!empty($submitted_values['_checked']['sameasbilling']));
			if (!$sameasbilling && !$this->validateAddress( $submitted_values, $this->shipping_input_prefix, $submitted_values['shipping_address_id'] ))
			{
				$response['msg'] = $helper->generateMessage( JText::_( "SHIPPING ADDRESS ERROR" )." :: ".$this->getError() );
				$response['error'] = '1';
				echo ( json_encode( $response ) );
				return;
			}
		}

		// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
		$results = array();
		$dispatcher =& JDispatcher::getInstance();
		$results = $dispatcher->trigger( "onValidateSelectShipping", array( $submitted_values ) );

		for ($i=0; $i<count($results); $i++)
		{
			$result = $results[$i];
			if (!empty($result->error))
			{
				$response['msg'] = $helper->generateMessage( $result->message );
				$response['error'] = '1';
			}
			else
			{
				// if here, all is OK
				$response['error'] = '0';
			}
		}

		echo ( json_encode( $response ) );
		return;
	}

	/**
	 * Validates the select payment form
	 */
	function validateSelectPayment( $submitted_values )
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();

		// fail if not checked terms & condition
		if( TiendaConfig::getInstance()->get('require_terms') && empty($submitted_values['_checked']['shipping_terms']) )
		{
			$response['msg'] = $helper->generateMessage(JText::_('Please Check the Terms & Conditions'));
			$response['error'] = '1';
		}
		else
		{

			// fail if no payment method selected
			if (empty($submitted_values['_checked']['payment_plugin']) && !empty($submitted_values['order_total']) )
			{
				$response['msg'] = $helper->generateMessage(JText::_('Please select payment method'));
				$response['error'] = '1';
			}
			elseif ( (float)$submitted_values['order_total'] == (float)'0.00' )
			{
				$response['error'] = '0';
			}
			else
			{
				// Validate the results of the payment plugin
				$results = array();
				$dispatcher =& JDispatcher::getInstance();
				$results = $dispatcher->trigger( "onGetPaymentFormVerify", array( $submitted_values['_checked']['payment_plugin'], $submitted_values) );

				for ($i=0; $i<count($results); $i++)
				{
					$result = $results[$i];
					if (!empty($result->error))
					{
						$response['msg'] = $helper->generateMessage( $result->message );
						$response['error'] = '1';
					}
					else
					{
						// if here, all is OK
						$response['error'] = '0';
					}
				}
			}
		}

		// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
		$results = array();
		$dispatcher =& JDispatcher::getInstance();
		$results = $dispatcher->trigger( "onValidateSelectPayment", array( $submitted_values ) );

		for ($i=0; $i<count($results); $i++)
		{
			$result = $results[$i];
			if (!empty($result->error))
			{
				$response['msg'] = $helper->generateMessage( $result->message );
				$response['error'] = '1';
			}
			else
			{
				// if here, all is OK
				$response['error'] = '0';
			}
		}

		echo ( json_encode( $response ) );
		return;
	}

	/**
	 * Validates a submitted address inputs
	 */
	function validateAddress( $values, $prefix, $address_id )
	{
		$model = $this->getModel( 'Addresses', 'TiendaModel' );
		$table = $model->getTable();
		$addressArray = $this->getAddress( $address_id, $prefix, $values );

		// IS Guest Checkout?
		$user_id = JFactory::getUser()->id;
		if(TiendaConfig::getInstance()->get('guest_checkout_enabled', '1') && $user_id == 0)
		$addressArray['user_id'] = 9999; // Fake id for the checkout process
			
		$table->bind( $addressArray );
		if (!$table->check())
		{
			$this->setError( $table->getError() );
			return false;
		}
		return true;
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
	 *
	 * @param $values
	 * @return unknown_type
	 */
	function setAddresses( $values )
	{
		$order =& $this->_order; // a TableOrders object (see constructor)

		// Get the currency from the configuration
		$currency_id			= TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected
		$billing_address_id     = (!empty($values['billing_address_id'])) ? $values['billing_address_id'] : 0;
		$shipping_address_id    = (!empty($values['shipping_address_id'])) ? $values['shipping_address_id'] : 0;
		//$shipping_method_id     = $values['shipping_method_id'];
		$same_as_billing        = (!empty($values['sameasbilling'])) ? true : false;
		$user_id                = JFactory::getUser()->id;
		$billing_input_prefix   = $this->billing_input_prefix;
		$shipping_input_prefix  = $this->shipping_input_prefix;

		// Guest checkout
		if ($user_id == 0 && TiendaConfig::getInstance()->get('guest_checkout_enabled', '1'))
		{
			$user_id = 9999;
		}

		$billing_zone_id = 0;
		$billingAddressArray = $this->getAddress( $billing_address_id, $billing_input_prefix, $values );
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
			$shippingAddressArray = $this->getAddress($shipping_address_id, $shipping_input_prefix, $values);
		}

		if (array_key_exists('zone_id', $shippingAddressArray))
		{
			$shipping_zone_id = $shippingAddressArray['zone_id'];
		}

		// keep the array for binding during the save process
		$this->_orderinfoBillingAddressArray = $this->filterArrayUsingPrefix($billingAddressArray, '', 'billing_', true);
		$this->_orderinfoShippingAddressArray = $this->filterArrayUsingPrefix($shippingAddressArray, '', 'shipping_', true);
		$this->_billingAddressArray = $billingAddressArray;
		$this->_shippingAddressArray = $shippingAddressArray;

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$billingAddress = JTable::getInstance('Addresses', 'TiendaTable');
		$shippingAddress = JTable::getInstance('Addresses', 'TiendaTable');

		// set the order billing address
		$billingAddress->bind( $billingAddressArray );
		$billingAddress->user_id = $user_id;
		$order->setAddress( $billingAddress, 'billing' );

		// set the order shipping address
		$shippingAddress->bind( $shippingAddressArray );
		$shippingAddress->user_id = $user_id;
		$order->setAddress( $shippingAddress, 'shipping' );

		return;
	}

	/**
	 *
	 * @param unknown_type $address_id
	 * @param unknown_type $input_prefix
	 * @param unknown_type $form_input_array
	 * @return unknown_type
	 */
	function getAddress( $address_id, $input_prefix, $form_input_array )
	{
		$addressArray = array();
		if (!empty($address_id))
		{
			$addressArray = $this->retrieveAddressIntoArray($address_id);
		}
		else
		{
			$addressArray = $this->filterArrayUsingPrefix($form_input_array, $input_prefix, '', false );
			// set the zone name
			$zone = JTable::getInstance('Zones', 'TiendaTable');
			$zone->load( @$addressArray['zone_id'] );
			$addressArray['zone_name'] = $zone->zone_name;
			// set the country name
			$country = JTable::getInstance('Countries', 'TiendaTable');
			$country->load( @$addressArray['country_id'] );
			$addressArray['country_name'] = $country->country_name;
		}
		return $addressArray;
	}

	/**
	 * Gets an address formatted for display
	 *
	 * @param int $address_id
	 * @return string html
	 */
	function getAddressHtml( $address_id )
	{
		$html = '';
		$model = JModel::getInstance( 'Addresses', 'TiendaModel' );
		$model->setId( $address_id );
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

		return $html;
	}

	/**
	 * Gets an address form for display
	 *
	 * @param string $prefix
	 * @return string html
	 */
	function getAddressForm( $prefix, $guest = false, $forShipping=false )
	{
		$html = '';
		$model = $this->getModel( 'Addresses', 'TiendaModel' );
		$view   = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->set( 'form_prefix', $prefix );
		$view->set( 'guest', $guest );
		$view->setModel( $model, true );
		$view->setLayout( 'form_address' );

		// Checking whether shipping is required
		$showShipping = false;
		$cartsModel = $this->getModel('carts');
		if ($isShippingEnabled = $cartsModel->getShippingIsEnabled())
		{
			$showShipping = true;
		}
		$view->assign( 'showShipping', $showShipping );
		$view->assign( 'forShipping', $forShipping );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Gets the selected shipping method
	 *
	 * @param $shipping_method_id
	 * @return unknown_type
	 */
	function getShippingHtml( $layout='shipping_yes' )
	{
		$html = '';
		$model = $this->getModel( 'Checkout', 'TiendaModel' );
		$view   = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->setLayout( $layout );

		switch (strtolower($layout))
		{
			case "shipping_calculate":
				break;
			case "shipping_no":
				break;
			case "shipping_yes":
			default:
				$rates = $this->getShippingRates();
				$view->assign( 'rates', $rates );
				break;
		}

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Gets the applicable rates
	 *
	 * @return array
	 */
	function getShippingRates()
	{
		// get all the enabled shipping plugins
		Tienda::load( 'TiendaHelperPlugin', 'helpers.plugin' );
		$plugins = TiendaHelperPlugin::getPluginsWithEvent( 'onGetShippingPlugins' );

		$dispatcher =& JDispatcher::getInstance();

		$rates = array();
		if ($plugins)
		{
			foreach ($plugins as $plugin)
			{
				$results = $dispatcher->trigger( "onGetShippingRates", array( $plugin->element, $this->_order ) );

				foreach ($results as $result)
				{
					if(is_array($result))
					{
						foreach( $result as $r )
						{
							$rates[] = $r;
						}
					}
				}// endforeach results

			} // endforeach plugins
		} // endif plugins

		return $rates;
	}

	/**
	 * Updates shipping rates and captures output
	 * Returns via json_encode
	 *
	 * @return unknown_type
	 */
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
			$response['msg'] = $this->getShippingHtml('shipping_calculate');
			$response['msg'] .= $helper->generateMessage(JText::_("Error while validating the parameters"));
			echo ( json_encode( $response ) );
			return;
		}

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		$submitted_values = $helper->elementsToArray( $elements );

		// Use AJAX to show plugins that are available
		JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );
		$guest = JRequest::getVar( 'guest', '0');
		if ($guest == '1' && TiendaConfig::getInstance()->get('guest_checkout_enabled'))
		{
			$guest = true;
		}
		else
		{
			$guest = false;
		}

		$values = &$this->populateOrder($guest);

		$this->setAddresses( $submitted_values );
		$this->validateAddress( $submitted_values, $this->shipping_input_prefix, '0' );
		// fail if shipping address is invalid
		// if we're checking shipping and the sameasbilling is checked, then this is good
		if ($submitted_values['shippingrequired'])
		{
			$prefix = $this->shipping_input_prefix;
			if ($sameasbilling = (!empty($submitted_values['_checked']['sameasbilling'])))
			{
				$prefix = $this->billing_input_prefix;
			}

			if (!$this->validateAddress( $submitted_values, $prefix, @$submitted_values['shipping_address_id'] ))
			{
				$response['msg'] = $this->getShippingHtml('shipping_calculate');
				$response['msg'] .= $helper->generateMessage( JText::_( "SHIPPING ADDRESS ERROR" )." :: ".$this->getError() );
				$response['error'] = '1';
				echo ( json_encode( $response ) );
				return;
			}
		}

		$text = "";
		$user = JFactory::getUser();

		$rates = $this->getShippingRates();

		//Set display
		$view = $this->getView( 'checkout', 'html' );
		$view->setLayout('shipping_yes');
		$view->set( '_doTask', true);

		//Get and Set Model
		$model = $this->getModel('checkout');
		$view->setModel( $model, true );

		$view->set( 'hidemenu', false);
		$view->assign( 'rates', $rates );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		// set response array
		$response = array();
		$response['msg'] = $html;

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);

		return;
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
		$order =& $this->_order; // a TableOrders object (see constructor)

		// bind what you can from the post
		$order->bind( $values );

		// set the currency
		$order->currency_id = TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected

		// set the shipping method
		$order->shipping = new JObject();
		$order->shipping->shipping_price      = $values['shipping_price'];
		$order->shipping->shipping_extra   = $values['shipping_extra'];
		$order->shipping->shipping_name        = $values['shipping_name'];
		$order->shipping->shipping_tax      = $values['shipping_tax'];

		// set the addresses
		$this->setAddresses( $values );

		// get the items and add them to the order
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$items = TiendaHelperCarts::getProductsInfo();
		foreach ($items as $item)
		{
			$order->addItem( $item );
		}

		// get the order totals
		$order->calculateTotals();

		// now get the summary
		$html = $this->getOrderSummary();

		$response = array();
		$response['msg'] = $html;
		$response['error'] = '';

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);

		return;
	}

	/**
	 * Prepare the review tmpl
	 *
	 * @return unknown_type
	 */
	function selectPayment()
	{
		$this->current_step = 1;

		// get the posted values
		$values = JRequest::get('post');

		// get the order object so we can populate it
		$order =& $this->_order; // a TableOrders object (see constructor)

		$user_id = JFactory::getUser()->id;
		// Guest Checkout
		$guest = false;
		if ($user_id == 0 && TiendaConfig::getInstance()->get('guest_checkout_enabled', '1'))
		{
			$email_address = $values['email_address'];
			$guest = true;
			$user_id = 9999;
		}

		$order->bind( $values );
		$order->user_id = $user_id;
		//$order->shipping_method_id = $values['shipping_method_id'];

		// set the shipping method
		$order->shipping = new JObject();
		$order->shipping->shipping_price      = @$values['shipping_price'];
		$order->shipping->shipping_extra      = @$values['shipping_extra'];
		$order->shipping->shipping_name       = @$values['shipping_name'];
		$order->shipping->shipping_tax        = @$values['shipping_tax'];

		$this->setAddresses( $values );

		// get the items and add them to the order
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$items = TiendaHelperCarts::getProductsInfo();
		foreach ($items as $item)
		{
			$order->addItem( $item );
		}

		// get the order totals
		$order->calculateTotals();

		// now that the order object is set, get the orderSummary html
		$html = $this->getOrderSummary();

		$values = JRequest::get('post');

		//Set key information from post
		$billing_address_id     = (!empty($values['billing_address_id'])) ? $values['billing_address_id'] : 0;
		$shipping_address_id    = (!empty($values['shipping_address_id'])) ? $values['shipping_address_id'] : 0;
		$same_as_billing        = (!empty($values['sameasbilling'])) ? true : false;
		//$shipping_method_id     = $values['shipping_method_id'];
		$customerNote           = @$values['customer_note'];

		$progress = $this->getProgress();

		//Set display
		$view = $this->getView( 'checkout', 'html' );
		$view->setLayout('selectpayment');
		$view->set( '_doTask', true);

		//Get and Set Model
		$model = $this->getModel('checkout');
		$view->setModel( $model, true );

		// Checking whether shipping is required
		$showShipping = false;
		$cartsModel = $this->getModel('carts');
		if ($isShippingEnabled = $cartsModel->getShippingIsEnabled())
		{
			$showShipping = true;
		}
		$view->assign( 'showShipping', $showShipping );

		//Get Addresses
		//$shippingAddressArray = $this->retrieveAddressIntoArray($shipping_address_id);
		//$billingAddressArray = $this->retrieveAddressIntoArray($billing_address_id);
		$billingAddressArray = $this->_billingAddressArray;
		$shippingAddressArray = $this->_shippingAddressArray;

		// save the addresses
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$billingAddress = JTable::getInstance('Addresses', 'TiendaTable');
		$shippingAddress = JTable::getInstance('Addresses', 'TiendaTable');

		// set the order billing address
		$billingAddress->load( $billing_address_id );
		$billingAddress->bind( $billingAddressArray );
		$billingAddress->user_id = $user_id;
		$billingAddress->save();

		$values['billing_address_id'] = $billingAddress->address_id;
		if ($same_as_billing)
		{
			$shipping_address_id = $values['billing_address_id'];
		}

		// set the order shipping address
		if (!$same_as_billing)
		{
			$shippingAddress->load( $shipping_address_id );
			$shippingAddress->bind( $shippingAddressArray );
			$shippingAddress->user_id = $user_id;
			$shippingAddress->save();
			$shipping_address_id = $shippingAddress->address_id;
		}
		$values['shipping_address_id'] = $shipping_address_id;

		$shippingMethodName = @$values['shipping_name'];

		//Assign Addresses and Shippping Method to view
		$view->assign('shipping_method_name',$shippingMethodName);
		//$view->assign('shipping_method_id',$shipping_method_id);
		$view->assign('shipping_info',$shippingAddressArray);
		$view->assign('billing_info',$billingAddressArray);
		$view->assign('customer_note', $customerNote);
		$view->assign('values', $values);
		$view->assign('progress', $progress);
		$view->assign('guest', $guest);

		$view->set( 'hidemenu', false);
		$view->assign( 'order', $order );
		$view->assign( 'orderSummary', $html );

		$showPayment = true;
		if ((float)$order->order_total == (float)'0.00')
		{
			$showPayment = false;
		}
		$view->assign( 'showPayment', $showPayment );

		// get all the enabled payment plugins
		Tienda::load( 'TiendaHelperPlugin', 'helpers.plugin' );
		$plugins = TiendaHelperPlugin::getPluginsWithEvent( 'onGetPaymentPlugins' );
		$view->assign('plugins', $plugins);

		$dispatcher =& JDispatcher::getInstance();

		ob_start();
		$dispatcher->trigger( 'onBeforeDisplaySelectPayment', array( $order ) );
		$view->assign( 'onBeforeDisplaySelectPayment', ob_get_contents() );
		ob_end_clean();

		ob_start();
		$dispatcher->trigger( 'onAfterDisplaySelectPayment', array( $order ) );
		$view->assign( 'onAfterDisplaySelectPayment', ob_get_contents() );
		ob_end_clean();


		$view->display();
		$this->footer();
	}

	/**
	 * Fires selected tienda payment plugin and captures output
	 * Returns via json_encode
	 *
	 * @return unknown_type
	 */
	function getPaymentForm()
	{
		// Use AJAX to show plugins that are available
		JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );
		$values = JRequest::get('post');
		$html = '';
		$text = "";
		$user = JFactory::getUser();
		$element = JRequest::getVar( 'payment_element' );
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

	/**
	 * This method occurs before payment is attempted
	 * and fires the onPrePayment plugin event
	 *
	 * @return unknown_type
	 */
	function preparePayment()
	{

		$this->current_step = 2;
		// verify that form was submitted by checking token
		JRequest::checkToken() or jexit( 'TiendaControllerCheckout::preparePayment - Invalid Token' );
			
		// 1. save the order to the table with a 'pre-payment' status

		// Get post values
		$values = JRequest::get('post');
		$user = JFactory::getUser();

		// Guest Checkout: Silent Registration!
		if (TiendaConfig::getInstance()->get('guest_checkout_enabled', '1') && $values['guest'] == '1')
		{
			Tienda::load( 'TiendaHelperUser', 'helpers.user' );
			$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');

			if ($userHelper->emailExists($values['email_address']))
			{
				// TODO user already exists

			}
			else
			{
				// create a guest email address to be stored in the __users table
				//get the domain from the uri
				$uri = JURI::getInstance();
				$domain = $uri->gethost();
				$lastUserId = $userHelper->getLastUserId();
				$guestId = $lastUserId + 1;
				// format: guest_[id]@domain.com
				$guest_email = "guest_".$guestId."@".$domain;
					
				// send the guest user credentials to the user's real email address
				$details = array(
					'email' => $values['email_address'],
					'name' => "guest_".$guestId,
					'username' => "guest_".$guestId			
				);
					
				// use a random password, and send password2 for the email
				jimport('joomla.user.helper');
				$details['password']    = JUserHelper::genRandomPassword();
				$details['password2']   = $details['password'];

				// create the new user
				$msg = $this->getError();
				$user = $userHelper->createNewUser($details, true);

				if (empty($user->id))
				{
					// TODO what to do if creating new user failed?
				}

				// but don't save the user's real email in the __users db table
				$userEmailUpdate = $userHelper->updateUserEmail($user->id, $guest_email);

				// save the real user's info in the userinfo table
				JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
				$userinfo = JTable::getInstance('UserInfo', 'TiendaTable');
				$userinfo->load( array('user_id'=>$user->id) );
				$userinfo->user_id = $user->id;
				$userinfo->email = $values['email_address'];
				$userinfo->save();

				// login the user
				$userHelper->login(
				array('username' => $user->username, 'password' => $details['password'])
				);
			}
		}

		// Save the order with a pending status
		if (!$this->saveOrder($values))
		{
			// Output error message and halt
			JError::raiseNotice( 'Error Saving Order', $this->getError() );
			return false;
		}
			
		// Get Order Object
		$order =& $this->_order;

		// Update the addresses' user id!
		$shippingAddress = $order->getShippingAddress();
		$billingAddress = $order->getBillingAddress();

		$shippingAddress->user_id = $user->id;
		$billingAddress->user_id = $user->id;

		// Checking whether shipping is required
		$showShipping = false;
		$cartsModel = $this->getModel('carts');
		if ($isShippingEnabled = $cartsModel->getShippingIsEnabled())
		{
			$showShipping = true;
		}

		if ($showShipping && !$shippingAddress->save())
		{
			// Output error message and halt
			JError::raiseNotice( 'Error Updating the Shipping Address', $shippingAddress->getError() );
			return false;
		}

		if (!$billingAddress->save())
		{
			// Output error message and halt
			JError::raiseNotice( 'Error Updating the Billing Address', $billingAddress->getError() );
			return false;
		}

		$orderpayment_type = $values['payment_plugin'];
		$transaction_status = JText::_( "Incomplete" );
		// in the case of orders with a value of 0.00, use custom values
		if ( (float) $order->order_total == (float)'0.00' )
		{
			$orderpayment_type = 'free';
			$transaction_status = JText::_( "Complete" );
		}

		// Save an orderpayment with an Incomplete status
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
		$orderpayment->order_id = $order->order_id;
		$orderpayment->orderpayment_type = $orderpayment_type; // this is the payment plugin selected
		$orderpayment->transaction_status = $transaction_status; // payment plugin updates this field onPostPayment
		$orderpayment->orderpayment_amount = $order->order_total; // this is the expected payment amount.  payment plugin should verify actual payment amount against expected payment amount
		if (!$orderpayment->save())
		{
			// Output error message and halt
			JError::raiseNotice( 'Error Saving Pending Payment Record', $orderpayment->getError() );
			return false;
		}

		// send the order_id and orderpayment_id to the payment plugin so it knows which DB record to update upon successful payment
		$values["order_id"]             = $order->order_id;
		$values["orderinfo"]            = $order->orderinfo;
		$values["orderpayment_id"]      = $orderpayment->orderpayment_id;
		$values["orderpayment_amount"]  = $orderpayment->orderpayment_amount;

		// IMPORTANT: Store the order_id in the user's session for the postPayment "View Invoice" link
		$mainframe =& JFactory::getApplication();
		$mainframe->setUserState( 'tienda.order_id', $order->order_id );
		$mainframe->setUserState( 'tienda.orderpayment_id', $orderpayment->orderpayment_id );
			
		// 2. perform payment process
		// this is the onPrePayment plugin event
		// in the case of offsite payment plugins (like Paypal), they will display an order summary (perhaps with ****** for CC number)
		// with a button that submits a form to the external site (button: "confirm order" or Paypal, MB, Alertpay, whatever)
		// the return url will point to the method that fires the onPostPayment plugin event:
		// target: index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=xxxxxx
		// in the case of onsite payment plugins, they will display an order summary (perhaps with ****** for CC number)
		// with a button that submits a form to the method that fires the onPostPayment plugin event ("confirm order")
		// target: index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=xxxxxx
		// onPostPayment, payment plugin to update order status with payment status

		// in the case of orders with a value of 0.00, we redirect to the confirmPayment page
		if ( (float) $order->order_total == (float)'0.00' )
		{
			JFactory::getApplication()->redirect( 'index.php?option=com_tienda&view=checkout&task=confirmPayment' );
			return;
		}

		$dispatcher    =& JDispatcher::getInstance();
		$results = $dispatcher->trigger( "onPrePayment", array( $values['payment_plugin'], $values ) );

		// Display whatever comes back from Payment Plugin for the onPrePayment
		$html = "";
		for ($i=0; $i<count($results); $i++)
		{
			$html .= $results[$i];
		}

		// get the order summary
		$summary = $this->getOrderSummary();

		// Get Addresses
		$shipping_address = $order->getShippingAddress();
		$billing_address = $order->getBillingAddress();

		$shippingAddressArray = $showShipping ? $this->_shippingAddressArray : array();
		$billingAddressArray = $this->_billingAddressArray;
			
		$shippingMethodName = $values['shipping_name'];

		$progress = $this->getProgress();

		// Set display
		$view = $this->getView( 'checkout', 'html' );
		$view->setLayout('prepayment');
		$view->set( '_doTask', true);
		$view->assign('order', $order);
		$view->assign('plugin_html', $html);
		$view->assign('progress', $progress);
		$view->assign('orderSummary', $summary);
		$view->assign('shipping_info', $shippingAddressArray);
		$view->assign('billing_info', $billingAddressArray);
		$view->assign('shipping_method_name',$shippingMethodName);
		$view->assign( 'showShipping', $showShipping );

		// Get and Set Model
		$model = $this->getModel('checkout');
		$view->setModel( $model, true );

		ob_start();
		$dispatcher->trigger( 'onBeforeDisplayPrePayment', array( $order ) );
		$view->assign( 'onBeforeDisplayPrePayment', ob_get_contents() );
		ob_end_clean();

		ob_start();
		$dispatcher->trigger( 'onAfterDisplayPrePayment', array( $order ) );
		$view->assign( 'onAfterDisplayPrePayment', ob_get_contents() );
		ob_end_clean();

		$view->display();

		return;
	}

	/**
	 * This method occurs after payment is attempted,
	 * and fires the onPostPayment plugin event
	 *
	 * @return unknown_type
	 */
	function confirmPayment()
	{
		$this->current_step = 3;
		$orderpayment_type = JRequest::getVar('orderpayment_type');

		// Get post values
		$values = JRequest::get('post');

		// get the order_id from the session set by the prePayment
		$mainframe =& JFactory::getApplication();
		$order_id = (int) $mainframe->getUserState( 'tienda.order_id' );
		$order_link = 'index.php?option=com_tienda&view=orders&task=view&id='.$order_id;

		$dispatcher =& JDispatcher::getInstance();
		$html = "";
		$order =& $this->_order;
		$order->load( array('order_id'=>$order_id) );
		if ( (!empty($order_id)) && (float) $order->order_total == (float)'0.00' )
		{
			$order->order_state_id = '17'; // PAYMENT RECEIVED
			$order->save();

			// send notice of new order
			Tienda::load( "TiendaHelperBase", 'helpers._base' );
			$helper = TiendaHelperBase::getInstance('Email');
			$order_model = Tienda::getClass("TiendaModelOrders", "models.orders");
			$order_model->setId( $order_id );
			$order_model_item = $order_model->getItem();
			$helper->sendEmailNotices($order_model_item, 'new_order');

			Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
			TiendaHelperOrder::setOrderPaymentReceived( $order_id );
		}
		else
		{
			// get the payment results from the payment plugin
			$results = $dispatcher->trigger( "onPostPayment", array( $orderpayment_type, $values ) );

			// Display whatever comes back from Payment Plugin for the onPrePayment
			for ($i=0; $i<count($results); $i++)
			{
				$html .= $results[$i];
			}
		}

		// $order_id would be empty on posts back from Paypal, for example
		if (!empty($order_id))
		{
			$progress = $this->getProgress();

			// Set display
			$view = $this->getView( 'checkout', 'html' );
			$view->setLayout('postpayment');
			$view->set( '_doTask', true);
			$view->assign('order_link', $order_link );
			$view->assign('progress', $progress );
			$view->assign('plugin_html', $html);

			// Get and Set Model
			$model = $this->getModel('checkout');
			$view->setModel( $model, true );

			ob_start();
			$dispatcher->trigger( 'onBeforeDisplayPostPayment', array( $order_id ) );
			$view->assign( 'onBeforeDisplayPostPayment', ob_get_contents() );
			ob_end_clean();

			ob_start();
			$dispatcher->trigger( 'onAfterDisplayPostPayment', array( $order_id ) );
			$view->assign( 'onAfterDisplayPostPayment', ob_get_contents() );
			ob_end_clean();

			$view->display();
		}
		return;
	}

	/**
	 * Saves the order to the database
	 *
	 * @param $values
	 * @return unknown_type
	 */
	function saveOrder($values)
	{
		$error = false;
		$order =& $this->_order; // a TableOrders object (see constructor)
		$order->bind( $values );
		$order->user_id = JFactory::getUser()->id;

		$order->ip_address = $_SERVER['REMOTE_ADDR'];
		$this->setAddresses( $values );

		// set the shipping method
		$order->shipping = new JObject();
		$order->shipping->shipping_price      = $values['shipping_price'];
		$order->shipping->shipping_extra   = $values['shipping_extra'];
		$order->shipping->shipping_name        = $values['shipping_name'];
		$order->shipping->shipping_tax      = $values['shipping_tax'];

		// Store the text verion of the currency for order integrity
		Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
		$order->order_currency = TiendaHelperOrder::currencyToParameters($order->currency_id);

		//get the items and add them to the order
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$reviewitems = TiendaHelperCarts::getProductsInfo();

		foreach ($reviewitems as $reviewitem)
		{
			$order->addItem( $reviewitem );
		}
		$order->order_state_id = $this->initial_order_state;
		$order->calculateTotals();
		$order->getShippingTotal();
		$order->getInvoiceNumber();

		$model  = JModel::getInstance('Orders', 'TiendaModel');
		//TODO: Do Something with Payment Infomation
		if ( $order->save() )
		{
		 $model->setId( $order->order_id );

			// save the order items
			if (!$this->saveOrderItems())
			{
				// TODO What to do if saving order items fails?
				$error = true;
			}

			// save the order vendors
			if (!$this->saveOrderVendors())
			{
				// TODO What to do if saving order vendors fails?
				$error = true;
			}

			// save the order info
			if (!$this->saveOrderInfo())
			{
				// TODO What to do if saving order info fails?
				$error = true;
			}

			// save the order history
			if (!$this->saveOrderHistory())
			{
				// TODO What to do if saving order history fails?
				$error = true;
			}

			// save the order taxes
			if (!$this->saveOrderTaxes())
			{
				// TODO What to do if saving order taxes fails?
				$error = true;
			}

			// save the order shipping info
			if (!$this->saveOrderShippings())
			{
				// TODO What to do if saving order shippings fails?
				$error = true;
			}
		}

		if ($error)
		{
			return false;
		}
		return true;
	}

	/**
	 * Saves each individual item in the order to the DB
	 *
	 * @return unknown_type
	 */
	function saveOrderItems()
	{
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$order =& $this->_order;
		$items = $order->getItems();


		if (empty($items) || !is_array($items))
		{
			$this->setError( "saveOrderItems:: ".JText::_( "Items Array is Invalid" ) );
			return false;
		}
			
		$error = false;
		$errorMsg = "";
		foreach ($items as $item)
		{
			$item->order_id = $order->order_id;

			if (!$item->save())
			{
				// track error
				$error = true;
				$errorMsg .= $item->getError();
			}
			else
			{
				// does the orderitem create a subscription?
				if (!empty($item->orderitem_subscription))
				{
					$date = JFactory::getDate();
					// these are only for one-time payments that create subscriptions
					// recurring payment subscriptions are handled differently - by the payment plugins
					$subscription = JTable::getInstance('Subscriptions', 'TiendaTable');
					$subscription->user_id = $order->user_id;
					$subscription->order_id = $order->order_id;
					$subscription->product_id = $item->product_id;
					$subscription->orderitem_id = $item->orderitem_id;
					$subscription->transaction_id = ''; // in recurring payments, this is the subscr_id
					$subscription->created_datetime = $date->toMySQL();
					$subscription->subscription_enabled = '0'; // disabled at first, enabled after payment clears
					switch($item->subscription_period_unit)
					{
						case "Y":
							$period_unit = "YEAR";
							break;
						case "M":
							$period_unit = "MONTH";
							break;
						case "W":
							$period_unit = "WEEK";
							break;
						case "D":
						default:
							$period_unit = "DAY";
							break;
					}

					if (!empty($item->subscription_lifetime))
					{
						// set expiration 100 years in future
						$period_unit = "YEAR";
						$item->subscription_period_interval = '100';
						$subscription->lifetime_enabled = '1';
					}
					$database = JFactory::getDBO();
					$query = " SELECT DATE_ADD('{$subscription->created_datetime}', INTERVAL {$item->subscription_period_interval} $period_unit ) ";
					$database->setQuery( $query );
					$subscription->expires_datetime = $database->loadResult();

					if (!$subscription->save())
					{
						$error = true;
						$errorMsg .= $subscription->getError();
					}

					// add a sub history entry, email the user?
					$subscriptionhistory = JTable::getInstance('SubscriptionHistory', 'TiendaTable');
					$subscriptionhistory->subscription_id = $subscription->subscription_id;
					$subscriptionhistory->subscriptionhistory_type = 'creation';
					$subscriptionhistory->created_datetime = $date->toMySQL();
					$subscriptionhistory->notify_customer = '0'; // notify customer of new trial subscription?
					$subscriptionhistory->comments = JText::_( 'NEW SUBSCRIPTION CREATED' );
					$subscriptionhistory->save();
				}
					
				// Save the attributes also
				if (!empty($item->orderitem_attributes))
				{
					$attributes = explode(',', $item->orderitem_attributes);
					foreach (@$attributes as $attribute)
					{
						unset($productattribute);
						unset($orderitemattribute);
						$productattribute = JTable::getInstance('ProductAttributeOptions', 'TiendaTable');
						$productattribute->load( $attribute );
						$orderitemattribute = JTable::getInstance('OrderItemAttributes', 'TiendaTable');
						$orderitemattribute->orderitem_id = $item->orderitem_id;
						$orderitemattribute->productattributeoption_id = $productattribute->productattributeoption_id;
						$orderitemattribute->orderitemattribute_name = $productattribute->productattributeoption_name;
						$orderitemattribute->orderitemattribute_price = $productattribute->productattributeoption_price;
						$orderitemattribute->orderitemattribute_prefix = $productattribute->productattributeoption_prefix;
						if (!$orderitemattribute->save())
						{
							// track error
							$error = true;
							$errorMsg .= $orderitemattribute->getError();
						}
					}
				}
			}
		}

		if ($error)
		{
			$this->setError( $errorMsg );
			return false;
		}
		return true;
	}

	/**
	 * Saves the order info to the DB
	 * @return unknown_type
	 */
	function saveOrderInfo()
	{
		$order =& $this->_order;
			
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$row = JTable::getInstance('OrderInfo', 'TiendaTable');
		$row->order_id = $order->order_id;
		$row->user_email = JFactory::getUser()->get('email');
		$row->bind( $this->_orderinfoBillingAddressArray );
		$row->bind( $this->_orderinfoShippingAddressArray );

		// Get Addresses
		$shipping_address = $order->getShippingAddress();
		$billing_address = $order->getBillingAddress();

		// set zones and countries
		$row->billing_zone_id       = $billing_address->zone_id;
		$row->billing_country_id    = $billing_address->country_id;
		$row->shipping_zone_id      = $shipping_address->zone_id;
		$row->shipping_country_id   = $shipping_address->country_id;
			
		if (!$row->save())
		{
			$this->setError( $row->getError() );
			return false;
		}

		$order->orderinfo = $row;
		return true;
	}

	/**
	 * Adds an order history record to the DB for this order
	 * @return unknown_type
	 */
	function saveOrderHistory()
	{
		$order =& $this->_order;
			
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$row = JTable::getInstance('OrderHistory', 'TiendaTable');
		$row->order_id = $order->order_id;
		$row->order_state_id = $order->order_state_id;

		$row->notify_customer = '0'; // don't notify the customer on prepayment
		$row->comments = JRequest::getVar('order_history_comments', '', 'post');

		if (!$row->save())
		{
			$this->setError( $row->getError() );
			return false;
		}
		return true;
	}

	/**
	 * Saves each vendor related to this order to the DB
	 * @return unknown_type
	 */
	function saveOrderVendors()
	{
		$order =& $this->_order;
		$items = $order->getVendors();

		if (empty($items) || !is_array($items))
		{
			// No vendors other than store owner, so just skip this
			//$this->setError( "saveOrderVendors:: ".JText::_( "Vendors Array is Invalid" ) );
			//return false;
			return true;
		}

		$error = false;
		$errorMsg = "";
		foreach ($items as $item)
		{
			if (empty($item->vendor_id))
			{
				continue;
			}
			$item->order_id = $order->order_id;
			if (!$item->save())
			{
				// track error
				$error = true;
				$errorMsg .= $item->getError();
			}
		}

		if ($error)
		{
			$this->setError( $errorMsg );
			return false;
		}
		return true;
	}

	/**
	 * Adds an order tax class/rate record to the DB for this order
	 * for each relevant tax class & rate
	 *
	 * @return unknown_type
	 */
	function saveOrderTaxes()
	{
		$order =& $this->_order;
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );

		$taxclasses = $order->getTaxClasses();
		foreach ($taxclasses as $taxclass)
		{
			unset($row);
			$row = JTable::getInstance('OrderTaxClasses', 'TiendaTable');
			$row->order_id = $order->order_id;
			$row->tax_class_id = $taxclass->tax_class_id;
			$row->ordertaxclass_amount = $order->getTaxClassAmount( $taxclass->tax_class_id );
			$row->ordertaxclass_description = $taxclass->tax_rate_description;
			$row->save();
		}

		$taxrates = $order->getTaxRates();
		foreach ($taxrates as $taxrate)
		{
			unset($row);
			$row = JTable::getInstance('OrderTaxRates', 'TiendaTable');
			$row->order_id = $order->order_id;
			$row->tax_rate_id = $taxrate->tax_rate_id;
			$row->ordertaxrate_rate = $taxrate->tax_rate;
			$row->ordertaxrate_amount = $order->getTaxRateAmount( $taxrate->tax_rate_id );
			$row->ordertaxrate_description = $taxrate->tax_rate_description;
			$row->save();
		}

		// TODO Better error tracking necessary here
		return true;
	}

	/**
	 * Saves the order shipping info to the DB
	 * @return unknown_type
	 */
	function saveOrderShippings()
	{
		$order =& $this->_order;

		$shipping_plugin = JRequest::getVar('shipping_plugin', '');
		$shipping_name = JRequest::getVar('shipping_name', '');
		$shipping_price = JRequest::getVar('shipping_price', '');
		$shipping_tax = JRequest::getVar('shipping_tax', '');
		$shipping_extra = JRequest::getVar('shipping_extra', '');
			
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$row = JTable::getInstance('OrderShippings', 'TiendaTable');
		$row->order_id = $order->order_id;
		$row->ordershipping_type = $shipping_plugin;
		$row->ordershipping_price = $shipping_price;
		$row->ordershipping_name = $shipping_name;
		$row->ordershipping_tax = $shipping_tax;
		$row->ordershipping_extra = $shipping_extra;
			
		if (!$row->save())
		{
			$this->setError( $row->getError() );
			return false;
		}

		// Let the plugin store the information about the shipping
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( "onPostSaveShipping", array( $shipping_plugin, $row ) );

		return true;
	}

	/**
	 *
	 * @param unknown_type $oldArray
	 * @param unknown_type $old_prefix
	 * @param unknown_type $new_prefix
	 * @param unknown_type $append
	 * @return unknown_type
	 */
	function filterArrayUsingPrefix( $oldArray, $old_prefix, $new_prefix, $append )
	{
		// create array with input form keys and values
		$address_input = array();

		foreach ($oldArray as $key => $value)
		{
			if (($append) || (strpos($key, $old_prefix) !== false))
			{
				$new_key = '';
				if ($append){$new_key = $new_prefix.$key;}
				else{
					$new_key = str_replace($old_prefix, $new_prefix, $key);
				}
				if (strlen($new_key)>0){
					$address_input[$new_key] = $value;
				}
			}
		}
		return $address_input;
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

	/*
	 * Regiter the new user with the Form
	 */
	function registerNewUser ($values){

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		
		//  Register an User
		Tienda::load( 'TiendaHelperUser', 'helpers.user' );
		$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');

		if ($userHelper->emailExists($values['email_address']))
		{
			// TODO user already exists

		}
		else
		{
			Tienda::load( 'TiendaHelperUser', 'helpers.user' );
			$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');

			$details = array(
					'email' => $values['email_address'],
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

			// save the real user's info in the userinfo table also
			JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
			$userinfo = JTable::getInstance('UserInfo', 'TiendaTable');
			$userinfo->load( array('user_id'=>$user->id) );
			$userinfo->user_id = $user->id;
			$userinfo->first_name = $values['billing_input_first_name'];
			$userinfo->last_name = $values['billing_input_last_name'];
			$userinfo->company = $values['billing_input_company'];
			$userinfo->middle_name = $values['billing_input_middle_name'];
			$userinfo->phone_1 = $values['billing_input_phone_1'];
			$userinfo->email = $values['email_address'];
			$userinfo->save();

			// login the user
			$userHelper->login(
			array('username' => $user->username, 'password' => $details['password'])
			);
		
		}
	}


}
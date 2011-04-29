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
	var $onepage_checkout	   = false;				

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
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $cart_helper = &TiendaHelperBase::getInstance( 'Carts' );
		$items = $cart_helper->getProductsInfo();

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
	
		// set userid
		if( !$this->_order->user_id && !(JFactory::getUser()->guest) ) $this->_order->user_id = JFactory::getUser()->id;
	
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
            
       if(TiendaConfig::getInstance()->get('one_page_checkout', '0'))
       {
       		$this->onepage_checkout = true;
       }     
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
	
		
		//check if we have one page checkout
		if($this->onepage_checkout)
		{			
			// Display the onepage checkout view
			JRequest::setVar('layout', 'onepage');			
			$view = $this->getView( 'checkout', 'html' );			
		
			$order =& $this->_order;
			$order = $this->populateOrder();
				
			//get order summarry
			$html = $this->getOrderSummary();
			$view->assign( 'orderSummary', $html );	
			$view->assign( 'order', $order );
			
			$view->assign( 'user', $user );	
					
			if(!$user->id)
			{
				$view->assign( 'checkoutMethod', $this->getCheckoutMethod() );
			}
					
			//get addresses
			JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
			$model = JModel::getInstance( 'addresses', 'TiendaModel' );
			$model->setState("filter_userid", JFactory::getUser()->id);
			$model->setState("filter_deleted", 0);
			$addresses = $model->getList();			
		
			// Checking whether shipping is required
			$showShipping = false;		

			$cartsModel = $this->getModel('carts');			
			if ($isShippingEnabled = $cartsModel->getShippingIsEnabled())
			{
				$showShipping = true;
			}
		
			$billingAddress = $order->getBillingAddress();
	
			if(!$billingAddress)
			{
				$billing_address_form = $this->getAddressForm( $this->billing_input_prefix );
				$view->assign( 'billing_address_form', $billing_address_form );
			}
			
			$view->assign( 'showShipping', $showShipping );		
			$view->assign( 'billing_address', $billingAddress);
			
			if($showShipping)
			{
				$shippingAddress = $order->getShippingAddress();
			
				$shipping_address_form = $this->getAddressForm( $this->shipping_input_prefix, false ,true );				
				
				$view->assign( 'shipping_address', $shippingAddress );			
				$view->assign( 'shipping_address_form', $shipping_address_form );	
			}	
			
			Tienda::load( 'TiendaHelperPlugin', 'helpers.plugin' );
	        $dispatcher =& JDispatcher::getInstance();
	        
	        if($showShipping)
	        {
	        	$rates = $this->getShippingRates();
		        $default_rate = array();
                if (count($rates) == 1)
                {
                    $default_rate = $rates[0];
                }
		        
	        	$shipping_layout = "shipping_yes";
				if (empty( $shippingAddress ))
				{
					$shipping_layout = "shipping_calculate";
				}
		        
		        $shipping_method_form = $this->getShippingHtml( $shipping_layout );
				$view->assign( 'showShipping', $showShipping );
				$view->assign( 'shipping_method_form', $shipping_method_form );
		        
		        $view->assign( 'rates', $rates );		               
	        }
	        	    
	        $view->assign( 'payment_options_html', $this->getPaymentOptionsHtml() );
	        $view->assign( 'order', $order );	        
	      
	       	// are there any enabled coupons?
			$coupons_present = false;
			$model = JModel::getInstance( 'Coupons', 'TiendaModel' );
			$model->setState('filter_enabled', '1');
			if ($coupons = $model->getList())
			{
			    $coupons_present = true;
			}
			$view->assign( 'coupons_present', $coupons_present );
			
		}
		else
		{
			$guest_var = JRequest::getInt( 'guest', '0' );
			$guest = false;
			if ($guest_var == '1')
			{
			    $guest = true;
			}

			$register_var = JRequest::getInt( 'register', '0' );
	        $form_register = '';
	        $register = false;
			if ($register_var == '1')
			{
	            $register = true;
	            $form_register = $this->getRegisterForm();    
			}
		
			// determine layout based on login status
			// Login / Register / Checkout as a guest
			if (empty($user->id) && !($guest || $register))
			{
				// Display a form for selecting either to register or to login
				JRequest::setVar('layout', 'form');
				Tienda::load( "TiendaHelperRoute", 'helpers.route' );
				$helper = new TiendaHelperRoute();
				$view = $this->getView( 'checkout', 'html' );
				$checkout_itemid = $helper->findItemid( array('view'=>'checkout') );
				if (empty($checkout_itemid))
				{
				    $checkout_itemid = JRequest::getInt('Itemid');
				}
				$view->assign('checkout_itemid', $checkout_itemid );
				parent::display();
				return;
			}
		
			if (($guest && TiendaConfig::getInstance()->get('guest_checkout_enabled')) || $register)
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
				$view->assign( 'register', $register );
				$view->assign( 'form_register', $form_register );
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

		}
	
		parent::display();
		return;
	}
	
	/**
	 * Method to get the #form view	 
	 */
	function getCheckoutMethod()
	{
		// Display a form for selecting either to register or to login
		
		Tienda::load( "TiendaHelperRoute", 'helpers.route' );
		$helper = new TiendaHelperRoute();	
		$checkout_itemid = $helper->findItemid( array('view'=>'checkout') );
		if (empty($checkout_itemid))
		{
			   $checkout_itemid = JRequest::getInt('Itemid');
		}
				
		$view = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->assign('onepage', true );		
		$view->assign('checkout_itemid', $checkout_itemid );	
		$view->setLayout( 'form' );

		// Get and Set Model
		$model = $this->getModel('checkout');
		$view->setModel( $model, true );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		$ajax = JRequest::getInt('ajax', '0');
	 	if ($ajax)
        {
	        // set response array
			$response = array();	
			$response['msg'] = $html;
			$response['label'] = JText::_('Checkout Method');
			// encode and echo (need to echo to send back to browser)
			echo json_encode($response);
			return;
    	}
		
		return $html;
	}
	
	function getCustomerInfo()
	{		
		Tienda::load( 'TiendaUrl', 'library.url' );
	
		$view = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->assign('user', JFactory::getUser() );
		$view->setLayout( 'customer_info' );

		// Get and Set Model
		$model = $this->getModel('checkout');
		$view->setModel( $model, true );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		
	     // set response array
		$response = array();	
		$response['msg'] = $html;
		
		echo json_encode($response);
		return;
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
            Tienda::load( "TiendaHelperBase", 'helpers._base' );
            $user_helper = &TiendaHelperBase::getInstance( 'User' );
			
			$billingAddress = $user_helper->getPrimaryAddress( JFactory::getUser()->id, 'billing' );
			$shippingAddress = $user_helper->getPrimaryAddress( JFactory::getUser()->id, 'shipping' );			
			
			$order->setAddress( $billingAddress, 'billing' );
			$order->setAddress( $shippingAddress, 'shipping' );			
		}

		// get the items and add them to the order
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $cart_helper = &TiendaHelperBase::getInstance( 'Carts' );
		$items = $cart_helper->getProductsInfo();

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
		$cartsModel = $this->getModel('carts');
		if ($isShippingEnabled = $cartsModel->getShippingIsEnabled())
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
        
		$view->setLayout( 'cart' );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
	
	/**
	 * Prepares data for and returns the html of the total amount
	 * This assumes that $this->_order has already had its properties set
	 *
	 * @return unknown_type
	 */
	function getTotalAmountDue()
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
		$orderitems = $order->order_total;
		$view->assign( 'orderitems', $orderitems );
        
		$view->setLayout( 'total' );

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
			case "selectshipping":
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
						$response['msg'] .= $helper->generateMessage(JText::_("All Fields of registration sections are Mandatory"));
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
				
				// Check if there are errors in the Shipping area. If yes, return without going on
				if( !$this->validateSelectShipping( $submitted_values ) )
				{
					return;
				}
				break;
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
				return false;
			}
		}				

		$order =& $this->_order;
        // get the items and add them to the order
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $items = TiendaHelperCarts::getProductsInfo();
        foreach ($items as $item)
        {
            $order->addItem( $item );
        }
        $order->calculateTotals();		
        if ( (float) $order->order_total == (float) '0.00' )
        {
            $response['error'] = '0';
            echo ( json_encode( $response ) );
            return false;
        }

		// fail if billing address is invalid
		if (!$this->validateAddress( $submitted_values, $this->billing_input_prefix , @$submitted_values['billing_address_id'] ))
		{
			$response['msg'] = $helper->generateMessage( JText::_( "BILLING ADDRESS ERROR" )." :: ".$this->getError() );
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return false;
		}

		// fail if shipping address is invalid
		if($submitted_values['shippingrequired'])
		{
			$sameasbilling = (!empty($submitted_values['_checked']['sameasbilling']));
			if ( !$this->validateAddress( $submitted_values, $this->shipping_input_prefix, @$submitted_values['shipping_address_id'] ))
			{
				$response['msg'] = $helper->generateMessage( JText::_( "SHIPPING ADDRESS ERROR" ).$this->shipping_input_prefix." :: ".$this->getError() );
				$response['error'] = '1';
				echo ( json_encode( $response ) );
				return false;
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
				echo ( json_encode( $response ) );
				return false;
			}
			else
			{
				// if here, all is OK
				$response['error'] = '0';
			}
		}
		
		//we will not echo the response if its onpagecheckout
		if($this->onepage_checkout)
		{
			return true;
		}

		echo ( json_encode( $response ) );
		
		// Return to Parent function the result!
		if($response['error'] == '1')
		{
			return false;
		}
		
		return true;
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
		}

		//we will not echo the response if its onpagecheckout
		if($this->onepage_checkout)
		{
			return true;
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

		// IS Guest Checkout or register??
		$user_id = JFactory::getUser()->id;
		$register = !empty($values['register']);
		if((TiendaConfig::getInstance()->get('guest_checkout_enabled', '1') && $user_id == 0) || $register)
			$addressArray['user_id'] = 9999; // Fake id for the checkout process
			
		$table->bind( $addressArray );
		
		// Add type of the array
		switch($prefix)
		{
			case 'shipping_input_':
				$address_type = '2';
				break;
			default:
			case 'billing_input_':
				$address_type = '1';
				break;
		}

		$table->addresstype_id = $address_type;
		
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
		
		if (empty($country_id))
		{
		    $html = JText::_( "Select a Country" );
		}
    		else
		{
		    $html = TiendaSelect::zone( '', $prefix.'zone_id', $country_id );
		}
			
		$response = array();
		$response['msg'] = $html . " *";
		$response['error'] = '';

		// encode and echo (need to echo to send back to browser)
		echo ( json_encode($response) );

		return;
	}

	/**
	 *
	 * @param $values
	 * @para boolean - save the addresses
	 * @return unknown_type
	 */
	function setAddresses( $values, $saved = false )
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
		if($saved) $billingAddress->save();
		
		$order->setAddress( $billingAddress);

		// set the order shipping address
		$shippingAddress->bind( $shippingAddressArray );
		$shippingAddress->user_id = $user_id;
		if($saved) $shippingAddress->save();
		
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

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$countries_model = JModel::getInstance( 'Countries', 'TiendaModel' );
        $default_country = $countries_model->getDefault();
        $default_country_id = $default_country->country_id; 
		
        Tienda::load( 'TiendaSelect', 'library.select' );
        $zones = TiendaSelect::zone( '', $prefix.'zone_id', $default_country_id );

        $view->assign( 'default_country_id', $default_country_id );
        $view->assign( 'zones', $zones );
        
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
		        $default_rate = array();
                if (count($rates) == 1)
                {
                    $default_rate = $rates[0];
                }
				$view->assign( 'rates', $rates );
				$view->assign( 'default_rate', $default_rate );
				break;
		}

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
	
    /**
     * Gets the Register Form
     *
     * @param string $shipping_method_id
     * @param array $values
     * @return unknown_type
     */
    function getRegisterForm( $layout='form_register', $values= array() )
    {
        $html = '';
        $model = $this->getModel( 'Checkout', 'TiendaModel' );
        $view   = $this->getView( 'checkout', 'html' );
        $view->set( '_controller', 'checkout' );
        $view->set( '_view', 'checkout' );
        $view->set( '_doTask', true);
        $view->set( 'hidemenu', true);
        $view->assign( 'values', $values );
        $view->setModel( $model, true );
        $view->setLayout( $layout );
        ob_start();
        $view->display();
        $html = ob_get_contents();
        ob_end_clean();
        
        if (TiendaConfig::getInstance()->get('one_page_checkout') && empty($values))
        {
	        // set response array
			$response = array();
			$response['msg'] = '<form action="index.php?option=com_tienda&view=checkout" method="post" id="tienda_registration_form" name="adminForm" enctype="multipart/form-data">';
			$response['msg'] .= "<div class='tienda_registration'>".$html."</div>";
			$response['msg'] .= "</form>";   
			$response['label'] = JText::_('Register'); 
     
			// encode and echo (need to echo to send back to browser)
			echo json_encode($response);
			return;
    	}
    	
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
		//$plugins = TiendaHelperPlugin::getPluginsWithEvent( 'onGetShippingPlugins' );
		$model = JModel::getInstance('Shipping', 'TiendaModel');
		$model->setState('filter_enabled', '1');
        $plugins = $model->getList();
        
		$dispatcher =& JDispatcher::getInstance();

		$rates = array();
  
		if ($plugins)
		{
			foreach ($plugins as $plugin)
			{
				
                $shippingOptions = $dispatcher->trigger( "onGetShippingOptions", array( $plugin->element, $this->_order ) );
         
                if (in_array(true, $shippingOptions, true))
                {
                    $results = $dispatcher->trigger( "onGetShippingRates", array( $plugin->element, $this->_order ) );
	                foreach ($results as $result)
					{
						if(is_array($result))
						{
							foreach( $result as $r )
							{
								$extra = 0;
								// here is where a global handling rate would be added
								if ($global_handling = TiendaConfig::getInstance()->get( 'global_handling' ))
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
				$response['msg'] .= $helper->generateMessage( JText::_( "SHIPPING ADDRESS ERROR" )." :: ".$this->getError());
				$response['error'] = '1';
				echo ( json_encode( $response ) );
				return;
			}
		}

		$text = "";
		$user = JFactory::getUser();

		$rates = $this->getShippingRates();
		$default_rate = array();
		if (count($rates) == 1)
		{
		    $default_rate = $rates[0];
		}

		//Set display
		$view = $this->getView( 'checkout', 'html' );
		$view->setLayout('shipping_yes');
		$view->set( '_doTask', true);

		//Get and Set Model
		$model = $this->getModel('checkout');
		$view->setModel( $model, true );

		$view->set( 'hidemenu', false);
		$view->assign( 'rates', $rates );
        $view->assign( 'default_rate', $default_rate );
        
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
		$order->shipping->shipping_price      = @$values['shipping_price'];
		$order->shipping->shipping_extra      = @$values['shipping_extra'];
		$order->shipping->shipping_name       = @$values['shipping_name'];
		$order->shipping->shipping_tax        = @$values['shipping_tax'];

		// set the addresses
		$this->setAddresses( $values );

		// get the items and add them to the order
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $cart_helper = &TiendaHelperBase::getInstance( 'Carts' );
		$items = $cart_helper->getProductsInfo();
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
		$html = $this->getOrderSummary();

		$response = array();
		$response['msg'] = $html;	
		$response['error'] = '';

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);

		return;
	}

	/**
	 * Returning total amount value
	 *
	 * @return unknown_type
	 */
	function totalAmountDue()
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

		// get the items and add them to the order
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $cart_helper = &TiendaHelperBase::getInstance( 'Carts' );
		$items = $cart_helper->getProductsInfo();
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
		$html = $this->getTotalAmountDue();

		$response = array();
		$response['msg'] = $html;
		$response['error'] = '';

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);
	}
	
	function getPaymentOptionsHtml()
	{
		$html = '';
		$model = $this->getModel( 'Checkout', 'TiendaModel' );
		$view   = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->setLayout( 'payment_options' ); 

        $payment_plugins = $this->getPaymentOptions($this->_order);		
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
	
	/**
	 * Method to get the Payment Methods
	 * @param object $order - 
	 * @return array
	 */	
	function getPaymentOptions($order = null)
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
	
	function updatePaymentOptions()
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

		// Use AJAX to show plugins that are available
		JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );
		
		$this->setAddresses( $submitted_values );
		if (!$this->validateAddress( $submitted_values, $this->billing_input_prefix, @$submitted_values['billing_address_id'] ))
		{			
			$response['msg'] = $helper->generateMessage( JText::_( "BILLING ADDRESS ERROR" )." :: ".$this->getError());
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return;
		}
	
		$model = $this->getModel( 'Checkout', 'TiendaModel' );
		$view   = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->setLayout( 'payment_options' ); 
		
		$payment_plugins = $this->getPaymentOptions($this->_order);		
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
	
		// set response array
		$response = array();
		$response['msg'] = $html;

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
        if ( !empty($values['register']) && empty($user_id) )
        {
            $this->registerNewUser($values);
            $user_id = JFactory::getUser()->id;
        }
		
		// Guest Checkout
		$guest = false;
		if ( empty($user_id) && TiendaConfig::getInstance()->get('guest_checkout_enabled', '1') )
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
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $cart_helper = &TiendaHelperBase::getInstance( 'Carts' );
		$items = $cart_helper->getProductsInfo();
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
		
        $showBilling = true;
        if (empty($billingAddress->address_id))
        {
            $showBilling = false;
        }
        $view->assign( 'showBilling', $showBilling );

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

		// are there any enabled coupons?
		$coupons_present = false;
		$model = JModel::getInstance( 'Coupons', 'TiendaModel' );
		$model->setState('filter_enabled', '1');
		if ($coupons = $model->getList())
		{
		    $coupons_present = true;
		}
		$view->assign( 'coupons_present', $coupons_present );
		
		
		// get all the enabled payment plugins
		Tienda::load( 'TiendaHelperPlugin', 'helpers.plugin' );
		$payment_plugins = TiendaHelperPlugin::getPluginsWithEvent( 'onGetPaymentPlugins' );
		
        $dispatcher =& JDispatcher::getInstance();

        $plugins = array();
        if ($payment_plugins)
        {
            foreach ($payment_plugins as $plugin)
            {
                $results = $dispatcher->trigger( "onGetPaymentOptions", array( $plugin->element, $order ) );
                if (in_array(true, $results, true))
                {
                    $plugins[] = $plugin;
                }
            }
        }
		
        if (count($plugins) == 1)
        {
            $plugins[0]->checked = true;
            ob_start();
            $this->getPaymentForm( $plugins[0]->element );
            $html = json_decode( ob_get_contents() );
            ob_end_clean();
            $view->assign( 'payment_form_div', $html->msg );                                               
        }               
		$view->assign('plugins', $plugins);

		//$dispatcher =& JDispatcher::getInstance();

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
	
	/**
	 * This method is called after the submitted values is successfully validated and order successfully save
	 * It will prepare the data to be passed to the function _prePayment() of payment plugin
	 * @return unknown
	 */
	function preparePaymentOnepage($values)
	{
		$data = new JObject();	
		$data->html = '';
		$data->summary = '';
		
		// Get Order Object
		$order =& $this->_order;
		$user = JFactory::getUser();

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
			$data->_errors = $shippingAddress->getError();		
			return $data;	
		}

		if (!$billingAddress->save())
		{						
			$data->_errors = $billingAddress->getError();		
			return $data;		
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
			$data->_errors = $orderpayment->getError();					
			return $data;	
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
	
		$dispatcher    =& JDispatcher::getInstance();
		$results = $dispatcher->trigger( "onPrePayment", array( $values['payment_plugin'], $values ) );

		// Display whatever comes back from Payment Plugin for the onPrePayment
		$html = "";
		for ($i=0; $i<count($results); $i++)
		{
			$html .= $results[$i];
		}
		
		$data->html = $html;		
		$data->summary = $this->getOrderSummary();
		return $data;
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
				if( TiendaConfig::getInstance()->get('obfuscate_guest_email', '0' ) )
				{
					$lastUserId = $userHelper->getLastUserId();
					$guestId = $lastUserId + 1;
					// format: guest_[id]@domain.com
					$guest_email = "guest_".$guestId."@".$domain;
					$userEmailUpdate = $userHelper->updateUserEmail($user->id, $guest_email);
				}

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
			
			// re-load the order in case the payment plugin updated it
			$order->load( array('order_id'=>$order_id) );
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

			// get the articles to display after checkout
			$articles = array();
			switch ($order->order_state_id)
			{
			    case "2":
			    case "3":
			    case "5":
			    case "17":
			        $articles = $this->getOrderArticles( $order_id );
			        break;
			}
			$view->assign( 'articles', $articles );
		
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
	
	function saveOrderOnePage()
	{		
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';
		$response['anchor'] = '';

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
		
		if(empty($submitted_values['_checked']['payment_plugin']))
		{			
			$response['msg'] = $helper->generateMessage(JText::_("Please select payment method"));
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return;
		}
		
		//override the payment plugin with the check value
		$submitted_values['payment_plugin'] = $submitted_values['_checked']['payment_plugin'];
		
		if(!$this->validateSelectShipping($submitted_values))
		{		
			return;
		}	
		
		if(!$this->validateSelectPayment($submitted_values))
		{
			return;
		}					
		//set data
		$this->setAddresses($submitted_values, true);
			
		if (TiendaConfig::getInstance()->get('guest_checkout_enabled', '1') && $submitted_values['guest'] == '1')
		{
			//check email if in correct format
			jimport('joomla.mail.helper');
			if(!JMailHelper::isEmailAddress($submitted_values['email_address']))
			{
				$response['msg'] = $helper->generateMessage(JText::_("Please enter correct email.")); 				
				$response['error'] = '1';
				echo json_encode($response);		
				return;
			}
			
			Tienda::load( 'TiendaHelperUser', 'helpers.user' );
			$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');

			if ($userHelper->emailExists($submitted_values['email_address']))
			{				
				$response['msg'] = $helper->generateMessage(JText::_("Email already exist."));
				$response['error'] = '1';				
				echo ( json_encode($response) );			
				return false;
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
					'email' => $submitted_values['email_address'],
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
				$userinfo->email = $submitted_values['email_address'];
				$userinfo->save();

				// login the user
				$userHelper->login(
				array('username' => $user->username, 'password' => $details['password'])
				);
			}
		}
	
		//check if we have a user
		if(!JFactory::getUser()->id)
		{
			// Output error message and halt			
			$response['msg'] = $helper->generateMessage(JText::_("User registration is required or provide an email for guest checkout."));
			$response['error'] = '1';
			$response['anchor'] = '#tiendaRegistration';
			// encode and echo (need to echo to send back to browser)
			echo ( json_encode($response) );			
			return false;
		}
		
		//save order
		if(!$this->saveOrder($submitted_values))
		{
			// Output error message and halt			
			$response['msg'] = $helper->generateMessage($this->getError());
			$response['error'] = '1';
			// encode and echo (need to echo to send back to browser)
			echo ( json_encode($response) );			
			return false;
		}
				
		$data = $this->preparePaymentOnepage($submitted_values);

		if(!empty($data->_errors))
		{
			$response['msg'] = $helper->generateMessage($data->_errors);
			$response['error'] = '1';
		}
		else
		{
			$response['msg'] = $data->html;
			$response['summary'] = $data->summary;
			$response['error'] = '';
		}
		// encode and echo (need to echo to send back to browser)
		echo ( json_encode($response) );

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
		if($values['shippingrequired'])
		{
			$order->shipping = new JObject();
			$order->shipping->shipping_price      = $values['shipping_price'];
			$order->shipping->shipping_extra   = $values['shipping_extra'];
			$order->shipping->shipping_name        = $values['shipping_name'];
			$order->shipping->shipping_tax      = $values['shipping_tax'];
		}

		// Store the text verion of the currency for order integrity
		Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
		$order->order_currency = TiendaHelperOrder::currencyToParameters($order->currency_id);

		//get the items and add them to the order
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		
		//we dont need to add items in the order if onepage checkout since its already added in the shipping validation		
		if(!$this->onepage_checkout)
		{
			$reviewitems = TiendaHelperCarts::getProductsInfo();
			foreach ($reviewitems as $reviewitem)
			{
				$order->addItem( $reviewitem );
			}
		}
		
	    // get all coupons and add them to the order
        $coupons_enabled = TiendaConfig::getInstance()->get('coupons_enabled');
        $mult_enabled = TiendaConfig::getInstance()->get('multiple_usercoupons_enabled');
        if (!empty($values['coupons']) && $coupons_enabled)
        {
            foreach ($values['coupons'] as $coupon_id)
            {
                $coupon = JTable::getInstance('Coupons', 'TiendaTable');
                $coupon->load(array('coupon_id'=>$coupon_id));
                $order->addCoupon( $coupon );
                if (empty($mult_enabled))
                {
                    // this prevents Firebug users from adding multiple coupons to orders
                    break;
                }                
            }
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
			
		    // save the order coupons
            if (!$this->saveOrderCoupons())
            {
                // TODO What to do if saving order coupons fails?
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
				//fire onAfterSaveOrderItem
        		$dispatcher = JDispatcher::getInstance();
        		$dispatcher->trigger( 'onAfterSaveOrderItem', array( $item ) );				
				
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
						$orderitemattribute->orderitemattribute_code = $productattribute->productattributeoption_code;
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
		$shipping_code = JRequest::getVar('shipping_code', '');
		$shipping_price = JRequest::getVar('shipping_price', '');
		$shipping_tax = JRequest::getVar('shipping_tax', '');
		$shipping_extra = JRequest::getVar('shipping_extra', '');
			
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$row = JTable::getInstance('OrderShippings', 'TiendaTable');
		$row->order_id = $order->order_id;
		$row->ordershipping_type = $shipping_plugin;
		$row->ordershipping_price = $shipping_price;
		$row->ordershipping_name = $shipping_name;
		$row->ordershipping_code = $shipping_code;
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
     * Saves the order coupons to the DB
     * @return unknown_type
     */
    function saveOrderCoupons()
    {
        $order =& $this->_order;
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        
        $error = false;
        $errorMsg = "";        
        $ordercoupons = $order->getOrderCoupons();
        foreach ($ordercoupons as $ordercoupon)
        {
            $ordercoupon->order_id = $order->order_id;
            if (!$ordercoupon->save())
            {
                // track error
                $error = true;
                $errorMsg .= $ordercoupon->getError();
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
     * Validate Coupon Code
     *
     * @return unknown_type
     */
    function validateCouponCode()
    {
        JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );            
        $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

        // convert elements to array that can be binded
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper = TiendaHelperBase::getInstance();
        $values = $helper->elementsToArray( $elements );
        
        $coupon_code = JRequest::getVar( 'coupon_code', '');
        
        $response = array();
        $response['msg'] = '';
        $response['error'] = '';
        
        // check if coupon code is valid
        $user_id = JFactory::getUser()->id;
        Tienda::load( 'TiendaHelperCoupon', 'helpers.coupon' );
        $helper_coupon = new TiendaHelperCoupon();
        $coupon = $helper_coupon->isValid( $coupon_code, 'code', $user_id );
        if (!$coupon)
        {
            $response['error'] = '1';
            $response['msg'] = $helper->generateMessage( $helper_coupon->getError() );
            echo json_encode($response);
            return;
        }

        if (!empty($values['coupons']) && in_array($coupon->coupon_id, $values['coupons']))
        {
            $response['error'] = '1';
            $response['msg'] = $helper->generateMessage( JText::_( "This Coupon Has Already Been Added to the Order" ) );
            echo json_encode($response);
            return;
        }
     	        
        // TODO Check that the user can add this coupon to the order
        $can_add = true;
        if (!$can_add)
        {
            $response['error'] = '1';
            $response['msg'] = $helper->generateMessage( JText::_( "Cannot Add This Coupon to Order" ) );
            echo json_encode($response);
            return;
        }
    
        // if valid, return the html for the coupon
        $response['msg'] = " <input type='hidden' name='coupons[]' value='$coupon->coupon_id'>";
 
        echo json_encode($response);
        return;
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
 	 * Method to show the customer info after registration via ajax
 	 * TODO: used view
 	 */
    function showCustomerInfo()
    {
    	Tienda::load( 'TiendaUrl', 'library.url' );
    	$user = JFactory::getUser();    	
    	$response = array();	    	
    	$response['msg'] = '<legend class="tienda-collapse-processed">'.JText::_('Customer Information').'</legend>';
		$response['msg'] .= '<div id="tienda_customer">';	
		$response['msg'] .= '<div class="note">';
		$response['msg'] .= JText::_('Order information will be sent to your account e-mail listed below.');
    	$response['msg'] .= '</div>';	
    	$response['msg'] .= JText::_('E-mail address').': '.$user->email.'( '.TiendaUrl::popup( "index.php?option=com_user&view=user&task=edit&tmpl=component", JText::_('edit'), array('update' => true) ).' )';	
    	
    	echo json_encode($response);
		return;  	
    }
	
	function registerNewUserOnepage()
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';
		$response['target']= '';	
		$response['logged']= '';		
		
		// get elements from post
		$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
		
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		
		if (empty($elements))
		{			
			$response['error'] = '1';			
			$response['msg'] = $helper->generateMessage(JText::_("Error while validating the parameters"));
			$response['target']= 'tienda_checkout_onepage';
			echo ( json_encode( $response ) );
			return;
		}
				
		$submitted_values = $helper->elementsToArray( $elements );		
		
		$button = false;
		if($submitted_values['target'] == 'tienda_btn_register')
		{
			$button = true;
		}
				
		//check email if in correct format
		jimport('joomla.mail.helper');
		if(!JMailHelper::isEmailAddress($submitted_values['email_address']) && ($submitted_values['target'] == 'email_address' || $button) )
		{
			$response['msg'] = JText::_('Please enter correct email.');
			$response['error'] = '1';
			$response['target']= 'email_address';			
			echo json_encode($response);		
			return;
		}
				
		//do indiviual checking
		$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');
		if ($userHelper->emailExists($submitted_values['email_address']) && ($submitted_values['target'] == 'email_address' || $button))
		{							
			$response['msg'] = JText::_('This e-mail address is already registered.');
			$response['error'] = '1';
			$response['target']= 'email_address';
			echo json_encode($response);		
			return;
		}		
		
		//check name		
		if(empty($submitted_values['name']) && ($submitted_values['target'] == 'name' || $button))
		{
			$response['msg'] = JText::_('Name is required.');
			$response['error'] = '1';
			$response['target']= 'name';
			echo json_encode($response);		
			return;
		}
		
		//check username		
		if(empty($submitted_values['username']) && ($submitted_values['target'] == 'username' || $button))
		{
			$response['msg'] = JText::_('Username is required.');
			$response['error'] = '1';
			$response['target']= 'username';
			echo json_encode($response);		
			return;
		}
		else 
		{
			$db = JFactory::getDBO();
			Tienda::load( 'TiendaQuery', 'library.query' );			
			$query = new TiendaQuery();
			$query->select( 'tbl.*' );				
			$query->from('#__users AS tbl');  
			
			$key    = $db->Quote($db->getEscaped( trim( strtolower( $submitted_values['username'] ) ) ));            
            $query->where('tbl.username = '.$key);
			$db->setQuery( (string) $query );
			$result = $db->loadObject(); 
			
			//username exist
	        if($result && ($submitted_values['target'] == 'username' || $button))      
	        {
	        	$response['msg'] = JText::_('This username is already in use.');
				$response['error'] = '1';
				$response['target']= 'username';
				echo json_encode($response);
				return;		
	        }
		}

		//check username		
		if( ($submitted_values['password'] != $submitted_values['password2']) && ($submitted_values['target'] == 'password2' || $button))
		{
			$response['msg'] = JText::_('The passwords do not match.');
			$response['error'] = '1';
			$response['target']= 'password2';
			echo json_encode($response);		
			return;
		}
				
		// create the new user
		if($button)
		{
			$details = array(
					'email' => $submitted_values['email_address'],
					'name' => $submitted_values['name'],
					'username' => $submitted_values['username'],
					'password'=> $submitted_values['password'], 
					'password2'=> $submitted_values['password2']		
			);
			$user = $userHelper->createNewUser($details);
			
			if (empty($user->id))
			{
				$response['msg'] =  $userHelper->getError();
				$response['error'] = '1';
				$response['target']= 'tienda_checkout_onepage';
				echo json_encode($response);
				return;
			}
							
			// login the user
			$userHelper->login(
			array('username' => $user->username, 'password' => $details['password'])
			);	
					
			$response['logged'] = '1';			
		}		
		else 
		{
			switch($submitted_values['target'])
			{
				case 'email_address':
					$response['msg'] = JText::_('Email address is available.');
					break;
				case 'username':
					$response['msg'] = JText::_('Username is available.');
					break;
				case 'password2':
					$response['msg'] = JText::_('The passwords match.');
					break;
				default:
					break;
			}			
		}
							              
		echo json_encode($response);
		return;		
	}

	/*
	 * Regiter the new user with the Form
	 */
	function registerNewUser ($values){

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		
		//  Register an User
		Tienda::load( 'TiendaHelperUser', 'helpers.user' );
		$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');
		
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		if ($userHelper->emailExists($values['email_address']))
		{
			// TODO user already exists		
			$response['error'] = '1';	
			$response['msg'] = JText::_('Email already exist!');
			$response['key'] = 'email_address';
			return $response;
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
			$user = $userHelper->createNewUser($details, isset($values['guest']));

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
		
			return true;
		}
	}
	
	/**
	 * Returns an array of objects, 
	 * each containing the parsed html of all articles that should be displayed
	 * after an order is completed,
	 * based on the defined global article and any product-, shippingmethod-, and paymentmethod-specific articles
	 * 
	 * @param $order_id
	 * @return array
	 */
	function getOrderArticles( $order_id )
	{
        Tienda::load( 'TiendaArticle', 'library.article' );
        
	    $articles = array();
	    
	    $article_id = TiendaConfig::getInstance()->get( 'article_checkout' );
	    if (!empty($article_id))
	    {
	        $articles[] = TiendaArticle::display( $article_id );
	    }
	    
	    JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'OrderItems', 'TiendaModel' );
        $model->setState( 'filter_orderid', $order_id);
        $orderitems = $model->getList();
        foreach ($orderitems as $item)
        {
            if (!empty($item->product_article))
            {
                $articles[] = TiendaArticle::display( $item->product_article );
            }            
        }
	    
	    $dispatcher =& JDispatcher::getInstance();
	    $dispatcher->trigger( 'onGetOrderArticles', array( $order_id, &$articles ) );
	    
	    return $articles;
	}
}
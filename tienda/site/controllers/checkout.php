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
	var $initial_order_state   = '15'; // pre-payment/orphan // TODO also set this in the constructor, and use a config setting
	var $billing_input_prefix  = 'billing_input_';
	var $shipping_input_prefix = 'shipping_input_';
	var $defaultShippingMethod = null; // set in constructor
	
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		$this->set('suffix', 'checkout');
		// create the order object
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $this->_order = JTable::getInstance('Orders', 'TiendaTable');
        $this->defaultShippingMethod = '2'; // TODO Use a config setting for this
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

		// determine layout based on login status
		if (empty($user->id)) 
		{
			JRequest::setVar('layout', 'form');
		}
    		else 
		{
			// Get User Address Information
			JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
			$model = JModel::getInstance( 'addresses', 'TiendaModel' );
			$model->setState("filter_userid", $user->id);
			$model->setState("filter_deleted", 0);
			$items = $model->getList();
			// no addresses, redirect to address form			
			if (empty($items))
			{
				// TODO make this a true redirect using JFactory::getApplication()->redirect();
				JRequest::setVar( 'view', 'addresses');
				JRequest::setVar('layout', 'form');
			}
                else
			{
				$shipping_method_id = $this->defaultShippingMethod;
				
		        // get the order object so we can populate it
		        $order = &$this->_order; // a TableOrders object (see constructor)
		        
		        // set the currency
		        $order->currency_id = TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected
		        
		        // set the shipping method
		        $order->shipping_method_id = $shipping_method_id;
		        
		        // set the order's addresses based on the form inputs
		        // set to user defaults
		        JLoader::import( 'com_tienda.helpers.user', JPATH_ADMINISTRATOR.DS.'components' );
		        $billingAddress = TiendaHelperUser::getPrimaryAddress( JFactory::getUser()->id );
		        $shippingAddress = TiendaHelperUser::getPrimaryAddress( JFactory::getUser()->id, 'shipping' );
		        $order->setAddress( $billingAddress, 'billing' );
		        $order->setAddress( $shippingAddress, 'shipping' );

		        // get the items and add them to the order
		        JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );
        		$items = TiendaHelperCarts::getProductsInfo();
		        foreach ($items as $item)
		        {
		            $order->addItem( $item );
		        }

		        // get the order totals
		        $order->calculateTotals();
		        
		        // now that the order object is set, get the orderSummary html
		        $html = $this->getOrderSummary();

		        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		        $model = JModel::getInstance( 'addresses', 'TiendaModel' );
		        $model->setState("filter_userid", JFactory::getUser()->id);
		        $model->setState("filter_deleted", 0);
		        $addresses = $model->getList();
		        
		        // now display the entire checkout page
		        $view = $this->getView( 'checkout', 'html' );
		        $view->set( 'hidemenu', false);
		        $view->assign( 'order', $order );
		        $view->assign( 'addresses', $addresses );
                $view->assign( 'billing_address', $billingAddress );
                $view->assign( 'shipping_address', $shippingAddress );
				$view->assign( 'orderSummary', $html );
				JRequest::setVar('layout', 'default');
			}
		}
	
		parent::display();
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
        $order = &$this->_order; // a TableOrders object (see constructor)
        
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
        $view->assign( 'shipping_total', $order->getShippingTotal() );
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
		
        // get elements from post
        $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
        
        // Test if elements are empty
        // Return proper message to user
        if (empty($elements))
        {
           // do form validation
           // if it fails check, return message
           $response['error'] = '1';
           $response['msg'] = '
                    <dl id="system-message">
                    <dt class="notice">notice</dt>
                    <dd class="notice message fade">
                        <ul style="padding: 10px;">'.
                        JText::_("Could not process form").Tienda::dump($elements)                        
                        .'</ul>
                    </dd>
                    </dl>
                    ';
       		echo ( json_encode( $response ) );
       		return;
        }
        
        // convert elements to array that can be binded             
        JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );
        $submitted_values = TiendaHelperBase::elementsToArray( $elements );
            
		if (empty($submitted_values['_checked']['payment_plugin']))
		{
			// TODO abstract this to some kind of helper, such as TiendaHelperBase::generateMessage( $string );
           $response['msg'] = '
                    <dl id="system-message">
                    <dt class="notice">notice</dt>
                    <dd class="notice message fade">
                        <ul style="padding: 10px;">'.
                        JText::_('Please select payment method')
                        .'</ul>
                    </dd>
                    </dl>
                    ';
			$response['error'] = '1';
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
		            $response['msg'] = '
		                    <dl id="system-message">
		                    <dt class="notice">notice</dt>
		                    <dd class="notice message fade">
		                        <ul style="padding: 10px;">'.
		                        $result->message
		                        .'</ul>
		                    </dd>
		                    </dl>
		                    ';
		             $response['error'] = '1';
	            } 
                    else 
	            {
	                $response['error'] = '0';
	            }
	        }
		}
		
		echo ( json_encode( $response ) );
        
        return;
	}
	
    /**
     * Prepare the review tmpl
     * 
     * @return unknown_type
     */
    function review()
    {
    	// get the posted values
        $values = JRequest::get('post');
        
        // get the order object so we can populate it
        $order = &$this->_order; // a TableOrders object (see constructor)
        
        $order->bind( $values );
        $order->user_id = JFactory::getUser()->id;
        $order->shipping_method_id = $values['shipping_method_id']; 
        $this->setAddresses( $values );

        // get the items and add them to the order
        JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );
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
        $shipping_address_id = $values['shipping_address_id'];
        $billing_address_id = $values['billing_address_id'];
        $shipping_method_id = $values['shipping_method_id'];
        $customerNote = $values['customer_note'];
        
        //Set display
        $view = $this->getView( 'checkout', 'html' );       
        $view->setLayout('selectpayment');
        $view->set( '_doTask', true);
        
        //Get and Set Model
        $model = $this->getModel('checkout');
        $view->setModel( $model, true );
        
        //Get Addresses
        $shippingAddressArray = $this->retrieveAddressIntoArray($shipping_address_id);
        $billingAddressArray = $this->retrieveAddressIntoArray($billing_address_id);
        
        $shippingMethodName = $this->getShippingMethod($shipping_method_id);
        
        //Assign Addresses and Shippping Method to view
        $view->assign('shipping_method_name',$shippingMethodName);
        $view->assign('shipping_method_id',$shipping_method_id);
        $view->assign('shipping_info',$shippingAddressArray);
        $view->assign('billing_info',$billingAddressArray);
        $view->assign('customer_note', $customerNote);
        $view->assign('values', $values);

        $view->set( 'hidemenu', false);
        $view->assign( 'order', $order );
        $view->assign( 'orderSummary', $html );
        
        // get all the enabled payment plugins
        JLoader::import( 'com_tienda.helpers.plugin', JPATH_ADMINISTRATOR.DS.'components' );
        $plugins = TiendaHelperPlugins::getPluginsWithEvent( 'onGetPaymentPlugins' );
        $view->assign('plugins', $plugins);
        
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
	 * 
	 * @param $values
	 * @return unknown_type
	 */
	function setAddresses( $values )
    {
    	$order = $this->_order; // a TableOrders object (see constructor)
		
    	// Get the currency from the configuration
        $currency_id			= TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected
        $billing_address_id     = $values['billing_address_id'];
        $shipping_address_id    = $values['shipping_address_id'];
        $shipping_method_id     = $values['shipping_method_id'];
        //$same_as_billing      = @$values['_checked']['sameasbilling']; // this is for later
        $user_id                = JFactory::getUser()->id;
        $billing_input_prefix   = $this->billing_input_prefix;
        $shipping_input_prefix  = $this->shipping_input_prefix;
            	    	
        $billing_zone_id = 0;
        $billingAddressArray = $this->getAddress( $billing_address_id, $billing_input_prefix, $values );
        if (array_key_exists('zone_id', $billingAddressArray)) 
        {
            $billing_zone_id = $billingAddressArray['zone_id'];
        }

        //SHIPPING ADDRESS: get shipping address from dropdown or form (depending on selection)
        $shipping_zone_id = 0;      
        $shippingAddressArray = $this->getAddress($shipping_address_id, $shipping_input_prefix, $values);
        if (array_key_exists('zone_id', $shippingAddressArray)) 
        {
            $shipping_zone_id = $shippingAddressArray['zone_id'];
        }

        // keep the array for binding during the save process
        $this->_billingAddressArray = $this->filterArrayUsingPrefix($billingAddressArray, '', 'billing_', true);
        $this->_shippingAddressArray = $this->filterArrayUsingPrefix($shippingAddressArray, '', 'shipping_', true);

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
		}		
		return $addressArray;	
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
		return get_object_vars( $item );
	}
	
	/**
	 * Gets the selected shipping method
	 * 
	 * @param $shipping_method_id
	 * @return unknown_type
	 */
	function getShippingMethod($shipping_method_id)
	{
		$model = JModel::getInstance( 'ShippingMethods', 'TiendaModel' );
		$model->setId($shipping_method_id);
		$item = $model->getItem();
		return $item->shipping_method_name;
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
       	JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );
        $values = TiendaHelperBase::elementsToArray( $elements );
		
       	// Assign the shipping method to the order object
        $shipping_method_id = @$values['_checked']['shipping_method_id'];
        
        // get the order object so we can populate it
        $order = &$this->_order; // a TableOrders object (see constructor)
        
        // bind what you can from the post
        $order->bind( $values );
        
        // set the currency
        $order->currency_id = TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected
        
        // set the shipping method
        $order->shipping_method_id = $shipping_method_id;

        // set the addresses
        $this->setAddresses( $values );
        
        // get the items and add them to the order
        JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );
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
     * This method occurs before payment is attempted
     * and fires the onPrePayment plugin event
     *  
     * @return unknown_type
     */
    function preparePayment()
    {
        // verify that form was submitted by checking token
        JRequest::checkToken() or jexit( 'TiendaControllerCheckout::preparePayment - Invalid Token' );
         
        // 1. save the order to the table with a 'pre-payment' status
        
        // Get post values
        $values = JRequest::get('post');    

        // Save the order with a pending status
        if (!$this->saveOrder($values))
        {
            // Output error message and halt
            JError::raiseNotice( 'Error Saving Order', $this->getError() );
            return false;
        }
        
        // Get Order Object
        $order = $this->_order;
                
        // Save an orderpayment with an Incomplete status
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->order_id = $order->order_id;
        $orderpayment->orderpayment_type = $values['payment_plugin']; // this is the payment plugin selected
        $orderpayment->transaction_status = JText::_( "Incomplete" ); // payment plugin updates this field onPostPayment
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
        $shippingAddressArray = $this->retrieveAddressIntoArray($shipping_address->id);
        $billingAddressArray = $this->retrieveAddressIntoArray($billing_address->id);
               
        $shippingMethodName = $this->getShippingMethod($order->shipping_method_id);
        
        // Set display
        $view = $this->getView( 'checkout', 'html' );       
        $view->setLayout('prepayment');
        $view->set( '_doTask', true);
        $view->assign('order', $order);
        $view->assign('plugin_html', $html);
        $view->assign('orderSummary', $summary);
        $view->assign('shipping_info', $shippingAddressArray);
        $view->assign('billing_info', $billingAddressArray);
        $view->assign('shipping_method_name',$shippingMethodName);
                        
        // Get and Set Model
        $model = $this->getModel('checkout');
        $view->setModel( $model, true );
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
		$orderpayment_type = JRequest::getVar('orderpayment_type');
		
        // Get post values
        $values = JRequest::get('post');
		
        $dispatcher =& JDispatcher::getInstance();
        $results = $dispatcher->trigger( "onPostPayment", array( $orderpayment_type, $values ) );
        
        // Display whatever comes back from Payment Plugin for the onPrePayment
        $html = "";
        for ($i=0; $i<count($results); $i++) 
        {
            $html .= $results[$i];
        }
        
        // get the order_id from the session set by the prePayment 
        $mainframe =& JFactory::getApplication();
        $order_id = $mainframe->getUserState( 'tienda.order_id' );      
        $order_link = 'index.php?option=com_tienda&view=orders&task=view&id='.$order_id;
                
        // Set display
        $view = $this->getView( 'checkout', 'html' );       
        $view->setLayout('postpayment');
        $view->set( '_doTask', true);
        $view->assign('order_link', $order_link );
        $view->assign('plugin_html', $html);
        
        // Get and Set Model
        $model = $this->getModel('checkout');
        $view->setModel( $model, true );
        $view->display();
        
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
        $order->shipping_method_id = $values['shipping_method_id']; 
        $this->setAddresses( $values );
        
        // Store the text verion of the currency for order integrity
        JLoader::import( 'com_tienda.helpers.order', JPATH_ADMINISTRATOR.DS.'components' );
        $order->order_currency = TiendaHelperOrder::currencyToParameters($order->currency_id);
        
        //get the items and add them to the order
        JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );
        $reviewitems = TiendaHelperCarts::getProductsInfo();
        
        foreach ($reviewitems as $reviewitem)
        {   
            $order->addItem( $reviewitem );
        }
        $order->order_state_id = $this->initial_order_state;
        $order->calculateTotals();
        $order->getShippingTotal();
        $order->getOrderNumber();
        
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
        $row->bind( $this->_billingAddressArray );  
        $row->bind( $this->_shippingAddressArray );
       
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
        // TODO Should the code for sending email to the customer be inserted to the table ->store() method?
        $row->customer_notified = '1'; 
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
}
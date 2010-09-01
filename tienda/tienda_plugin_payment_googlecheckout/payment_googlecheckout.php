<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_googlecheckout extends TiendaPaymentPlugin
{
	/**
	 * @var string
	 * @access protected
	 */
	var $_payment_type = 'payment_googlecheckout';
	var $_element    = 'payment_googlecheckout';

	/**
	 * @var boolean
	 * @access protected
	 */
	var $_isLog = false;

	/**
	 * @var object
	 * @access protected
	 */
	var $_logObj;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */

	function plgTiendaPayment_googlecheckout(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}

	/**
	 * Wraps the given text in the HTML
	 *
	 * @param string $text
	 * @return string
	 * @access protected
	 */
	function _renderHtml($message = '')
	{
		$vars = new JObject();
		$vars->message = $message;

		$html = $this->_getLayout('message', $vars);

		return $html;
	}

	/************************************
	 * Note to 3pd:
	 *
	 * The methods between here
	 * and the next comment block are
	 * yours to modify
	 *
	 ************************************/

	/**
	 * Prepares the payment form
	 * and returns HTML Form to be displayed to the user
	 * generally will have a message saying, 'confirm entries, then click complete order'
	 *
	 * Submit button target for onsite payments & return URL for offsite payments should be:
	 * index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=xxxxxx
	 * where xxxxxxx = $_element = the plugin's filename
	 *
	 * @param $data     array       form post data
	 * @return string   HTML to display
	 */
	function _prePayment( $data )
	{
		// prepare the payment form
		/*
		* get all necessary data and prepare vars for assigning to the template
		*/

		$vars = new JObject();
		$vars->order_id = $data['order_id'];
		$vars->orderpayment_id = $data['orderpayment_id'];
		$vars->orderpayment_amount = $data['orderpayment_amount'];
		$vars->orderpayment_type = $this->_element;

		// set paypal checkout type
		$order = JTable::getInstance('Orders', 'TiendaTable');
		$order->load( $data['order_id'] );
		$items = $order->getItems();
		$vars->is_recurring = $order->isRecurring();

		// if order has both recurring and non-recurring items,
		if ($vars->is_recurring && count($items) > '1')
		{
			$vars->cmd = '_cart';
			$vars->mixed_cart = true;
			// Adjust the orderpayment amount since it's a mixed cart
			// first orderpayment is just the non-recurring items total
			// then upon return, ask user to checkout again for recurring items
			$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
			$orderpayment->load( $vars->orderpayment_id );
			$vars->amount = $order->recurring_trial ? $order->recurring_trial_price : $order->recurring_amount;
			$orderpayment->orderpayment_amount = $orderpayment->orderpayment_amount - $vars->amount;
			$orderpayment->save();
			$vars->orderpayment_amount = $orderpayment->orderpayment_amount;
		}
		elseif ($vars->is_recurring && count($items) == '1')
		{
			// only recurring
			$vars->cmd = '_xclick-subscriptions';
			$vars->mixed_cart = false;
		}
		else
		{
			// do normal cart checkout
			$vars->cmd = '_cart';
			$vars->mixed_cart = false;
		}
		$vars->order = $order;
		$vars->orderitems = $items;

		// set payment plugin variables
		$vars->merchant_email = $this->_getParam( 'merchant_email' );
		$vars->post_url = $this->_getPostUrl();

		// are there both recurring and non-recurring items in cart?
		// if so, then user must perform two checkouts,
		// so store a flag in the return_url
		$vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=display_message&checkout=1";
		$vars->cancel_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=cancel";
		$vars->notify_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process&tmpl=component";
		$vars->currency_code = $this->_getParam( 'currency', 'USD' ); // TODO Eventually use: TiendaConfig::getInstance()->get('currency');

		// set variables for user info
		$vars->first_name   = $data['orderinfo']->shipping_first_name;
		$vars->last_name    = $data['orderinfo']->shipping_last_name;
		$vars->email        = $data['orderinfo']->user_email;
		$vars->address_1    = $data['orderinfo']->shipping_address_1;
		$vars->address_2    = $data['orderinfo']->shipping_address_2;
		$vars->city         = $data['orderinfo']->shipping_city;
		$vars->country      = $data['orderinfo']->shipping_country_name;
		$vars->region       = $data['orderinfo']->shipping_zone_name;
		$vars->postal_code  = $data['orderinfo']->shipping_postal_code;

		$vars->merchant_id = $this->_getParam('merchant_id');
		$vars->type_id = JRequest::getInt('id');
		//$vars->post_url = JRoute::_("index.php?option=com_tienda&controller=payment&task=process&ptype={$this->_payment_type}&paction=proceed&tmpl=component");
		$vars->button_url = $this->_getPostUrl(false);
		$vars->note = JText::_( 'GoogleCheckout Note Default' );
		$uri =& JFactory::getURI();
		$url = $uri->toString(array('path', 'query', 'fragment'));
		$vars->r = base64_encode($url);

		// creating the hard xml etc for Google Check
		// Include all the required files
		require_once dirname(__FILE__) . "/{$this->_payment_type}/library/googlecart.php";
		require_once dirname(__FILE__) . "/{$this->_payment_type}/library/googleitem.php";
		require_once dirname(__FILE__) . "/{$this->_payment_type}/library/googleshipping.php";
		require_once dirname(__FILE__) . "/{$this->_payment_type}/library/googletax.php";
			
		$user =& JFactory::getUser();
		$app =& JFactory::getApplication();
		
		$cart = new GoogleCart($this->_getParam('merchant_id'), $this->_getParam('merchant_key'), $this->_getServerType(), $this->params->get('currency', 'USD'));
			
		foreach($items as $itemObject){
			$item_temp = new GoogleItem($itemObject->orderitem_name,
			$itemObject->orderitem_name,$itemObject->orderitem_quantity,$itemObject->orderitem_price);
			// in argument of GoogleItem first itemname , itemDescription,quantity, unti price
			$cart->AddItem($item_temp);

		}
		// Add shipping
		$shipTemp = new GooglePickup($data['shipping_name'], $data['shipping_price']); // shipping name and Price as an argument
		$cart->AddShipping($shipTemp);

		// Add Tax
		//		$tax_rule = new GoogleDefaultTaxRule(0.15);
		//		$tax_rule->SetWorldArea(true);
		//		$cart->AddDefaultTaxRules($tax_rule);


		$checkout_return_url = JURI::root() . "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process&tmpl=component";
		$cart->SetContinueShoppingUrl($checkout_return_url);
     
		//echo $cart->GetXML();exit;

		// send a server-2-server request
		// and if it's OK redirect the user to the google checkout page
		list($status, $error) = $cart->CheckoutServer2Server();

		// if we reach this point, something went wrong
		JError::raiseWarning('', $this->params->get('sandbox') ? $error : JText::_('GOOGLECHECKOUT MESSAGE INVALID ACTION'));
		$app->redirect($return);

		$vars->cart=$cart;

    	$html = $this->_getLayout('prepayment', $vars);
		return $html;
		//        $text = array();
		//		$text[] = $html;
		//		$text[] = $this->params->get( 'title', 'Google Checkout' );
		//		return $text;

	}


	/**
	 * Processes the payment form
	 * and returns HTML to be displayed to the user
	 * generally with a success/failed message
	 *
	 * @param $data     array       form post data
	 * @return string   HTML to display
	 */
	function _postPayment( $data )
	{
		// Process the payment
		$paction = JRequest::getVar('paction');
		echo "now I am calling janu ji ";
		var_dump($_REQUEST);
		echo "Ynaahs e data";

		$vars = new JObject();

		switch ($paction)
		{
			case "display_message":
				$checkout = JRequest::getInt('checkout');
				// get the order_id from the session set by the prePayment
				$mainframe =& JFactory::getApplication();
				$order_id = (int) $mainframe->getUserState( 'tienda.order_id' );
				$order = JTable::getInstance('Orders', 'TiendaTable');
				$order->load( $order_id );
				$items = $order->getItems();

				// if order has both recurring and non-recurring items,
				if ($order->isRecurring() && count($items) > '1' && $checkout == '1')
				{
					$html = $this->_secondPayment( $order_id );
				}
				else
				{
					$vars->message = JText::_('GOOGLE CHECKOUT MESSAGE PAYMENT ACCEPTED FOR VALIDATION');
					$html = $this->_getLayout('message', $vars);
					$html .= $this->_displayArticle();
				}
				break;
			case "process":
				$vars->message = $this->_process();
				$html = $this->_getLayout('message', $vars);
				echo $html; // TODO Remove this
				$app =& JFactory::getApplication();
				$app->close();
				break;
			case "cancel":
				$vars->message = JText::_( 'Goolge Checkout Message Cancel' );
				$html = $this->_getLayout('message', $vars);
				break;
			default:
				$vars->message = JText::_( 'Goolge Checkout Message Invalid Action' );
				$html = $this->_getLayout('message', $vars);
				break;
		}

		return $html;
	}

	/**
	 * Prepares variables for the payment form
	 *
	 * @return unknown_type
	 */
	function _renderForm( $data )
	{
		$user = JFactory::getUser();
		$vars = new JObject();


		$html = $this->_getLayout('form', $vars);
		return $html;
	}

	/**
	 * Gets the value for the Paypal variable
	 *
	 * @param string $name
	 * @return string
	 * @access protected
	 */
	function _getParam( $name, $default='' )
	{
		$return = $this->params->get($name, $default);
			
		$sandbox_param = "sandbox_$name";
		$sb_value = $this->params->get($sandbox_param);
		if ($this->params->get('sandbox') && !empty($sb_value))
		{
			$return = $this->params->get($sandbox_param, $default);
		}

		return $return;
	}

	//    /**
	//	 * Gets the gateway URL
	//	 *
	//	 * @param boolean $full
	//	 * @return string
	//	 * @access protected
	//	 */
	//	function _getActionUrl($full = true)
	//	{
	//		if ($full) {
	//			$url  = $this->params->get('sandbox') ? 'https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/' : 'https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/';
	//			$url .= $this->_getParam('merchant_id');
	//		}
	//		else {
	//			$url = $this->params->get('sandbox') ? 'https://checkout.google.com' : 'https://sandbox.google.com/checkout';
	//		}
	//
	//		return $url;
	//	}


	/**
	 * Gets the Paypal gateway URL
	 *
	 * @param boolean $full
	 * @return string
	 * @access protected
	 */
	function _getPostUrl($full = true)
	{
		if ($full) {
			$url  = $this->params->get('sandbox') ? 'https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/' : 'https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/';
			$url .= $this->_getParam('merchant_id');
		}
		else {
			$url = $this->params->get('sandbox') ? 'https://checkout.google.com' : 'https://sandbox.google.com/checkout';
		}

		return $url;
	}

	/**
	 * Gets the return url from the Request
	 *
	 * @return string
	 * @access protected
	 */
	function _getReturnURL()
	{
		$url = JRequest::getVar('r', '', 'default', 'base64');
		$url = base64_decode($url);

		if (empty($url) || !JURI::isInternal($url)) {
			$url = JURI::base();
		}

		return $url;
	}

	/**
	 * Returns the Google Checkout server type
	 *
	 * @return string
	 * @access protected
	 */
	function _getServerType()
	{
		return $this->params->get('sandbox') ? 'sandbox' : 'production';
	}

	function _process()
	{
		require_once dirname(__FILE__) . "/{$this->_payment_type}/library/googleresponse.php";
		require_once dirname(__FILE__) . "/{$this->_payment_type}/library/googleresult.php";
		require_once dirname(__FILE__) . "/{$this->_payment_type}/library/googlerequest.php";

		$response = new GoogleResponse($this->_getParam('merchant_id'), $this->_getParam('merchant_key'));
		$request = new GoogleRequest($this->_getParam('merchant_id'), $this->_getParam('merchant_key'), $this->_getServerType(), $this->params->get('currency', 'USD'));

		// setup the log files
		if ($this->_isLog) {
			$path = JPATH_ROOT . '/cache';
				
			$response->SetLogFiles($path . '/google_error.log', $path . '/google_message.log', L_ALL);
			$this->_logObj =& $response->log;
		}

		// retrieve the XML sent in the HTTP POST request to the ResponseHandler
		$xml_response = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents('php://input');

		if ( ! $xml_response) {
			echo "No response received', 'error'";
			die('No response received');
		}

		if (get_magic_quotes_gpc()) {
			$xml_response = stripslashes($xml_response);
		}

		list($root, $data) = $response->GetParsedXML($xml_response);

		// validate the data (comment for testing)
		$response->SetMerchantAuthentication($this->_getParam('merchant_id'), $this->_getParam('merchant_key'));
		if ( ! $response->HttpAuthentication()) {
			$this->_log('Authentication failed', 'error');
			die('Authentication failed');
		}

		// prepare the payment data
  		$data = $data[$root];
		$payment_details = $this->_getFormattedPaymentDetails($xml_response);
		
  		// process the payment
  		$error = '';		
  		if ($root == 'new-order-notification') {		
			$payment_error = $this->_processSale( $data, $error );
  		}
	//  		else {
	//  			$error = $this->_processPaymentUpdate($root, $data, $payment_details);			
	//  		}
		$payment_error = $this->_processSale( $data, $error );
		//$response->SendAck();

		// if here, all went well
		$error = 'processed';
		return $error;
	}

	/**
     * Processes the sale payment
     * 
     * @param array $data IPN data
     * @return boolean Did the IPN Validate?
     * @access protected
     */
    function _processSale( $data, $error='' )
    {
        /*
         * validate the payment data
         */
        $errors = array();
        
        if (!empty($error))
        {
        	$errors[] = $error;
        }
        // load the orderpayment record and set some values
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $data['custom'] );
        if (empty($data['custom']) || empty($orderpayment->orderpayment_id))
        {
            $errors[] = JText::_('GOOGLE CHECKOUT INVALID ORDERPAYMENTID');
            return count($errors) ? implode("\n", $errors) : '';
        }
        $orderpayment->transaction_details  = $data['transaction_details'];
        $orderpayment->transaction_id       = $data['txn_id'];
        $orderpayment->transaction_status   = $data['payment_status'];
       
        // check the stored amount against the payment amount
        $stored_amount = number_format( $orderpayment->get('orderpayment_amount'), '2' );
        if ((float) $stored_amount !== (float) $data['mc_gross']) {
            $errors[] = JText::_('GOOGLE CHECKOUT AMOUNT INVALID');
        }
        
        // check the payment status
        if (empty($data['payment_status']) || ($data['payment_status'] != 'Completed' && $data['payment_status'] != 'Pending')) {
            $errors[] = JText::sprintf('GOOGLE CHECKOUT MESSAGE STATUS INVALID', @$data['payment_status']);
        }
        
        // set the order's new status and update quantities if necessary
        Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $orderpayment->order_id );
        if (count($errors)) 
        {
            // if an error occurred 
            $order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED
        }
            elseif (@$data['payment_status'] == 'Pending') 
        {
            // if the transaction has the "pending" status,
            $order->order_state_id = TiendaConfig::getInstance('pending_order_state', '1'); // PENDING
            // Update quantities for echeck payments
            TiendaHelperOrder::updateProductQuantities( $orderpayment->order_id, '-' );
            
            // remove items from cart
            TiendaHelperCarts::removeOrderItems( $orderpayment->order_id );
            
            // send email
            $send_email = true;
        }
            else 
        {
            $order->order_state_id = $this->params->get('payment_received_order_state', '17');; // PAYMENT RECEIVED
            $this->setOrderPaymentReceived( $orderpayment->order_id );
            
            // send email
            $send_email = true;
        }

        // save the order
        if (!$order->save())
        {
        	$errors[] = $order->getError();
        }
        
        // save the orderpayment
        if (!$orderpayment->save())
        {
        	$errors[] = $orderpayment->getError(); 
        }
        
        if ($send_email)
        {
            // send notice of new order
            Tienda::load( "TiendaHelperBase", 'helpers._base' );
            $helper = TiendaHelperBase::getInstance('Email');
            $model = Tienda::getClass("TiendaModelOrders", "models.orders");
            $model->setId( $orderpayment->order_id );
            $order = $model->getItem();
            $helper->sendEmailNotices($order, 'new_order');
        }

        return count($errors) ? implode("\n", $errors) : '';        
    }
    
	
	/**
	 * Logs to files using GoogleLog class 
	 * 
	 * @param $text
	 * @param $type
	 * @return unknown_type
	 */
	function _log($text, $type = 'message')
	{
		if ($this->_logObj !== null) {
			if ($type == 'error') {
				$this->_logObj->logError($text);
			}
			else {
				$this->_logObj->LogResponse($text);
			}
		}	
	}

}

if ( ! function_exists('plg_tienda_escape')) {

	/**
	 * Escapes a value for output in a view script
	 *
	 * @param mixed $var
	 * @return mixed
	 */
	function plg_tienda_escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}
}

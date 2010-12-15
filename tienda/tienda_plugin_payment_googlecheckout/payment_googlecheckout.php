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

	function plgTiendaPayment_googlecheckout(& $subject, $config)
	{
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

		/*
		 * get all necessary data and prepare vars for assigning to the template
		 */
		$vars = new JObject();
		$order = JTable::getInstance('Orders', 'TiendaTable');
		$order->load( $data['order_id'] );

		$items = $order->getItems();
		$vars->orderpayment_id = $data['orderpayment_id'];
		$vars->orderpayment_amount = $data['orderpayment_amount'];
		$vars->orderpayment_type = $this->_element;

		$vars->merchant_id = $this->_getParam('merchant_id');
		$vars->type_id = JRequest::getInt('id');
		//$vars->post_url = JRoute::_("index.php?option=com_tienda&controller=payment&task=process&ptype={$this->_element}&paction=proceed&tmpl=component");
		$vars->button_url = $this->_getPostUrl(false);
		$vars->note = JText::_( 'GoogleCheckout Note Default' );
		$uri =& JFactory::getURI();
		$url = $uri->toString(array('path', 'query', 'fragment'));
		$vars->r = base64_encode($url);
		// Include all the required files
		require_once dirname(__FILE__) . "/{$this->_element}/library/googlecart.php";
		require_once dirname(__FILE__) . "/{$this->_element}/library/googleitem.php";
		require_once dirname(__FILE__) . "/{$this->_element}/library/googleshipping.php";
		require_once dirname(__FILE__) . "/{$this->_element}/library/googletax.php";
		
		$cart = new GoogleCart($this->_getParam('merchant_id'), $this->_getParam('merchant_key'), $this->_getServerType(), $this->params->get('currency', 'USD'));
		$totalTax=0;

		//check if coupons is not empty
		//if not empty then we process the coupon name and value and add to the $items having negative value
		if(!empty($data['coupons']))
		{	
			$couponIds = array();
			$couponIds = $data['coupons'];
			
			//NOTE: checking the coupon if its valid for the user is already done in the controller
       		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
       		$model = JModel::getInstance( 'Coupons', 'TiendaModel' );
			$model->setState( 'filter_ids', $couponIds );
        	$coupons = $model->getList();
      	
			if(!empty($coupons))
			{
				foreach($coupons as $coupon)
				{
					$couponObj = new stdClass();
					$couponObj->orderitem_name = $coupon->coupon_code." ".JText::_( "(Discount)" );
					$couponObj->orderitem_quantity = (int)1;
					$couponObj->orderitem_price = "-".$coupon->coupon_value;
					
					$items[] = $couponObj;
				}
			}
		}			
				
		foreach($items as $itemObject)
		{
			$item_temp = new GoogleItem($itemObject->orderitem_name,
			$itemObject->orderitem_name,$itemObject->orderitem_quantity,$itemObject->orderitem_price);
			// in argument of GoogleItem first itemname , itemDescription,quantity, unti price
			$cart->AddItem($item_temp);
			$totalTax=$totalTax+$itemObject->orderitem_tax;
		}
		
		if ( !empty($data['shipping_plugin'] ) && ( $order->order_shipping > 0 ) )
		{
			// Add shipping
			$shipTemp = new GooglePickup($data['shipping_name'], $order->order_shipping + $order->order_shipping_tax); // shipping name and Price as an argument
			$cart->AddShipping($shipTemp);
		}
		
		
		
		//adjust the tax rate if discount is available
		if($order->order_discount > 0 )
		{
			$taxRate = $this->taxRateAdjustment( $order->order_subtotal, $order->order_discount, $order->order_tax);
		}
		else 
		{
			//compute the tax rate for default tax
			$totalOrder = $order->order_total - ($order->order_shipping + $order->order_shipping_tax);
			$taxRate = $this->calcDefaultTaxRate($totalOrder, $order->order_subtotal);
		}
					     
	    // Set default tax options
	    $tax_rule = new GoogleDefaultTaxRule($taxRate);
	    $tax_rule->SetWorldArea(true);
	    $cart->AddDefaultTaxRules($tax_rule);
		
		$reponseURL = JURI::root() ."plugins/tienda/{$this->_element}/library/responsehandler.php";		
		
		// Add merchant calculations options
    	$cart->SetMerchantCalculations(
		"{$reponseURL}", // merchant-calculations-url
        "true", // merchant-calculated tax
        "false", // accept-merchant-coupons
        "false"); // accept-merchant-gift-certificates

		$checkout_return_url = JURI::root() ."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=display_message";
		$cart->SetContinueShoppingUrl($checkout_return_url);

		// set oredr id and the Data
		$mcprivatedata= new MerchantPrivateData();
		$mcprivatedata->data= array("orderPaymentId"=>$data['orderpayment_id']);
		$cart->SetMerchantPrivateData($mcprivatedata);
		$vars->cart=$cart;

		$html = $this->_getLayout('prepayment', $vars);
		return $html;
	}
	
	/**
	 * 
	 * Method to calculate the default tax rate
	 * @param float $totalPrice
	 * @param float $netPrice
	 * @return float
	 */
	function calcDefaultTaxRate($totalPrice, $netPrice)
	{
		$taxRate = ($totalPrice/$netPrice) -1; 
		
		return $taxRate;
	}

	/**
	 * 
	 * Method to calculate the tax rate if coupon is available
	 * Since we passed the coupon as item with negative value so google also calculate the tax 
	 * googtax = ItemsNetPrice - CouponValue
	 * 
	 * @param float $itemNetPrice
	 * @param float $couponValue
	 * @param float $taxRateSite
	 * @return float
	 */
	function taxRateAdjustment($itemNetPrice, $couponValue, $taxRateSite)
	{
		//check if coupon value is greater than the items net price to avoid having negative tax on google
		//supposedly we will not end up here since the order becomes free
		if($couponValue > $itemNetPrice) return 0;
		
		$taxRate = $taxRateSite / ($itemNetPrice - $couponValue);
		
		return $taxRate;
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
		$vars = new JObject();
		$html="";
		switch ($paction)
		{
			case "display_message":
				$html .= $this->_renderHtml( JText::_('GOOGLECHECKOUT MESSAGE PAYMENT ACCEPTED FOR VALIDATION') );
				$html .= $this->_displayArticle();
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

		if (empty($url) || !JURI::isInternal($url))
		{
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
	
	/**
	 * 
	 * This method calls on the notify url of the Google checkout and performed the  task accordingly
	 * 
	 */

	function _process()
	{
		require_once dirname(__FILE__) . "/{$this->_element}/library/googleresponse.php";
		require_once dirname(__FILE__) . "/{$this->_element}/library/googleresult.php";
		require_once dirname(__FILE__) . "/{$this->_element}/library/googlerequest.php";

		$response = new GoogleResponse($this->_getParam('merchant_id'), $this->_getParam('merchant_key'));
		
		// setup the log files
		if ($this->_isLog)
		{
			$path = JPATH_ROOT . '/cache';

			$response->SetLogFiles($path . '/google_error.log', $path . '/google_message.log', L_ALL);
			$this->_logObj =& $response->log;
		}

		// retrieve the XML sent in the HTTP POST request to the ResponseHandler
		$xml_response = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents('php://input');
		if ( ! $xml_response)
		{
			echo "No response received', 'error'";
			die('No response received');
		}

		if (get_magic_quotes_gpc()) {
			$xml_response = stripslashes($xml_response);
		}

		list($root, $data) = $response->GetParsedXML($xml_response);

		// TODO Need to Check the Header Information of the request for Authentication
		
		//		// validate the data (comment for testing)
		//				$response->SetMerchantAuthentication($this->_getParam('merchant_id'), $this->_getParam('merchant_key'));
		//				if ( ! $response->HttpAuthentication()) {
		//					$this->_log('Authentication failed', 'error');
		//					die('Authentication failed');
		//				}
		
		// prepare the payment data
		$data = $data[$root];
		$payment_details = $this->_getFormattedPaymentDetails($xml_response);

		// process the payment
		$error = '';
		// svae the goggole orderid in the transaction id
		if ($root == 'new-order-notification')
		{
			$payment_error = $this->_saveTransaction( $data, $error );
		}

		if ($root == 'order-state-change-notification')
		{	// it's amount charged
			if( $data ['new-financial-order-state']['VALUE']=='CHARGED')
			{
				$payment_error = $this->_processSale( $data, $error,$payment_details );
				$response->SendAck();
			}

		}

		$error = 'processed';
		return $error;
	}

		/**
		 *  this is updating the transaction id with the Google Order id
		 *
		 */
		function _saveTransaction($data, $error='')
		{
			$errors = array();
			JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
			$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
			$oredrPaymentId=$data['shopping-cart']['merchant-private-data']['orderPaymentId']['VALUE'];
		
			$orderpayment->load($oredrPaymentId);
			if (empty($orderpayment->orderpayment_id))
			{
				$errors[] = JText::_('GOOGLE CHECKOUT INVALID ORDERPAYMENTID');
				return count($errors) ? implode("\n", $errors) : '';
			}
		
			$orderpayment->transaction_id = $data['google-order-number']['VALUE'];
		
			// update the orderpayment
			if (!$orderpayment->save())
			{
				$errors[] = $orderpayment->getError();
			}
			// set the order's new status and update quantities if necessary
			Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
			Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
			$order = JTable::getInstance('Orders', 'TiendaTable');
		
			$order->load( $orderpayment->order_id );
			// if the transaction has the "pending" status,
			$order->order_state_id = TiendaConfig::getInstance('pending_order_state', '1'); // PENDING
		
			// Update quantities for echeck payments
			TiendaHelperOrder::updateProductQuantities( $orderpayment->order_id, '-' );
		
			// remove items from cart
			TiendaHelperCarts::removeOrderItems( $orderpayment->order_id );
		
			// send email
			$send_email = true;
		
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
		 * Processes the sale payment
		 *
		 * @param array $data Google Respnse data
		 * @return boolean Did the Response Validate ?
		 * @access protected
		 */
		function _processSale( $data, $error='' , $payment_details)
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
				
			$googleOrderNumber=$data['google-order-number']['VALUE'];
				
			// Loading the order payment on ht basis of the google-order-number
				
			$orderpayment->load(array ('transaction_id'=> $googleOrderNumber) );
		
			if (empty($orderpayment->orderpayment_id))
			{
				$errors[] = JText::_('GOOGLE CHECKOUT INVALID GOOGLE CHECKOUT ORDER ID');
				return count($errors) ? implode("\n", $errors) : '';
			}
				
			//	Svaing Financial order state
			$orderpayment->transaction_details  = $payment_details;
		
			// Svaing payment status Completed
			$orderpayment->transaction_status   = "Completed";
		
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
			else
			{
				$order->order_state_id = $this->params->get('payment_received_order_state', '17');; // PAYMENT RECEIVED

                // do post payment actions
                $setOrderPaymentReceived = true;
		
				// send email
				$send_email = true;
			}
		
			// update the order
			if (!$order->save())
			{
				$errors[] = $order->getError();
			}
		
			// update the orderpayment
			if (!$orderpayment->save())
			{
				$errors[] = $orderpayment->getError();
			}
			
		    if (!empty($setOrderPaymentReceived))
            {
                $this->setOrderPaymentReceived( $orderpayment->order_id );
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
			if ($this->_logObj !== null)
			{
				if ($type == 'error') {
					$this->_logObj->logError($text);
				}
				else {
					$this->_logObj->LogResponse($text);
				}
			}
		}
		
		/**
		 * Formatts the payment data before storing
		 *
		 * @param string $data XML data
		 * @return string
		 */
		function _getFormattedPaymentDetails($data)
		{
			$data = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $data);
			$data = str_replace(array('<', '>'), array('[', ']'), $data);
		
			return $data;
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
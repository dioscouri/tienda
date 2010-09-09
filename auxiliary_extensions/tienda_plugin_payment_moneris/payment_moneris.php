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


/** Import library dependencies */

require_once "payment_moneris/mpgClasses.php";

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_moneris extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = 'payment_moneris';

	// test values
	//var $_store_id = 'store1';
	//var $_api_token = 'yesguy';
	var $_store_id = null;
	var $_api_token = null;

	/**
	 * The ID number of the item being purchased
	 * @var int
	 */
	var $_item_number = null;

	/**
	 * @var string
	 * @access protected
	 */
	var $_payment_type = 'payment_moneris';

	/**
	 * @var array
	 * @access protected
	 */
	var $_recurring_fields = array(
        'is_recurring' => '1', // is the subscription type recurring? 
		'moneris_recurring_times' => '6', //The number of times the subscription recurs
		'recurring_period_unit' => 'month', // day | week | month
        'recurring_period' => '1' // The interval. "1" here + "month" above results in a monthly subscription. "1" here + "week" above results in a weekly subscription.
	);

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
	function plgTiendaPayment_moneris(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );

		// Set class vars based on plugin's XML parameters
		$this->_store_id = $this->params->get( 'store_id' );
		$this->_api_token = $this->params->get( 'api_token' );
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

		var_dump($data);

		$vars = new JObject();
		$vars->url = JRoute::_( "index.php?option=com_tienda&view=checkout" );
		$vars->form = new JObject();
		$vars->form->action = JRoute::_( "index.php?option=com_tienda&controller=checkout&task=confirmPayment&ptype={$this->_payment_type}&paction=process", false, $this->params->get( 'secure_post', '0' ) );

		$vars->order_id = $data['order_id'];
		$vars->orderpayment_id = $data['orderpayment_id'];
		$vars->orderpayment_amount = $data['orderpayment_amount'];

		$vars->orderpayment_type = $this->_element;
		$vars->cardtype =JRequest::getVar("card_type");
		$vars->cardnum = JRequest::getVar("card_number");

		// converting dat in the MMYY format

		if( strlen(JRequest::getVar("expiration_month"))==1)
		{
			$date="0".(String)JRequest::getVar("expiration_month");
		}
		else
		{
			$date=JRequest::getVar("expiration_month");
		}

		if( strlen(JRequest::getVar("expiration_year"))==4)
		{
			$date = $date . substr(JRequest::getVar("expiration_year"),0,2);
		}
		else {
			$date = $date .JRequest::getVar("expiration_year");
		}

		$expire_date=$this->_getFormattedCardExprDate('my',$date);

		$vars->cardexp =$expire_date;


		$vars->cardcvv = JRequest::getVar("cvv_number");
		$vars->cardnum_last4 = substr( JRequest::getVar("card_number"), -4 );
		$vars->data =$data;
	  

		$html = $this->_getLayout('prepayment', $vars);
		return $html;

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
					$vars->message = JText::_('PAYPAL MESSAGE PAYMENT ACCEPTED FOR VALIDATION');
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
				$vars->message = JText::_( 'Paypal Message Cancel' );
				$html = $this->_getLayout('message', $vars);
				break;
			default:
				$vars->message = JText::_( 'Paypal Message Invalid Action' );
				$html = $this->_getLayout('message', $vars);
				break;
		}

		return $html;
	}
	/**
	 * Generates a dropdown list of valid CC types
	 * @param $fieldname
	 * @param $default
	 * @param $options
	 * @return unknown_type
	 */
	function _cardTypesField( $field='card_type', $default='', $options='' )
	{
		$types = array();
		$types[] = JHTML::_('select.option', 'Visa', JText::_( "Visa" ) );
		$types[] = JHTML::_('select.option', 'Mastercard', JText::_( "Mastercard" ) );
		$types[] = JHTML::_('select.option', 'AmericanExpress', JText::_( "American Express" ) );
		$types[] = JHTML::_('select.option', 'Discover', JText::_( "Discover" ) );
		$types[] = JHTML::_('select.option', 'DinersClub', JText::_( "Diners Club" ) );
		$types[] = JHTML::_('select.option', 'JCB', JText::_( "JCB" ) );

		$return = JHTML::_('select.genericlist', $types, $field, $options, 'value','text', $default);
		return $return;
	}

	/**
	 * Prepares variables for the payment form
	 *
	 * @param $data     array       form post data for pre-populating form
	 * @return string   HTML to display
	 */

	function _renderForm( $data )
	{
		$user = JFactory::getUser();
		$vars = new JObject();
		$vars->selectCardType = $this->_cardTypesField();
		$html = $this->_getLayout('form', $vars);
		return $html;
	}



	/**
	 *
	 * @param $moneris_values
	 * @return unknown_type
	 */
	function _verifyForm( $data )
	{
		Tienda::load( 'TiendaHelperUser', 'helpers.user' );
		$object = new JObject();
		$object->error = false;
		$object->message = '';
		$user = JFactory::getUser();

		$required = array(
            'first_name', 'last_name', 'address_line_1', 'city', 'state', 'postal_code', 
            'card_type', 'card_number', 'expiration_month', 'expiration_year', 'cvv_number'
            );

            // verify the fields in the form
            // if any fail verification, set
            // $object->error = true
            // $object->message .= '<li>x item failed verification</li>'
            foreach ($data as $key=>$value)
            {
            	switch (strtolower($key))
            	{
            		case "email":
            			if (!$user->id) {
            				if (!isset($data[$key]) || !JString::strlen($data[$key])) {
            					$object->error = true;
            					$object->message .= "<li>".JText::_( "Email Address Required" )."</li>";
            				}
            				if ($emailExists = TiendaHelperUser::emailExists($data[$key])) {
            					$object->error = true;
            					$object->message .= '<li>'.JText::_( 'Email Exists' ).'</li>';
            				}
            				jimport( 'joomla.mail.helper' );
            				if (!$isValidEmail = JMailHelper::isEmailAddress($data[$key])) {
            					$object->error = true;
            					$object->message .= "<li>".JText::_( "Email Address Invalid" )."</li>";
            				}
            			}
            			break;
            		default:
            			if (in_array($key, $required) && empty($value))
            			{
            				$object->error = true;
            				$object->message .= "<li>".JText::_( "Invalid ".$key )."</li>";
            			}
            			break;
            	}
            }

            return $object;
	}


	/**
	 * Formats the value of the card expiration date
	 *
	 * @param string $format
	 * @param $value
	 * @return string|boolean date string or false
	 * @access protected
	 */
	function _getFormattedCardExprDate($format, $value)
	{
		// we assume we received a $value in the format MMYY
		$month = substr($value, 0, 2);
		$year = substr($value, 2);

		if (strlen($value) != 4 || empty($month) || empty($year) || strlen($year) != 2) {
			return false;
		}

		$date = date($format, mktime(0, 0, 0, $month, 1, $year));
		return $date;
	}

	/************************************
	 * Note to 3pd:
	 *
	 * The methods between here
	 * and the next comment block are
	 * specific to this payment plugin
	 *
	 ************************************/

	/**
	 * Gets the Paypal gateway URL
	 *
	 * @param boolean $full
	 * @return string
	 * @access protected
	 */
	function _getPostUrl($full = true)
	{
		$url = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

		if ($full)
		{
			$url = 'https://' . $url . '/cgi-bin/webscr';
		}

		return $url;
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
	 * Validates the IPN data
	 *
	 * @param array $data
	 * @return string Empty string if data is valid and an error message otherwise
	 * @access protected
	 */
	function _validateIPN( $data )
	{
		$secure_post = $this->params->get( 'secure_post', '0' );
		$paypal_url = $this->_getPostUrl(false);

		$req = 'cmd=_notify-validate';
		foreach ($data as $key => $value) {
			if ($key != 'view' && $key != 'layout') {
				$value = urlencode($value);
				$req .= "&$key=$value";
			}
		}

		// post back to PayPal system to validate
		$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		//$header .= "Host: " . $this->_getPostURL(false) . ":443\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

		if ($secure_post) {
			// If possible, securely post back to paypal using HTTPS
			// Your PHP server will need to be SSL enabled
			$fp = fsockopen ('ssl://' . $paypal_url , 443, $errno, $errstr, 30);
		}
		else {
			$fp = fsockopen ($paypal_url, 80, $errno, $errstr, 30);
		}

		if ( ! $fp) {
			return JText::sprintf('PAYPAL ERROR POSTING IPN DATA BACK', $errstr, $errno);
		}
		else {
			fputs ($fp, $header . $req);
			while ( ! feof($fp)) {
				$res = fgets ($fp, 1024); //echo $res;
				if (strcmp ($res, 'VERIFIED') == 0) {
					return '';
				}
				elseif (strcmp ($res, 'INVALID') == 0) {
					return JText::_('PAYPAL ERROR IPN VALIDATION');
				}
			}
		}
			
		fclose($fp);
		return '';
	}

	/**
	 *
	 * @return HTML
	 */
	function _process()
	{


		$data = JRequest::get('post');

		 
		//    	$item_number = JRequest::getVar( 'item_number', '', 'post', 'int' );
		//        $subtype = AmbrasubsHelperPayment::getTable( 'Type' );
		//        $subtype->load( $item_number );
		//        $subtypeParams = $this->_getSubscriptionParams( $subtype->params );
		 
		$order = JTable::getInstance('Orders', 'TiendaTable');
		$order->load( $data['order_id'] );
		$items = $order->getItems();
		$orderpayment_id = $data['orderpayment_id'];
		$orderpayment_amount = $data['orderpayment_amount'];

		/************************ Request Variables ***************************/

		$store_id = $this->_store_id;
		$api_token = $this->_api_token;

		/********************* Transactional Variables ************************/

		$type = 'purchase';
		$order_id = $data['order_id'];
		$cust_id = JFactory::getUser()->id;
		$amount = $orderpayment_amount;
		$pan = $data['cardnum']; // '4242424242424242';
		$expiry_date = $data['expiration_year'] . $data['expiration_month'];        // YYMM, so 0812 = December 2008
		$crypt = '7'; // SSL-enabled merchant
		$commcard_invoice = '';
		$commcard_tax_amount = '';

		/******************* Customer Information Variables ********************/

		$first_name = $data['first_name'];
		$last_name = $data['last_name'];
		$company_name = '';
		$address = $data['address_line_1'];
		if ($data['address_line_2']){
			$address .= ", " . $data['address_line_2'];
		}
		$city = $data['city'];
		$province = $data['state'];
		$postal_code = $data['postal_code'];
		$country = @$data['country'];
		$phone_number = '';
		$fax = '';
		$tax1 = '';
		$tax2 = '';
		$tax3 = '';
		$shipping_cost = '';
		$email = JFactory::getUser()->email;
		$instructions = '';

		/*********************** Line Item Variables **************************/
		//
		//		$item_name[0] = $subtype->title;
		//		$item_quantity[0] = '1';
		//		$item_product_code[0] = $subtype->title;
		//		$item_extended_amount[0] = $subtype->value;

		/************************** Recur Variables *****************************/
		//        if ($subtypeParams->get('is_recurring'))
		//        {
		//	        $recurUnit = $subtypeParams->get('recurring_period_unit'); // (day | week | month)
		//	        $recurInterval = $subtypeParams->get('recurring_period'); // '10';
		//            $numRecurs = $subtypeParams->get('moneris_recurring_times'); //'4';
		//	        $recurAmount = $subtype->value;
		//	        $startNow = 'true';
		//            $startDate = date("Y/m/d"); // '2006/11/30'; //yyyy/mm/dd
		//
		//	        /****************************** Recur Array **************************/
		//	        $recurArray = array(
		//	                            'recur_unit'=>$recurUnit,
		//	                            'start_date'=>$startDate,
		//	                            'num_recurs'=>$numRecurs,
		//	                            'start_now'=>$startNow,
		//	                            'period' => $recurInterval,
		//	                            'recur_amount'=> $recurAmount
		//	        );
		//	        /****************************** Recur Object **************************/
		//	        $mpgRecur = new mpgRecur($recurArray);
		//        }

		/************************** AVS Variables *****************************/

		$avs_street_number = intval( $address );
		$avs_street_name = $address;
		$avs_zipcode = $postal_code;

		/************************** CVD Variables *****************************/

		$cvd_indicator = '1'; // yes, we're using CVD
		$cvd_value = $data['cardcvv'];

		/********************** AVS Associative Array *************************/

		$avsTemplate = array(
		                     'avs_street_number'=>$avs_street_number,
		                     'avs_street_name' =>$avs_street_name,
		                     'avs_zipcode' => $avs_zipcode
		);

		/********************** CVD Associative Array *************************/

		$cvdTemplate = array(
		                     'cvd_indicator' => $cvd_indicator,
		                     'cvd_value' => $cvd_value
		);

		/************************** AVS Object ********************************/

		$mpgAvsInfo = new mpgAvsInfo ($avsTemplate);

		/************************** CVD Object ********************************/

		$mpgCvdInfo = new mpgCvdInfo ($cvdTemplate);

		/******************** Customer Information Object *********************/

		$mpgCustInfo = new mpgCustInfo();

		/********************** Set Customer Information **********************/

		$billing = array(
		                 'first_name' => $first_name,
		                 'last_name' => $last_name,
		                 'company_name' => $company_name,
		                 'address' => $address,
		                 'city' => $city,
		                 'province' => $province,
		                 'postal_code' => $postal_code,
		                 'country' => $country,
		                 'phone_number' => $phone_number,
		                 'fax' => $fax,
		                 'tax1' => $tax1,
		                 'tax2' => $tax2,
		                 'tax3' => $tax3,
		                 'shipping_cost' => $shipping_cost
		);

		$mpgCustInfo->setBilling($billing);

		$shipping = array(
		                 'first_name' => $first_name,
		                 'last_name' => $last_name,
		                 'company_name' => $company_name,
		                 'address' => $address,
		                 'city' => $city,
		                 'province' => $province,
		                 'postal_code' => $postal_code,
		                 'country' => $country,
		                 'phone_number' => $phone_number,
		                 'fax' => $fax,
		                 'tax1' => $tax1,
		                 'tax2' => $tax2,
		                 'tax3' => $tax3,
		                 'shipping_cost' => $shipping_cost
		);

		$mpgCustInfo->setShipping($shipping);

		$mpgCustInfo->setEmail($email);
		$mpgCustInfo->setInstructions($instructions);

		/*********************** Set Line Item Information *********************/


		foreach($items as $itemObject)
		{
			$items_temp[0] = array(
		               'name'=>$itemObject->orderitem_name,
		               'quantity'=>$itemObject->orderitem_quantity,
		               'product_code'=>$itemObject->orderitem_name,   // NEED TO CONFIRM orderitem_sku
		               'extended_amount'=>$itemObject->orderitem_price
			);
			$mpgCustInfo->setItems($items_temp[0]);
		}




		/***************** Transactional Associative Array ********************/

		$txnArray=array(
		                'type'=>$type,
		                'order_id'=>$orderpayment_id,  // Set the $orderpayment_id since we will get back $order_id with it
		                'cust_id'=>$cust_id,
		                'amount'=>$amount,
		                'pan'=>$pan,
		                'expdate'=>$expiry_date,
		                'crypt_type'=>$crypt,
		                'commcard_invoice'=>$commcard_invoice,
		                'commcard_tax_amount'=>$commcard_tax_amount
		);

		/********************** Transaction Object ****************************/

		$mpgTxn = new mpgTransaction($txnArray);

		/******************** Set Customer Information ************************/

		$mpgTxn->setCustInfo($mpgCustInfo);

		/************************ Set AVS and CVD *****************************/

		$mpgTxn->setAvsInfo($mpgAvsInfo);
		$mpgTxn->setCvdInfo($mpgCvdInfo);

		/************************* Request Object *****************************/

		$mpgRequest = new mpgRequest($mpgTxn);

		/************************ HTTPS Post Object ***************************/

		$mpgHttpPost = new mpgHttpsPost( $store_id, $api_token, $mpgRequest);

		/****************8********** Response *********************************/

		$mpgResponse = $mpgHttpPost->getMpgResponse();

		return $this->_evaluateResponse( $mpgResponse );

		}
		/**
		 * Evaluates the response from the payment processor
		 * and returns html
		 *
		 * @param $response
		 * @return html
		 */
		function _evaluateResponse( $response )
		{
			$responseCode = $response->getResponseCode();
			 
			if (is_null($responseCode))
			{
				// not sent
				// invalid
				$error = JText::_( "Payment Request Not Sent" );
			}
			elseif ($responseCode < '50')
			{
				// approved
				$data = new JObject();
				$this->_fillPaymentStatusVars( $data, true );

				$data->user = JFactory::getUser();
				
				$data->transaction_id = $response->getTxnNumber();
				$data->payment_details = $this->_convertResponseToText( $response );
				
				// TODO 
				// Find out the Order payment Id
				$data->$orderpayment_id = 1111;
	    
				$error = $this->_processSale( $data, array() );
				if (empty($error))
				{
					// payment processed successfully
					$error = JText::_( " Processed Successfully" );
					$error .= $this->_displayArticle();
				}

			}
			elseif ($responseCode >= '50')
			{
				// declined
				$data = new JObject();
				$this->_fillPaymentStatusVars( $data, false );
				$data->user = JFactory::getUser();
				$data->transaction_id = $response->getTxnNumber();
				$data->payment_details = $this->_convertResponseToText( $response );
				
				// TODO 
				// Find out the Order payment Id
				$data->$orderpayment_id = 1111;
				$error = $this->_saveTransaction( $data, array(JText::_( "Payment Declined" )) );
				$error = JText::_( "Payment Declined" );
			}
			else
			{
				// should never end up here,
				// but is invalid if it does
				$error = JText::_( "Payment Invalid" );
			}
			 
			return $error;
		}

		/*
		 * Process to complete the sale 
		 * It will update the Oreder and order payment on the basis of the response 
		 * @param  data Array of response 
		 * @param  error 
		 */
		function _processSale( $data, $error='')
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

			$orderpayment->load($data->$orderpayment_id );

			//	Svaing Financial order state
			$orderpayment->transaction_details  = $data->payment_details;

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
				$this->setOrderPaymentReceived( $orderpayment->order_id );

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

			// TODO Send mail on payment charged
			//	if ($send_email)
			//	{
			//		// send notice of new order
			//		Tienda::load( "TiendaHelperBase", 'helpers._base' );
			//		$helper = TiendaHelperBase::getInstance('Email');
			//		$model = Tienda::getClass("TiendaModelOrders", "models.orders");
			//		$model->setId( $orderpayment->order_id );
			//		$order = $model->getItem();
			//		$helper->sendEmailNotices($order, 'new_order');
			//	}

			return count($errors) ? implode("\n", $errors) : '';
	}
	
		/**
		 *  this is updating the transaction id and staus in case of not completed state
		 *	@param  data Array of response 
		 *	@param  error
		 */
		function _saveTransaction($data, $error='')
		{
		
			$errors = array();

			if (!empty($error))
			{
				$errors[] = $error;
			}
			// load the orderpayment record and set some values
			JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
			$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');

			$orderpayment->load($data->$orderpayment_id );

			//	Svaing Financial order state
			$orderpayment->transaction_details  = $data->payment_details;

			// Svaing payment status Completed
			$orderpayment->transaction_status   = "Payment Declined";

			
			// update the orderpayment
			if (!$orderpayment->save())
			{
				$errors[] = $orderpayment->getError();
			}

			// TODO Send mail on payment charged
			//	if ($send_email)
			//	{
			//		// send notice of new order
			//		Tienda::load( "TiendaHelperBase", 'helpers._base' );
			//		$helper = TiendaHelperBase::getInstance('Email');
			//		$model = Tienda::getClass("TiendaModelOrders", "models.orders");
			//		$model->setId( $orderpayment->order_id );
			//		$order = $model->getItem();
			//		$helper->sendEmailNotices($order, 'new_order');
			//	}
		
			return count($errors) ? implode("\n", $errors) : '';
		}
	
	

}

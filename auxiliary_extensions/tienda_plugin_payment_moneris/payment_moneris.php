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

		// converting date in the MMYY format

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
			
		// saving the productpayment_id which will use to update the Transaction fail condition
		$session = JFactory::getSession();

		// After getiing the response if the transaction will fail it will used
		$session->set( 'orderpayment_id', $data['orderpayment_id'] );

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
			case "process":
				$vars->message = $this->_process();
				$html = $this->_getLayout('message', $vars);
				break;
			case "cancel":
				$vars->message = JText::_('MONERIS Message Cancel');
				$html = $this->_getLayout('message', $vars);
				break;
			default:
				$vars->message = JText::_('Paypal Message Invalid Action');
				$html = $this->_getLayout('MONERIS', $vars);
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
		$types[] = JHTML::_('select.option', 'Visa', JText::_('COM_TIENDA_VISA') );
		$types[] = JHTML::_('select.option', 'Mastercard', JText::_('COM_TIENDA_MASTERCARD') );
		$types[] = JHTML::_('select.option', 'AmericanExpress', JText::_('COM_TIENDA_AMERICANEXPRESS') );
		$types[] = JHTML::_('select.option', 'Discover', JText::_('COM_TIENDA_DISCOVER') );
		$types[] = JHTML::_('select.option', 'DinersClub', JText::_('Diners Club') );
		$types[] = JHTML::_('select.option', 'JCB', JText::_('COM_TIENDA_JCB') );

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
            					$object->message .= "<li>".JText::_('Email Address Required')."</li>";
            				}
            				if ($emailExists = TiendaHelperUser::emailExists($data[$key])) {
            					$object->error = true;
            					$object->message .= '<li>'.JText::_('Email Exists').'</li>';
            				}
            				jimport( 'joomla.mail.helper' );
            				if (!$isValidEmail = JMailHelper::isEmailAddress($data[$key])) {
            					$object->error = true;
            					$object->message .= "<li>".JText::_('Email Address Invalid')."</li>";
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

	/**
	 * Gets the value for the Moneris variable
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
	 *
	 * @return HTML
	 */
	function _process()
	{
		$data = JRequest::get('post');
		$order = DSCTable::getInstance('Orders', 'TiendaTable');
		$order->load( $data['order_id'] );
		$items = $order->getItems();
		$orderpayment_id = $data['orderpayment_id'];
		$orderpayment_amount = $data['orderpayment_amount'];
		$amount=$data['orderpayment_amount'];
		/************************ Request Variables ***************************/

		$store_id = $this->_store_id;
		$api_token = $this->_api_token;

		/************************** Recur Variables *****************************/

		$is_recurring = $order->isRecurring();

		// Check recurring items are present or not

		if ($is_recurring)
		{
			$vars->cmd = '_cart';
			$vars->mixed_cart = true;
			// Adjust the orderpayment amount since it's a mixed cart
			// first orderpayment is just the non-recurring items total
			// then upon return, ask user to checkout again for recurring items
			$recurAmount = $order->recurring_trial ? $order->recurring_trial_price : $order->recurring_amount;
			$recurInterval = $order->recurring_trial ? $order->recurring_trial_period_interval : $order->recurring_period_interval; // '10';
			$numRecurs = recurring_payments; //'4';
			$recurUnit = $order->recurring_trial ? $order->recurring_trial_period_unit : $order->recurring_period_unit;// (day | week | month)
			$orderpayment->orderpayment_amount = $orderpayment->orderpayment_amount - $recurAmount;
			$orderpayment->save();
			$amount = $orderpayment->orderpayment_amount;
			$startNow = 'true';
			$startDate = date("Y/m/d"); // '2006/11/30'; //yyyy/mm/dd

			/****************************** Recur Array **************************/
			$recurArray = array(
			                    'recur_unit'=>$recurUnit,
			                    'start_date'=>$startDate,
			                    'num_recurs'=>$numRecurs,
			                    'start_now'=>$startNow,
			                    'period' => $recurInterval,
			                    'recur_amount'=> $recurAmount
			);
		  /****************************** Recur Object **************************/
			$mpgRecur = new mpgRecur($recurArray);
		   }

			/****************** Transactional Variables ************************/

			$type = 'purchase';
				
			// genrate the unique Order Id to preserve the payment Id also
			$orderid ='ord-'.$orderpayment_id.'-'.date("dmy-G:i:s");



			//Check decimal (.) exist or not
			$temp_amount =explode ('.',$amount);
			if( count($temp_amount) <= 1){
				$amount=$amount.".0";
			}

			$cust_id = JFactory::getUser()->id;
			$pan = $data['cardnum']; // '4242424242424242';
			$expiry_date =  $data['cardexp']; // YYMM, so 0812 = December 2008
			$crypt = '7'; // SSL-enabled merchant
			$commcard_invoice = '';
			$commcard_tax_amount = '';

			/******************* Customer Information Variables ********************/
			$instructions = '';
			$billing = $this->_getBillingAddress($data) ;
			
			/************************** AVS Variables *****************************/

			$avs_street_number = intval( $billing['address'] );
			$avs_street_name = $billing['address'];
			$avs_zipcode = $billing['postal_code'];

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
			$mpgCustInfo->setBilling($billing);
			$shipping = $this->_getShippingAddress($data);
			$mpgCustInfo->setShipping($shipping);

			$email = JFactory::getUser()->email;
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
                 'order_id'=>$orderid,  // Set the $orderpayment_id since we will get back $order_id with it
				 'cust_id'=>$cust_id,
				 'amount'=>$amount,
				 'pan'=>$pan,
				 'expdate'=>$expiry_date,
				 'crypt_type'=>$crypt,
				 'commcard_invoice'=>'',
				 'commcard_tax_amount'=>''
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
				$error = JText::_('Payment Request Not Sent');
			}
			elseif ($responseCode < '50')
			{
				// approved
				$data = new JObject();
				$data->user = JFactory::getUser();

				$data->transaction_id = $response->getTxnNumber();
				$data->payment_details = $this->_convertResponseToText( $response );
				$data->transactionId = $response->getTxnNumber();


				// Find out the Order payment Id
				$reciptId= $response->getReceiptId();
				$temp_reciptid=explode("-",$reciptId);
				$data->orderpayment_id = $temp_reciptid[1];  // it was created as required at sending time


				$error = $this->_processSale( $data, array() );
				if (empty($error))
				{
					// payment processed successfully
					$error = JText::_(' Processed Successfully');
					$error .= $this->_displayArticle();
				}

			}
			elseif ($responseCode >= '50')
			{
				// declined
				$data = new JObject();
				$data->user = JFactory::getUser();
				$data->transaction_id = $response->getTxnNumber();
				$data->payment_details = $this->_convertResponseToText( $response );
				$data->transactionId = $response->getTxnNumber();

					
				// Find out the Order payment Id
				$reciptId= $response->getReceiptId();

				// since when the transaction fails it return null
				if($reciptId != "null")
				{
					$temp_reciptid=explode("-",$reciptId);
					$data->orderpayment_id = $temp_reciptid[1];  // it was created as required at sending time
				}
				else {
					// TODO when the response is comming null

				 $error = JText::_('Payment Declined Recipit could not recived or null  ');
				 // saving the orderpayment_id which will use to update the Transaction fail condition
				 //                $session = JFactory::getSession();
				 //                var_dump($session);
				 //				$data->orderpayment_id=; // Set the order pament Id from session which saved at the time of payment creation
				 return $error ;
				}
				$error = $this->_saveTransaction( $data, array(JText::_('Payment Declined')) );
				$error = JText::_('Payment Declined');
			}
			else
			{
				// should never end up here,
				// but is invalid if it does
				$error = JText::_('Payment Invalid');
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
			DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
			$orderpayment = DSCTable::getInstance('OrderPayments', 'TiendaTable');

			$orderpayment->load($data->orderpayment_id );

			//	Svaing Financial order state
			$orderpayment->transaction_details  = $data->payment_details;

			// Svaing payment status Completed
			$orderpayment->transaction_status   = $data->transactionId;


			// Svaing payment transaction_id as TXN number
			$orderpayment->transaction_id   = "Completed";

			// set the order's new status and update quantities if necessary
			Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
			Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
			$order = DSCTable::getInstance('Orders', 'TiendaTable');
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
		 *  this is updating the transaction id and staus in case of not completed state
		 *	@param  data Array of response
		 *	@param  error
		 */
		function _saveTransaction($data, $error='')
		{
            $send_email = false;
			$errors = array();
			if (!empty($error))
			{
				$errors[] = $error;
			}

			// load the orderpayment record and set some values
			DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
			$orderpayment = DSCTable::getInstance('OrderPayments', 'TiendaTable');

			$orderpayment->load($data->orderpayment_id );

			//	Svaing Financial order state
			$orderpayment->transaction_details  = $data->payment_details;

			// Svaing payment status Completed
			$orderpayment->transaction_status   = "Payment Declined";

			// Svaing payment status Completed
			$orderpayment->transaction_status   = $data->transactionId;
			;

			// update the orderpayment
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
		 *
		 * @param $response
		 * @return unknown_type
		 */
		function _convertResponseToText( $response )
		{
			$string = "";
			$string .= "\nCardType = " . $response->getCardType() ;
			$string .= "\nTransAmount = " . $response->getTransAmount();
			$string .= "\nTxnNumber = " . $response->getTxnNumber();
			$string .= "\nReceiptId = " . $response->getReceiptId();
			$string .= "\nTransType = " . $response->getTransType();
			$string .= "\nReferenceNum = " . $response->getReferenceNum();
			$string .= "\nResponseCode = " . $response->getResponseCode();
			$string .= "\nISO = " . $response->getISO();
			$string .= "\nMessage = " . $response->getMessage();
			$string .= "\nAuthCode = " . $response->getAuthCode();
			$string .= "\nComplete = " . $response->getComplete();
			$string .= "\nTransDate = " . $response->getTransDate();
			$string .= "\nTransTime = " . $response->getTransTime();
			$string .= "\nTicket = " . $response->getTicket();
			$string .= "\nTimedOut = " . $response->getTimedOut();
			$string .= "\nRecurSuccess = " . $response->getRecurSuccess();
			return $string;
		}

		/*
		 * Get the Billing Array from the Order info
		 */

		function _getBillingAddress($data)
		{
			// order info
			$orderinfo = DSCTable::getInstance('OrderInfo', 'TiendaTable');
			$orderinfo->load( array( 'order_id'=>$data['order_id']) );

			DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
			$order = DSCTable::getInstance('Orders', 'TiendaTable');
			$order->load( $data['order_id'] );

			$address = $orderinfo->billing_address_1;
			if ($orderinfo->billing_address_2){
				$address .= ", " . $orderinfo->billing_address_1;
			}
			$phone_number = '';
			$fax = '';
			$tax1 = '';
			$tax2 = '';
			$tax3 = '';
			$shipping_cost = $order->order_shipping;
			$email = JFactory::getUser()->email;
			$instructions = '';

			$billing = array(
			                 'first_name' => $orderinfo->billing_first_name,
			                 'last_name' =>$orderinfo->billing_last_name,
			                 'company_name' =>$orderinfo->billing_company,
			                 'address' => $address,
			                 'city' => $orderinfo->billing_city,
			                 'province' => $orderinfo->billing_zone_name,
			                 'postal_code' => $orderinfo->billing_postal_code,
			                 'country' =>$orderinfo->billing_country_name,
			                 'phone_number' => $phone_number,
			                 'fax' => $fax,
			                 'tax1' => $tax1,
			                 'tax2' => $tax2,
			                 'tax3' => $tax3,
			                 'shipping_cost' => $shipping_cost
			);// "5424000000000015";


			return $billing;
		}


		/*
		 * Get the Shiiping Array from the Order info
		 */

		function _getShippingAddress($data)
		{
			/// order info
			$orderinfo = DSCTable::getInstance('OrderInfo', 'TiendaTable');
			$orderinfo->load( array( 'order_id'=>$data['order_id']) );

			DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
			$order = DSCTable::getInstance('Orders', 'TiendaTable');
			$order->load( $data['order_id'] );

			$address = $orderinfo->shipping_address_1;
			if ($orderinfo->shipping_address_2){
				$address .= ", " . $orderinfo->shipping_address_1;
			}
			$phone_number = '';
			$fax = '';
			$tax1 = '';
			$tax2 = '';
			$tax3 = '';
			$shipping_cost = $order->order_shipping;
			$email = JFactory::getUser()->email;
			$instructions = '';

			$shipping =array();
			// Check shipping Address is present or not
			if ($orderinfo->shipping_first_name == null){

				$shipping = $this->_getBillingAddress($data);
			}
			else {

				$shipping = array(
			                'first_name' => $orderinfo->shipping_first_name,
			                 'last_name' =>$orderinfo->shipping_last_name,
			                 'company_name' =>$orderinfo->shipping_company,
			                 'address' => $address,
			                 'city' => $orderinfo->shipping_city,
			                 'province' => $orderinfo->shipping_zone_name,
			                 'postal_code' => $orderinfo->shipping_postal_code,
			                 'country' =>$orderinfo->shipping_country_name,
			                 'phone_number' => $phone_number,
			                 'fax' => $fax,
			                 'tax1' => $tax1,
			                 'tax2' => $tax2,
			                 'tax3' => $tax3,
			                 'shipping_cost' => $shipping_cost
				);// "5424000000000015";

			}
			return $shipping;
		}

	}

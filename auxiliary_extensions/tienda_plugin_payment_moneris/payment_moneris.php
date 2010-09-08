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
		echo "I am calling Jnau ji ";
		die();
		
		$data = JRequest::get('post');

		// validate the IPN info
		$error = $this->_validateIPN($data);
		if (!empty($error))
		{
			// ipn Validation failed
			$data['ipn_validation_results'] = $error;
		}

		// prepare some data
		$order_id           = (int) @$data['item_number_1'];
		$orderpayment_id    = (int) @$data['custom'];
		$data['transaction_details'] = $this->_getFormattedTransactionDetails( $data );

		// process the payment based on its type
		if ( !empty($data['txn_type']) )
		{
			$payment_error = '';

			if ($data['txn_type'] == 'cart') {
				// Payment received for multiple items; source is Express Checkout or the PayPal Shopping Cart.
				$payment_error = $this->_processSale( $data, $error );
			}
			elseif (strpos($data['txn_type'], 'subscr_') === 0) {
				$payment_error = $this->_processSubscription( $data, $error );
			}
			else {
				// other methods not supported right now
				$payment_error = JText::_( "PAYPAL ERROR INVALID TRANSACTION TYPE" ).": ".$data['txn_type'];
			}

			if ($payment_error) {
				// it seems like an error has occurred during the payment process
				$error .= $error ? "\n" . $payment_error : $payment_error;
			}
		}

		if ($error) {
			// send an emails to site's administrators with error messages
			$this->_sendErrorEmails($error, $data['transaction_details']);
			return $error;
		}

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
	function _processSale( $data, $ipnValidationFailed='' )
	{
		/*
		 * validate the payment data
		 */
		$errors = array();

		if (!empty($ipnValidationFailed))
		{
			$errors[] = $ipnValidationFailed;
		}

		// is the recipient correct?
		if (empty($data['receiver_email']) || $data['receiver_email'] != $this->_getParam( 'merchant_email' )) {
			$errors[] = JText::_('PAYPAL MESSAGE RECEIVER INVALID');
		}

		if (empty($data['custom']))
		{
			$errors[] = JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID');
			return count($errors) ? implode("\n", $errors) : '';
		}

		// load the orderpayment record and set some values
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
		$orderpayment->load( $data['custom'] );
		if (empty($data['custom']) || empty($orderpayment->orderpayment_id))
		{
			$errors[] = JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID');
			return count($errors) ? implode("\n", $errors) : '';
		}
		$orderpayment->transaction_details  = $data['transaction_details'];
		$orderpayment->transaction_id       = $data['txn_id'];
		$orderpayment->transaction_status   = $data['payment_status'];
			
		// check the stored amount against the payment amount
		$stored_amount = number_format( $orderpayment->get('orderpayment_amount'), '2' );
		if ((float) $stored_amount !== (float) $data['mc_gross']) {
			$errors[] = JText::_('PAYPAL MESSAGE AMOUNT INVALID');
		}

		// check the payment status
		if (empty($data['payment_status']) || ($data['payment_status'] != 'Completed' && $data['payment_status'] != 'Pending')) {
			$errors[] = JText::sprintf('PAYPAL MESSAGE STATUS INVALID', @$data['payment_status']);
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
	 * Processes the subscription payment
	 *
	 * @param array $data IPN data
	 * @return boolean Did the IPN Validate?
	 * @access protected
	 */
	function _processSubscription( $data, $ipnValidationFailed='' )
	{
		/*
		 * validate the payment data
		 */
		$errors = array();

		if (!empty($ipnValidationFailed))
		{
			$errors[] = $ipnValidationFailed;
		}

		// is the recipient correct?
		if (empty($data['receiver_email']) || $data['receiver_email'] != $this->_getParam( 'merchant_email' )) {
			$errors[] = JText::_('PAYPAL MESSAGE RECEIVER INVALID');
		}

		// Evaluate the payment based on txn_type, mc_gross, subscr_id
		switch ($data['txn_type'])
		{
			case 'subscr_signup':
				if (!$this->_processSubscriptionSignup( $data ))
				{
					$errors[] = $this->getError();
				}
				break;
			case 'subscr_payment':
				if (!$this->_processSubscriptionPayment( $data ))
				{
					$errors[] = $this->getError();
				}
				break;
			case 'subscr_eot':
				if (!$this->_processSubscriptionEndOfTerm( $data ))
				{
					$errors[] = $this->getError();
				}
				break;
			case 'subscr_cancel':
				if (!$this->_processSubscriptionCancel( $data ))
				{
					$errors[] = $this->getError();
				}
				break;
			case 'subscr_modify':
				if (!$this->_processSubscriptionModify( $data ))
				{
					$errors[] = $this->getError();
				}
				break;
			case 'subscr_failed':
				if (!$this->_processSubscriptionFailed( $data ))
				{
					$errors[] = $this->getError();
				}
				break;
			default:
				$errors[] = JText::_('PAYPAL MESSAGE INVALID TRANSACTION TYPE');
				break;
		}

		return count($errors) ? implode("\n", $errors) : '';

		/** TODO Remove the rest of this **/
			
		// check the stored amount against the payment amount
		$stored_amount = number_format( $orderpayment->get('orderpayment_amount'), '2' );
		if ((float) $stored_amount !== (float) $data['mc_gross']) {
			$errors[] = JText::_('PAYPAL MESSAGE AMOUNT INVALID');
		}

		// check the payment status
		if (empty($data['payment_status']) || ($data['payment_status'] != 'Completed' && $data['payment_status'] != 'Pending')) {
			$errors[] = JText::sprintf('PAYPAL MESSAGE STATUS INVALID', @$data['payment_status']);
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
	 * Formatts the payment data for storing
	 *
	 * @param array $data
	 * @return string
	 */
	function _getFormattedTransactionDetails( $data )
	{
		$separator = "\n";
		$formatted = array();

		foreach ($data as $key => $value)
		{
			if ($key != 'view' && $key != 'layout')
			{
				$formatted[] = $key . ' = ' . $value;
			}
		}

		return count($formatted) ? implode("\n", $formatted) : '';
	}

	/**
	 * Sends error messages to site administrators
	 *
	 * @param string $message
	 * @param string $paymentData
	 * @return boolean
	 * @access protected
	 */
	function _sendErrorEmails($message, $paymentData)
	{
		$mainframe =& JFactory::getApplication();

		// grab config settings for sender name and email
		$config     = &TiendaConfig::getInstance();
		$mailfrom   = $config->get( 'emails_defaultemail', $mainframe->getCfg('mailfrom') );
		$fromname   = $config->get( 'emails_defaultname', $mainframe->getCfg('fromname') );
		$sitename   = $config->get( 'sitename', $mainframe->getCfg('sitename') );
		$siteurl    = $config->get( 'siteurl', JURI::root() );

		$recipients = $this->_getAdmins();
		$mailer =& JFactory::getMailer();

		$subject = JText::sprintf('PAYPAL EMAIL PAYMENT NOT VALIDATED SUBJECT', $sitename);

		foreach ($recipients as $recipient)
		{
			$mailer = JFactory::getMailer();
			$mailer->addRecipient( $recipient->email );

			$mailer->setSubject( $subject );
			$mailer->setBody( JText::sprintf('PAYPAL EMAIL PAYMENT FAILED BODY', $recipient->name, $sitename, $siteurl, $message, $paymentData) );
			$mailer->setSender(array( $mailfrom, $fromname ));
			$sent = $mailer->send();
		}

		return true;
	}

	/**
	 * Gets admins data
	 *
	 * @return array|boolean
	 * @access protected
	 */
	function _getAdmins()
	{
		$db =& JFactory::getDBO();
		$q = "SELECT name, email FROM #__users "
		. "WHERE LOWER(usertype) = \"super administrator\" "
		. "AND sendEmail = 1 "
		;
		$db->setQuery($q);
		$admins = $db->loadObjectList();

		if ($error = $db->getErrorMsg()) {
			JError::raiseError(500, $error);
			return false;
		}

		return $admins;
	}

	/**
	 *
	 * Enter description here ...
	 * @param $data
	 * @return unknown_type
	 */
	function _processSubscriptionSignup( $data )
	{
		// the user has created a new subscription profile.
		// these are IMMEDIATELY followed by a subscr_payment IPN notification,
		// EXCEPT if the subscription has a FREE trial.
		// In the case of a FREE trial, ONLY a subscr_signup IPN is sent,
		// so code accordingly

		$errors = array();
		// Check that custom (orderpayment_id) is present, we need it for payment amount verification
		if (empty($data['custom']))
		{
			$this->setError( JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID') );
			return false;
		}
		// load the orderpayment record and set some values
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
		$orderpayment->load( $data['custom'] );
		if (empty($data['custom']) || empty($orderpayment->orderpayment_id))
		{
			$this->setError( JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID') );
			return false;
		}

		// if the payment amount is FREE
		// create new subscription for the user
		// for the order's recurring_trial_period_interval
		// using it's recurring_trial_period_unit
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$order = JTable::getInstance('Orders', 'TiendaTable');
		$order->load( $data['item_number'] );
		$items = $order->getItems();
		if (!empty($order->recurring_trial) && (float) $order->recurring_trial_price == (float) '0.00' )
		{
			$orderpayment->transaction_details  = $data['transaction_details'];
			$orderpayment->transaction_id       = $data['subscr_id'];
			$orderpayment->transaction_status   = JText::_( "Created" );
			if (!$orderpayment->save())
			{
				$errors[] = $orderpayment->getError();
			}

			if (count($items) == '1')
			{
				// update order status
				Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
				Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
				$order->order_state_id = $this->params->get('payment_received_order_state', '17');; // PAYMENT RECEIVED
				$this->setOrderPaymentReceived( $orderpayment->order_id );

				// send email
				$send_email = true;

				// save the order
				if (!$order->save())
				{
					$errors[] = $order->getError();
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
			}

			// Update orderitem_status
			$order_item = $order->getRecurringItem();
			$orderitem = JTable::getInstance('OrderItems', 'TiendaTable');
			$orderitem->orderitem_id = $order_item->orderitem_id;
			$orderitem->orderitem_status = '1';
			$orderitem->save();

			$date = JFactory::getDate();
			// create free subscription
			$subscription = JTable::getInstance('Subscriptions', 'TiendaTable');
			$subscription->user_id = $order->user_id;
			$subscription->order_id = $order->order_id;
			$subscription->product_id = $orderitem->product_id;
			$subscription->orderitem_id = $orderitem->orderitem_id;
			$subscription->transaction_id = $data['subscr_id'];
			$subscription->created_datetime = $date->toMySQL();
			$subscription->subscription_enabled = '1';

			switch($order->recurring_trial_period_unit)
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
			$database = JFactory::getDBO();
			$query = " SELECT DATE_ADD('{$subscription->created_datetime}', INTERVAL {$order->recurring_trial_period_interval} $period_unit ) ";
			$database->setQuery( $query );
			$subscription->expires_datetime = $database->loadResult();

			if (!$subscription->save())
			{
				$this->setError( $subscription->getError() );
				return false;
			}

			// add a sub history entry, email the user?
			$subscriptionhistory = JTable::getInstance('SubscriptionHistory', 'TiendaTable');
			$subscriptionhistory->subscription_id = $subscription->subscription_id;
			$subscriptionhistory->subscriptionhistory_type = 'creation';
			$subscriptionhistory->created_datetime = $date->toMySQL();
			$subscriptionhistory->notify_customer = '0'; // notify customer of new trial subscription?
			$subscriptionhistory->comments = JText::_( 'NEW TRIAL SUBSCRIPTION CREATED' );
			$subscriptionhistory->save();
		}

		$error = count($errors) ? implode("\n", $errors) : '';
		if (!empty($error))
		{
			$this->setError( $error );
			return false;
		}
		return true;
	}

	/**
	 *
	 * Enter description here ...
	 * @param $data
	 * @return unknown_type
	 */
	function _processSubscriptionPayment( $data )
	{
		// if we're here, a successful payment has been made.
		// the normal notice that requires action.
		// create a subscription_id if no subscr_id record exists
		// set expiration dates
		// add a sub history entry, email the user?

		$errors = array();
		// Check that custom (orderpayment_id) is present, we need it for payment amount verification
		if (empty($data['custom']))
		{
			$this->setError( JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID') );
			return false;
		}
		// load the orderpayment record and set some values
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
		$orderpayment->load( $data['custom'] );
		if (empty($data['custom']) || empty($orderpayment->orderpayment_id))
		{
			$this->setError( JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID') );
			return false;
		}
		$orderpayment->transaction_details  = $data['transaction_details'];
		$orderpayment->transaction_id       = $data['txn_id'];
		$orderpayment->transaction_status   = $data['payment_status'];
		if (!$orderpayment->save())
		{
			$errors[] = $orderpayment->getError();
		}

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$order = JTable::getInstance('Orders', 'TiendaTable');
		$order->load( $data['item_number'] );
		$items = $order->getItems();

		// Update orderitem_status
		$order_item = $order->getRecurringItem();
		$orderitem = JTable::getInstance('OrderItems', 'TiendaTable');
		$orderitem->orderitem_id = $order_item->orderitem_id;
		$orderitem->orderitem_status = '1';
		$orderitem->save();

		// TODO Here we need to verify the payment amount

		// if no subscription exists for this subscr_id,
		// create new subscription for the user
		$subscription = JTable::getInstance('Subscriptions', 'TiendaTable');
		$subscription->load( array('transaction_id'=>$data['subscr_id']));
		if (empty($subscription->subscription_id))
		{
			$date = JFactory::getDate();
			// create new subscription
			// if recurring trial, set it
			// for the order's recurring_trial_period_interval
			// using its recurring_trial_period_unit
			// otherwise, do the normal recurring_period_interval
			// and the recurring_period_unit
			$recurring_period_unit = $order->recurring_period_unit;
			$recurring_period_interval = $order->recurring_period_interval;
			if (!empty($order->recurring_trial))
			{
				$recurring_period_unit = $order->recurring_trial_period_unit;
				$recurring_period_interval = $order->recurring_trial_period_interval;
			}

			$subscription->user_id = $order->user_id;
			$subscription->order_id = $order->order_id;
			$subscription->product_id = $orderitem->product_id;
			$subscription->orderitem_id = $orderitem->orderitem_id;
			$subscription->transaction_id = $data['subscr_id'];
			$subscription->created_datetime = $date->toMySQL();
			$subscription->subscription_enabled = '1';
			switch($recurring_period_unit)
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
			$database = JFactory::getDBO();
			$query = " SELECT DATE_ADD('{$subscription->created_datetime}', INTERVAL {$recurring_period_interval} $period_unit ) ";
			$database->setQuery( $query );
			$subscription->expires_datetime = $database->loadResult();

			if (!$subscription->save())
			{
				$this->setError( $subscription->getError() );
				return false;
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
		else
		{
			// subscription exists, just update its expiration date
			// based on normal interval and period
			switch($order->recurring_period_unit)
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
			$database = JFactory::getDBO();
			$today = $date = JFactory::getDate();
			$query = " SELECT DATE_ADD('{$today}', INTERVAL {$order->recurring_period_interval} $period_unit ) ";
			$database->setQuery( $query );
			$subscription->expires_datetime = $database->loadResult();

			if (!$subscription->save())
			{
				$this->setError( $subscription->getError() );
				return false;
			}

			// add a sub history entry, email the user?
			$subscriptionhistory = JTable::getInstance('SubscriptionHistory', 'TiendaTable');
			$subscriptionhistory->subscription_id = $subscription->subscription_id;
			$subscriptionhistory->subscriptionhistory_type = 'payment';
			$subscriptionhistory->created_datetime = $date->toMySQL();
			$subscriptionhistory->notify_customer = '0'; // notify customer of new trial subscription?
			$subscriptionhistory->comments = JText::_( 'NEW SUBSCRIPTION PAYMENT RECEIVED' );
			$subscriptionhistory->save();
		}

		if (count($items) == '1')
		{
			// update order status
			Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
			Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
			$order->order_state_id = $this->params->get('payment_received_order_state', '17');; // PAYMENT RECEIVED
			$this->setOrderPaymentReceived( $orderpayment->order_id );

			// send email
			$send_email = true;

			// save the order
			if (!$order->save())
			{
				$errors[] = $order->getError();
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
		}

		$error = count($errors) ? implode("\n", $errors) : '';
		if (!empty($error))
		{
			$this->setError( $error );
			return false;
		}
		return true;
	}

	/**
	 *
	 * Enter description here ...
	 * @param $data
	 * @return unknown_type
	 */
	function _processSubscriptionEndOfTerm( $data )
	{
		// $data['custom'] is available

		$substr = substr($data['subscr_id'], 0, 2);
		switch($substr)
		{
			case "S-":
				//If your profile ID starts with S- then your EOT IPN works as follows:
				//If the profile is canceled then you get an EOT at the end of the time that the buyer paid for.
				//If the profile naturally ends then you get an EOT at the end of the time that the buyer paid for.
				break;
			case "I-":
				//If your profile ID starts with I- then your EOT IPN works as follows:
				//If the profile is canceled then you never get an EOT IPN.
				//If the profile naturally ends then you will get an EOT IPN right when the final payment is made.
				//You will not get an IPN when the time paid for is completed.
				//You will need to calculate that time period on your own
				//and then when the time the customer paid is up you no longer give them access to your service.
				break;

				// either way, Tienda does nothing
				// the sub's expires_datetime is already set from the last payment instance,
				// and Tienda will deactivate subscriptions in the system plugin
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param $data
	 * @return unknown_type
	 */
	function _processSubscriptionCancel( $data )
	{
		// do nothing.  user may be cancelling mid-period.
		// the sub's expires_datetime is already set from the last payment instance,
		// and Tienda will deactivate subscriptions in the system plugin

		// sample code for deactivating a subscription:
		//        // get the subscription_id based on the orderpayment_id=>order_id=>orderitem_id=>subscription_id
		//        $model = Tienda::getClass("TiendaModelSubscriptions", "models.subscriptions");
		//        $model->setState( 'filter_orderid', $data['item_number'] );
		//        $model->setState( 'filter_transactionid', $data['subscr_id'] );
		//        if (!$subscriptions = $model->getList())
		//        {
		//            // return some error
		//            $return = JText::_('PAYPAL MESSAGE NO RECURRING ITEM FOUND');
		//            return $return;
		//        }
		//        $subscription = $subscriptions[0];
		//
		//        // Cancel the subscription using the Subscription Helper and the subscription_id
		//        Tienda::load( "TiendaHelperBase", 'helpers._base' );
		//        $helper = TiendaHelperBase::getInstance('Subscription');
		//        if (!$helper->cancel( $subscription->subscription_id ))
		//        {
		//            $this->setError( $helper->getError() );
		//            return false;
		//        }
		//        return true;
}

/**
 *
 * Enter description here ...
 * @param $data
 * @return unknown_type
 */
function _processSubscriptionModify( $data )
{
	// we don't really do anything with this transaction type
}

/**
 * When one of the recurring payments for a subscription has failed
 * this method will be triggered
 *
 * @param $data
 * @return unknown_type
 */
function _processSubscriptionFailed( $data )
{
	// don't cancel the subscription.  an EOT will be triggered if all the payment retry attempts fail,
	// so cancel the subscription only in _processSubscriptionEndOfTerm (EOT)
	// TODO perhaps send an email when this happens? "hello, payment failed, want to check your credit card expiration?"
}


/* TYPICAL RESPONSE FROM PAYPAL INCLUDES:
 * mc_gross=49.99
 * &protection_eligibility=Eligible
 * &address_status=confirmed
 * &payer_id=Q5HTJ93G8FQKC
 * &tax=0.00
 * &address_street=10101+Some+Street
 * &payment_date=12%3A13%3A19+Dec+05%2C+2008+PST
 * &payment_status=Completed
 * &charset=windows-1252
 * &address_zip=11259
 * &first_name=John
 * &mc_fee=1.75
 * &address_country_code=US
 * &address_name=John+Doe
 * &custom=some+custom+value
 * &payer_status=verified
 * &business=receiver%40domain.com
 * &address_country=United+States
 * &address_city=Some+City
 * &quantity=1
 * &payer_email=sender%40emaildomain.com
 * &txn_id=3JK16594EX581780W
 * &payment_type=instant
 * &payer_business_name=John+Doe
 * &last_name=Doe
 * &address_state=CA
 * &receiver_email=receiver%40domain.com
 * &payment_fee=1.75
 * &receiver_id=YG9UDRP6DE45G
 * &txn_type=web_accept
 * &item_name=Name+of+item
 * &mc_currency=USD
 * &item_number=Number+of+Item
 * &residence_country=US
 * &handling_amount=0.00
 * &transaction_subject=Subject+of+Transaction
 * &payment_gross=49.99
 * &shipping=0.00
 * &=
 */

/**
 * VALID PAYMENT_STATUS VALUES returned from Paypal
 *
 * Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
 * Completed: The payment has been completed, and the funds have been added successfully to your account balance.
 * Created: A German ELV payment is made using Express Checkout.
 * Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
 * Expired: This authorization has expired and cannot be captured.
 * Failed: The payment has failed. This happens only if the payment was made from your customer’s bank account.
 * Pending: The payment is pending. See pending_reason for more information.
 * Refunded: You refunded the payment.
 * Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
 * Processed: A payment has been accepted.
 * Voided: This authorization has been voided.
 */

}

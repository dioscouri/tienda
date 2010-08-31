<?php
/**
 * @version	1.5
 * @package	Ambrasubs
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__) . '/../processor.php';

/**
 * Tienda PayPalPro ExpressCheckout Processor
 *
 * @package		Joomla 
 * @since 		1.5
 */
class plgTiendaPayment_Paypalpro_Processor_Expresscheckout extends plgTiendaPayment_Paypalpro_Processor
{
	/**
	 * @var array
	 */
	var $_response;
	
	/**	 
	 * @see plugins/tienda/payment_paypalpro/library/plgTiendaPayment_Paypalpro_Processor#validateData()
	 */
	function validateData($validate_token = true)
	{ 
		/*
		 * perform initial checks 
		 */
		if (!count($this->_data)) {
			$this->setError(JText::_('PaypalPro No Data is Provided'));
			return false;
		}
		
		if ($validate_token) {
			if (!JRequest::checkToken()) {
				$this->setError(JText::_('Invalid Token'));
				return false;
			}
		}
		
		if (!$this->getSubscrTypeObj()) {
			$this->setError(JText::_('Paypalpro Message Invalid Item Type'));
			return false;
		}
		
		if (!$this->_getParam('api_username') || !$this->_getParam('api_password') || !$this->_getParam('api_signature')) {
			$this->setError(JText::_('PaypalPro Message Merchant Credentials are invalid'));
			return false;	
		}
		
		return true;
	}
	
	/**	 
	 * Executes the setExpressCheckout process
	 * and redirects a user to PayPal website
	 */
	function processSetExpressCheckout()
	{
		// clear all possible error settings
		$this->_errors = array();	
		
		$subscr_type_params = $this->getSubscrTypeParams();
		if ($subscr_type_params->get('is_recurring')) {
			// process a recurring subscription sign-up
			$return = $this->_sendSetExpressCheckoutRecurringRequest();
		}
		else {
			// process a one-time (sale) subscription
			$return = $this->_sendSetExpressCheckoutRequest();
		}
				
		return $return;
	}
	
	/**
	 * Executes the DoExpressCheckout process
	 * and creates a subscription in Tienda
	 * 
	 * @return boolean
	 * @access public
	 */
	function processDoExpressCheckout()
	{
		// clear all possible error settings
		$this->_errors = array();
		
		$subscr_type_params = $this->getSubscrTypeParams();
		if ($subscr_type_params->get('is_recurring')) {
			// process a recurring subscription sign-up
			
			if ($this->_sendDoExpressCheckoutRecurringRequest()) {
				$return = $this->_evaluateDoExpressCheckoutRecurringResponse();
			}
			else {
				$return = false;
			}
		}
		else {
			// process a one-time (sale) subscription						
			if($this->_sendDoExpressCheckoutRequest()) {
				$return = $this->_evaluateDoExpressCheckoutResponse();
			}
			else {
				$return = false;
			}
		}

		return $return;
	}
	
	/**
	 * Sends the SetExpressCheckout request to the PayPal server
	 * to process a sale subscription
	 * 
	 * @return boolean
	 * @access protected
	 */
	function _sendSetExpressCheckoutRequest()
	{
		// prepare data for the SetExpressCheckout request
		$action = 'SetExpressCheckout';
		$amount	= $this->_getFormattedAmount();
		
		$data = new stdClass();
		
		$data->RETURNURL		= $this->_getReturnURL();
		$data->CANCELURL		= $this->_getCancelURL();
		$data->NOSHIPPING		= '1';
		$data->EMAIL			= $this->_getUserEmail();
		
		$data->PAYMENTREQUEST_0_AMT				= $amount;
		$data->PAYMENTREQUEST_0_CURRENCYCODE	= $this->_params->get('currency', 'USD');
		$data->PAYMENTREQUEST_0_DESC			= $this->_getItemDesc();
		$data->PAYMENTREQUEST_0_PAYMENTACTION	= 'Sale';
		$data->PAYMENTREQUEST_0_ITEMAMT			= $amount;
		
		$data->L_PAYMENTREQUEST_0_NAME0			= $this->_getItemDesc();
		$data->L_PAYMENTREQUEST_0_AMT0			= $amount;
		
		$this->_response = $this->_request($action, $data);
		$this->_logResponse();
		
		/*
		 * check the response
		 */
		if (!is_array($this->_response)) {
			// nothing to process
			$this->setError(JText::_('PaypalPro No Response Received from PayPal'));
			return false;
		}
		
		if (isset($this->_response['curl_error_no'])) {
			$this->setError(JText::_('PaypalPro Message Caller Error'));
			return false;
		}
		
		if (!empty($this->_response['ACK']) && (strtolower($this->_response['ACK']) == 'success' || strtolower($this->_response['ACK']) == 'successwithwarning')) {
			// redirect to PayPal
			$this->_redirectToPayPal();
		}
		else {
			$errors = $this->_getFailedPaymentErrors();
			foreach ($errors as $error) {
				$this->setError($error);
			}
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Sends the DoExpressCheckout request to the PayPal server
	 * to process a sale subscription
	 * 
	 * @return array|boolean
	 * @access protected
	 */
	function _sendDoExpressCheckoutRequest()
	{
		// firstly, let's get user details using GetExpressCheckoutDetails API
		$action = 'GetExpressCheckoutDetails';
		
		$data = new stdClass();
		$data->TOKEN = $this->_data['token'];
		
		$this->_response = $this->_request($action, $data);
		$this->_logResponse();
		
		/*
		 * check the response
		 */
		if (!is_array($this->_response)) {
			// nothing to process
			$this->setError(JText::_('PaypalPro No Response Received from PayPal'));
			return false;
		}
		
		if (isset($this->_response['curl_error_no'])) {
			$this->setError(JText::_('PaypalPro Message Caller Error'));
			return false;
		}
		
		if (!empty($this->_response['ACK']) && (strtolower($this->_response['ACK']) == 'success' || strtolower($this->_response['ACK']) == 'successwithwarning')) {
			$user_details = $this->_response;
			
			// prerare the DoExpressCheckout Request
			$action = 'DoExpressCheckoutPayment';
			
			$data = new stdClass();			
			$data->TOKEN 	= $this->_data['token'];
			$data->PAYERID	= $this->_data['PayerID'];
			
			$data->PAYMENTREQUEST_0_PAYMENTACTION = 'Sale';
			
			$amount = $this->_getFormattedAmount();
			$desc	= $this->_getItemDesc($this->_data['user_id']);
			
			$data->PAYMENTREQUEST_0_AMT 			= $amount;
			$data->PAYMENTREQUEST_0_CURRENCYCODE	= $this->_params->get('currency', 'USD');
			$data->PAYMENTREQUEST_0_ITEMAMT			= $amount;
			$data->PAYMENTREQUEST_0_DESC			= $desc;
			
			$data->L_PAYMENTREQUEST_0_NAME0			= $desc;
			$data->L_PAYMENTREQUEST_0_AMT0			= $amount;
			
			$this->_response = $this->_request($action, $data);
			$this->_response['PAYPAL_EMAIL'] = $user_details['EMAIL'];
			$this->_response['PAYERID']		 = $this->_data['PayerID'];
			
			$this->_logResponse();
			
			return $this->_response;
		}
		else {
			$errors = $this->_getFailedPaymentErrors();
			foreach ($errors as $error) {
				$this->setError($error);
			}
			
			return false;
		}
	}
	
	/**
	 * Sends the DoExpressCheckout (CreateRecurringPaymentsProfile) request to the PayPal server
	 * to process a recurring subscription
	 * 
	 * @return array|boolean
	 * @access protected
	 */
	function _sendDoExpressCheckoutRecurringRequest()
	{
		// firstly, let's get user details using GetExpressCheckoutDetails API
		$action = 'GetExpressCheckoutDetails';
		
		$data = new stdClass();
		$data->TOKEN = $this->_data['token'];
		
		$this->_response = $this->_request($action, $data);
		$this->_logResponse();
		
		/*
		 * check the response
		 */
		if (!is_array($this->_response)) {
			// nothing to process
			$this->setError(JText::_('PaypalPro No Response Received from PayPal'));
			return false;
		}
		
		if (isset($this->_response['curl_error_no'])) {
			$this->setError(JText::_('PaypalPro Message Caller Error'));
			return false;
		}
		
		if (!empty($this->_response['ACK']) && (strtolower($this->_response['ACK']) == 'success' || strtolower($this->_response['ACK']) == 'successwithwarning')) {
			$user_details = $this->_response;
			
			// prerare the CreateRecurringPaymentsProfile Request
			$action = 'CreateRecurringPaymentsProfile';
			
			$amount 		= $this->_getFormattedAmount();					
			$desc			= $this->_getItemDesc($this->_data['user_id']);
			$subscr_params 	= $this->getSubscrTypeParams();
			$subscr_obj		= $this->getSubscrTypeObj();
			
			$data = new stdClass();			
			$data->TOKEN 	= $this->_data['token'];
			$data->PAYERID	= $this->_data['PayerID'];
			
			
			$data->PROFILESTARTDATE		= gmdate('Y-m-d H:i:s'); // start the billing upon creation of the profile
			$data->DESC					= $desc;
		
			$data->BILLINGPERIOD		= $subscr_params->get('paypalpro_recurring_period_unit');
			$data->BILLINGFREQUENCY		= $subscr_params->get('paypalpro_recurring_period');
		
			if ($total_occurrences = $subscr_params->get('paypalpro_recurring_times')) {
			$data->TOTALBILLINGCYCLES	= $total_occurrences;
			}
		
			$data->AMT					= $amount;
			$data->CURRENCYCODE			= $this->_params->get('currency', 'USD');
		
			if ($subscr_params->get('paypalpro_trial_1_period_unit')) {
			$data->TRIALBILLINGPERIOD		= $subscr_params->get('paypalpro_trial_1_period_unit');
			$data->TRIALBILLINGFREQUENCY	= $subscr_params->get('paypalpro_trial_1_period');
			$data->TRIALTOTALBILLINGCYCLES	= $subscr_params->get('paypalpro_trial_1_recurring_times');
			$data->TRIALAMT					= $subscr_params->get('paypalpro_trial_1_price');
			}
			
			$this->_response = $this->_request($action, $data);
			$this->_response['PAYPAL_EMAIL'] = $user_details['EMAIL'];
			$this->_response['PAYERID']		 = $this->_data['PayerID'];
			
			$this->_logResponse();
			
			return $this->_response;
		}
		else {
			$errors = $this->_getFailedPaymentErrors();
			foreach ($errors as $error) {
				$this->setError($error);
			}
			
			return false;
		}
	}
	
	/**
	 * Logs PayPal response
	 * 
	 * @return void
	 * @access protected
	 */
	function _logResponse()
	{
		$this->_log($this->_response, 'ExpressCheckout');
	}
	
	/**
	 * Evaluates the received from PayPal response
	 * and creates a one-time subscription in Tienda
	 * 
	 * @return boolean
	 * @access protected
	 */
	function _evaluateDoExpressCheckoutResponse()
	{
		if (!is_array($this->_response)) {
			// nothing to process
			$this->setError(JText::_('PaypalPro No Response Received from PayPal'));
			return false;
		}
		
		if (isset($this->_response['curl_error_no'])) {
			$this->setError(JText::_('PaypalPro Message Caller Error'));
			return false;
		}
		
		if (!empty($this->_response['ACK']) && (strtolower($this->_response['ACK']) == 'success' || strtolower($this->_response['ACK']) == 'successwithwarning')) {
			if ( ! ($user =& $this->_getUser($this->_data['user_id'], $this->_response['PAYPAL_EMAIL'], $this->_data['PayerID']))) {			
				$this->setError(JText::_('PaypalPro Message Unknown User'));

				$user =& JFactory::getUser();
				$user->set('id', 0);
			}
			
			// check the payment amount
			$subsrc_type_obj = $this->getSubscrTypeObj();
			
			if ((float)$this->_response['PAYMENTINFO_0_AMT'] < (float)$subsrc_type_obj->get('value')) {
				$this->setError(JText::_('PaypalPro Message Payment Amount Invalid'));
			}
			
			// prepare a new payment entry for storing
			$payment_data = new JObject();
		
			$payment_data->user =& $user;
			$payment_data->payment_details = $this->_getFormattedPaymentDetails();
			$payment_data->transaction_id = $this->_response['PAYMENTINFO_0_TRANSACTIONID'];
			$payment_data->payment_amount = $this->_response['PAYMENTINFO_0_AMT'];
			$payment_data->type_id = $this->_subscr_type_id;
		
			if(count($this->getErrors())) {
				// if an error occurred or the transaction has the "pending" status,
				// then subs_status = 0, payment_status = 0, and subs_expires = gmdate( "Y-m-d H:i:s" );
				$this->_fillPaymentStatusVars($payment_data, false);
			}
			else {
				$this->_fillPaymentStatusVars($payment_data, true);			
			}
		
			$payment_error = $this->_createPayment($payment_data);		
			if ($payment_error) {
				$this->setError($payment_error);
			}
			
			return true; // we return TRUE here to indicate that the subscription was created even with errors/warnings
		}
		else {
			// payment failed, no subscription will be created
			$errors = $this->_getFailedPaymentErrors();
			foreach ($errors as $error) {
				$this->setError($error);
			}
			
			return false; // we return FALSE here to indicate that the payment wasn't processed and no subscription was created
		}
	}
	
	/**
	 * Sends request to the PayPal server
	 * to process a recurring subscription
	 * 
	 * @return boolean
	 * @access protected
	 */
	function _sendSetExpressCheckoutRecurringRequest()
	{
		$subscr_params 	= $this->getSubscrTypeParams();
		$subscr_obj		= $this->getSubscrTypeObj();
		
		// prepare data for the SetExpressCheckout request
		$action = 'SetExpressCheckout';
		$amount	= $this->_getFormattedAmount();
		
		$data = new stdClass();
		
		$data->L_BILLINGTYPE0					= 'RecurringPayments';
		$data->L_BILLINGAGREEMENTDESCRIPTION0	= $this->_getItemDesc();
		$data->AMT								= $amount;
		$data->MAXAMT							= $amount;
		
		$data->CURRENCYCODE			= $this->_params->get('currency', 'USD');
		
		$data->RETURNURL		= $this->_getReturnURL();
		$data->CANCELURL		= $this->_getCancelURL();
		$data->NOSHIPPING		= '1';
		$data->EMAIL			= $this->_getUserEmail();
		
		$this->_response = $this->_request($action, $data);
		$this->_logResponse();
		
		/*
		 * check the response
		 */
		if (!is_array($this->_response)) {
			// nothing to process
			$this->setError(JText::_('PaypalPro No Response Received from PayPal'));
			return false;
		}
		
		if (isset($this->_response['curl_error_no'])) {
			$this->setError(JText::_('PaypalPro Message Caller Error'));
			return false;
		}
		
		if (!empty($this->_response['ACK']) && (strtolower($this->_response['ACK']) == 'success' || strtolower($this->_response['ACK']) == 'successwithwarning')) {
			// redirect to PayPal
			$this->_redirectToPayPal();
		}
		else {
			$errors = $this->_getFailedPaymentErrors();
			foreach ($errors as $error) {
				$this->setError($error);
			}
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Evaluates the received from PayPal response
	 * and creates a recurring subscription in Tienda
	 * 
	 * @return boolean
	 * @access protected
	 */
	function _evaluateDoExpressCheckoutRecurringResponse()
	{
		if (!is_array($this->_response)) {
			// nothing to process
			$this->setError(JText::_('PaypalPro No Response Received from PayPal'));
			return false;
		}
		
		if (isset($this->_response['curl_error_no'])) {
			$this->setError(JText::_('PaypalPro Message Caller Error'));
			return false;
		}
		
		if (!empty($this->_response['ACK']) && (strtolower($this->_response['ACK']) == 'success' || strtolower($this->_response['ACK']) == 'successwithwarning')) {
			if ( ! ($user =& $this->_getUser($this->_data['user_id'], $this->_response['PAYPAL_EMAIL'], $this->_data['PayerID']))) {		
				$this->setError(JText::_('PaypalPro Message Unknown User'));

				$user =& JFactory::getUser();
				$user->set('id', 0);
			}
			
			// check the profile status
			if (strtolower($this->_response['PROFILESTATUS']) != 'activeprofile') {
				$this->setError(JText::_('PaypalPro Message Profile Not Active'));
			}			
			
			$subscr_type_obj = $this->getSubscrTypeObj();
			$subscr_params = $this->getSubscrTypeParams();
			
			$amount = $subscr_params->get('paypalpro_trial_1_price') ? $subscr_params->get('paypalpro_trial_1_price') : $subscr_type_obj->get('value');
			
			// prepare a new payment entry for storing
			$payment_data = new JObject();
		
			$payment_data->user =& $user;
			$payment_data->payment_details = $this->_getFormattedPaymentDetails();
			$payment_data->transaction_id = $this->_response['PROFILEID'];
			$payment_data->payment_amount = $amount;
			$payment_data->type_id = $this->_subscr_type_id;
		
			if(count($this->getErrors())) {
				// if an error occurred or the transaction has the "pending" status,
				// then subs_status = 0, payment_status = 0, and subs_expires = gmdate( "Y-m-d H:i:s" );
				$this->_fillPaymentStatusVars($payment_data, false);
			}
			else {
				$this->_fillPaymentStatusVars($payment_data, true);

				// calculate the expiration date of the subscription based on the subscription type's params
				if ($subscr_params->get('paypalpro_trial_1_period_unit')) {
					$duration = $subscr_params->get('paypalpro_trial_1_period');
					$duration_unit = $subscr_params->get('paypalpro_trial_1_period_unit');
				}				
				else {
					$duration = $subscr_params->get('paypalpro_recurring_period');
					$duration_unit = $subscr_params->get('paypalpro_recurring_period_unit');
				}
				
				$payment_date = gmdate('Y-m-d H:i:s');
				$payment_data->subs_expires = $this->_getExpirationDate($payment_date, $duration, $duration_unit);				
			}
		
			$payment_error = $this->_createPayment($payment_data);		
			if ($payment_error) {
				$this->setError($payment_error);
			}
			
			return true; // we return TRUE here to indicate that the subscription was created even with errors/warnings
		}
		else {
			// payment failed, no subscription will be created
			$errors = $this->_getFailedPaymentErrors();
			foreach ($errors as $error) {
				$this->setError($error);
			}
			
			return false; // we return FALSE here to indicate that the payment wasn't processed and no subscription was created
		}
	}
	
	/**
	 * Parses the response fields and gets all errors
	 * 
	 * @return array
	 * @access protected
	 */
	function _getFailedPaymentErrors()
	{
		$errors = array();
		
		foreach ($this->_response as $field => $value) {
			if (strpos($field, 'L_ERRORCODE') === 0) {
				// get error's number
				$error_num = (int) str_replace('L_ERRORCODE', '', $field);
				$error_message = 'L_LONGMESSAGE' . $error_num;
				
				if (isset($this->_response[$error_message])) {
					$errors[] = $this->_response[$error_message] . ' (' . $value . ')';
				}
			}
		}
		
		return $errors;
	}
	
	/**
	 * @see plugins/tienda/payment_paypalpro/library/plgTiendaPayment_Paypalpro_Processor#_getPaypalUrl()
	 */
	function _getPaypalUrl()
	{
		if ($this->_params->get('sandbox')) {
			$url = 'https://api-3t.sandbox.paypal.com/nvp';
		}	
		else {
			$url = 'https://api-3t.paypal.com/nvp';
		}
		
		return $url;
	}
	
	/**
	 * @see plugins/tienda/payment_paypalpro/library/plgTiendaPayment_Paypalpro_Processor#_getFormattedPaymentDetails()
	 */	
	function _getFormattedPaymentDetails()
	{
		$separator = "\n";
		$formatted = array();

		foreach ($this->_response as $key => $value) {			
			$formatted[] = $key . ' = ' . $value;			
		}
		
		return count($formatted) ? implode("\n", $formatted) : '';	
	}
	
	/**
 	 * @see plugins/tienda/payment_paypalpro/library/plgTiendaPayment_Paypalpro_Processor#_getUserEmail()
 	 */
    function _getUserEmail()
    {
    	$user =& JFactory::getUser();
    	
    	if ($user->get('id')) {
    		return $user->get('email');
    	}
    	
    	return '';    	
    }
    
    /**
     * Gets an ID of Joomla user
     * 
     * @return int
     * @access protected
     */
    function _getUserID()
    {
    	$user =& JFactory::getUser();
    	return $user->get('id');
    }
    
    /**
     * Gets a URL which PayPal will post a user back to
     * 
     * @return string
     * @access protected
     */
    function _getReturnURL()
    {
    	$secure_post = $this->_params->get('secure_post', '0');
    	
    	$url  = 'index.php?option=com_tienda&controller=payment&task=process&ptype=' . $this->_plugin_type . '&paction=process_doexpresscheckout';
		$url .= '&user_id=' . $this->_getUserID() . '&item_number=' . $this->_subscr_type_id;
		
		$url = JURI::root() . JRoute::_($url, false, $secure_post);
    	return $url;
    }
    
	/**
     * Gets a URL which PayPal will post a user back to
     * if he or she decides to cancel a payment
     * 
     * @return string
     * @access protected
     */
    function _getCancelURL()
    {
    	$url  = 'index.php?option=com_tienda&controller=payment&task=process&ptype=' . $this->_plugin_type . '&paction=cancel';
    	
    	$url = JURI::root() . JRoute::_($url, false);
    	return $url;
    }

    /**
     * Redirects to the Paypal ExpressCheckout page
     * 
     * @return void
     * @access protected
     */
    function _redirectToPayPal()
    {
    	$app =& JFactory::getApplication();
    	
    	$url  = $this->_params->get('sandbox') ? 'https://www.sandbox.paypal.com/webscr' : 'https://www.paypal.com/webscr';
    	$url .= '?cmd=_express-checkout&token=' . $this->_response['TOKEN'];
    	
    	header('HTTP/1.1 302 Object moved');
		header('Location: ' . $url);
		
		$app->close();
    }
    
    /** Gets an existing user or creates a new one
	 * 
	 * @param int $id
	 * @param string $email
	 * @param string $unique_gateway_id The ID that uniquely identifies the user to the payment system
	 * @return JUser object
	 * @access protected
	 */
	function & _getUser($id, $email, $unique_gateway_id = false)
	{
		$config =& TiendaConfig::getInstance();
		
		$user =& JFactory::getUser($id);
		if ($user->id) {
			return $user;
		}
		
		// try to find out if the email is registered
		jimport('joomla.user.helper');
		if ($id = JUserHelper::getUserId($email)) {
			$user =& JFactory::getUser($id);
			
			if ($user->id) {
				return $user;
			}
		}
		
		if ($unique_gateway_id) {
			// try to find a user in the payment_details if he ever made a payment
			if ($id = $this->_findUserId($unique_gateway_id, 'PAYERID')) {		
				$user =& JFactory::getUser($id);
				if ($user->id) {
					return $user;
				}			
			}
		}		
		
		// if no existing user found, create a new one
		$msg = new stdClass();
		$msg->type 		= '';
		$msg->message 	= '';
		
		$newuser_email = $email;
		// create user from email
		jimport('joomla.user.helper');
		$details['name'] 		= $newuser_email;
		$details['username'] 	= $newuser_email;
		$details['email'] 		= $newuser_email;
		$details['password'] 	= JUserHelper::genRandomPassword();
		$details['password2'] 	= $details['password'];
		$details['block'] 		= $config->get('block_automatically_registered') ? '1' : '0';
		
		if ($user =& TiendaHelperUser::createNewUser( $details, $msg )) {
			if ( ! $config->get('block_automatically_registered')) {
				// login the new user
				$login = TiendaHelperUser::login( $details, '1' );
			}
			
			// indicate that user was registed by AS automatically
			$user->set('automatically_registered', true);
		}
		
		return $user;
	}	
		
}
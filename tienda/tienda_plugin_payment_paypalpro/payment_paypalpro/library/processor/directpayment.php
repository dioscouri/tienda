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
 * Tienda PayPalPro DirectPayment Processor
 *
 * @package		Joomla 
 * @since 		1.5
 */
class plgTiendaPayment_Paypalpro_Processor_Directpayment extends plgTiendaPayment_Paypalpro_Processor
{
	/**
	 * @var array
	 */
	var $_required = array(
		'first_name', 'last_name', 'address1', 'city', 'state', 'zip',
		'country', 'cardtype', 'cardnum', 'cardexp_month', 'cardexp_year', 'cardcvv',	
	);
	
	/**
	 * @var array
	 */
	var $_response;
	
	
	/**	 
	 * @see plugins/tienda/payment_paypalpro/library/plgTiendaPayment_Paypalpro_Processor#validateData()
	 */
	function validateData()
	{
		/*
		 * perform initial checks 
		 */
		if (!count($this->_data)) {
			$this->setError(JText::_('PaypalPro No Data is Provided'));
			return false;
		}
		
		if (!JRequest::checkToken()) {
			$this->setError(JText::_('Invalid Token'));
			return false;
		}
		
		if (!$this->getSubscrTypeObj()) {
			$this->setError(JText::_('Paypalpro Message Invalid Item Type'));
			return false;
		}
		
		if (!$this->_getParam('api_username') || !$this->_getParam('api_password') || !$this->_getParam('api_signature')) {
			$this->setError(JText::_('PaypalPro Message Merchant Credentials are invalid'));
			return false;	
		}
		
		/*
		 * do form verification to make sure information is both present and valid
		 */ 
		
		// check required fields
		foreach ($this->_required as $required_field) {
			if (empty($this->_data[$required_field])) {
				$this->setError(JText::_('PayPalPro Message Fill in Required Fields'));
				return false;
			}
		}
		
		// check some specific fields
		if (JString::strlen($this->_data['state']) != 2) {
			$this->setError(JText::_('PayPalPro Message State Invalid'));
			return false;
		}
		
		$user =& JFactory::getUser();
		if (!$user->id) {
			// require email address for guest users
			jimport( 'joomla.mail.helper' ); 
			
			if (empty($this->_data['email']) || !JMailHelper::isEmailAddress($this->_data['email'])) {
				$this->setError(JText::_('PaypalPro Message Email Address Required'));
				return false;
			}

			if (TiendaHelperUser::emailExists($this->_data['email'])) {				
				$this->setError(JText::_('PaypalPro Message Email Exists'));
				return false;
			}
		}
		
		if (JString::strlen($this->_data['cardexp_month']) != 2 || JString::strlen($this->_data['cardexp_year']) != 4) {
			$this->setError(JText::_('PayPalPro Message Expiration Date Invalid'));
			return false;
		}
		
		return true;
	}
	
	/**	 
	 * @see plugins/tienda/payment_paypalpro/library/plgTiendaPayment_Paypalpro_Processor#process()
	 */
	function process()
	{
		// clear all possible error settings
		$this->_errors = array();		
		
		$subscr_type_params = $this->getSubscrTypeParams();
		if ($subscr_type_params->get('is_recurring')) {
			// process a recurring subscription sign-up
			$this->_sendRecurringRequest();
			$this->_logResponse();
			
			$return = $this->_evaluateRecurringResponse();
		}
		else {
			// process a one-time (sale) subscription
			$this->_sendSaleRequest();
			$this->_logResponse();
		
			$return = $this->_evaluateSaleResponse();
		}
				
		return $return;
	}
	
	/**
	 * Sends request to the PayPal server
	 * to process a sale subscription
	 * 
	 * @return array
	 * @access protected
	 */
	function _sendSaleRequest()
	{
		// prepare data for the DoDirectPayment request
		$action = 'DoDirectPayment';
		
		$data = new stdClass();
		$data->PAYMENTACTION 		= 'Sale';
		$data->IPADDRESS 			= $this->_getUserIPAddress();
		
		$data->CREDITCARDTYPE 		= $this->_data['cardtype'];
		$data->ACCT 				= $this->_data['cardnum'];
		$data->EXPDATE				= $this->_getFormattedExpDate();
		$data->CVV2					= $this->_data['cardcvv'];
		
		$data->EMAIL				= $this->_getUserEmail();
		$data->FIRSTNAME			= $this->_data['first_name'];
		$data->LASTNAME				= $this->_data['last_name'];
		$data->STREET				= $this->_data['address1'];
		$data->STREET2				= $this->_data['address2'];
		$data->CITY					= $this->_data['city'];
		$data->STATE				= $this->_data['state'];
		$data->COUNTRYCODE			= $this->_data['country'];
		$data->ZIP					= $this->_data['zip'];
		
		$data->AMT					= $this->_getFormattedAmount();
		$data->CURRENCYCODE			= $this->_params->get('currency', 'USD');
		$data->DESC					= $this->_getItemDesc();

		//$data->NOTIFYURL			= JURI::root()."index.php?option=com_tienda&controller=payment&task=process&ptype={$this->_plugin_type}&paction=process_recurring&tmpl=component";
		
		$this->_response = $this->_request($action, $data);
		return $this->_response;
	}
	
	/**
	 * Logs PayPal response
	 * 
	 * @return void
	 * @access protected
	 */
	function _logResponse()
	{
		$this->_log($this->_response, 'DirectPayment');
	}
	
	/**
	 * Evaluates the received from PayPal response
	 * and creates a one-time subscription in Tienda
	 * 
	 * @return boolean
	 * @access protected
	 */
	function _evaluateSaleResponse()
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
			if ( ! ($user =& $this->_getUser($this->_getUserEmail()))) {			
				$this->setError(JText::_('PaypalPro Message Unknown User'));

				$user =& JFactory::getUser();
				$user->set('id', 0);
			}
			
			// check the payment amount
			$subsrc_type_obj = $this->getSubscrTypeObj();
			
			if ((float)$this->_response['AMT'] < (float)$subsrc_type_obj->get('value')) {
				$this->setError(JText::_('PaypalPro Message Payment Amount Invalid'));
			}
			
			// prepare a new payment entry for storing
			$payment_data = new JObject();
		
			$payment_data->user =& $user;
			$payment_data->payment_details = $this->_getFormattedPaymentDetails();
			$payment_data->transaction_id = $this->_response['TRANSACTIONID'];
			$payment_data->payment_amount = $this->_response['AMT'];
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
	 * @return array
	 * @access protected
	 */
	function _sendRecurringRequest()
	{
		$subscr_params 	= $this->getSubscrTypeParams();
		$subscr_obj		= $this->getSubscrTypeObj();
		
		// prepare data for the CreateRecurringPaymentsProfile request
		$action = 'CreateRecurringPaymentsProfile';
		
		$data = new stdClass();		
		//$data->IPADDRESS 			= $this->_getUserIPAddress();
		
		$data->SUBSCRIBERNAME		= $this->_data['first_name'] . ' ' . $this->_data['last_name'];
		$data->PROFILESTARTDATE		= gmdate('Y-m-d H:i:s'); // start the billing upon creation of the profile
		$data->DESC					= $this->_getItemDesc();
		
		$data->BILLINGPERIOD		= $subscr_params->get('paypalpro_recurring_period_unit');
		$data->BILLINGFREQUENCY		= $subscr_params->get('paypalpro_recurring_period');
		
		if ($total_occurrences = $subscr_params->get('paypalpro_recurring_times')) {
		$data->TOTALBILLINGCYCLES	= $total_occurrences;
		}
		
		$data->AMT					= $this->_getFormattedAmount();
		$data->CURRENCYCODE			= $this->_params->get('currency', 'USD');
		
		if ($subscr_params->get('paypalpro_trial_1_period_unit')) {
		$data->TRIALBILLINGPERIOD		= $subscr_params->get('paypalpro_trial_1_period_unit');
		$data->TRIALBILLINGFREQUENCY	= $subscr_params->get('paypalpro_trial_1_period');
		$data->TRIALTOTALBILLINGCYCLES	= $subscr_params->get('paypalpro_trial_1_recurring_times');
		$data->TRIALAMT					= $subscr_params->get('paypalpro_trial_1_price');
		}
		
		$data->CREDITCARDTYPE 		= $this->_data['cardtype'];
		$data->ACCT 				= $this->_data['cardnum'];
		$data->EXPDATE				= $this->_getFormattedExpDate();
		$data->CVV2					= $this->_data['cardcvv'];
		
		$data->EMAIL				= $this->_getUserEmail();
		$data->FIRSTNAME			= $this->_data['first_name'];
		$data->LASTNAME				= $this->_data['last_name'];
		$data->STREET				= $this->_data['address1'];
		$data->STREET2				= $this->_data['address2'];
		$data->CITY					= $this->_data['city'];
		$data->STATE				= $this->_data['state'];
		$data->COUNTRYCODE			= $this->_data['country'];
		$data->ZIP					= $this->_data['zip'];		
		
		
		$this->_response = $this->_request($action, $data);
		return $this->_response;
	}
	
	/**
	 * Evaluates the received from PayPal response
	 * and creates a recurring subscription in Tienda
	 * 
	 * @return boolean
	 * @access protected
	 */
	function _evaluateRecurringResponse()
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
			if ( ! ($user =& $this->_getUser($this->_getUserEmail()))) {			
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
	 * Gets user's IP address
	 * 
	 * @return string
	 * @access protected
	 */
	function _getUserIPAddress()
	{
		$ip = '0.0.0.0';
      
		if (getenv('REMOTE_ADDR') && getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		}
		elseif (getenv('REMOTE_ADDR')) {
			$ip = getenv('REMOTE_ADDR');
		}
		elseif (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		}
		elseif (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		
		if ($ip == '0.0.0.0') {
			return $ip;
		}
		
		// get the last ip from the list
		if (strstr($ip, ',')) {
			$ips = explode(',', $ip);
			$ip = end($ips);
		}
		
		return $ip;
	}
	
	/**
	 * Gets a card expiration date in format MMYYYY
	 * 
	 * @return string
	 * @access protected
	 */
	function _getFormattedExpDate()
	{
		return $this->_data['cardexp_month'] . $this->_data['cardexp_year'];
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
		
}

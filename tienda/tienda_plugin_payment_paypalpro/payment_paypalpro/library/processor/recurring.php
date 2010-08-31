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

require_once dirname(__FILE__) . '/../processor.php';

/**
 * Tienda PayPalPro Recurring Payments Processor
 * 
 * This class is called when an IPN response is received with txt_type = "recurring_payment"
 *
 * @package		Joomla 
 * @since 		1.5
 */
class plgTiendaPayment_Paypalpro_Processor_Recurring extends plgTiendaPayment_Paypalpro_Processor
{
	/**
	 * @var array
	 */
	var $_response;
	
	
	/**	 
	 * @see plugins/Tienda/payment_paypalpro/library/plgTiendaPayment_Paypalpro_Processor#validateData()
	 */
	function validateData()
	{
		// no additional requrest is needed like with other processors
		$this->_response = $this->_data;	
		
		// log IPN data before any validations
		$this->_logResponse();
		
		/*
		 * perform initial checks 
		 */
		if (!count($this->_data)) {
			$this->setError(JText::_('PaypalPro No Data is Provided'));
			return false;
		}
		
		if (!$this->getSubscrTypeObj()) {
			$this->setError(JText::_('PaypalPro Message Invalid Item Type'));
			return false;
		}
		
		$subscr_params = $this->getSubscrTypeParams();
		if (!$subscr_params->get('is_recurring')) {
			$this->setError(JText::_('PaypalPro Message Subscription is not Recurring'));
			return false;
		}
		
		if (!$this->_getParam('api_username') || !$this->_getParam('api_password') || !$this->_getParam('api_signature')) {
			$this->setError(JText::_('PaypalPro Message Merchant Credentials are invalid'));
			return false;	
		}		
		
		// validate IPN response				
		if ($ipn_error = $this->_validateIPN()) {
			$this->setError($ipn_error);
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
		
		$return = $this->_evaluateRecurringResponse();
		return $return;
	}
	
	/**
	 * Logs PayPal response
	 * 
	 * @return void
	 * @access protected
	 */
	function _logResponse()
	{
		$this->_log($this->_response, 'IPNResponse');
	}
	
	/**
	 * Evaluates the received from PayPal IPN response
	 * and creates a recurring subscription in Tienda
	 * 
	 * @return boolean
	 * @access protected
	 */
	function _evaluateRecurringResponse()
	{
		/*
		 * If this is the first recurring payment
		 * we just skip it, because we've already created a user subscription
		 * during the "sign-up" stage
		 */
		if ($this->_getIsFirstRecPayment()) {
			// update the payment details of the sign-up subscription payment with a received data 
			$this->_updateSignupSubs();
			return true;
		}

		$subscr_type_obj 	= $this->getSubscrTypeObj();
		$subscr_params		= $this->getSubscrTypeParams();
		
		// check the regular amount and two possible trial amounts as well
		if ((float)$subscr_type_obj->get('value') > (float)$this->_response['mc_gross'] && (float)$subscr_type_obj->get('paypalpro_trial_1_price') > (float)$this->_response['mc_gross']) {
			$this->setError(JText::_('PAYPALPRO MESSAGE AMOUNT INVALID'));
		}
		
		if (empty($this->_response['payment_status']) || ($this->_response['payment_status'] != 'Completed')) {
			$this->setError(JText::sprintf('PAYPALPRO MESSAGE STATUS INVALID', @$this->_data['payment_status']));
		}

		if (!($user =& $this->_getUser($this->_response['payer_email'], $this->_response['recurring_payment_id']))) {			
			$this->setError(JText::_('PaypalPro Message Unknown User'));

			$user =& JFactory::getUser();
			$user->set('id', 0);
		}		
		
		// prepare a new payment entry for storing
		$payment_data = new JObject();		
		
		$payment_data->payment_plugin_data = array(
			'is_recurring' => true
		);
		
		$payment_data->user =& $user;
		$payment_data->payment_details = $this->_getFormattedPaymentDetails();
		$payment_data->transaction_id = $this->_response['txn_id'];
		$payment_data->payment_amount = $this->_response['mc_gross'];
		$payment_data->type_id = $this->_subscr_type_id;		
		
		if(count($this->getErrors())) {
				// if an error occurred or the transaction has the "pending" status,
				// then subs_status = 0, payment_status = 0, and subs_expires = gmdate( "Y-m-d H:i:s" );
				$this->_fillPaymentStatusVars($payment_data, false);
		}
		else {
			$this->_fillPaymentStatusVars($payment_data, true);

			// calculate the expiration date of the subscription based on the subscription type's params
			if ($subscr_params->get('paypalpro_trial_1_period_unit') && (float)$subscr_params->get('paypalpro_trial_1_price') === (float)$this->_response['mc_gross']) {
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
	
	/**
	 * Gets PayPal URL for IPN validation
	 * 
	 * also @see plugins/tienda/payment_paypalpro/library/plgTiendaPayment_Paypalpro_Processor#_getPaypalUrl()
	 */
	function _getPaypalUrl()
	{
		if ($this->_params->get('sandbox')) {
			$url = 'www.sandbox.paypal.com';
		}	
		else {
			$url = 'www.paypal.com';
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
			if ($key != 'view' && $key != 'layout') {
				$formatted[] = $key . ' = ' . $value;
			}			
		}
		
		return count($formatted) ? implode("\n", $formatted) : '';	
	}
	
	/**
	 * Validates the IPN data
	 * 
	 * @return string Empty string if data is valid and an error message otherwise
	 * @access protected
	 */
	function _validateIPN()
	{
		$secure_post = $this->_params->get( 'secure_post', '0' );
		$paypal_url = $this->_getPaypalUrl();
		
		$req = 'cmd=_notify-validate';
		foreach ($this->_data as $key => $value) {
			if ($key != 'view' && $key != 'layout') {
				$value = urlencode($value);
				$req .= "&$key=$value";
			}
		}
        
        // post back to PayPal system to validate
		$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		//$header .= "Host: " . $this->_getURL(false) . ":443\r\n";
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
            return JText::sprintf('PAYPALPRO ERROR POSTING IPN DATA BACK', $errstr, $errno);
        }
        else {
            fputs ($fp, $header . $req);
            while ( ! feof($fp)) {
                $res = fgets ($fp, 1024); //echo $res;
				if (strcmp ($res, 'VERIFIED') == 0) {
					return '';
				}
				elseif (strcmp ($res, 'INVALID') == 0) {
					return JText::_('PAYPALPRO ERROR IPN VALIDATION');
				}
            }        
        }
         
        fclose($fp);        
        return '';
	}
	
	
	/**
	 * Checks whether this is the first recurring payment
	 * of the particular subscription or not
	 * 
	 * @param string $field_name
	 * @return boolean
	 * @access protected
	 */
	function _getIsFirstRecPayment($field_name = 'recurring_payment_id')
	{
		$db =& JFactory::getDBO();
		$keyword = $field_name . " = " . $this->_response['recurring_payment_id'];
		
		$q = "SELECT COUNT(id) "	
		   . "FROM #__tienda_orderpayments "
		   . "WHERE orderpayment_type = " . $db->quote($this->_plugin_type) . " "
		   . "AND transaction_details LIKE '%" . addcslashes($db->getEscaped($keyword), '%_') . "%'"       
		   ;

		$db->setQuery($q);
		$count = $db->loadResult();
		
		return !$count ? true : false;
	}
		
	/**
	 * Updates the subscription payment details
	 * with new data
	 * 
	 * @return boolean
	 * @access protected
	 */
	function _updateSignupSubs()
	{
		$db =& JFactory::getDBO();
		$payment_details = $this->_getFormattedPaymentDetails();
			
		$q = "
			UPDATE
				#__tienda_orderpayments
			SET
				transaction_details = CONCAT(payment_details, '\n', " . $db->Quote($payment_details) . ")		   			
			WHERE
				orderpayments_id = " . $db->Quote($this->_response['recurring_payment_id']) // for sign-up payments their profile ID is used as a payment ID
		;
		$db->setQuery($q);
		
		if (!$db->query()) {
			return false;
		}
		
		return true;
	}	
		
}
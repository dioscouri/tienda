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

/**
 * Ambrasubs PayPalPro Base Processor
 *
 * @package		Joomla 
 * @since 		1.5
 */
class plgTiendaPayment_Paypalpro_Processor extends JObject
{
	/**
	 * @var string
	 */
	var $_plugin_type;
	
	/**
	 * @var object
	 */
	var $_params;
	
	/**
	 * @var array
	 */
	var $_data;
	
	/**
	 * @var int
	 */
	var $_subscr_type_id;
	
	/**
	 * @var object
	 */
	var $_subscr_type_obj;
	
	/**
	 * @var object
	 */
	var $_caller;
	
	/**
	 * @var string
	 */
	var $_api_version = '64.0';
	
	/**
	 * @var boolean
	 */
	var $_is_log = false;
	
	
	/**
	 * Class constructor
	 * 
	 * @param object $params
	 * @param string $plugin_type
	 * @return void
	 * @access public
	 */
	function plgTiendaPayment_Paypalpro_Processor(&$params, $plugin_type)
	{
		$this->setParams($params);
		$this->setPluginType($plugin_type);
	}

    /**
     * Public setter for $_params
     * 
     * @param object $params
     * @return void
     * @access public
     */
    function setParams(&$params)
    {
    	$this->_params =& $params;
    }
    
	/**
     * Public setter for $_plugin_type
     * 
     * @param string $params
     * @return void
     * @access public
     */
    function setPluginType($plugin_type)
    {
    	$this->_plugin_type = $plugin_type;
    }
    
	/**
     * Public setter for $_data
     * 
     * @param array $data
     * @return void
     * @access public
     */
    function setData($data)
    {
    	$this->_data = $data;
    }
    
	/**
     * Public getter for $_data
     * 
     * @return array
     * @access public
     */
    function getData()
    {
    	return $this->_data;
    }
    
	/**
     * Public setter for $_subscr_type_id
     * 
     * @param int $id
     * @return void
     * @access public
     */
    function setSubscrTypeID($id)
    {
    	$this->_subscr_type_id = $id;
    }
    
    /**
     * Public getter for $_subscr_type_id
     * 
     * @return object
     * @access public
     */
    function & getSubscrTypeObj()
    {
    	if ($this->_subscr_type_obj === null) {
			$type = TiendaHelperPayment::getTable('Type');
			$type->load($this->_subscr_type_id); 
		
			if (!$type->id) {
				$this->_subscr_type_obj = null;
			}
			else {
				$this->_subscr_type_obj =& $type;
			}
    	}
    	
    	return $this->_subscr_type_obj;
    }
    
    /**
     * Gets a subscription type's parameters
     * 
     * @return object JParameter or JObject
     */
    function & getSubscrTypeParams()
    {
    	$subscr_type_obj = $this->getSubscrTypeObj();
    	if ($subscr_type_obj !== null) {
    		$params = new JParameter($subscr_type_obj->params);			
    	}
    	else {    		
    		$params = new JObject();
    	}
    	
    	return $params;
    }
        
    /**
     * Sets the $_is_log property
     * 
     * @param boolean $value
     * @return void
     */
    function setIsLog($value)
    {
    	$this->_is_log = (boolean) $value;
    }
        
    /**
     * Gets a value of the plugin parameter
     * 
     * @param string $name
     * @param string $default
     * @return string
     * @access protected
     */
    function _getParam($name, $default = '') 
    {
    	$sandbox_param = "sandbox_$name";
    	$sb_value = $this->_params->get($sandbox_param);
    	
        if ($this->_params->get('sandbox') && !empty($sb_value)) {
            $param = $this->_params->get($sandbox_param, $default);
        }
        else {
        	$param = $this->_params->get($name, $default);
        }
        
        return $param;
    }
    
    /**
     * Get's user email address
     * 
     * @return string
     * @access protected
     */
    function _getUserEmail()
    {
    	$user =& JFactory::getUser();
    	
    	if ($user->get('id')) {
    		return $user->get('email');
    	}
    	elseif (!empty($this->_data['email'])) {
    		return $this->_data['email'];
    	}
    	else {
    		return '';
    	}
    }
    
    /**
     * Gets formatted payment amount
     * 
     * @return string
     * @access protected
     */
    function _getFormattedAmount()
    {
    	$subscr_obj = $this->getSubscrTypeObj();
    	$amount 	= (float) $subscr_obj->get('value');
    	
    	$amount = sprintf('%01.2f', $amount);
    	return $amount;
    }
    
    /**
     * Gets a Subscription Type's description
     * 
     * @param int $user_id
     * @return string
     * @access protected
     */
    function _getItemDesc($user_id = null)
    {
    	$subscr_obj =  $this->getSubscrTypeObj();
    	
    	if ($user_id) {
    		$user =& JFactory::getUser($user_id);
    	}
    	else {
    		$user =& JFactory::getUser();
    	}
    	
    	$desc =  $subscr_obj->get('id') . ':' . $subscr_obj->get('title') . (' - [ ' . ($user->get('id') ? $user->get('username') : JText::_('PAYPALPRO NEW USER'))  . ' ]');
    	return $desc;
    }
    
    /**
     * Sends an API request
     * 
     * @param string $action
     * @param object $data
     * @return array
     * @access protected
     */
    function _request($action, $data)
    {
    	require_once dirname(__FILE__) . '/caller.php';
    	$caller = new plgTiendaPayment_Paypalpro_Caller();
    	
    	$data->USER 		= $this->_getParam('api_username');
    	$data->PWD	  		= $this->_getParam('api_password');
    	$data->SIGNATURE	= $this->_getParam('api_signature');
    	$data->VERSION 		= $this->_api_version;
    	$data->METHOD		= $action;
    	
    	$url = $this->_getPaypalUrl();
    	
    	$response = $caller->request($url, $data);
    	return $response;
	}
	
	/**
	 * Simple logger 
	 * 
	 * @param mixed $text
	 * @param string $type
	 * @return void
	 */
	function _log($text, $type = 'message')
	{
		if ($this->_is_log) {
			$app =& JFactory::getApplication();
			
			// make sure we are dealing with a string
			if (is_array($text) || is_object($text)) {
				$text = print_r($text, true);
			}
			
			$file = $app->getCfg('log_path') . "/{$this->_plugin_type}.log";			
			$date = JFactory::getDate();

			$existing_text = '';
			if (JFile::exists($file)) {
				$existing_text = file_get_contents($file);
			}
			
			$text = $existing_text . "\n\n" . $date->toFormat('%Y-%m-%d %H:%M:%S') . "\n" . $type . ': ' . $text;
			
			$written = JFile::write($file, $text);
		}
	}
		
	/** Gets an existing user or creates a new one
	 * 
	 * @param string $email
	 * @param string $unique_gateway_id The ID that uniquely identifies the user to the payment system
	 * @return JUser object
	 * @access protected
	 */
	function & _getUser($email, $unique_gateway_id = false)
	{
		$config =& TiendaConfig::getInstance();
		
		$user =& JFactory::getUser();
		if ($user->id) {
			return $user;
		}
		
		if ($unique_gateway_id) {
			// try to find a user in the payment_details if he ever made a payment
			if ($id = $this->_findUserId($unique_gateway_id)) {			
				$user =& JFactory::getUser($id);
				if ($user->id) {
					return $user;
				}			
			}
		}		
		
		// try to find out if the email is registered
		jimport('joomla.user.helper');
		if ($id = JUserHelper::getUserId($email)) {
			$user =& JFactory::getUser($id);
			
			if ($user->id) {
				return $user;
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
	
	/**
	 * Finds a user id in his payment history
	 *
	 * @param string $id
	 * @param string $field_name
	 * @return int
	 * @access protected
	 */
	function _findUserId($id, $field_name = 'PROFILEID')
	{
		$db =& JFactory::getDBO();
		$keyword = $field_name . " = " . $id; 
		
		$q = "SELECT created_by "	
		   . "FROM #__Tienda_payments "
		   . "WHERE payment_type = " . $db->quote($this->_plugin_type) . " "
		   . "AND payment_details LIKE '%" . addcslashes($db->getEscaped($keyword), '%_') . "%'"       
		   ;

		$db->setQuery($q);
		return $db->loadResult();
	}
	
	/**
	 * Fills in the payment status related fields depending on the payment state
	 * 
	 * @param JObject $payment_object
	 * @param boolean $active
	 * @return void
	 * @access protected
	 */
	function _fillPaymentStatusVars( & $payment_object, $active)
	{
		if ($active) {
			$payment_object->payment_status = '1';
			$payment_object->subs_status = '1';
			$payment_object->subs_expires = null;
		}
		else {
			$payment_object->payment_status = '0';
			$payment_object->subs_status = '0';
			$payment_object->subs_expires = gmdate( "Y-m-d H:i:s" );
		}
	}
	
	/**
	 * Creates a new payment entry
	 * 
	 * @param array $data
	 * @return string An error message or empty string
	 * @access protected
	 */
	function _createPayment($data)
	{
		$paymentError = '';
		$errors = $this->getErrors();
		
		$paymentPluginData = array(
			'validation_errors' => $errors,
			'payment_method_title' => 'PayPal',
			'payment_method_code' => 'paypalpro'
		);
		
		$payment =& new TiendaHelperPayment();
		$payment->payment_type = $this->_plugin_type; 			// unique identifier, should be the plugin name
		$payment->created_by = $data->user->get('id');			// user creating the payment record
		$payment->created_datetime = gmdate( "Y-m-d H:i:s" ); 	// always in GMT
		$payment->payment_id = $data->transaction_id;			// transaction id from payment method
		$payment->payment_status = $data->payment_status;		// status of the PAYMENT
		$payment->payment_amount = $data->payment_amount;		// payment amount
		$payment->payment_details = $data->payment_details;		// text - any info about payment
		$payment->payment_datetime = gmdate( "Y-m-d H:i:s" ); 	// always in GMT
		$payment->userid = $data->user->get('id');				// user to be associated with the subscription
		$payment->typeid = $data->type_id;						// id value for subscription type
		$payment->status = $data->subs_status;					// status of the SUBSCRIPTION
		$payment->paymentid = ''; 								// will be set by save process
		$payment->expires_datetime = $data->subs_expires;		// if not set, will be set to the correct day in the future by save process - only set this (=NOW) if the payment was invalid
		
		// set plugin data for further processing by the AS email system
		$payment->payment_plugin_data = !empty($data->payment_plugin_data) ? array_merge($data->payment_plugin_data, $paymentPluginData) : $paymentPluginData;
		
		if ( ! ($already = TiendaHelperPayment::getInstance( $payment->payment_id, $payment->payment_type, '1', 'payment_id' )) ) { 
			if ( ! $payment->save()) {
				$paymentError = JText::_( 'PAYPALPRO MESSAGE PAYMENT STORE FAILED' );
			}
			
			// if we get to here without errors and a user was blocked
			// it seems like we can unblock him
			$user =& $data->user;
			if ( ! count($errors) && $user->get('automatically_registered') && $user->get('block') ) {
				TiendaHelperUser::unblockUser($user->get('id'));
			}

			// if this is not the first recurring payment we need to deactivate previous user subscriptions
			if (!empty($payment->payment_plugin_data['is_recurring'])) {
				$this->_processOldSubscriptions($data->user->get('id'), $data->type_id, $payment->id);
			}
		}
		else {
			$paymentError = JText::_( 'PAYPALPRO MESSAGE TRANSACTION INVALID' );
			$payment->payment_plugin_data['validation_errors'][] = $paymentError;

			// because we don't use the TiendaHelperPayment::save method
			// we have to trigger the email plugin here manually
			$import = JPluginHelper::importPlugin( 'Tienda', 'emails' );        
        	$dispatcher =& JDispatcher::getInstance();
        	
        	$emailTypePrefix = !empty($payment->payment_plugin_data['is_recurring']) ? 'recurring' : 'new';
        	$emailType = $emailTypePrefix . '_failed';
        	$emailData = get_object_vars($payment);        	
        	
        	$dispatcher->trigger( 'sendEmailNotices', array( $emailData, $data->user, $emailType ) );			
		}
		
		return $paymentError;
	}
	
	/**
	 * Deactivates old user subscriptions
	 * 
	 * @param int $user_id
	 * @param int $type_id
	 * @param int $new_payment_id
	 * @return bool
	 * @access protected
	 */
	function _processOldSubscriptions($user_id, $type_id, $new_payment_id)
	{
		$db =& JFactory::getDBO();
		
		// get subscriptions to deactivate
		$q = "		
			SELECT
				s.u2tid as id
			FROM
				#__Tienda_users2types s
			INNER JOIN
				#__Tienda_payments p
			ON 
				s.paymentid = p.id
			WHERE
				p.id != " . (int) $new_payment_id . "
			AND
				s.userid = " . (int) $user_id . " 
				AND s.typeid = " . (int) $type_id . "
				AND s.status = 1
				AND p.payment_type = " . $db->Quote($this->_plugin_type) . "		
		";
		$db->setQuery($q);
		
		if ($data = $db->loadResultArray()) {
			$ids = implode (',', $data);
			
			$q = "
				UPDATE
					#__Tienda_users2types
				SET
					`status` = 0
				WHERE
					u2tid IN (" . $ids . ")			
			";
			$db->setQuery($q);
			$db->query();
		}
		
		return true;
	}
	
	/**
	 * Calculates the subscription expiration date
	 * 
	 * @param string $payment_date
	 * @param int $duration
	 * @param string $duration_unit 
	 * @return string MySQL formatted date
	 * @access protected
	 */
	function _getExpirationDate($payment_date, $duration, $duration_unit)
	{
		$duration = $duration * $this->_getDuration($duration_unit);
			
		$p_date =& JFactory::getDate($payment_date);			
		$expires_date = $p_date->toUnix() + ( $duration * 24 * 3600 );
		
		$expires_datetime =& JFactory::getDate($expires_date);
		$expires_datetime = $expires_datetime->toMySQL();
		
		return $expires_datetime;
	}
	
	/**
	 * Translates the duration unit into the days
	 * 
	 * @param string $unit
	 * @return string|boolean
	 */
	function _getDuration($unit)
	{
		switch ($unit) {
			case 'Day' : return 1;
			case 'Week' : return 7;
			case 'Month' : return 30;
			case 'Year' : return 365;
						
			default : return false;
		}		
	}
    
    /**
     * Validates the received data
     * 
     * @abstract      
     * @return boolean
     * @access public
     */
    function validateData() {}
    
	/**
     * Processes the payment
     * 
     * @abstract
     * @return boolean
     * @access public
     */
    function process() {}    
    
    /**
     * Gets PayPal URL to send the request
     * 
     * @abstract
     * @return string
     * @access protected
     */
    function _getPaypalUrl() {}
    
    
    /**
     * Gets ready-for-storing payment details
     * 
     * @abstract
     * @return string
     * @access protected
     */
    function _getFormattedPaymentDetails() {}
    
        
}
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

/**
 * Tienda PayPalPro Base Processor
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
//			$type = TiendaHelperPayment::getTable('Type');
			JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
			$type = &JTable::getInstance( 'orderpayments', 'TiendaTable' );			
			
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
    	$amount 	= (float) $subscr_obj->orderpayment_amount; 
    	
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
		   . "FROM #__Tienda_orderpayments "
		   . "WHERE  orderpayment_type = " . $db->quote($this->_plugin_type) . " "
		   . "AND transaction_details LIKE '%" . addcslashes($db->getEscaped($keyword), '%_') . "%'"       
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
     * Processes the sale payment
     * 
     * @param array $data
     * 
     * @access protected
     */
	function _updatePayment($data)
    {
        $errors = $this->getErrors();
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = &JTable::getInstance( 'orderpayments', 'TiendaTable' );
        $orderpayment->load($data->orderpayment_id);
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data->order_id );
        // this is updating the order status
        $order->order_state_id = $this->_params->get('payment_received_order_state', '17'); // PAYMENT RECEIVED
        
        // do post payment actions
        $setOrderPaymentReceived = true;
        
        // send email
        $send_email = true;
        
        $orderpayment->transaction_id = $data->transaction_id;            // transaction id from payment method
        $orderpayment->transaction_status = $data->payment_status;        // status of the PAYMENT
        $orderpayment->orderpayment_amount = $data->payment_amount; 
        $payment->transaction_details = $data->payment_details;       // payment amount
       
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
     * This method can be executed by a payment plugin after a succesful payment
     * to perform acts such as enabling file downloads, removing items from cart,
     * updating product quantities, etc
     *
     * @param unknown_type $order_id
     * @return unknown_type
     */
    function setOrderPaymentReceived( $order_id )
    {
       Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
       TiendaHelperOrder::setOrderPaymentReceived( $order_id ); 
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
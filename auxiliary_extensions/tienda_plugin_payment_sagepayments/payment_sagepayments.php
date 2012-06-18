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

class plgTiendaPayment_sagepayments extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_sagepayments';
    
    var $merchant_id = '';
    var $merchant_key = '';
    
    /**
     * 
     * @param $subject
     * @param $config
     * @return unknown_type
     */
	function plgTiendaPayment_sagepayments(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		
		$this->merchant_id = $this->_getParam('merchant_id');
		$this->merchant_key = $this->_getParam('merchant_key');
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
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_amount = $data['orderpayment_amount'];
        $vars->orderpayment_type = $this->_element;
        
        $vars->cardtype = JRequest::getVar("cardtype");
        $vars->cardholder = JRequest::getVar("cardholder");
        $vars->cardnum = JRequest::getVar("cardnum");
        
        $exp_month = JRequest::getInt("cardexp_month");
        if ($exp_month < '10') { $exp_month = '0'.$exp_month; } 
        $exp_year = JRequest::getInt("cardexp_year");
        $exp_year = $exp_year - 2000;
        $cardexp = $exp_month.$exp_year;
        
        $vars->cardexp = $cardexp;
        $vars->cardcv2 = JRequest::getVar("cardcv2");
        
        $this->_genAsterixes($vars);
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
        $vars = new JObject();
        
        $app =& JFactory::getApplication();
        $paction = JRequest::getVar( 'paction' );
        
        switch ($paction)
        {
            case 'process_recurring':
                // TODO Complete this
                // $this->_processRecurringPayment();
                $app->close();
              break;
            case 'process': // process respond from the server
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
              break;
            default:
                $vars->message = JText::_('COM_TIENDA_INVALID_ACTION');
                $html = $this->_getLayout('message', $vars);
              break;
        }
        
        return $html;
    }
    
    /**
     * Prepares variables and 
     * Renders the form for collecting payment info
     * 
     * @return unknown_type
     */
    function _renderForm( $data )
    {
        $vars = new JObject();
        $vars->prepop = array();
        $vars->cctype_input   = $this->_cardTypesField();
        
        $html = $this->_getLayout('form', $vars);
        
        return $html;
    }
    
    /**
     * Verifies that all the required form fields are completed
     * if any fail verification, set 
     * $object->error = true  
     * $object->message .= '<li>x item failed verification</li>'
     * 
     * @param $submitted_values     array   post data
     * @return unknown_type
     */
    function _verifyForm( $submitted_values )
    {
        $object = new JObject();
        $object->error = false;
        $object->message = '';
        $user = JFactory::getUser();
 
        foreach ($submitted_values as $key=>$value) 
        {
            switch ($key) 
            {
            	case "cardtype":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))  // the field is required
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('Sagepayments Card Type Invalid')."</li>";
                    }
                  break;
            	case "cardholder":
            		$len = JString::strlen($submitted_values[$key]);
                    if ($submitted_values['cardtype'] != 'PAYPAL' && // not a required field for paypal
                    	(!isset($submitted_values[$key]) || !$len || $len > 50))  // the date has to have exactly 50 digits max
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('Sagepayments Card Holder Invalid')."</li>";
                    }
                  break;
                case "cardnum":
            		$len = JString::strlen($submitted_values[$key]);
                	if ($submitted_values['cardtype'] != 'PAYPAL' &&  // not a required field for paypal
                	(!isset($submitted_values[$key]) || !$len || $len > 20))   // the date has to have 20 digits max
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('Sagepayments Card Number Invalid')."</li>";
                    }
                  break;
                case "cardexp":
            		$len = JString::strlen($submitted_values[$key]);
                	if ($submitted_values['cardtype'] != 'PAYPAL') // not a required field for paypal
                	{
                		if($len != 4) // the date has to have exactly 4 digits
	                	{
	                        $object->error = true;
	                        $object->message .= "<li>".JText::_('Sagepayments Card Expiration Date Invalid')."</li>";
	                    }
                    	else
                    	{
                    		switch($this->_checkFormatDate($submitted_values[$key], true, $submitted_values['cardexp']))
                    		{
                    			case 0 : // invalid format
			                        $object->error = true;
			                        $object->message .= "<li>".JText::_('Sagepayments Card Expiration Date Invalid')."</li>";
		                    		break;
                    			case 2 : // not valid anymore
			                        $object->error = true;
			                        $object->message .= "<li>".JText::_('Sagepayments Card Expiration Date Invalid Card')."</li>";
		                    		break;
                    		}
                    	}
                	}
                  break;
                case "cardcv2":
            		$len = JString::strlen($submitted_values[$key]);
            		if ($submitted_values['cardtype'] != 'PAYPAL') // not a required field for paypal
            		{
            			if(($submitted_values['cardtype'] == 'AMEX' && $len != 4) ||
            			   ($submitted_values['cardtype'] != 'AMEX' && $len != 3))
            			{            			
	                        $object->error = true;
	                        $object->message .= "<li>".JText::_('Sagepayments Card CV2 Invalid')."</li>";
            			}
                    } 
                  break;
                  
                default:
                  break;
            }
        }   
        return $object;
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
     * Checks date format of a card if the card is still valid
     * @param $value Value to check
     * @param $exp If it is the Expiration Date or Start Date
     * @param $start Value of Start Date field
     * @return Integer (0 - Invalid; 1 - Valid, 2 - Not yet/Not any more)
     * @access protected
     */
    function _checkFormatDate( $value, $exp = false, $start = '')
    {
        // we assume we received a $value in the format MMYY
        $month = substr($value, 0, 2);
        $year = substr($value, 2);
        
        if (empty($month) || empty($year) || strlen($year) != 2 || (int)$month > 12 || (int)$month < 1) {
            return 0;
        }
        
        $act = date('ym'); // get actual date
        if($exp) // if we are checking the Expiration Date
        {
	        if($act > $year.$month) // see if the expiration date isn't smaller than the actual date
    	    	return 2; // the card is no longer valid
    	    	
    	    if(isset($start) && $year.$month < $start)
	   	    	return 0; // the card dates are invalid
        }
        else // or we are checking the Start Date
        {
	        if($act < $year.$month) // see if the expiration date isn't smaller than the actual date
    	    	return 2; // the card is not valid yet
        }
        return 1; // everything is ok
    }
    
    /**
     * Generates a dropdown list of valid CC types
     * @param $fieldname
     * @param $default
     * @param $options
     * @return unknown_type
     * @access protected
     */
    function _cardTypesField( $field='cardtype', $default='', $options='' )
    {       
        $types = array();
        $types[] = JHTML::_('select.option', 'VISA', JText::_('COM_TIENDA_VISA') );
//        $types[] = JHTML::_('select.option', 'DELTA', JText::_('Visa Delta') );
//        $types[] = JHTML::_('select.option', 'UKE', JText::_('Visa Electron') );
        $types[] = JHTML::_('select.option', 'MC', JText::_('COM_TIENDA_MASTERCARD') );
        $types[] = JHTML::_('select.option', 'MAESTRO', JText::_('COM_TIENDA_MAESTRO') );
//        $types[] = JHTML::_('select.option', 'MAESTRO', JText::_('International Maestro') );
//        $types[] = JHTML::_('select.option', 'SOLO', JText::_('Solo') );
        $types[] = JHTML::_('select.option', 'Amex', JText::_('COM_TIENDA_AMERICANEXPRESS') );
//        $types[] = JHTML::_('select.option', 'DINERS', JText::_('COM_TIENDA_DINERSCLUB') );
//        $types[] = JHTML::_('select.option', 'JCB', JText::_('COM_TIENDA_JCB') );
//        $types[] = JHTML::_('select.option', 'LASER', JText::_('Laser') );
//        $types[] = JHTML::_('select.option', 'PAYPAL', JText::_('Paypal') );
        
        $return = JHTML::_('select.genericlist', $types, $field, $options, 'value','text', $default);
        return $return;
    }

    /**
     * Generates asterix strings for all secret fields
     * @param $vars
     * @access protected
     */
    function _genAsterixes( &$vars )
    {
    	// show only last 4 digits of the Card Number
        $vars->cardnum_last4 = substr( JRequest::getVar("cardnum"), -4 );
        
        // hide whole CV2 number
        if($vars->cardcv2 != '')
        {
	        $len = JString::strlen($vars->cardcv2);
	        for($i = 0; $i < $len; $i++)
		    	$vars->cardcv2_asterix .= '* ';
        }
        else
        	$vars->cardcv2_asterix='';
	    	
    }
    
    /**
     * Formats the value of the card date
     * 
     * @param string $format
     * @param $value
     * @return string|boolean date string or false
     * @access protected
     */
    function _getFormattedCardDate($format, $value)
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
     * Gets the gateway URL
     * 
     * @param string $type 3D or non-3D-Secure
     * @return string
     * @access protected
     */
    function _getActionUrl($type = 'non-secure')
    {
        if ($type == 'non-secure') 
        {
            $url  = $this->params->get('sandbox') ? 'https://www.sagepayments.net/cgi-bin/eftBankcard.dll?transaction' : 'https://www.sagepayments.net/cgi-bin/eftBankcard.dll?transaction';
        }
            else 
        {
            $url = $this->params->get('sandbox') ? 'https://www.sagepayments.net/cgi-bin/eftBankcard.dll?transaction' : 'https://www.sagepayments.net/cgi-bin/eftBankcard.dll?transaction';         
        }
        
        return $url;
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
        $sb_value = $this->params->get($sandbox_param);
        
        if ($this->params->get('sandbox') && !empty($sb_value)) {
            $param = $this->params->get($sandbox_param, $default);
        }
        else {
            $param = $this->params->get($name, $default);
        }
        
        return $param;
    }
    
    /**
     * Gets an existing user or creates a new one
     * 
     * @param array $submitted_values Data for a new user
     * @param int $user_id Existing user id (optional)
     * @return JUser object
     * @access protected
     */
    function _getUser( $submitted_values, $user_id = 0 )
    {
        $config = Tienda::getInstance();
        
        if ($user_id) {
            $user =& JFactory::getUser($user_id);
        }
        else {
            $user =& JFactory::getUser();   
        }   
        
        if ($user->id) {
            return $user;
        }
        
        Tienda::load( 'TiendaHelperUser', 'helpers.user' );
        
        $newuser_email = $submitted_values['CustomerEMail'];
        // create user from email
        jimport('joomla.user.helper');
        $details['name']        = $newuser_email;
        $details['username']    = $newuser_email;
        $details['email']       = $newuser_email;
        $details['password']    = JUserHelper::genRandomPassword();
        $details['password2']   = $details['password'];
        $details['block']       = $config->get('block_automatically_registered') ? '1' : '0';
        
        if ($user =& TiendaHelperUser::createNewUser( $details )) {
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
     * Processes the payment
     * 
     * This method process only real time (simple and subscription create) payments
     * The scheduled recurring payments are processed by the corresponding method
     * 
     * @return string
     * @access protected
     */
    function _process()
    {
        /*
         * perform initial checks 
         */
        if ( ! JRequest::checkToken() ) {
            return $this->_renderHtml( JText::_('COM_TIENDA_INVALID_TOKEN') );
        }
        
        $data = JRequest::get('post');
        
        // get order information
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data['order_id'] );
        if ( empty($order->order_id) ) {
            return JText::_('Tienda Sagepayments Message Invalid Order');
        }
         
        if ( empty($this->merchant_id)) {
            return JText::_('Tienda Sagepayments Message Missing Merchant Id');
        }
        
        if ( empty($this->merchant_key)) {
            return JText::_('Tienda Sagepayments Message Missing Merchant Key');
        }
		
        // prepare the form for submission to sagepayments.com
        $process_vars = $this->_getProcessVars($data);
        
        return $this->_processSimplePayment($process_vars);
    }
    
    /**
     * Prepares parameters for the payment processing
     * 
     * @param object $data Post variables
     * @return array
     * @access protected
     */
    function _getProcessVars($data)
    {
        $merchant_id = $this->merchant_id;
        $merchant_key = $this->merchant_key; 
        
		// for now, we impomenet only standard payment method
        $paymenttype           =  '01';

        // joomla info
        $user =& JFactory::getUser();
        $sagepayments_userid                = $user->id;
        
        // order info
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data['order_id'] );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $data['orderpayment_id'] );
        $orderinfo = JTable::getInstance('OrderInfo', 'TiendaTable');
        $orderinfo->load( array( 'order_id'=>$data['order_id']) );
        $billingzone = JTable::getInstance('Zones', 'TiendaTable');
        $billingzone->load( $orderinfo->billing_zone_id );
		$currency = JTable::getInstance('Currencies', 'TiendaTable');
		$currency->load( array( 'currency_id'=>$order->currency_id) );
		$country = JTable::getInstance('Countries', 'TiendaTable');
		$country->load( array( 'country_id'=>$orderinfo->billing_country_id) );
		
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $sagepayments_description           = JText::_('COM_TIENDA_ORDER_NUMBER').": ".$order->order_id;
        $sagepayments_amount                = TiendaHelperBase::number( $orderpayment->orderpayment_amount, array( 'thousands'=>'' ) );
        $sagepayments_invoice_num           = $data['orderpayment_id']; 
        
        // customer information
        $sagepayments_fname                 = $orderinfo->billing_first_name;
        $sagepayments_lname                 = $orderinfo->billing_last_name;
        $sagepayments_address1               = substr($orderinfo->billing_address_1,0,100); 
        $sagepayments_address2               = substr($orderinfo->billing_address_2,0,100);
        $sagepayments_address               = $sagepayments_address1;
        if (!empty($sagepayments_address2)) { $sagepayments_address .= $sagepayments_address2; }     
        
        $sagepayments_city                  = $orderinfo->billing_city;
        $sagepayments_zip                   = $orderinfo->billing_postal_code;
        $sagepayments_country               = $country->country_isocode_2;
        $sagepayments_state                 = $billingzone->code;
        	    
        $sagepayments_card_num              = str_replace(" ", "", str_replace("-", "", $data['cardnum'] ) ); 
        $sagepayments_phone                 = $orderinfo->billing_phone_1;
        
        $sagepayments_exp_date              = $this->_getFormattedCardDate('my', $data['cardexp'] );
               
        $sagepayments_useremail             = empty($orderinfo->user_email) ? $user->email : $orderinfo->user_email;
        

        // put all values into an array (does not support Gift Aid Payment)
        $sagepayments_values             = array
        (
        	"T_code"				=> $paymenttype,
            "M_id"               	=> $merchant_id,
            "M_key"                 => $merchant_key,
        	"T_amt"				    => $sagepayments_amount,
            "T_ordernum"            => $order->order_id,
            "C_name" 	            => $data['cardholder'],
            "C_cardnumber"          => $sagepayments_card_num,
            "C_exp"	                => $sagepayments_exp_date,
            "C_cvv" 		        => $data['cardcv2'],
            "C_address"             => $sagepayments_address,
        	"C_city"                => $sagepayments_city,
            "C_state"    		    => $sagepayments_state,
            "C_zip"                 => $sagepayments_zip,
            "C_email"               => $sagepayments_useremail
        );
        
        return $sagepayments_values;
    }
    

    /**
     * Sends a request to the server using cURL
     * 
     * @param string $url
     * @param string $content
     * @param arrray $http_headers (optional)
     * @return string
     * @access protected 
     */
    function _sendRequest($url, $content, $http_headers = array())
    {
        $ch = curl_init($url); 
        
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        
        if (is_array($http_headers) && count($http_headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        }
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($ch);
        curl_close ($ch);

        return $resp;
    }
    
    /**
     * Simple logger 
     * 
     * @param string $text
     * @param string $type
     * @return void
     */
    function _log($text, $type = 'message')
    {
        if (!empty($this->_isLog)) 
        {
            $file = JPATH_ROOT . "/cache/{$this->_element}.log";
            $date = JFactory::getDate();
            
            $f = fopen($file, 'a');
            fwrite($f, "\n\n" . $date->toFormat('%Y-%m-%d %H:%M:%S'));
            fwrite($f, "\n" . $type . ': ' . $text);            
            fclose($f);
        }   
    }
        

    /**
     * Processes a simple (non-recurring payment)
     * by sending data to sagepayments.com and interpreting the response
     * and managing the order as required
     *
     * @param array $sagepayments_values  
     * @return string
     * @access protected
     */
    function _processSimplePayment($sagepayments_values) 
    {
        $html = '';
        
        // prepare the array for posting to sagepayments.com
        $fields = '';
        foreach( $sagepayments_values as $key => $value ) {
            $fields .= "$key=" . urlencode( $value ) . "&"; 
        }
            
        // send a request
        $resp = $this->_sendRequest($this->_getActionUrl('non-secure'), rtrim( $fields, "& " ));     
        $this->_log($resp);
        
        // evaluate the response
        $evaluateResponse = $this->_evaluateSimplePaymentResponse( $resp, $sagepayments_values );
        $html = $evaluateResponse;

        return $html;
    }
    
    /**
     * Proceeds the simple payment
     * 
     * @param string $resp
     * @param array $submitted_values
     * @return object Message object
     * @access protected
     */
    function _evaluateSimplePaymentResponse( $resp, $submitted_values )
    {
        $object = new JObject();
        $object->message = '';
        $errors = array();
        $payment_status = '0';
        $order_status = '0';
        
        $posted = false;
        if ( ! ($user =& $this->_getUser( $submitted_values ))) {
            $errors[] = JText::_('Sagepayments Message Unknown User');

            $user =& JFactory::getUser();
            $user->set('id', 0);
        }
        $send_email = false;
        
        // Evaluate a typical response from sagepayments.com
        $exploded = array();
        $exploded["approval_indicator"]         = $resp[1];
        $exploded["approval_code"]              = substr($resp, 2, 6);
        $exploded["approval_message"]           = substr($resp, 8, 32);
        $exploded["frontend_indicator"]         = substr($resp, 40, 2);
        $exploded["cvv_indicator"]              = $resp[42];
        $exploded["avs_indicator"]              = $resp[43];
        $exploded["risk_indicator"]             = substr($resp, 44, 2);
        $exploded["reference"]                  = substr($resp, 46, 10);
        $exploded["field_separator"]            = $resp[56]; // ascii 28, chr(28)

        // explode the rest of the string by the field_separator
        $resp_string = substr($resp, 57, strpos( $resp, chr(03) ) );
        $resp_array = explode( $exploded["field_separator"], $resp_string );
        
        $exploded["order_number"]               = $resp_array[0];

        foreach ($exploded as $key=>$value)
        {
            if (empty($value)) {
                $value = "NO VALUE RETURNED";
            }
            $value = trim($value);
            
            switch ($key) 
            {
            	case 'approval_indicator' : // status in human-readable form
            		switch($value)
            		{
            			case 'A' : // approved
            			    $payment_status = '1';
            				break;
            			case 'E' : // front-end declined
            			case 'X' : // gateway declined
                            $payment_status = '0';            				
            				$errors[] = JText::sprintf("TIENDA SAGEPAYMENTS MESSAGE PAYMENT NOT APPROVED CODE %s", $value);
            				$errors[] = $exploded["approval_message"];
                            break;
            			default : // if something went wrong
	            			$errors[] = $value;
	            			break;
            		}
            		break;
                case 'approval_code' :
                    switch($value)
                    {
                        default : //
                            break; 
                    }
                    break;
            	case 'cvv_indicator' :
                    switch($value)
                    {
                        case 'M' : // matched & approved
                            break;
                        case 'N' : // no match
                            break;
                        case 'P' : // Not Processed
                            break;
                        case 'S' : // Merchant Has Indicated that CVV2 Is Not Present
                            break;
                        case 'U' : // Issuer is not certified and/or has not provided Visa Encryption Keys
                            break;
                        default : // if something went wrong
                            $errors[] = $value;
                            break;
                    }
                    break;
                case 'avs_indicator' :
                    switch($value)
                    {
                        default : //
                            break; 
                    }
                    break;
                case 'risk_indicator' :
                    switch($value)
                    {
                        default : //
                            break; 
                    }
                    break;
                //                case 'avs_indicator': // Response Code
                //                    switch ($value) 
                //                    {
                //                    	case "INVALID":
                //                        case "MALFORMED":
                //                    	case "ERROR":
                //                            // Error
                //                            $payment_status = '0';
                //                            $order_status = '0';
                //                            $errors[] = JText::_('Tienda Sagepayments Error processing payment');
                //                          break;
                //                        case "REJECTED":
                //                            // Declined
                //                            $payment_status = '0';
                //                            $order_status = '0';
                //                            $errors[] = JText::_('Tienda Sagepayments Card was declined');
                //                          break;
                //                        case "REGISTERED":
                //                        case "OK":
                //                        // Approved
                //                            $payment_status = '1';
                //                           break;
                //                        default:
                //                          break;
                //                    }
                //                  break;
            }
        }
        
        if($posted) // if the payment has been already processed, show the message only
        	return count($errors) ? implode("\n", $errors) : '';

        // =======================
        // verify & create payment
        // =======================
        // check that payment amount is correct for order_id
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load(array('order_id'=>$submitted_values['T_ordernum']));

        $orderpayment->transaction_details  = Tienda::dump( $resp );
        $orderpayment->transaction_id       = $exploded["reference"];
        $orderpayment->transaction_status   = $exploded["approval_message"];
           
        // set the order's new status and update quantities if necessary
        Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $submitted_values['T_ordernum'] );
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
		
		if (empty($errors))
		{
			$return = JText::_('TIENDA SAGEPAYMENTS MESSAGE PAYMENT SUCCESS');
			return $return;                
		}

		$vars = new JObject();
		$vars->message = implode("\n", $errors);
        $html = $this->_getLayout('fail', $vars);
		
		return $html;
    }
    
    /**
     * 
     * Enter description here ...
     * @param $row
     * @return unknown_type
     */
    public function showCVV($row)
    {
        if (!$this->_isMe($row))
        {
            return null;
        }
        $vars = new JObject();
        echo $this->_getLayout('showcvv', $vars);
        return;
    }
}

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

class plgTiendaPayment_authorizedotnet extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_authorizedotnet';
    var $login_id    = '';
    var $tran_key    = '';
    var $_isLog      = false;
    
    /**
     * 
     * @param $subject
     * @param $config
     * @return unknown_type
     */
	function plgTiendaPayment_authorizedotnet(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		
        $this->login_id = $this->_getParam( 'login_id' ); 
        $this->tran_key = $this->_getParam( 'tran_key' );
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
        
        $vars->cardtype = !empty($data['cardtype']) ? $data['cardtype'] : JRequest::getVar("cardtype");
        $vars->cardnum = !empty($data['cardnum']) ? $data['cardnum'] : JRequest::getVar("cardnum");      
        $vars->cardcvv = !empty($data['cardcvv']) ? $data['cardcvv'] : JRequest::getVar("cardcvv");
        $vars->cardnum_last4 = substr( $vars->cardnum, -4 );

        $exp_month = !empty($data['cardexp_month']) ? $data['cardexp_month'] : JRequest::getVar("cardexp_month");
        if ($exp_month < '10') { $exp_month = '0'.$exp_month; } 
        $exp_year = !empty($data['cardexp_year']) ? $data['cardexp_year'] : JRequest::getVar("cardexp_year");
        $exp_year = $exp_year - 2000;
        $cardexp = $exp_month.$exp_year;
        $vars->cardexp = $cardexp;
        
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
        
        $app = JFactory::getApplication();
        $paction = JRequest::getVar( 'paction' );
        
        switch ($paction)
        {
            case 'process_recurring':
                // TODO Complete this
                // $this->_processRecurringPayment();
                $app->close();                  
              break;
            case 'process':
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
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key])) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('COM_TIENDA_AUTHORIZEDOTNET_CARD_TYPE_INVALID')."</li>";
                    }
                  break;
                case "cardnum":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key])) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('COM_TIENDA_AUTHORIZEDOTNET_CARD_NUMBER_INVALID')."</li>";
                    } 
                  break;
                case "cardexp":
                    if (!isset($submitted_values[$key]) || JString::strlen($submitted_values[$key]) != 4) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('COM_TIENDA_AUTHORIZEDOTNET_CARD_EXPIRATION_DATE_INVALID')."</li>";
                    } 
                  break;
                case "cardcvv":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key])) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('COM_TIENDA_AUTHORIZEDOTNET_CARD_CVV_INVALID')."</li>";
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
     * Generates a dropdown list of valid CC types
     * @param $fieldname
     * @param $default
     * @param $options
     * @return unknown_type
     */
    function _cardTypesField( $field='cardtype', $default='', $options='' )
    {       
        $types = array();
        $types[] = JHTML::_('select.option', 'Visa', JText::_('COM_TIENDA_VISA') );
        $types[] = JHTML::_('select.option', 'Mastercard', JText::_('COM_TIENDA_MASTERCARD') );
        $types[] = JHTML::_('select.option', 'AmericanExpress', JText::_('COM_TIENDA_AMERICANEXPRESS') );
        $types[] = JHTML::_('select.option', 'Discover', JText::_('COM_TIENDA_DISCOVER') );
        $types[] = JHTML::_('select.option', 'DinersClub', JText::_('COM_TIENDA_DINERSCLUB') );
        $types[] = JHTML::_('select.option', 'JCB', JText::_('COM_TIENDA_JCB') );
        
        $return = JHTML::_('select.genericlist', $types, $field, $options, 'value','text', $default);
        return $return;
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
     * Gets the gateway URL
     * 
     * @param string $type Simple or subscription
     * @return string
     * @access protected
     */
    function _getActionUrl($type = 'simple')
    {
        if ($type == 'simple') 
        {
            $url  = $this->params->get('sandbox') ? 'https://test.authorize.net/gateway/transact.dll' : 'https://secure.authorize.net/gateway/transact.dll';
        }
            else 
        {
            // recurring billing url
            $url = $this->params->get('sandbox') ? 'https://apitest.authorize.net/xml/v1/request.api' : 'https://api.authorize.net/xml/v1/request.api';         
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
            $user = JFactory::getUser($user_id);
        }
        else {
            $user = JFactory::getUser();   
        }   
        
        if ($user->id) {
            return $user;
        }
        
        Tienda::load( 'TiendaHelperUser', 'helpers.user' );
        
        $newuser_email = $submitted_values['email'];
        // create user from email
        jimport('joomla.user.helper');
        $details['name']        = $newuser_email;
        $details['username']    = $newuser_email;
        $details['email']       = $newuser_email;
        $details['password']    = JUserHelper::genRandomPassword();
        $details['password2']   = $details['password'];
        $details['block']       = $config->get('block_automatically_registered') ? '1' : '0';
        
        if ($user = TiendaHelperUser::createNewUser( $details )) {
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
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data['order_id'] );
        if ( empty($order->order_id) ) {
            return JText::_('COM_TIENDA_AUTHORIZEDOTNET_MESSAGE_INVALID_ORDER');
        }
         
        if ( empty($this->login_id)) {
            return JText::_('COM_TIENDA_AUTHORIZEDOTNET_MESSAGE_MISSING_MERCHANT_LOGIN_ID');
        }
        if ( empty($this->tran_key)) {
            return JText::_('COM_TIENDA_AUTHORIZEDOTNET_MESSAGE_MISSING_TRANSACTION_KEY');
        }
        
        // prepare the form for submission to auth.net
        $process_vars = $this->_getProcessVars($data);
        
        return $this->_processSimplePayment($process_vars);
        
        // do form verification to make sure information is both present and valid
        //        $verifyForm = $this->_verifyForm( $process_vars );
        //        if ($verifyForm->error == true ) 
        //        {
        //            JError::raiseNotice( 'Invalid Form Values', $this->getError() );
        //            return;
        //            // TODO We could make the plugin output a form requesting a second or third payment attempt?
        //            // display the form again, prepopulated, with an error message saying why it wasn't submitted to auth.net
        //            // return $this->_renderForm( $type, $process_vars, '1', '1');
        //        }
        
        // perform further processing based on the payment type
        //        if ($typeParams->get('is_recurring')) {     
        //            return $this->_processSubscriptionCreate($type, $typeParams, $process_vars);
        //        }
        //        else {
        //            return $this->_processSimplePayment($type, $process_vars);
        //        }
    }
    
    /**
     * Prepares parameters for the payment processing
     * 
     * @param object $data Post variables
     * @param string $auth_net_login_id
     * @param string $auth_net_tran_key
     * @return array
     * @access protected
     */
    function _getProcessVars($data)
    {
        $auth_net_login_id = $this->login_id; 
        $auth_net_tran_key = $this->tran_key;
        
        // testing values
        $DEBUGGING                  = 0;                # Display additional information to track down problems
        $TESTING                    = 0;                # Set the testing flag so that transactions are not live
        $ERROR_RETRIES              = 2;                # Number of transactions to post if soft errors occur
        
        // NOT to be changed
        $auth_x_delim_char          = "|";              # not to be changed
        
        // leave this alone too -- used to identify the payment type
        $auth_paymenttype           = $this->_element;
        
        // transaction info
        // TODO Add a param for cc/echeck/both?
        $auth_method                = "CC";                 // or ECHECK
            # ECHECK DATA           // see http://developer.authorize.net/guides/AIM/eCheck_Developer_Guide/Transaction_Data_Requirements.htm
            # x_bank_aba_code       // 9 digits     
            # x_bank_acct_num       // up to 20 digits
            # x_bank_acct_type      // CHECKING, BUSINESSCHECKING, SAVINGS
            # x_bank_name           // Up to 50
            # x_bank_acct_name      // up to 50
            # x_echeck_type         // ARC, BOC, CCD, PPD, TEL, WEB
            # x_bank_check_number   // Up to 15
            # x_recurring_billing   // required if x_echeck_type=WEB    //  TRUE, FALSE

        // joomla info
        $user = JFactory::getUser();
        $submitted_email            = !empty($data['email']) ? $data['email'] : '';
        $auth_userid                = $user->id;
        $auth_useremail             = empty($user->id) ? $submitted_email : $user->email;
        
        // order info
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data['order_id'] );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $data['orderpayment_id'] );
        $orderinfo = JTable::getInstance('OrderInfo', 'TiendaTable');
        $orderinfo->load( array( 'order_id'=>$data['order_id']) );

        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $auth_description           = JText::_('COM_TIENDA_ORDER_NUMBER').": ".$order->order_id;
        $auth_amount                = TiendaHelperBase::number( $orderpayment->orderpayment_amount, array( 'thousands'=>'' ) );
        $auth_invoice_num           = $data['orderpayment_id']; 
        
        // customer information
        $auth_fname                 = $orderinfo->billing_first_name; //"Charles D.";
        $auth_lname                 = $orderinfo->billing_last_name; // "Gaulle";
        $auth_address               = $orderinfo->billing_address_1. " ".$orderinfo->billing_address_2; 
                                                                                 //"342 N. Main Street #150";
        $auth_city                  = $orderinfo->billing_city; //"Ft. Worth";
        $auth_state                 = $orderinfo->billing_zone_name; //"TX";
        $auth_zip                   = $orderinfo->billing_postal_code; //"12345";
        $auth_country               = $orderinfo->billing_country_name; // "US";
        $auth_card_num              = str_replace(" ", "", str_replace("-", "", $data['cardnum'] ) ); 
                                                                                 // "5424000000000015";
        $auth_exp_date              = $this->_getFormattedCardExprDate('my', $data['cardexp'] ); // "1209";
        $auth_cvv                   = $data['cardcvv']; //"";

        $chars = array( '(', ')', ' ', '-', '.' );
        $billing_phone = str_replace( $chars, '', $orderinfo->billing_phone_1 );
        $auth_phone                 = $billing_phone;
        
        // put all values into an array
        $authnet_values             = array
        (
            "x_login"               => $auth_net_login_id,
            "x_version"             => "3.1",
            "x_delim_char"          => $auth_x_delim_char,
            "x_delim_data"          => "TRUE",
            "x_type"                => "AUTH_CAPTURE",
            "x_method"              => $auth_method,
            "x_tran_key"            => $auth_net_tran_key,
            "x_relay_response"      => "FALSE",
            "x_card_num"            => $auth_card_num,
            "x_exp_date"            => $auth_exp_date,
            "x_description"         => $auth_description,
            "x_amount"              => $auth_amount,
            "x_first_name"          => $auth_fname,
            "x_last_name"           => $auth_lname,
            "x_address"             => $auth_address,
            "x_city"                => $auth_city,
            "x_state"               => $auth_state,
            "x_zip"                 => $auth_zip,
            "x_country"             => $auth_country,
            "x_cust_id"             => $auth_userid,
            "x_email"               => $auth_useremail,
            "x_card_code"           => $auth_cvv,
            "x_invoice_num"         => $auth_invoice_num,
        	"x_phone"               => $auth_phone,
            "tienda_order_id"       => $data['order_id'],
            "tienda_orderpayment_id"    => $data['orderpayment_id'],
            "cardtype"              => $data['cardtype'],
            "email"                 => $auth_useremail,
        );
        
        return $authnet_values;
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
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // uncomment this line if you get no gateway response. ###
        
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
        if ($this->_isLog) {
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
     * by sending data to auth.net and interpreting the response
     * and managing the order as required
     *
     * @param array $authnet_values  
     * @return string
     * @access protected
     */
    function _processSimplePayment($authnet_values) 
    {
        $html = '';
        
        // prepare the array for posting to authorize.net
        $fields = '';
        foreach( $authnet_values as $key => $value ) {
            $fields .= "$key=" . urlencode( $value ) . "&"; 
        }
            
        // send a request
        $resp = $this->_sendRequest($this->_getActionUrl('simple'), rtrim( $fields, "& " ));
        $this->_log($resp);
        
        // evaluate the response
        $evaluateResponse = $this->_evaluateSimplePaymentResponse( $resp, $authnet_values );
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
        $send_email = false;
        $object = new JObject();
        $object->message = '';
        $html = '';
        $errors = array();
        $payment_status = '0';
        $order_status = '0';

        $auth_x_delim_char          = "|";              # not to be changed
        
        if ( ! ($user = $this->_getUser( $submitted_values ))) {
            $errors[] = JText::_('COM_TIENDA_AUTHORIZEDOTNET_MESSAGE_UNKNOWN_USER');

            $user = JFactory::getUser();
            $user->set('id', 0);
        }       
        
        // Evaluate a typical response from auth.net
        $exploded = explode( $auth_x_delim_char, $resp );
                
        for ($i=0; $i<count($exploded); $i++)
        {
            $value = $exploded[$i]; 
    
            if ($value == "") {
                $value = "NO VALUE RETURNED";
            }
            
            $key = $i + 1;          
            switch ($key) 
            {
                case 1:
                    // Response Code
                    $paymentResponse = $value;
                    switch ($value) 
                    {
                        case "1":
                            // Approved
                            $payment_status = '1';
                            $subs_status = '1';
                          break;
                        case "2":
                            // Declined
                            $payment_status = '0';
                            $order_status = '0';
                            $errors[] = JText::_('COM_TIENDA_CARD_WAS_DECLINED');
                          break;
                        case "3":
                        default:
                            // Error
                            $payment_status = '0';
                            $order_status = '0';
                            $errors[] = JText::_('COM_TIENDA_TIENDA_AUTHORIZEDOTNET_PAYMENT_ERROR_PROCESSING_PAYMENT_MESSAGE') . $exploded[3];
                          break;
                    }
                  break;
                case 2:
                    // Response Subcode
                    $subcodeResponse = $value;
                    break;  
                case 3:
                    // Response Reason Code
                    $reasoncodeResponse = $value;
                    break;  
                case 4:
                    // Response Reason Text
                    $reasonResponse = $value;
                    break;  
                case 5:
                    // Approval Code
                    $approvalcodeResponse = $value;
                    break;  
                case 6:
                    // AVS Result Code
                    $avsResponse = $value;
                    break;  
                case 7:
                    // Transaction ID
                    $transactionidResponse = $value;
                    break;  
                case 8:
                    // Invoice Number (x_invoice_num)
                    $invoiceResponse = $value;
                    break;  
                case 9:
                    // Description (x_description)
                    $descriptionResponse = $value;
                    break;  
                case 10:
                    // Amount (x_amount)
                    $amountResponse = $value;
                    break;  
                case 11:
                    // Method (x_method)
                    $methodResponse = $value;
                    break;  
                case 12:
                    // Transaction Type (x_type)
                    $transactiontypeResponse = $value;
                    break;  
                case 13:
                    // Customer ID (x_cust_id)
                    $customeridResponse = $value;
                    break;  
                case 14:
                    // Cardholder First Name (x_first_name)
                    $fnameResponse = $value;
                    break;  
                case 15:
                    // Cardholder Last Name (x_last_name)
                    $lnameResponse = $value;
                    break;  
                case 16:
                    // Company (x_company)
                    $companyResponse = $value;
                    break;  
                case 17:
                    // Billing Address (x_address)
                    $addressResponse = $value;
                    break;  
                case 18:
                    // City (x_city)
                    $cityResponse = $value;
                    break;  
                case 19:
                    // State (x_state)
                    $stateResponse = $value;
                    break;  
                case 20:
                    // ZIP (x_zip)
                    $zipResponse = $value;
                    break;  
                case 21:
                    // Country (x_country)
                    $countryResponse = $value;
                    break;  
                case 22:
                    // Phone (x_phone)
                    $phoneResponse = $value;
                    break;  
                case 23:
                    // Fax (x_fax)
                    $faxResponse = $value;
                    break;  
                case 24:
                    // E-Mail Address (x_email)
                    $emailResponse = $value;
                    break;  
                case 25:
                    // Ship to First Name (x_ship_to_first_name)
                    $shipfnameResponse = $value;
                    break;  
                case 26:
                    // Ship to Last Name (x_ship_to_last_name)
                    $shiplnameResponse = $value;
                    break;  
                case 27:
                    // Ship to Company (x_ship_to_company)
                    $shipcompanyResponse = $value;
                    break;  
                case 28:
                    // Ship to Address (x_ship_to_address)
                    $shipaddressResponse = $value;
                    break;  
                case 29:
                    // Ship to City (x_ship_to_city)
                    $shipcityResponse = $value;
                    break;  
                case 30:
                    // Ship to State (x_ship_to_state)
                    $shipstateResponse = $value;
                    break;  
                case 31:
                    // Ship to ZIP (x_ship_to_zip)
                    $shipzipResponse = $value;
                    break;  
                case 32:
                    // Ship to Country (x_ship_to_country)
                    $shipcountryResponse = $value;
                    break;  
                case 33:
                    // Tax Amount (x_tax)
                    $taxResponse = $value;
                    break;  
                case 34:
                    // Duty Amount (x_duty)
                    $dutyResponse = $value;
                    break;  
                case 35:
                    // Freight Amount (x_freight)
                    $freightResponse = $value;
                    break;  
                case 36:
                    // Tax Exempt Flag (x_tax_exempt)
                    $taxexemptResponse = $value;
                    break;  
                case 37:
                    // PO Number (x_po_num)
                    $ponumResponse = $value;
                    break;  
                case 38:
                    // MD5 Hash
                    $md5hashResponse = $value;
                    break;  
                case 39:
                    // Card Code Response (CVV)
                    $cvvResponse = $value;
                    switch ($value) 
                    {
                        case "M":
                            // Match
                            $cvv_status = '1';
                          break;
                        case "N":
                            // NO Match
                            $cvv_status = '0';
                            $payment_status = '0';
                            $order_status = '0';
                            $errors[] = JText::_('COM_TIENDA_CVV_DID_NOT_MATCH');
                          break;
                        case "P":
                            // Not Processed
                            $cvv_status = '0';
                          break;
                        case "S":
                            // Should have been present
                            $cvv_status = '0';
                            $payment_status = '0';
                            $order_status = '0';
                            $errors[] = JText::_('COM_TIENDA_CVV_SHOULD_HAVE_BEEN_PRESENT');
                          break;
                        case "U":
                            // Issuer unable to process request
                            $cvv_status = '0';
                            $payment_status = '0';
                            $order_status = '0';
                            $errors[] = JText::_('COM_TIENDA_CVV_ISSUER_UNABLE_TO_PROCESS_REQUEST');
                          break;
                        default:
                            // No Value returned
                            $cvv_status = null;
                          break;
                    }
                    break;  
                case 40:
                case 41:
                case 42:
                case 43:
                case 44:
                case 45:
                case 46:
                case 47:
                case 48:
                case 49:
                case 50:
                case 51:
                case 52:
                case 53:
                case 54:
                case 55:
                case 55:
                case 56:
                case 57:
                case 58:
                case 59:
                case 60:
                case 61:
                case 62:
                case 63:
                case 64:
                case 65:
                case 66:
                case 67:
                case 68:
                    // Reserved by auth.net
                    break;  
                default:
                    // if here, then $key is not one of the reserved 68 fields
                    // we're now processing custom fields sent through auth.net and back to us
                    // such as itemTypeid, paymentType, and cardtype
                    switch ($key) 
                    {
                        case "tienda_orderpayment_id":
                            $orderpayment_id = $value;
                          break;
                        default:
                            // orphan key
                          break;
                    }
                    break;
            }
        }
        
        // orderpayment_id is always in this part of the response
        $orderpayment_id = $exploded[69];
        
        // =======================
        // verify & create payment
        // =======================
            // check that payment amount is correct for order_id
            JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
            $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
            $orderpayment->load( $orderpayment_id );
            if (empty($orderpayment->order_id))
            {
                // TODO fail
            }
            $orderpayment->transaction_details  = $resp;
            $orderpayment->transaction_id       = $transactionidResponse;
            $orderpayment->transaction_status   = $paymentResponse;

            Tienda::load( 'TiendaHelperBase', 'helpers._base' );
            $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
            $respond_amount = TiendaHelperBase::number( $amountResponse, array( 'thousands'=>'' ) );
            if ($stored_amount != $respond_amount ) {
                $errors[] = JText::_('COM_TIENDA_TIENDA_AUTHORIZEDOTNET_MESSAGE_PAYMENT_AMOUNT_INVALID');
                $errors[] = $stored_amount . " != " . $respond_amount;
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
                $return = JText::_('COM_TIENDA_TIENDA_AUTHORIZEDOTNET_MESSAGE_PAYMENT_SUCCESS');
                return $return;                
            }
            
            if (!empty($errors))
            {
                $string = implode("\n", $errors);
                $return = "<div class='note_pink'>" . $string . "</div>";
                return $return;
            }

        // ===================
        // end custom code
        // ===================
    }

    /**
     * Shows the CVV popup
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

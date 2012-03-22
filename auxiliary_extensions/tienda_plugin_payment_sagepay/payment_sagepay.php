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

class plgTiendaPayment_sagepay extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_sagepay';
    
    var $vendor_name = '';
    var $sagepay_protocol = '2.23';
    
    /**
     * 
     * @param $subject
     * @param $config
     * @return unknown_type
     */
	function plgTiendaPayment_sagepay(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		
		$this->vendor_name = $this->_getParam('vendor_name');
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
        $vars->cardexp = JRequest::getVar("cardexp");
        $vars->cardst = JRequest::getVar("cardst");
        $vars->cardissuenum = JRequest::getVar("cardissuenum");
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
                $vars->message = JText::_('Invalid Action');
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
                        $object->message .= "<li>".JText::_('Sagepay Card Type Invalid')."</li>";
                    }
                  break;
            	case "cardholder":
            		$len = JString::strlen($submitted_values[$key]);
                    if ($submitted_values['cardtype'] != 'PAYPAL' && // not a required field for paypal
                    	(!isset($submitted_values[$key]) || !$len || $len > 50))  // the date has to have exactly 50 digits max
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('Sagepay Card Holder Invalid')."</li>";
                    }
                  break;
                case "cardnum":
            		$len = JString::strlen($submitted_values[$key]);
                	if ($submitted_values['cardtype'] != 'PAYPAL' &&  // not a required field for paypal
                	(!isset($submitted_values[$key]) || !$len || $len > 20))   // the date has to have 20 digits max
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('Sagepay Card Number Invalid')."</li>";
                    }
                  break;
                case "cardst":
                	if ($submitted_values['cardtype'] != 'PAYPAL') // not a required field for paypal
                    {
	            		$len = JString::strlen($submitted_values[$key]);
                    	if($len && $len != 4) // the date has to have exactly 4 digits (if its entered)
                    	{
	                        $object->error = true;
	                        $object->message .= "<li>".JText::_('Sagepay Card Start Date Invalid')."</li>";
                    	}
                    	else
                    	{
                    		if($len == 4) // if the format of start date is correct
                    		{
	                    		switch($this->_checkFormatDate($submitted_values[$key]))
	                    		{
	                    			case 0 : // invalid format
				                        $object->error = true;
				                        $object->message .= "<li>".JText::_('Sagepay Card Start Date Invalid')."</li>";
	                    				break;
	                    			case 2 : // not valid yet
				                        $object->error = true;
				                        $object->message .= "<li>".JText::_('Sagepay Card Start Date Invalid Card')."</li>";
			                    		break;
                    		}
                    		}
                    	}
                    }
                  break;
                case "cardexp":
            		$len = JString::strlen($submitted_values[$key]);
                	if ($submitted_values['cardtype'] != 'PAYPAL') // not a required field for paypal
                	{
                		if($len != 4) // the date has to have exactly 4 digits
	                	{
	                        $object->error = true;
	                        $object->message .= "<li>".JText::_('Sagepay Card Expiration Date Invalid')."</li>";
	                    }
                    	else
                    	{
                    		switch($this->_checkFormatDate($submitted_values[$key], true, $submitted_values['cardst']))
                    		{
                    			case 0 : // invalid format
			                        $object->error = true;
			                        $object->message .= "<li>".JText::_('Sagepay Card Expiration Date Invalid')."</li>";
		                    		break;
                    			case 2 : // not valid anymore
			                        $object->error = true;
			                        $object->message .= "<li>".JText::_('Sagepay Card Expiration Date Invalid Card')."</li>";
		                    		break;
                    		}
                    	}
                	}
                  break;
                case "cardissuenum":
            		$len = JString::strlen($submitted_values[$key]);
                	if ($submitted_values['cardtype'] != 'PAYPAL' && // not a required field for paypal
	                	($len && ((int)$submitted_values[$key] < 0 || $len > 2))) // the number has to have 0, 1 or 2 digits
                	{
                        $object->error = true;
                        $object->message .= "<li>".JText::_('Sagepay Card Issue Number Invalid')."</li>";
                    } 
                  break;
                case "cardcv2":
            		$len = JString::strlen($submitted_values[$key]);
            		if ($submitted_values['cardtype'] != 'PAYPAL' && $len) // not a required field for paypal
            		{
            			if(($submitted_values['cardtype'] == 'AMEX' && $len != 4) ||
            			   ($submitted_values['cardtype'] != 'AMEX' && $len != 3))
            			{            			
	                        $object->error = true;
	                        $object->message .= "<li>".JText::_('Sagepay Card CV2 Invalid')."</li>";
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
        $types[] = JHTML::_('select.option', 'VISA', JText::_('Visa') );
        $types[] = JHTML::_('select.option', 'DELTA', JText::_('Visa Delta') );
        $types[] = JHTML::_('select.option', 'UKE', JText::_('Visa Electron') );
        $types[] = JHTML::_('select.option', 'MC', JText::_('Mastercard') );
        $types[] = JHTML::_('select.option', 'MAESTRO', JText::_('UK Maestro') );
        $types[] = JHTML::_('select.option', 'MAESTRO', JText::_('International Maestro') );
        $types[] = JHTML::_('select.option', 'SOLO', JText::_('Solo') );
        $types[] = JHTML::_('select.option', 'Amex', JText::_('American Express') );
        $types[] = JHTML::_('select.option', 'DINERS', JText::_('DinersClub') );
        $types[] = JHTML::_('select.option', 'JCB', JText::_('JCB') );
        $types[] = JHTML::_('select.option', 'LASER', JText::_('Laser') );
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
	    	
        if($vars->cardissuenum != '')
        {
        	$len = JString::strlen($vars->cardissuenum);
	        for($i = 0; $i < $len; $i++)
		    	$vars->cardissuenum_asterix .= '*';
        }
        else        
        	$vars->cardissuenum_asterix ='';
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
            $url  = $this->params->get('sandbox') ? 'https://test.sagepay.com/Simulator/VSPDirectGateway.asp' : 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
        }
            else 
        {
            // recurring billing url
            $url = $this->params->get('sandbox') ? 'https://test.sagepay.com/gateway/service/direct3dcallback.vsp' : 'https://live.sagepay.com/gateway/service/direct3dcallback.vsp';         
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
        $config = TiendaConfig::getInstance();
        
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
            return $this->_renderHtml( JText::_('Invalid Token') );
        }
        
        $data = JRequest::get('post');
        
        // get order information
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data['order_id'] );
        if ( empty($order->order_id) ) {
            return JText::_('Tienda Sagepay Message Invalid Order');
        }
         
        if ( empty($this->vendor_name)) {
            return JText::_('Tienda Sagepay Message Missing Vendor Name');
        }
		
        // prepare the form for submission to sagepay.com
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
        $vendor_name = $this->vendor_name; 
        
		// for now, we impomenet only standard payment method
        $paymenttype           =  'PAYMENT';

        // joomla info
        $user =& JFactory::getUser();
        $submitted_email            = !empty($data['email']) ? $data['email'] : '';
        $sagepay_userid                = $user->id;
        $sagepay_useremail             = empty($user->id) ? $submitted_email : $user->email;
        
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
        $sagepay_description           = JText::_('Order Number').": ".$order->order_id;
        $sagepay_amount                = TiendaHelperBase::number( $orderpayment->orderpayment_amount, array( 'thousands'=>'' ) );
        $sagepay_invoice_num           = $data['orderpayment_id']; 
        
        // customer information
        $sagepay_fname                 = $orderinfo->billing_first_name;
        $sagepay_lname                 = $orderinfo->billing_last_name;
        $sagepay_address1               = substr($orderinfo->billing_address_1,0,100); 
        $sagepay_address2               = substr($orderinfo->billing_address_2,0,100); 
        
        $sagepay_city                  = $orderinfo->billing_city;
        $sagepay_zip                   = $orderinfo->billing_postal_code;
        $sagepay_country               = $country->country_isocode_2;
        if($sagepay_country == 'US')
	        $sagepay_state                 = $billingzone->code;
	    else
			$sagepay_state = '';	    
        $sagepay_card_num              = str_replace(" ", "", str_replace("-", "", $data['cardnum'] ) ); 
        $sagepay_phone                 = $orderinfo->billing_phone_1;
        
        $sagepay_exp_date              = $this->_getFormattedCardDate('my', $data['cardexp'] );
        $sagepay_start_date              = $this->_getFormattedCardDate('my', $data['cardst'] );        
        $vendorTxCode=$data['order_id'];

        // put all values into an array (does not support Gift Aid Payment)
        $sagepay_values             = array
        (
        	"VPSProtocol"			=> $this->sagepay_protocol,
        	"TxType"				=> $paymenttype,
            "Vendor"               	=> $vendor_name,
            "VendorTxCode"          => $vendorTxCode,
        	"Amount"				=> $sagepay_amount,
            "Currency"	            => $currency->currency_code,
            "Description"           => $sagepay_description,
            "CardHolder" 	        => $data['cardholder'],
            "CardNumber"            => $sagepay_card_num,
            "StartDate"          	=> $sagepay_start_date,
            "ExpiryDate"	        => $sagepay_exp_date,
            "IssueNumber"           => $data['cardissuenum'],
            "CV2" 		            => $data['cardcv2'],
            "CardType"              => $data['cardtype'],
            "BillingSurname"        => $sagepay_lname,
            "BillingFirstnames"  	=> $sagepay_fname,
            "BillingAddress1"       => $sagepay_address1,
            "BillingAddress2"       => $sagepay_address2,
        	"BillingCity"           => $sagepay_city,
            "BillingPostCode"       => $sagepay_zip,
            "BillingCountry"        => $sagepay_country,
            "BillingState"    		=> $sagepay_state,
            "BillingPhone"          => $sagepay_phone,
        
        	// for now, delivery address is the same as the billing address
            "DeliverySurname"       => $sagepay_lname,
            "DeliveryFirstnames"  	=> $sagepay_fname,
            "DeliveryAddress1"      => $sagepay_address1,
            "DeliveryAddress2"      => $sagepay_address2,
        	"DeliveryCity"          => $sagepay_city,
            "DeliveryPostCode"      => $sagepay_zip,
            "DeliveryCountry"       => $sagepay_country,
            "DeliveryState"    		=> $sagepay_state,
            "DeliveryPhone"         => $sagepay_phone,
        	"CustomerEMail"			=> $sagepay_useremail,
        	"ClientIPAddress"		=> $_SERVER['REMOTE_ADDR'], // get client's IP
//        	"NotificationURL"		=> JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process_final&tmpl=component", // funny -> required field but official documentation does not say a word about it :)
        	"AccountType"			=> 'E' // just to be sure - default e-commerce merchant account
        );
        return $sagepay_values;
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
     * by sending data to sagepay.com and interpreting the response
     * and managing the order as required
     *
     * @param array $sagepay_values  
     * @return string
     * @access protected
     */
    function _processSimplePayment($sagepay_values) 
    {
        $html = '';
        
        // prepare the array for posting to sagepay.com
        $fields = '';
        foreach( $sagepay_values as $key => $value ) {
            $fields .= "$key=" . urlencode( $value ) . "&"; 
        }
            
        // send a request
        $resp = $this->_sendRequest($this->_getActionUrl('non-secure'), rtrim( $fields, "& " ));
        $this->_log($resp);
        
        // evaluate the response
        $evaluateResponse = $this->_evaluateSimplePaymentResponse( $resp, $sagepay_values );
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
        $sagepay_del_line = "\n";
        $sagepay_del_val = '=';
        $posted = false;
        $text_posted = 'The VendorTxCode \''.$submitted_values['VendorTxCode'].'\' has been used before';
        if ( ! ($user =& $this->_getUser( $submitted_values ))) {
            $errors[] = JText::_('Sagepay Message Unknown User');

            $user =& JFactory::getUser();
            $user->set('id', 0);
        }
        $send_email = false;
        
        // Evaluate a typical response from sagepay.com
        $exploded = explode( $sagepay_del_line, $resp );

        for ($i=0; $i<count($exploded); $i++)
        {
            if (empty($exploded[$i]))
            {
                continue;
            }
            
            list($key, $value) = explode($sagepay_del_val, $exploded[$i]); // parse key and value
            if ($value == "") {
                $value = "NO VALUE RETURNED";
            }
            $value = trim($value);
            
            switch ($key) 
            {
            	case 'StatusDetail' : // status in human-readable form
            		switch($paymentResponse)
            		{
            			case 'OK' :
            				break;
            			case 'INVALID' :
            				if(strpos($value,$text_posted) !== false) // if the paymant has been already processed, inform the customer
            				{
            					$posted = true;
		            			$errors[] = JText::_('Tienda Sagepay Message Payment Already Occured');
            					break;
            				}
            			default : // if something went wrong
	            			$errors[] = $value;
            		}
            		break;
            	case 'VSPProtocol' : // Protocol
            		if($value != $this->sagepay_protocol) // protocols of transation are not equal
            		{
						// Error
						$payment_status = '0';
						$order_status = '0';
						$errors[] = JText::_('Tienda Sagepay Error processing payment');
            		}
                case 'Status': // Response Code
                    $paymentResponse = $value;
                    switch ($value) 
                    {
                    	case "INVALID":
                        case "MALFORMED":
                    	case "ERROR":
                            // Error
                            $payment_status = '0';
                            $order_status = '0';
                            $errors[] = JText::_('Tienda Sagepay Error processing payment');
                          break;
                        case "REJECTED":
                            // Declined
                            $payment_status = '0';
                            $order_status = '0';
                            $errors[] = JText::_('Tienda Sagepay Card was declined');
                          break;
                        case "REGISTERED":
                        case "OK":
                        // Approved
                            $payment_status = '1';
                           break;
                        default:
                          break;
                    }
                  break;
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
        $orderpayment->load(array('order_id'=>$submitted_values['VendorTxCode']));

        $orderpayment->transaction_details  = $resp;
        $orderpayment->transaction_id       = $paymentResponse;
        $orderpayment->transaction_status   = $paymentResponse;
           
        // set the order's new status and update quantities if necessary
        Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $submitted_values['VendorTxCode'] );
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
			$return = JText::_('TIENDA SAGEPAY MESSAGE PAYMENT SUCCESS');
			return $return;                
		}
		
		return count($errors) ? implode("\n", $errors) : '';
    }
}

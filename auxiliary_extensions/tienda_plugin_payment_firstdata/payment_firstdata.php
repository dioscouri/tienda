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

class plgTiendaPayment_firstdata extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    	= 'payment_firstdata';
    var $store_number   = '';
    var $key_file	    = '';
    var $debugging		= false;
    
    /**
     * 
     * @param $subject
     * @param $config
     * @return unknown_type
     */
	function plgTiendaPayment_firstdata(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
        $this->store_number = $this->_getParam( 'store_number' ); 
        $this->key_file 	= $this->_getParam( 'key_file' );
        $this->debugging	= $this->params->get('debug');
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
	 * get the payment gateway host
	 */
    function _getPaymentGatewayHost()
    {
		return $this->params->get('sandbox') ? 'staging.linkpt.net' : 'secure.linkpt.net';
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
        
        $newuser_email = $submitted_values['email'];
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
        
        $vars->cardnum = JRequest::getVar("cardnum");
        $vars->cardexpmonth = JRequest::getVar("cardexpmonth");
        $vars->cardexpyear = JRequest::getVar("cardexpyear");        
        $vars->cardcvv = JRequest::getVar("cardcvv");
        $vars->cardnum_last4 = substr( JRequest::getVar("cardnum"), -4 );
        
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
            case 'process':
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
              break;
            default:
                $vars->message = JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE INVALID ACTION');
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
                case "cardnum":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key])) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('TIENDA LINKPOINT FIRSTDATA CARD NUMBER INVALID')."</li>";
                    } 
                  break;
                case "cardexpmonth":
                    if (!isset($submitted_values[$key]) || JString::strlen($submitted_values[$key]) != 2) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('TIENDA LINKPOINT FIRSTDATA CARD EXPIRATION MONTH INVALID')."</li>";
                    } 
                  break;
                case "cardexpyear":
                    if (!isset($submitted_values[$key]) || JString::strlen($submitted_values[$key]) != 2) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('TIENDA LINKPOINT FIRSTDATA CARD EXPIRATION YEAR INVALID')."</li>";
                    } 
                  break;                  
                case "cardcvv":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key])) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('TIENDA LINKPOINT FIRSTDATA CARD CVV INVALID')."</li>";
                    } 
                  break;
                default:
                  break;
            }
        }   
            
        return $object;
    }
    
    function _getUserEmail($data)
    {
        // joomla info
        $user =& JFactory::getUser();
        $submitted_email            = !empty($data['email']) ? $data['email'] : '';
        return empty($user->id) ? $submitted_email : $user->email;
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
            return $this->_renderHtml( JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE INVALID TOKEN') );
        }
        
        //validate plugin parameters have been set
		if ( empty($this->store_number)) {
            return JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE MISSING STORE NUMBER');
        }
        if ( empty($this->key_file)) {
            return JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE MISSING CERTIFICATE FILE');
        }             

		//get the information to send to LinkPoint / FirstData        
        $data = JRequest::get('post');
        
        // get order information
        $order_id 			= $data['order_id'];
        $order_payment_id	= $data['orderpayment_id'];
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $order_id );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $order_payment_id );
        $orderinfo = JTable::getInstance('OrderInfo', 'TiendaTable');
        $orderinfo->load( array( 'order_id'=>$order_id) );
        
        if ( empty($order->order_id) || empty($orderpayment) || empty($orderinfo)) {
            return JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE INVALID ORDER');
        }        

        //setting up the payment gateway information
        $host			= $this->_getPaymentGatewayHost();        
        $port			= "1129";
       
    	//card information
        $card_num              		= str_replace(" ", "", str_replace("-", "", $data['cardnum'] ) ); 
        $card_expmonth				= $data["cardexpmonth"];
        $card_expyear				= $data["cardexpyear"];  
        $cvv          				= $data['cardcvv'];
        
        //set the order total to be charged to the credit card
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $charge_total               = TiendaHelperBase::number( $orderpayment->orderpayment_amount, array( 'thousands'=>'' ) );
        
        $user_email = $this->_getUserEmail($data);
        
        $order_values = array
        (
        	"host"			=>	$host,
        	"port"			=>	$port,
        	"keyfile"		=> 	realpath($this->key_file), 
			"configfile" 	=>	$this->store_number,        
        	"cvmindicator"	=> 	"provided",
        	"cardnumber"	=>	$card_num,
			"cardexpmonth" 	=>	$card_expmonth,
			"cardexpyear"	=>	$card_expyear,
			"cvmvalue"		=>	$cvv,
			"name"			=>	$orderinfo->billing_first_name.' '.$orderinfo->billing_last_name,
        	"company"		=>	$orderinfo->billing_company,
			"address1"		=>	$orderinfo->billing_address_1,
        	"address2"		=>	$orderinfo->billing_address_2,
        	"city"			=>	$orderinfo->billing_city,
        	"state"			=>	$orderinfo->billing_zone_name,
        	"country"		=>	$orderinfo->billing_country_name,
        	"phone"			=>	$orderinfo->billing_phone_1,
			"fax"			=>	$orderinfo->billing_fax,
			"email"			=>	$user_email,
        	"zip"			=>	$orderinfo->billing_postal_code,
        	"chargetotal"	=>	$charge_total,
        	"oid"			=>  $order->order_id,
        	"ordertype"		=>  "SALE"
        );
        
        //process the payment through the LinkPoint / FirstData payment gateway
		$result = $this->curl_process($order_values, $this->params->get('sandbox'));  # use curl methods

        return $this->_evaluatePaymentResponse( $result, $order_values, $order_payment_id );
    }
    
    function printResponse($result)
    {
		echo "<table border=1>";
		while (list($key, $value) = each($result))
		{
			echo "<tr>";
			echo "<td>" . htmlspecialchars($key) . "</td>";
			echo "<td><b>" . htmlspecialchars($value) . "</b></td>";
			echo "</tr>";
		}
		echo "</table><br>\n";
    }

    function _evaluatePaymentResponse( $result, $submitted_values, $orderpayment_id )
    {
    	if ($this->debugging){
    		$this->printResponse($result);
    	}
    	
        $errors = array();
     
        //check that the user is known
        if ( ! ($user =& $this->_getUser( $submitted_values ))) {
            $errors[] = JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE UNKNOWN USER');
            $user =& JFactory::getUser();
            $user->set('id', 0);
        }                

        //check if the payment got approved
    	if ($this->debugging)
    		echo "r_approved=".$result["r_approved"]."<br/>";
        $approved = ($result["r_approved"] == "APPROVED");
    	if (!$approved){
			if ($this->debugging)
				echo JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE DECLINED').' ('.$result["r_error"].')<br/>';		 
			$errors[] = JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE DECLINED').' ('.$result["r_error"].')';
		}

        //get the order payment record		        
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $orderpayment_id );
        if ($orderpayment->order_id != $result["r_ordernum"])
        {
        	$errors[] = JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE UNKNOWN ORDER');
        }
        
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$order = JTable::getInstance('Orders', 'TiendaTable');
		$order->load( $orderpayment->order_id );
		if (count($errors)){
			$order->order_state_id = $this->params->get('failed_order_state', '10'); //FAILED
		}		
		else{
			$order->order_state_id = $this->params->get('payment_received_order_state', '17'); // PAYMENT RECEIVED;
        	$setOrderPaymentReceived = true;
        	$send_email = true;
		}
        
        // save the order
        if (!$order->save())
        {
        	$errors[] = $order->getError();
		}		
        
        $orderpayment->transaction_details  = implode("\n", $result);
        $orderpayment->transaction_id       = $result["r_code"];
        $orderpayment->transaction_status   = $result["r_approved"];
        if (!$orderpayment->save())
        {
        	$errors[] = $orderpayment->getError(); 
		}
            
        if (!empty($setOrderPaymentReceived))
        {
        	$this->setOrderPaymentReceived( $orderpayment->order_id );
		}
            
        if (!empty($send_email))
        {
        	// send notice of new order
            Tienda::load( "TiendaHelperBase", 'helpers._base' );
			$helper = TiendaHelperBase::getInstance('Email');
			$model = Tienda::getClass("TiendaModelOrders", "models.orders");
			$model->setId( $orderpayment->order_id );
			$order = $model->getItem();
			$helper->sendEmailNotices($order, 'new_order');
		}

		if (empty($errors) && $approved)
        {
        	return JText::_('TIENDA LINKPOINT FIRSTDATA MESSAGE PAYMENT SUCCESS');
    	}            
        return count($errors) ? implode("\n", $errors) : '';
    }

    /**
     * 
     * Sends the payment request to the LinkPoint / FirstData payment gateway
     * @param $data
     * @param $sandbox
     */
	function curl_process($data, $sandbox)
	{
		if ($this->debugging){
			# print out incoming hash
			echo "at curl_process, incoming data: <br>";
			while (list($key, $value) = each($data))
				 echo htmlspecialchars($key) . " = " . htmlspecialchars($value) . "<BR>\n";
			reset($data); 
		}

		// otherwise convert incoming hash to XML string
		$xml = $this->buildXML($data);
		if ($this->debugging)
			echo "<br>sending xml string:<br>" . htmlspecialchars($xml) . "<br><br>";    

		// set up transaction variables
		$key = $data["keyfile"];
		$port = $data["port"];
		$host = "https://".$data["host"].":".$port."/LSGSXML";

		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL,$host);
		curl_setopt ($ch, CURLOPT_POST, 1); 
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt ($ch, CURLOPT_SSLCERT, $key);
		if($sandbox){
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

		if ($this->debugging)
			curl_setopt ($ch, CURLOPT_VERBOSE, 1);

		#  use curl to send the xml SSL string
		$result = curl_exec ($ch);
		
		if ($this->debugging)
		{
			if($result === false)
			{
			    echo 'Curl error: ' . curl_error($ch);
			}
			else
			{
			    echo 'Operation completed without any errors';
			}			
		}
		curl_close($ch);

		if (strlen($result) < 2)    # no response
			return "<r_approved>FAILURE</r_approved><r_error>Could not connect.</r_error>"; 

		if ($this->debugging)
			echo "<br>server responds:<br>" . htmlspecialchars($result) . "<br><br>";

		#convert xml response to hash
		return $this->decodeXML($result);
	}

	/**
	 * 
	 * Converts the LSGS response xml string to a hash of name-value pairs
	 * @param $xmlstg
	 */
	function decodeXML($xmlstg)
	{
		preg_match_all ("/<(.*?)>(.*?)\</", $xmlstg, $out, PREG_SET_ORDER);
		
		$n = 0;
		while (isset($out[$n]))
		{
			$retarr[$out[$n][1]] = strip_tags($out[$n][0]);
			$n++; 
		}
		return $retarr;
	}

	/**
	 * 
	 * converts a hash of name-value pairs to the correct XML format for LSGS
	 * @param $pdata
	 */
	function buildXML($pdata)
	{
		### ORDEROPTIONS NODE ###
		$xml = "<order><orderoptions>";

		if (isset($pdata["ordertype"]))
			$xml .= "<ordertype>" . $pdata["ordertype"] . "</ordertype>";

		if (isset($pdata["result"]))
			$xml .= "<result>" . $pdata["result"] . "</result>";

		$xml .= "</orderoptions>";

		### CREDITCARD NODE ###
		$xml .= "<creditcard>";

		if (isset($pdata["cardnumber"]))
			$xml .= "<cardnumber>" . $pdata["cardnumber"] . "</cardnumber>";

		if (isset($pdata["cardexpmonth"]))
			$xml .= "<cardexpmonth>" . $pdata["cardexpmonth"] . "</cardexpmonth>";

		if (isset($pdata["cardexpyear"]))
			$xml .= "<cardexpyear>" . $pdata["cardexpyear"] . "</cardexpyear>";

		if (isset($pdata["cvmvalue"]))
			$xml .= "<cvmvalue>" . $pdata["cvmvalue"] . "</cvmvalue>";

		if (isset($pdata["cvmindicator"]))
			$xml .= "<cvmindicator>" . $pdata["cvmindicator"] . "</cvmindicator>";

		if (isset($pdata["track"]))
			$xml .= "<track>" . $pdata["track"] . "</track>";

		$xml .= "</creditcard>";

		### BILLING NODE ###
		$xml .= "<billing>";

		if (isset($pdata["name"]))
			$xml .= "<name>" . $pdata["name"] . "</name>";

		if (isset($pdata["company"]))
			$xml .= "<company>" . $pdata["company"] . "</company>";

		if (isset($pdata["address1"]))
			$xml .= "<address1>" . $pdata["address1"] . "</address1>";
		elseif (isset($pdata["address"]))
			$xml .= "<address1>" . $pdata["address"] . "</address1>";

		if (isset($pdata["address2"]))
			$xml .= "<address2>" . $pdata["address2"] . "</address2>";

		if (isset($pdata["city"]))
			$xml .= "<city>" . $pdata["city"] . "</city>";
			
		if (isset($pdata["state"]))
			$xml .= "<state>" . $pdata["state"] . "</state>";
			
		if (isset($pdata["zip"]))
			$xml .= "<zip>" . $pdata["zip"] . "</zip>";

		if (isset($pdata["country"]))
			$xml .= "<country>" . $pdata["country"] . "</country>";

		if (isset($pdata["userid"]))
			$xml .= "<userid>" . $pdata["userid"] . "</userid>";

		if (isset($pdata["email"]))
			$xml .= "<email>" . $pdata["email"] . "</email>";

		if (isset($pdata["phone"]))
			$xml .= "<phone>" . $pdata["phone"] . "</phone>";

		if (isset($pdata["fax"]))
			$xml .= "<fax>" . $pdata["fax"] . "</fax>";

		if (isset($pdata["addrnum"]))
			$xml .= "<addrnum>" . $pdata["addrnum"] . "</addrnum>";

		$xml .= "</billing>";

		
		## SHIPPING NODE ##
		$xml .= "<shipping>";

		if (isset($pdata["sname"]))
			$xml .= "<name>" . $pdata["sname"] . "</name>";

		if (isset($pdata["saddress1"]))
			$xml .= "<address1>" . $pdata["saddress1"] . "</address1>";

		if (isset($pdata["saddress2"]))
			$xml .= "<address2>" . $pdata["saddress2"] . "</address2>";

		if (isset($pdata["scity"]))
			$xml .= "<city>" . $pdata["scity"] . "</city>";

		if (isset($pdata["sstate"]))
			$xml .= "<state>" . $pdata["sstate"] . "</state>";
		elseif (isset($pdata["state"]))
			$xml .= "<state>" . $pdata["state"] . "</state>";

		if (isset($pdata["szip"]))
			$xml .= "<zip>" . $pdata["szip"] . "</zip>";
		elseif (isset($pdata["sip"]))
			$xml .= "<zip>" . $pdata["zip"] . "</zip>";

		if (isset($pdata["scountry"]))
			$xml .= "<country>" . $pdata["scountry"] . "</country>";

		if (isset($pdata["scarrier"]))
			$xml .= "<carrier>" . $pdata["scarrier"] . "</carrier>";

		if (isset($pdata["sitems"]))
			$xml .= "<items>" . $pdata["sitems"] . "</items>";

		if (isset($pdata["sweight"]))
			$xml .= "<weight>" . $pdata["sweight"] . "</weight>";

		if (isset($pdata["stotal"]))
			$xml .= "<total>" . $pdata["stotal"] . "</total>";

		$xml .= "</shipping>";


		### TRANSACTIONDETAILS NODE ###
		$xml .= "<transactiondetails>";

		if (isset($pdata["oid"]))
			$xml .= "<oid>" . $pdata["oid"] . "</oid>";

		if (isset($pdata["ponumber"]))
			$xml .= "<ponumber>" . $pdata["ponumber"] . "</ponumber>";

		if (isset($pdata["recurring"]))
			$xml .= "<recurring>" . $pdata["recurring"] . "</recurring>";

		if (isset($pdata["taxexempt"]))
			$xml .= "<taxexempt>" . $pdata["taxexempt"] . "</taxexempt>";

		if (isset($pdata["terminaltype"]))
			$xml .= "<terminaltype>" . $pdata["terminaltype"] . "</terminaltype>";

		if (isset($pdata["ip"]))
			$xml .= "<ip>" . $pdata["ip"] . "</ip>";

		if (isset($pdata["reference_number"]))
			$xml .= "<reference_number>" . $pdata["reference_number"] . "</reference_number>";

		if (isset($pdata["transactionorigin"]))
			$xml .= "<transactionorigin>" . $pdata["transactionorigin"] . "</transactionorigin>";

		if (isset($pdata["tdate"]))
			$xml .= "<tdate>" . $pdata["tdate"] . "</tdate>";

		$xml .= "</transactiondetails>";


		### MERCHANTINFO NODE ###
		$xml .= "<merchantinfo>";

		if (isset($pdata["configfile"]))
			$xml .= "<configfile>" . $pdata["configfile"] . "</configfile>";

		if (isset($pdata["keyfile"]))
			$xml .= "<keyfile>" . $pdata["keyfile"] . "</keyfile>";

		if (isset($pdata["host"]))
			$xml .= "<host>" . $pdata["host"] . "</host>";

		if (isset($pdata["port"]))
			$xml .= "<port>" . $pdata["port"] . "</port>";

		if (isset($pdata["appname"]))
			$xml .= "<appname>" . $pdata["appname"] . "</appname>";

		$xml .= "</merchantinfo>";

		### PAYMENT NODE ###
		$xml .= "<payment>";

		if (isset($pdata["chargetotal"]))
			$xml .= "<chargetotal>" . $pdata["chargetotal"] . "</chargetotal>";

		if (isset($pdata["tax"]))
			$xml .= "<tax>" . $pdata["tax"] . "</tax>";

		if (isset($pdata["vattax"]))
			$xml .= "<vattax>" . $pdata["vattax"] . "</vattax>";

		if (isset($pdata["shipping"]))
			$xml .= "<shipping>" . $pdata["shipping"] . "</shipping>";

		if (isset($pdata["subtotal"]))
			$xml .= "<subtotal>" . $pdata["subtotal"] . "</subtotal>";

		$xml .= "</payment>";

		### CHECK NODE ### 
		if (isset($pdata["voidcheck"]))
		{
			$xml .= "<telecheck><void>1</void></telecheck>";
		}
		elseif (isset($pdata["routing"]))
		{
			$xml .= "<telecheck>";
			$xml .= "<routing>" . $pdata["routing"] . "</routing>";

			if (isset($pdata["account"]))
				$xml .= "<account>" . $pdata["account"] . "</account>";

			if (isset($pdata["bankname"]))
				$xml .= "<bankname>" . $pdata["bankname"] . "</bankname>";
	
			if (isset($pdata["bankstate"]))
				$xml .= "<bankstate>" . $pdata["bankstate"] . "</bankstate>";

			if (isset($pdata["ssn"]))
				$xml .= "<ssn>" . $pdata["ssn"] . "</ssn>";

			if (isset($pdata["dl"]))
				$xml .= "<dl>" . $pdata["dl"] . "</dl>";

			if (isset($pdata["dlstate"]))
				$xml .= "<dlstate>" . $pdata["dlstate"] . "</dlstate>";

			if (isset($pdata["checknumber"]))
				$xml .= "<checknumber>" . $pdata["checknumber"] . "</checknumber>";
				
			if (isset($pdata["accounttype"]))
				$xml .= "<accounttype>" . $pdata["accounttype"] . "</accounttype>";

			$xml .= "</telecheck>";
		}

		### PERIODIC NODE ###

		if (isset($pdata["startdate"]))
		{
			$xml .= "<periodic>";

			$xml .= "<startdate>" . $pdata["startdate"] . "</startdate>";

			if (isset($pdata["installments"]))
				$xml .= "<installments>" . $pdata["installments"] . "</installments>";

			if (isset($pdata["threshold"]))
						$xml .= "<threshold>" . $pdata["threshold"] . "</threshold>";

			if (isset($pdata["periodicity"]))
						$xml .= "<periodicity>" . $pdata["periodicity"] . "</periodicity>";

			if (isset($pdata["pbcomments"]))
						$xml .= "<comments>" . $pdata["pbcomments"] . "</comments>";

			if (isset($pdata["action"]))
				$xml .= "<action>" . $pdata["action"] . "</action>";

			$xml .= "</periodic>";
		}


		### NOTES NODE ###

		if (isset($pdata["comments"]) || isset($pdata["referred"]))
		{
			$xml .= "<notes>";

			if (isset($pdata["comments"]))
				$xml .= "<comments>" . $pdata["comments"] . "</comments>";

			if (isset($pdata["referred"]))
				$xml .= "<referred>" . $pdata["referred"] . "</referred>";

			$xml .= "</notes>";
		}

		### ITEMS AND OPTIONS NODES ###
	
		if ($this->debugging)	// make it easy to see
		{						// LSGS doesn't mind whitespace
			reset($pdata);

			while (list ($key, $val) = each ($pdata))
			{
				if (is_array($val))
				{
					$otag = 0;
					$ostag = 0;
					$items_array = $val;
					$xml .= "\n<items>\n";

					while(list($key1, $val1) = each ($items_array))
					{
						$xml .= "\t<item>\n";

						while (list($key2, $val2) = each ($val1))
						{
							if (!is_array($val2))
								$xml .= "\t\t<$key2>$val2</$key2>\n";

							else
							{
								if (!$ostag)
								{
									$xml .= "\t\t<options>\n";
									$ostag = 1;
								}

								$xml .= "\t\t\t<option>\n";
								$otag = 1;
								
								while (list($key3, $val3) = each ($val2))
									$xml .= "\t\t\t\t<$key3>$val3</$key3>\n";
							}

							if ($otag)
							{
								$xml .= "\t\t\t</option>\n";
								$otag = 0;
							}
						}

						if ($ostag)
						{
							$xml .= "\t\t</options>\n";
							$ostag = 0;
						}
					$xml .= "\t</item>\n";
					}
				$xml .= "</items>\n";
				}
			}
		}

		else // !debugging
		{
			while (list ($key, $val) = each ($pdata))
			{
				if (is_array($val))
				{
					$otag = 0;
					$ostag = 0;
					$items_array = $val;
					$xml .= "<items>";

					while(list($key1, $val1) = each ($items_array))
					{
						$xml .= "<item>";

						while (list($key2, $val2) = each ($val1))
						{
							if (!is_array($val2))
								$xml .= "<$key2>$val2</$key2>";

							else
							{
								if (!$ostag)
								{
									$xml .= "<options>";
									$ostag = 1;
								}

								$xml .= "<option>";
								$otag = 1;
								
								while (list($key3, $val3) = each ($val2))
									$xml .= "<$key3>$val3</$key3>";
							}

							if ($otag)
							{
								$xml .= "</option>";
								$otag = 0;
							}
						}

						if ($ostag)
						{
							$xml .= "</options>";
							$ostag = 0;
						}
					$xml .= "</item>";
					}
				$xml .= "</items>";
				}
			}
		}

		$xml .= "</order>";

		return $xml;
	}
}
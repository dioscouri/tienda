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

class plgTiendaPayment_paypalpro extends TiendaPaymentPlugin
{
	const TRXTYPE_SALE = 'S';
	const TRXTYPE_AUTH = 'A';
	const NO_SECURE_DATA = '123';
	
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = 'payment_paypalpro';
	var $_payment_type = 'payment_paypalpro';
	/**
	 * @var array
	 * @access protected
	 */
	var $_renderers = array();

	/**
	 * @var array
	 * @access protected
	 */
	var $_processors = array();

	/**
	 * @var boolean
	 */
	var $_is_log = false;
	
	var $_is_sandbox = false;

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
	function plgTiendaPayment_paypalpro(& $subject, $config) {
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, null, true);
	}
  
  
  /**
     * Prepares variables for the payment form
     * 
     * @return unknown_type
     */
    function _renderForm( )
    {
        $vars = new JObject();
        
        $html = $this->_getLayout('form', $vars);
        return $html;
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

		$opc = Tienda::getInstance()->get('one_page_checkout', 1);
        $user = JFactory::getUser();
        $vars = new JObject();
		$vars->mode = $this->params->get('sandbox') ? 'TEST' : '';
		
		$creds = array();
		$creds['pswd'] = $this->_getParam('api_password');
		$creds['partner'] = $this->_getParam('api_partner');
		$creds['merchant'] = $this->_getParam('api_merchant');

        // set paypal pro checkout type
        $order = JTable::getInstance('Orders', 'TiendaTable');
		JFactory::getSession()->set('tienda.order.id', $data['order_id'] );
        $order->load( $data['order_id'] );
		$creds['user'] = $this->_getParam('api_username');

		$urls = array();
        $urls['return'] = JRoute::_( JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process&tmpl=component" );
        $urls['cancel'] = JRoute::_( JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=cancel&tmpl=component" );
        $urls['error'] = JRoute::_( JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=display_message&tmpl=component" );
		
		$vars->security = $this->_getSecureToken($creds, $data['order_id'], $data['orderpayment_amount'], $data['orderinfo'], $urls);
		$session = JFactory::getSession();
		
		if( !isset( $vars->security['SECURETOKENID'] ) )  { // paypal couldnt generate the security data so store nonsense data that cant ever be generated
			$vars->security['SECURETOKENID'] = plgTiendaPayment_paypalpro::NO_SECURE_DATA;
		}
		$session->set( 'paypal_pro.security.id',  $vars->security['SECURETOKENID'] );

		if( !isset($vars->security['SECURETOKEN']) ) { // paypal couldnt generate the security data so store nonsense data that cant ever be generated
			$vars->security['SECURETOKEN'] = plgTiendaPayment_paypalpro::NO_SECURE_DATA;
		}
		$session->set( 'paypal_pro.security.token', $vars->security['SECURETOKEN'] );
		$session->set( 'paypal_pro.orderpayment.id', $data['orderpayment_id'] );
		
        $vars->is_recurring = $order->isRecurring();
       
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
        $ptype 		= JRequest::getVar( 'orderpayment_type' );
		if ($ptype == $this->_payment_type)
		{
			$html = '';
			$vars->tmpl = '';
			$app			  = JFactory::getApplication();
			$act 		  = $data['paction'];
			$session = JFactory::getSession();
			$orig_sec_id =  $session->get( 'paypal_pro.security.id', '' );
			$orig_sec_token = $session->get( 'paypal_pro.security.token', '' );
			
			$post_sec_id = isset( $data['SECURETOKENID'] ) ? $data['SECURETOKENID'] : '';
			$post_sec_token = isset( $data['SECURETOKEN'] ) ? $data['SECURETOKEN'] : '';

	        $order = JTable::getInstance('Orders', 'TiendaTable');
	        $order->load( JFactory::getSession()->set('tienda.order.id', 0 ) );
	        $items = $order->getItems();
			$creds['user'] = $this->_getParam('api_username');
			$order->calculateTotals();

			switch( $act )
			{
				case 'process_done' :
				case 'process' :
				{
					$part = $act == 'process' ? 0 : 1;
					if( $part == 0 ) {
						if( empty($orig_sec_id) || empty($orig_sec_token) || $orig_sec_id != $post_sec_id || $orig_sec_token != $post_sec_token ) { // somebody modified the data on the other side so let's not do anything
							$vars->mode = 3; // security check failed							
				        	$html .= $this->_getLayout('postpayment', $vars);
							return $html;
						}

						$vars->orderpayment_type = $this->_payment_type;
						$res = $this->_processSale( $data );
						
						JFactory::getSession()->set( 'tienda.paypal_pro.error', serialize( $res ) );
						if( strlen( $res ) ) { // something went wrong so just display the message
							$vars->mode = 1;
							$html .= $this->_getLayout('postpayment', $vars);
						} else { // everything went oK
							$vars->mode = 0; // everything went OK							
							$html .= $this->_getLayout('postpayment', $vars);
						}
					} else {
						$errors = 	JFactory::getSession()->get( 'tienda.paypal_pro.error', '' );
						if( strlen( $errors ) != 0 ) {
							$vars->errors = unserialize( $errors );
							$html .= $this->_getLayout('message', $vars );
						}
						$vars->mode = 4; // all went on
						$html .= $this->_getLayout('postpayment', $vars);
						$html .= $this->_displayArticle();
					}
					break;
				}
				case 'display_message':
				{
					if( empty($orig_sec_id) || empty($orig_sec_token) || $orig_sec_id != $post_sec_id || $orig_sec_token != $post_sec_token ) { // somebody modified the data on the other side so let's not do anything
						$vars->mode = 3; // security check failed							
			        	$html .= $this->_getLayout('postpayment', $vars);
						return $html;
					}
					$vars->orderpayment_type = $this->_payment_type;
					JFactory::getSession()->set( 'tienda.paypal_pro.error', serialize( $this->_processSale( $data ) ) );
					$vars->mode = 1;
					$html .= $this->_getLayout('postpayment', $vars);
					break;
				}
				
				case 'display_standalone_message':
				{
					$errors = 	JFactory::getSession()->get( 'tienda.paypal_pro.error', '' );
					if( strlen( $errors ) != 0 ) {
						JFactory::getSession()->set( 'tienda.paypal_pro.error', '' );
						$vars->errors = unserialize( $errors );
						$html .= $this->_getLayout('message', $vars );
					}
					break;
				}
				case 'cancel' :
				default:
				{
					if( empty($orig_sec_id) || empty($orig_sec_token) || $orig_sec_id != $post_sec_id || $orig_sec_token != $post_sec_token ) { // somebody modified the data on the other side so let's not do anything
						$vars->mode = 3; // security check failed							
			        	$html .= $this->_getLayout('postpayment', $vars);
						return $html;
					}

					$vars->mode = 2; // operation was cancelled
					$errors = $this->_cancelOrder( $data );	
					$msg = htmlspecialchars_decode( $this->_getLayout('postpayment', $vars) );
					
					if( !is_array($errors ) ) {
						$errors = array( $msg );
					} else {
						$errors []= $msg;
					}

					JFactory::getSession()->set( 'tienda.paypal_pro.error', serialize( $errors ) );
					$vars->mode = 1;
					$vars->orderpayment_type = $this->_payment_type;
					$html .= $this->_getLayout('postpayment', $vars);
					break;		
				}
			}
			
			return $html;
    	}
    }

    /**
     * This displays the content article
     * specified in the plugin's params
     * 
     * @return unknown_type
     */
    protected function _displayArticle()
    {
        $html = '';
        
        $articleid = $this->params->get('articleid');
        if ($articleid)
        {
            $html = DSCArticle::display( $articleid );
        }
        
        return $html;
    }
	
	/*
	 * This method cancels the order based on the input from Paypal service
	 */
	protected function _cancelOrder( $data )
	{
		$errors = array();
		$cancel_id = $this->_getParam('cancel_order_state');
		
		$payment_id = JFactory::getSession()->get('paypal_pro.orderpayment.id', 0);
        // load the orderpayment record and set some values
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $payment_id );

		ob_start();
		print_r( $data );
		
        $orderpayment->transaction_details  = ob_get_contents();
		ob_end_clean();

        $tbl_order = JTable::getInstance('Orders', 'TiendaTable');
        $tbl_order->load( $orderpayment->order_id );
		$tbl_order->order_state_id = $cancel_id;

        // save the order
        if (!$tbl_order->save()) {
            $errors[] = $tbl_order->getError();
        }

        // save the orderpayment
        if (!$orderpayment->save()) {
            $errors[] = $orderpayment->getError();
        }

        return count($errors) ? implode("\n", $errors) : '';
	}


    /**
     * Processes the sale payment
     *
     * @param  array   $data IPN data
     * @return boolean Did the IPN Validate?
     * @access protected
     */
    public function _processSale( $data )
    {
        /*
         * validate the payment data
         */
        $errors = array();

		$payment_id = JFactory::getSession()->get('paypal_pro.orderpayment.id', 0);
        // load the orderpayment record and set some values
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $payment_id );


		ob_start();
		print_r( $data );
		
        $orderpayment->transaction_details  = ob_get_contents();
		ob_end_clean();
        $orderpayment->transaction_id       = $data['PNREF'];
        $orderpayment->transaction_status   = $data['RESPMSG'];

        // check the stored amount against the payment amount
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
        $respond_amount = TiendaHelperBase::number( $data['AMT'], array( 'thousands'=>'' ) );
        if ($stored_amount != $respond_amount) {
            $errors[] = JText::_('PLG_TIENDA_PAYMENT_PAYPALPRO_MESSAGE_AMOUNT_INVALID');
            $errors[] = $stored_amount . " != " . $respond_amount;
        }

		// supported error codes
		$supported_errors = array( 0, 1, 2, 12, 30 );
        // check the payment status		
		if( empty($data['RESULT']) || (int)$data['RESULT'] > 0 ) { // dad, we've got a problem
			$data['RESULT'] = (int)$data['RESULT'];
			if( in_array( $data['RESULT'], $supported_errors )  === false ) { // unknown error => just play innocent and blame it on Paypal :)			
	            $errors[] = JText::sprintf('PLG_TIENDA_PAYMENT_PAYPALPRO_MESSAGE_STATUS_INVALID', @$data['RESULT']);
			} else { // OK, so this is a common error, so display a cool message
				switch( $data['RESULT'] )
				{
					case 1: // user authentication failed
					{
			            $errors[] = JText::_('PLG_TIENDA_PAYMENT_PAYPALPRO_MESSAGE_USER_AUTH_FAILED');
						break;	
					}
					case 2: // invalid tender type
					{
			            $errors[] = JText::_('PLG_TIENDA_PAYMENT_PAYPALPRO_MESSAGE_TENDER_INVALID');
						break;
					}
					case 12: // declined
					{
			            $errors[] = JText::_('PLG_TIENDA_PAYMENY_PAYPALPRO_MESSAGE_OPERATION_DECLINED');
						break;	
					}
					case 30: // duplicated transaction
					{
			            $errors[] = JText::_('PLG_TIENDA_PAYMENT_PAYPALPRO_MESSAGE_DUPLICATE_TRANSACTION');
						break;
					}
				}
			}
		}
		

        // set the order's new status and update quantities if necessary
        Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $tbl_order = JTable::getInstance('Orders', 'TiendaTable');
        $tbl_order->load( $orderpayment->order_id );
        if (count($errors)) {
            // if an error occurred
            $tbl_order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED
        } else { // we received the payment
            $tbl_order->order_state_id = $this->params->get('payment_received_order_state', '17');; // PAYMENT RECEIVED

            // do post payment actions
            $this->setOrderPaymentReceived( $orderpayment->order_id );
            
            // send notice of new order
            Tienda::load( "TiendaHelperBase", 'helpers._base' );
            $helper = TiendaHelperBase::getInstance('Email');
            $model = Tienda::getClass("TiendaModelOrders", "models.orders");
            $model->setId( $orderpayment->order_id );
            $order = $model->getItem();
            $helper->sendEmailNotices($order, 'new_order');
        }

        // save the order
        if (!$tbl_order->save()) {
            $errors[] = $tbl_order->getError();
        }

        // save the orderpayment
        if (!$orderpayment->save()) {
            $errors[] = $orderpayment->getError();
        }

        return count($errors) ? implode("\n", $errors) : '';
    }

	/**
     * Gets the value for the Paypal pro variable
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
	 * Generares an error string from an array
	 * 
	 * @param array $errors
	 * @param string $type html or text
	 * @return string
	 */
	function _getFormattedErrorMsg($errors, $type = 'html')
	{		
		if ($type == 'html') {
			return '<li>' . implode('</li><li>', $errors) . '</li>';		
		} 
		else {
			return implode("\n", $errors);
		}
	}
	
	/**
	 * Requests a valid secure token from Paypal service
	 * 
	 * @param array 	$creds		Merchant credentials
	 * @param decimal	$amount		Order total
	 * @return Object				Secure token and Secure token ID
	 */
    function _getSecureToken( $creds, $order_id, $amount, $orderinfo, $urls )
	{
		$token = uniqid('order'.$order_id);
		if(strlen( $token ) > 36 ) {
			$token = substr($token, 0, 36 );
		}
		
		// let's get zone and country codes
		$billing_zone_code = '';
		$shipping_zone_code = '';
		$billing_country_code = '';
		$shipping_country_code = '';

		if( $orderinfo->billing_country_id ) {
        	$countryTable = JTable::getInstance('Countries', 'TiendaTable');       
        	$countryTable->load( $orderinfo->billing_country_id );
			$billing_country_code = $countryTable->country_isocode_2;		
		}
		
		if( $orderinfo->shipping_country_id ) {
	        $countryTable = JTable::getInstance('Countries', 'TiendaTable');       
    	    $countryTable->load( $orderinfo->shipping_country_id );     
			$shipping_country_code = $countryTable->country_isocode_2;		
		}

		if( $orderinfo->billing_zone_id ) {
        	$zoneTable = JTable::getInstance('Zones', 'TiendaTable');       
        	$zoneTable->load( $orderinfo->billing_zone_id );
			$billing_zone_code = $zoneTable->code;		
		}
		
		if( $orderinfo->shipping_zone_id ) {
	        $zoneTable = JTable::getInstance('Zones', 'TiendaTable');       
    	    $zoneTable->load( $orderinfo->shipping_zone_id );     
			$shipping_zone_code = $zoneTable->code;		
		}
		
		$request = array(
		  "PARTNER" => $creds['partner'],
		  "VENDOR" => $creds['merchant'],
		  "USER" => $creds['user'],
		  "PWD" => $creds['pswd'], 
		  "TRXTYPE" => "S",
		  "AMT" => $amount,
		  "CREATESECURETOKEN" => "Y",
		  "SECURETOKENID" => $token,
		  "CURRENCY" => $this->_getParam( 'currency', 'US' ), // TODO Eventually use: Tienda::getInstance()->get('currency');
		  'RETURNURL' => $urls['return'],
		  'CANCELURL' =>  $urls['cancel'],
		  'ERRORURL' => $urls['error'],
		
		  "BILLTOFIRSTNAME" => isset( $orderinfo->billing_first_name ) ? $orderinfo->billing_first_name : '',
		  "BILLTOLASTNAME" => isset( $orderinfo->billing_last_name ) ? $orderinfo->billing_last_name : '',
		  "BILLTOSTREET" => isset( $orderinfo->billing_address_1 ) ? $orderinfo->billing_address_1 : '',
		  "BILLTOCITY" => isset( $orderinfo->billing_city ) ? $orderinfo->billing_city : '',
		  "BILLTOSTATE" => $billing_zone_code,
		  "BILLTOZIP" => isset( $orderinfo->billing_postal_code ) ? $orderinfo->billing_postal_code : '',
		  "BILLTOCOUNTRY" => $billing_country_code,
		
		  "SHIPTOFIRSTNAME" => isset( $orderinfo->shipping_first_name ) ? $orderinfo->shipping_first_name : '',
		  "SHIPTOLASTNAME" => isset( $orderinfo->shipping_last_name ) ? $orderinfo->shipping_last_name : '',
		  "SHIPTOSTREET" => isset( $orderinfo->shipping_address_1 ) ? $orderinfo->shipping_address_1 : '',
		  "SHIPTOCITY" => isset( $orderinfo->shipping_city ) ? $orderinfo->shipping_city : '',
		  "SHIPTOSTATE" => $shipping_zone_code,
		  "SHIPTOZIP" => isset( $orderinfo->shipping_postal_code ) ? $orderinfo->shipping_postal_code : '',
		  "SHIPTOCOUNTRY" => $shipping_country_code,
		);
		
		if( isset( $creds['user']) && !empty( $creds['user'] ) ) {
			$request['user'] = $creds['user'];
		}
		
		$res = $this->_runPaypalRequest($request);
		if( $res == false ) {
			return false;
		} else {
			return $res;
		}
	}
    
	function _runPaypalRequest( $data )
	{
	    $paramList = array();
	    foreach($data as $key => $value) {
	        $params[] = $key . "[" . strlen($value) . "]=" . $value;
	    }
	    
	    $apiStr = implode("&", $params);
	    
	    // Which endpoint will we be using?
	    if( $this->params->get('sandbox') ) {
	    	$endpoint = "https://pilot-payflowpro.paypal.com/";
	    } else {
			$endpoint = "https://payflowpro.paypal.com";
	    }
	
	    // Initialize our cURL handle.
	    $curl = curl_init($endpoint);
	    
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);	    
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	    
	    curl_setopt($curl, CURLOPT_POST, TRUE);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $apiStr);
	    
	    $res = curl_exec($curl);
	    if($res === FALSE) {
	      return FALSE;
	    }
	    else {
	    	return $this->_parsePaypalResonse( $res ) ;
		}
	}
    
	/**
	 * Parses response from Paypal service
	 * 
	 * @param array $str 
	 */
	function _parsePaypalResonse( $str )
	{
	    $workstr = $str;
	    $out = array();		
		
	    while(strlen($workstr) > 0) {
	        $loc = strpos($workstr, '=');
	        if($loc === FALSE) {
	            // Truncate the rest of the string, it's not valid
	            $workstr = "";
	            continue;
	        }
	        
	        $substr = substr($workstr, 0, $loc);
	        $workstr = substr($workstr, $loc + 1); // "+1" because we need to get rid of the "="
	        
	        if(preg_match('/^(\w+)\[(\d+)]$/', $substr, $matches)) {
	            // This one has a length tag with it.  Read the number of characters
	            // specified by $matches[2].
	            $count = intval($matches[2]);
	            
	            $out[$matches[1]] = substr($workstr, 0, $count);
	            $workstr = substr($workstr, $count + 1); // "+1" because we need to get rid of the "&"
	        } else {
	            // Read up to the next "&"
	            $count = strpos($workstr, '&');
	            if($count === FALSE) { // No more "&"'s, read up to the end of the string
	                $out[$substr] = $workstr;
	                $workstr = "";
	            } else {
	                $out[$substr] = substr($workstr, 0, $count);
	                $workstr = substr($workstr, $count + 1); // "+1" because we need to get rid of the "&"
	            }
	        }
	    }
	    
	    return $out;
	}
	
}

if ( ! function_exists('plg_tienda_escape')) {
	
	/**
	 * Escapes a value for output in a view script
	 * 
	 * @param mixed $var
	 * @return mixed
	 */
	function plg_tienda_escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}
}

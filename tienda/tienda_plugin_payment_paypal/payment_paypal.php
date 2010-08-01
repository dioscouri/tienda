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

class plgTiendaPayment_paypal extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_paypal';
    
	function plgTiendaPayment_paypal(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
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
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_amount = $data['orderpayment_amount'];
        $vars->orderpayment_type = $this->_element;

        // set paypal checkout type
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data['order_id'] );
        $items = $order->getItems();
        $vars->is_recurring = $order->isRecurring();
        
        // if order has both recurring and non-recurring items,
        if ($vars->is_recurring && count($items) > '1')
        {
            // TODO Complete this
            // do non-recurring first, 
            // then upon return, ask user to checkout again for recurring items
            $vars->cmd = '_cart';
        }
            elseif ($vars->is_recurring && count($items) == '1')
        {
            // only recurring
            $vars->cmd = '_xclick-subscriptions';
        }
            else
        {
            // do normal cart checkout
            $vars->cmd = '_cart';
        } 
        $vars->order = $order;
        $vars->orderitems = $items;
        
        // set payment plugin variables        
        $vars->merchant_email = $this->_getParam( 'merchant_email' );
        $vars->post_url = $this->_getPostUrl();
        $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=display_message";
        $vars->cancel_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=cancel";
        $vars->notify_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process&tmpl=component";
        $vars->currency_code = $this->_getParam( 'currency', 'USD' ); // TODO Eventually use: TiendaConfig::getInstance()->get('currency');

        // set variables for user info
        $vars->first_name   = $data['orderinfo']->shipping_first_name;
        $vars->last_name    = $data['orderinfo']->shipping_last_name;
        $vars->email        = $data['orderinfo']->user_email;
        $vars->address_1    = $data['orderinfo']->shipping_address_1;
        $vars->address_2    = $data['orderinfo']->shipping_address_2;
        $vars->city         = $data['orderinfo']->shipping_city;
        $vars->country      = $data['orderinfo']->shipping_country_name;
        $vars->region       = $data['orderinfo']->shipping_zone_name;
        $vars->postal_code  = $data['orderinfo']->shipping_postal_code;
        
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
        $paction = JRequest::getVar('paction');
        
        $vars = new JObject();
        
        switch ($paction) 
        {
            case "display_message":
                $vars->message = JText::_('PAYPAL MESSAGE PAYMENT ACCEPTED FOR VALIDATION');
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();
              break;
            case "process":
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
                echo $html; // TODO Remove this
                $app =& JFactory::getApplication();
                $app->close();
              break;
            case "cancel":
                $vars->message = JText::_( 'Paypal Message Cancel' );
                $html = $this->_getLayout('message', $vars);
              break;
            default:
                $vars->message = JText::_( 'Paypal Message Invalid Action' );
                $html = $this->_getLayout('message', $vars);
              break;
        }
        
        return $html;
    }   
    
    /**
     * Prepares variables for the payment form
     * 
     * @return unknown_type
     */
    function _renderForm( $data )
    {
        $user = JFactory::getUser();    
        $vars = new JObject();
        
        $html = $this->_getLayout('form', $vars);
        
        return $html;
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
     * Gets the value for the Paypal variable
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
     * Validates the IPN data
     * 
     * @param array $data
     * @return string Empty string if data is valid and an error message otherwise
     * @access protected
     */
    function _validateIPN( $data )
    {
        $secure_post = $this->params->get( 'secure_post', '0' );
        $paypal_url = $this->_getPostUrl(false);
        
        $req = 'cmd=_notify-validate';
        foreach ($data as $key => $value) {
            if ($key != 'view' && $key != 'layout') {
                $value = urlencode($value);
                $req .= "&$key=$value";
            }
        }
        
        // post back to PayPal system to validate
        $header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
        //$header .= "Host: " . $this->_getPostURL(false) . ":443\r\n";
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
            return JText::sprintf('PAYPAL ERROR POSTING IPN DATA BACK', $errstr, $errno);
        }
        else {
            fputs ($fp, $header . $req);
            while ( ! feof($fp)) {
                $res = fgets ($fp, 1024); //echo $res;
                if (strcmp ($res, 'VERIFIED') == 0) {
                    return '';
                }
                elseif (strcmp ($res, 'INVALID') == 0) {
                    return JText::_('PAYPAL ERROR IPN VALIDATION');
                }
            }        
        }
         
        fclose($fp);        
        return '';
    }
	
	/**
	 *
	 * @return HTML
	 */
	function _process() 
	{
		$data = JRequest::get('post');
		
		// validate the IPN info
		$error = $this->_validateIPN($data);
		if (!empty($error))
		{
			// ipn Validation failed
			$data['ipn_validation_results'] = $error;
		}
		
        // prepare some data
        $order_id           = (int) @$data['item_number_1'];
        $orderpayment_id    = (int) @$data['custom'];
        $data['transaction_details'] = $this->_getFormattedTransactionDetails( $data );
        
        // process the payment based on its type
        if ( !empty($data['txn_type']) ) 
        {
            $payment_error = '';
            
            if ($data['txn_type'] == 'cart') {
            	// Payment received for multiple items; source is Express Checkout or the PayPal Shopping Cart.
            	$payment_error = $this->_processSale( $data, $error );
            }
            elseif (strpos($data['txn_type'], 'subscr_') === 0) {
                $payment_error = $this->_processSubscription( $data, $error );
                //$payment_error = JText::_( "PAYPAL ERROR INVALID TRANSACTION TYPE SUBSCRIPTIONS UNSUPPORTED" ).": ".$data['txn_type'];
            }
            else {
                // other methods not supported right now
                $payment_error = JText::_( "PAYPAL ERROR INVALID TRANSACTION TYPE" ).": ".$data['txn_type'];                
            }
            
            if ($payment_error) {
                // it seems like an error has occurred during the payment process
                $error .= $error ? "\n" . $payment_error : $payment_error;
            }           
        }
                
        if ($error) {   
            // send an emails to site's administrators with error messages
            $this->_sendErrorEmails($error, $data['transaction_details']);
            return $error;    
        }
		
        // if here, all went well
        $error = 'processed';
		return $error;
	}
	
    /**
     * Processes the sale payment
     * 
     * @param array $data IPN data
     * @return boolean Did the IPN Validate?
     * @access protected
     */
    function _processSale( $data, $ipnValidationFailed='' )
    {
        /*
         * validate the payment data
         */
        $errors = array();
        
        if (!empty($ipnValidationFailed))
        {
        	$errors[] = $ipnValidationFailed;
        }
        
        // is the recipient correct?
        if (empty($data['receiver_email']) || $data['receiver_email'] != $this->_getParam( 'merchant_email' )) {
            $errors[] = JText::_('PAYPAL MESSAGE RECEIVER INVALID');
        }
        
        if (empty($data['custom']))
        {
            $errors[] = JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID');
            return count($errors) ? implode("\n", $errors) : '';
        }
        
        // load the orderpayment record and set some values
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $data['custom'] );
        if (empty($data['custom']) || empty($orderpayment->orderpayment_id))
        {
            $errors[] = JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID');
            return count($errors) ? implode("\n", $errors) : '';
        }
        $orderpayment->transaction_details  = $data['transaction_details'];
        $orderpayment->transaction_id       = $data['txn_id'];
        $orderpayment->transaction_status   = $data['payment_status'];
       
        // check the stored amount against the payment amount
        $stored_amount = number_format( $orderpayment->get('orderpayment_amount'), '2' );
        if ((float) $stored_amount !== (float) $data['mc_gross']) {
            $errors[] = JText::_('PAYPAL MESSAGE AMOUNT INVALID');
        }
        
        // check the payment status
        if (empty($data['payment_status']) || ($data['payment_status'] != 'Completed' && $data['payment_status'] != 'Pending')) {
            $errors[] = JText::sprintf('PAYPAL MESSAGE STATUS INVALID', @$data['payment_status']);
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
            elseif (@$data['payment_status'] == 'Pending') 
        {
            // if the transaction has the "pending" status,
            $order->order_state_id = TiendaConfig::getInstance('pending_order_state', '1'); // PENDING
            // Update quantities for echeck payments
            TiendaHelperOrder::updateProductQuantities( $orderpayment->order_id, '-' );
            
            // remove items from cart
            TiendaHelperCarts::removeOrderItems( $orderpayment->order_id );
            
            // send email
            $send_email = true;
        }
            else 
        {
            $order->order_state_id = $this->params->get('payment_received_order_state', '17');; // PAYMENT RECEIVED
            $this->setOrderPaymentReceived( $orderpayment->order_id );
            
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
     * Processes the subscription payment
     * 
     * @param array $data IPN data
     * @return boolean Did the IPN Validate?
     * @access protected
     */
    function _processSubscription( $data, $ipnValidationFailed='' )
    {
        /*
         * validate the payment data
         */
        $errors = array();
        
        if (!empty($ipnValidationFailed))
        {
            $errors[] = $ipnValidationFailed;
        }
        
        // is the recipient correct?
        if (empty($data['receiver_email']) || $data['receiver_email'] != $this->_getParam( 'merchant_email' )) {
            $errors[] = JText::_('PAYPAL MESSAGE RECEIVER INVALID');
        }
        
        // Evaluate the payment based on txn_type, mc_gross, subscr_id        
        switch ($data['txn_type'])
        {
            case 'subscr_signup':
                $errors[] = $this->_processSubscriptionSignup( $data );
                break;
            case 'subscr_payment':
                $errors[] = $this->_processSubscriptionPayment( $data );
                break;
            case 'subscr_eot':
                $errors[] = $this->_processSubscriptionEndOfTerm( $data );
                break;
            case 'subscr_cancel':
                $errors[] = $this->_processSubscriptionCancel( $data );
                break;
            case 'subscr_modify':
                $errors[] = $this->_processSubscriptionModify( $data );
                break;
            case 'subscr_failed':
                $errors[] = $this->_processSubscriptionFailed( $data );
                break;
            default:
                $errors[] = JText::_('PAYPAL MESSAGE INVALID TRANSACTION TYPE');               
                break;
        }
        
        return count($errors) ? implode("\n", $errors) : '';

        /** TODO Remove the rest of this **/
       
        // check the stored amount against the payment amount
        $stored_amount = number_format( $orderpayment->get('orderpayment_amount'), '2' );
        if ((float) $stored_amount !== (float) $data['mc_gross']) {
            $errors[] = JText::_('PAYPAL MESSAGE AMOUNT INVALID');
        }
        
        // check the payment status
        if (empty($data['payment_status']) || ($data['payment_status'] != 'Completed' && $data['payment_status'] != 'Pending')) {
            $errors[] = JText::sprintf('PAYPAL MESSAGE STATUS INVALID', @$data['payment_status']);
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
            elseif (@$data['payment_status'] == 'Pending') 
        {
            // if the transaction has the "pending" status,
            $order->order_state_id = TiendaConfig::getInstance('pending_order_state', '1'); // PENDING
            // Update quantities for echeck payments
            TiendaHelperOrder::updateProductQuantities( $orderpayment->order_id, '-' );
            
            // remove items from cart
            TiendaHelperCarts::removeOrderItems( $orderpayment->order_id );
            
            // send email
            $send_email = true;
        }
            else 
        {
            $order->order_state_id = $this->params->get('payment_received_order_state', '17');; // PAYMENT RECEIVED
            $this->setOrderPaymentReceived( $orderpayment->order_id );
            
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
     * Formatts the payment data for storing
     * 
     * @param array $data
     * @return string
     */
    function _getFormattedTransactionDetails( $data )
    {
        $separator = "\n";
        $formatted = array();

        foreach ($data as $key => $value) 
        {
            if ($key != 'view' && $key != 'layout') 
            {
                $formatted[] = $key . ' = ' . $value;
            }
        }
        
        return count($formatted) ? implode("\n", $formatted) : '';  
    }

    /**
     * Sends error messages to site administrators
     * 
     * @param string $message
     * @param string $paymentData
     * @return boolean
     * @access protected
     */
    function _sendErrorEmails($message, $paymentData)
    {
        $mainframe =& JFactory::getApplication();
                
        // grab config settings for sender name and email
        $config     = &TiendaConfig::getInstance();
        $mailfrom   = $config->get( 'emails_defaultemail', $mainframe->getCfg('mailfrom') );
        $fromname   = $config->get( 'emails_defaultname', $mainframe->getCfg('fromname') );
        $sitename   = $config->get( 'sitename', $mainframe->getCfg('sitename') );
        $siteurl    = $config->get( 'siteurl', JURI::root() );
        
        $recipients = $this->_getAdmins();
        $mailer =& JFactory::getMailer();
        
        $subject = JText::sprintf('PAYPAL EMAIL PAYMENT NOT VALIDATED SUBJECT', $sitename);

        foreach ($recipients as $recipient) 
        {
            $mailer = JFactory::getMailer();        
            $mailer->addRecipient( $recipient->email );
        
            $mailer->setSubject( $subject );
            $mailer->setBody( JText::sprintf('PAYPAL EMAIL PAYMENT FAILED BODY', $recipient->name, $sitename, $siteurl, $message, $paymentData) );          
            $mailer->setSender(array( $mailfrom, $fromname ));
            $sent = $mailer->send();
        }

        return true;
    }

    /**
     * Gets admins data
     * 
     * @return array|boolean
     * @access protected 
     */
    function _getAdmins()
    {
        $db =& JFactory::getDBO();
        $q = "SELECT name, email FROM #__users "
           . "WHERE LOWER(usertype) = \"super administrator\" "
           . "AND sendEmail = 1 "
           ;
        $db->setQuery($q);
        $admins = $db->loadObjectList();
            
        if ($error = $db->getErrorMsg()) {
            JError::raiseError(500, $error);
            return false;
        }
        
        return $admins;
    }
    
    /**
     * 
     * Enter description here ...
     * @param $data
     * @return unknown_type
     */
    function _processSubscriptionSignup( $data )
    {
        // the user has created a new subscription profile.
        // generally these are IMMEDIATELY followed by a subscr_payment IPN notification,
        // EXCEPT if the subscription has a FREE trial.
        // In the case of a FREE trial, ONLY a subscr_signup IPN is sent, 
        // so code accordingly 
    }
    
    /**
     * 
     * Enter description here ...
     * @param $data
     * @return unknown_type
     */
    function _processSubscriptionPayment( $data )
    {
        // the normal notice that requires action.
        
        // Check that custom (orderpayment_id) is present, we need it for payment amount verification
        if (empty($data['custom']))
        {
            $errors[] = JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID');
            return count($errors) ? implode("\n", $errors) : '';
        }
        // load the orderpayment record and set some values
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $data['custom'] );
        if (empty($data['custom']) || empty($orderpayment->orderpayment_id))
        {
            $errors[] = JText::_('PAYPAL MESSAGE INVALID ORDERPAYMENTID');
            return count($errors) ? implode("\n", $errors) : '';
        }
        $orderpayment->transaction_details  = $data['transaction_details'];
        $orderpayment->transaction_id       = $data['txn_id'];
        $orderpayment->transaction_status   = $data['payment_status'];
        
    }
    
    /**
     * 
     * Enter description here ...
     * @param $data
     * @return unknown_type
     */
    function _processSubscriptionEndOfTerm( $data )
    {
        // $data['custom'] is available
        
        $substr = substr($data['subscr_id'], 0, 2);
        switch($substr)
        {
            case "S-":
                //If your profile ID starts with S- then your EOT IPN works as follows:
                    //If the profile is canceled then you get an EOT at the end of the time that the buyer paid for.
                    //If the profile naturally ends then you get an EOT at the end of the time that the buyer paid for.
                break;
            case "I-":
                //If your profile ID starts with I- then your EOT IPN works as follows:
                    //If the profile is canceled then you never get an EOT IPN.
                    //If the profile naturally ends then you will get an EOT IPN right when the final payment is made.  
                        //You will not get an IPN when the time paid for is completed.  
                        //You will need to calculate that time period on your own 
                        //and then when the time the customer paid is up you no longer give them access to your service.
                break;
        }
    }
    
    /**
     * 
     * Enter description here ...
     * @param $data
     * @return unknown_type
     */
    function _processSubscriptionCancel( $data )
    {
        // get the subscription_id based on the orderpayment_id=>order_id=>orderitem_id=>subscription_id 
        // Cancel the subscription using the Subscription Helper and the subscription_id
        
    }
    
    /**
     * 
     * Enter description here ...
     * @param $data
     * @return unknown_type
     */
    function _processSubscriptionModify( $data )
    {
        // we don't really do anything with this transaction type
    }
    
    /**
     * When one of the recurring payments for a subscription has failed
     * this method will be triggered
     * 
     * @param $data
     * @return unknown_type
     */
    function _processSubscriptionFailed( $data )
    {
        // don't cancel the subscription.  an EOT will be triggered if all the payment retry attempts fail,
        // so cancel the subscription only in _processSubscriptionEndOfTerm (EOT)
        // TODO perhaps send an email when this happens? "hello, payment failed, want to check your credit card expiration?"
    }
    
}

      /* TYPICAL RESPONSE FROM PAYPAL INCLUDES:
       * mc_gross=49.99
       * &protection_eligibility=Eligible
       * &address_status=confirmed
       * &payer_id=Q5HTJ93G8FQKC
       * &tax=0.00
       * &address_street=10101+Some+Street
       * &payment_date=12%3A13%3A19+Dec+05%2C+2008+PST
       * &payment_status=Completed
       * &charset=windows-1252
       * &address_zip=11259
       * &first_name=John
       * &mc_fee=1.75
       * &address_country_code=US
       * &address_name=John+Doe
       * &custom=some+custom+value
       * &payer_status=verified
       * &business=receiver%40domain.com
       * &address_country=United+States
       * &address_city=Some+City
       * &quantity=1
       * &payer_email=sender%40emaildomain.com
       * &txn_id=3JK16594EX581780W
       * &payment_type=instant
       * &payer_business_name=John+Doe
       * &last_name=Doe
       * &address_state=CA
       * &receiver_email=receiver%40domain.com
       * &payment_fee=1.75
       * &receiver_id=YG9UDRP6DE45G
       * &txn_type=web_accept
       * &item_name=Name+of+item
       * &mc_currency=USD
       * &item_number=Number+of+Item
       * &residence_country=US
       * &handling_amount=0.00
       * &transaction_subject=Subject+of+Transaction
       * &payment_gross=49.99
       * &shipping=0.00
       * &=
      */

    /**
     * VALID PAYMENT_STATUS VALUES returned from Paypal
     * 
     * Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
     * Completed: The payment has been completed, and the funds have been added successfully to your account balance.
     * Created: A German ELV payment is made using Express Checkout.
     * Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
     * Expired: This authorization has expired and cannot be captured.
     * Failed: The payment has failed. This happens only if the payment was made from your customer’s bank account.
     * Pending: The payment is pending. See pending_reason for more information.
     * Refunded: You refunded the payment.
     * Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
     * Processed: A payment has been accepted.
     * Voided: This authorization has been voided.
    */
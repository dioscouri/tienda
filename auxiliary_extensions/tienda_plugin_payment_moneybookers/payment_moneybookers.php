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

class plgTiendaPayment_moneybookers extends TiendaPaymentPlugin
{
	/**
	 * @var string  
	 * $_element Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_moneybookers';
    
    /**
	 * @var boolean
	 * @access protected
	 */
	var $_isLog = false;
    
	function plgTiendaPayment_moneybookers(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
	/************************************
     * Note to 3pd: 
     * 
     * The methods between here
     * and the next comment block are 
     * the ones you would override 
     * in your payment plugin 
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
        // Process the payment
        
    	$vars = new JObject();        
        
        $vars->action_url = $this->_getActionUrl();        

        // properties as specified in moneybookers gateway manual
        $vars->pay_to_email = $this->_getParam( 'receiver_email' );
        $vars->transaction_id = $data['orderpayment_id'];
        $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=message&checkout=0";
        $vars->return_url_text = JText::_( 'TIENDA MONEYBOOKERS TEXT ON FINISH PAYMENT BUTTON' );
        $vars->cancel_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=cancel";
        $vars->status_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=process&tmpl=component";
        $vars->status_url2 = $this->_getParam( 'receiver_email' );
        $vars->language = $this->_getParam( 'language', 'EN' );
        $vars->confirmation_note = JText::_( 'TIENDA MONEYBOOKERS CONFIRMATION NOTE' );
        $vars->logo_url = JURI::root().$this->_getParam( 'logo_image' );
        $vars->user_id = JFactory::getUser()->id;
		$vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
	    $vars->orderpayment_type = $this->_element;	
	    $vars->currency = $this->_getParam( 'currency', 'USD' );
	    $vars->detail1_description = $data['order_id'];
     	$vars->detail1_text = JText::_( 'TIENDA MONEYBOOKERS DETAIL1 DESCRIPTION' );
	    $vars->detail2_description = $data['orderpayment_id'];
	    $vars->detail2_text = JText::_( 'TIENDA MONEYBOOKERS DETAIL2 DESCRIPTION' );
     	
	    $order = JTable::getInstance('Orders', 'TiendaTable');
		$order->load( $data['order_id'] );
	    $vars->is_recurring = $order->isRecurring();
	    $items = $order->getItems();
	    
	    JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'OrderItems', 'TiendaModel' );
        $model->setState( 'select', 'tbl.orderitem_id' );
        $model->setState( 'filter_orderid', $vars->order_id );
        $model->setState( 'filter_recurs', '1' );          	  	   	
        $recurring_orderitem_id = $model->getResult();        	
			
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $recurring_orderitem_table = JTable::getInstance( 'OrderItems', 'TiendaTable' );
		$recurring_orderitem_table->load( $recurring_orderitem_id );        	
		$recurring_orderitem_final_price = $recurring_orderitem_table->orderitem_final_price;
	    
	    // if order has both recurring and non-recurring items
	    if ($vars->is_recurring && count($items) > '1')
		{
			$vars->mixed_cart = true;
						
			$recurring_orderitem_table->delete();
										
			$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
            $orderpayment->load( $vars->orderpayment_id );
            $orderpayment->orderpayment_amount = $orderpayment->orderpayment_amount - $recurring_orderitem_final_price; 
            $orderpayment->save();
                        
            $order = JTable::getInstance('Orders', 'TiendaTable');
			$order->load( $data['order_id'] );
            $order->calculateTotals(); 
            $order->save();
            
            $vars->is_recurring = false;
            $vars->amount = $orderpayment->orderpayment_amount;
            $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=message&checkout=1";                   
		}
		    elseif ($vars->is_recurring && count($items) == '1')
		{
			$vars->mixed_cart = false;
			
		   	$vars->rec_amount = $order->recurring_trial ? $order->recurring_trial_price : $recurring_orderitem_final_price;
						
			$vars->rec_start_date = '';
			
			$vars->rec_period = $order->recurring_trial ? $order->recurring_trial_period_interval : $order->recurring_period_interval;
			$vars->rec_cycle = $this->_getDurationUnit($order->recurring_trial ? $order->recurring_trial_period_unit : $order->recurring_period_unit);// (day | week | month)
			
			// a period of days during which the customer can still process the transaction in case it originally failed.			
			$vars->rec_grace_period = 3;
			$vars->rec_status_url = $this->_getParam( 'receiver_email' ); 
		}
	    	else 
	    {
	    	$vars->mixed_cart = false;
	    	
			$vars->amount = $data['orderpayment_amount'];
	    }
	
        $html = $this->_getLayout('prepayment', $vars);
        return $html;
    } 
 
 	/**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     * 
     * IMPORTANT: It is the responsibility of each payment plugin
     * to tell clear the user's cart (if the payment status warrants it) by using:
     * 
     * $this->removeOrderItemsFromCart( $order_id );
     * 
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _postPayment( $data )
    {
        // Process the payment        
        
		$orderpayment_type = JRequest::getVar( 'orderpayment_type' );
		
		if ($orderpayment_type == $this->_element)
		{
			$paction 	= JRequest::getVar( 'paction' );
			$html = "";
			
			switch ($paction) {
				case "message":
					$user = JFactory::getUser();
					$checkout = JRequest::getInt('checkout');

					// check if cart has recurring item and if checkout variable is set to '1'
					Tienda::load( "TiendaHelperCarts", 'helpers.carts' );
                    $carts_helper = new TiendaHelperCarts();
                    if( $carts_helper->hasRecurringItem($user->id) && $checkout == '1' )
                    {
                    	// check if the cart has only 1 item  
                    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        				$model = JModel::getInstance( 'Carts', 'TiendaModel' );        
        				$model->setState( 'filter_user', $user->id );
        				$items = $model->getList();
        				
        				if (count($items) == '1')
        				{
        					// check if the item in the cart is recurring product
        					$model = JModel::getInstance( 'Products', 'TiendaModel' );
        					$model->setId( $items[0]->product_id );  
        					$product = $model->getItem();
        					
        					if( $product->product_recurs )
        					{
        						// prepare payment for the recurring item Click Here to View and Print an Invoice 
        						$html = $this->_secondPrePayment();
        						break;
        					}        					
        				}        				
                    }
                    
                    $text = JText::_( 'TIENDA MONEYBOOKERS MESSAGE PAYMENT SUCCESS' );
					$html .= $this->_renderHtml( $text );
					$html .= $this->_displayArticle();  									
				  break;
				case "process":
					$html = $this->_process();	
						
					$app =& JFactory::getApplication();
					$app->close();
				  break;
				case "cancel":					
					$text = JText::_( 'TIENDA MONEYBOOKERS MESSAGE CANCEL' );
					$html .= $this->_renderHtml( $text );
				  break;				
				default:
					$text = JText::_( 'TIENDA MONEYBOOKERS MESSAGE INVALID ACTION' );
					$html .= $this->_renderHtml( $text );
				  break;
			}
	        
	        return $html;
		}
    }
    
	/**
     * Prepares variables for the payment form
     *  
     * @param $data     array       form post data for pre-populating form
     * @return string   HTML to display
     */
    function _renderForm( $data )
    {
        // Render the form for collecting payment info   	
        
        $html = $this->_getLayout('form');
        return $html;
    }
    
    /************************************
     * Note to 3pd: 
     * 
     * Below is the code specific for
     * this plugin
     * 
     ************************************/
        
    /**
     * Processes the payment
     * 
     * This method process only real time (simple) payments
     * The scheduled recurring payments are processed by the corresponding method
     * 
     * - Merchant creates a pending transaction or order for X amount in their system.
	 * - Merchant redirects the customer to the Moneybookers Payment Gateway where the customer completes the transaction.
     * - Moneybookers posts the confirmation for a transaction to the status_url, which includes the 'mb_amount' parameter.
     * - The Merchant's application at 'status_url' first validates the parameters by calculating the md5sig (see Annex III – MD5 Signature) and if successful, it should compare the value from the confirmation post (amount parameter) to the one from the pending transaction/order in their system. Merchants may also wish to compare other parameters such as transaction id and pay_from_email. Once everything is correct the Merchant can process the transaction in their system, crediting the money to their customer's account or dispatching the goods ordered.
     * 
     * @return string
     * @access protected
     */
    function _process()
    {
    	$errors = array();
    	$send_email = false;
    	
    	$data = JRequest::get('post');  	
    	
    	$this->_logResponse($data);
    	
    	// make some initial validations
		if ($error = $this->_validatePayment($data)) {
			$errors[] = $error;
		}
		
		// process the payment if this is not a cancellation (-1) or charge back (-3)
		if ($data['status'] != '-1' && $data['status'] != '-3') {
			if ( ! isset($data['rec_payment_id'])) {
				$errors = $this->_processSale($data, $errors);
			}
			else {
				$errors = $this->_processSubscription($data, $errors);
			}
		}
		
	

        if (count($errors)) {						
			$error = implode("\n", $errors);
			return $error;
		}
		
		return 'processed';
    }   

    /**
     * Second prepayment
     * 
     * @param object $order order to process
     * @return string html to display
     */
    function _secondPrePayment()
	{	
		// Prepare order
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'controllers' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        
        // set the currency
		$order->currency_id = TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected
		// set the shipping method
		$order->shipping_method_id = TiendaConfig::getInstance()->get('defaultShippingMethod', '2');

		// Use AJAX to show plugins that are available
		JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );
		$guest = JRequest::getVar( 'guest', '0');
		if ($guest == '1' && TiendaConfig::getInstance()->get('guest_checkout_enabled'))
		{
			$guest = true;
		}
		else
		{
			$guest = false;
		}
		
		if (!$guest)
		{
			// set the order's addresses based on the form inputs
			// set to user defaults
			Tienda::load( 'TiendaHelperUser', 'helpers.user' );
			$billingAddress = TiendaHelperUser::getPrimaryAddress( JFactory::getUser()->id );
			$shippingAddress = TiendaHelperUser::getPrimaryAddress( JFactory::getUser()->id, 'shipping' );
			$order->setAddress( $billingAddress, 'billing' );
			$order->setAddress( $shippingAddress, 'shipping' );		
			$order->user_id = JFactory::getUser()->id;	
		}

		// get the items and add them to the order
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$items = TiendaHelperCarts::getProductsInfo();

		foreach ($items as $item)
		{
			$order->addItem( $item );
		}

		// get the order totals
		$order->calculateTotals();
		
		$order->order_state_id = '15';
		
		$order->save();
		
		// Prepare order payment
		$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
		$orderpayment->order_id = $order->order_id;
		$orderpayment->orderpayment_type = $this->_element;
        $orderpayment->orderpayment_amount = $order->order_total;
        $orderpayment->transaction_status = "Incomplete";	 
        $orderpayment->save();
		
        $vars = new JObject();        
        
        $vars->action_url = $this->_getActionUrl();        

        // properties as specified in moneybookers gateway manual
        $vars->pay_to_email = $this->_getParam( 'receiver_email' );
        $vars->transaction_id = $orderpayment->orderpayment_id;
        $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=message&checkout=0";
        $vars->return_url_text = JText::_( 'TIENDA MONEYBOOKERS TEXT ON FINISH PAYMENT BUTTON' );
        $vars->cancel_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=cancel";
        $vars->status_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=process&tmpl=component";
        $vars->status_url2 = $this->_getParam( 'receiver_email' );
        $vars->language = $this->_getParam( 'language', 'EN' );
        $vars->confirmation_note = JText::_( 'TIENDA MONEYBOOKERS CONFIRMATION NOTE' );
        $vars->logo_url = JURI::root().$this->_getParam( 'logo_image' );
        $vars->user_id = JFactory::getUser()->id;
		$vars->order_id = $order->order_id;
        $vars->orderpayment_id = $orderpayment->orderpayment_id;
	    $vars->orderpayment_type = $this->_element;	
	    $vars->currency = $this->_getParam( 'currency', 'USD' );
	    $vars->detail1_description = $order->order_id;
     	$vars->detail1_text = JText::_( 'TIENDA MONEYBOOKERS DETAIL1 DESCRIPTION' );
	    $vars->detail2_description = $orderpayment->orderpayment_id;
	    $vars->detail2_text = JText::_( 'TIENDA MONEYBOOKERS DETAIL2 DESCRIPTION' );
	    
	    $vars->is_recurring = $order->isRecurring();
	    $vars->mixed_cart = false;			
		$vars->rec_amount = $order->recurring_trial ? $order->recurring_trial_price : $order->order_total;
						
		$vars->rec_start_date = '';
			
		$vars->rec_period = $order->recurring_trial ? $order->recurring_trial_period_interval : $order->recurring_period_interval;
		$vars->rec_cycle = $this->_getDurationUnit($order->recurring_trial ? $order->recurring_trial_period_unit : $order->recurring_period_unit);// (day | week | month)
			
		// a period of days during which the customer can still process the transaction in case it originally failed.			
		$vars->rec_grace_period = 3;
		
		$html = $this->_getLayout('secondpayment', $vars);
        $html .= $this->_getLayout('prepayment', $vars);
        return $html;
    }

    /**
	 * Processes the sale payment
	 * 
	 * @param array $data Payment data
	 * @param object $user
	 * @param string $payment_details Formatted payment details to store
	 * @param array $errors 
	 * @return array
	 * @access protected
	 */
	function _processSale($data, $errors)
	{		
		$keyarray = $data;
		$keyarray['status'] = $this->_getMBStatus($data['status']);
		$payment_details = $this->_getFormattedPaymentDetails($keyarray);
    	
     	// check that payment amount is correct for order_id 
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $data['orderpayment_id'] );
        if (empty($orderpayment->order_id))
        {
             $errors[] = JText::_('TIENDA MONEYBOOKERS MESSAGE INVALID ORDER');
        }
        $orderpayment->transaction_details  = $payment_details;
        $orderpayment->transaction_id       = $data['mb_transaction_id'];
        $orderpayment->transaction_status   = $this->_getMBStatus($data['status']);

        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
        $respond_amount = TiendaHelperBase::number( $data['mb_amount'], array( 'thousands'=>'' ) );
        if ($stored_amount != $respond_amount ) {
        	$errors[] = JText::_('TIENDA MONEYBOOKERS MESSAGE PAYMENT AMOUNT INVALID');
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
            $order->order_state_id = $this->_getParam('failed_order_state', '10'); // FAILED
        }
        else 
        {
            $order->order_state_id = $this->_getParam('payment_received_order_state', '17');; // PAYMENT RECEIVED
                
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
	}
	
	/**
	 * Processes the recurring payment
	 * 
	 * @param array $data Payment data
	 * @param object $user
	 * @param string $payment_details Formatted payment details to store
	 * @param array $errors 
	 * @return array
	 * @access protected
	 */
	function _processSubscription($data, $errors)
	{
		
	}
    
	/**
	 * Validates the payment data posted back by MB  
	 * 
	 * @param array $data
	 * @return string Empty string if data is valid and an error message otherwise
	 * @access protected
	 */
	function _validatePayment($data)
	{
		// sig (i.e. data integrity)
		$sig = $this->_getParam('customer_id')
		     . $data['transaction_id']
		     . strtoupper(md5($this->_getParam('secret_word')))
		     . $data['mb_amount']
		     . $data['mb_currency']
		     . $data['status']
		     ;
		$sig = strtoupper(md5($sig));
		
		if ($sig != $data['md5sig']) {
			return JText::_('TIENDA MONEYBOOKERS MESSAGE SIG INVALID');
		}
		
		// receiver
		if ($this->_getParam('receiver_email') != $data['pay_to_email']) {
			return JText::_('TIENDA MONEYBOOKERS MESSAGE RECEIVER INVALID');
		}
		
		// payment status (processed (2) or pending (0))
		if ($data['status'] != '2' && $data['status'] != '0') {
			return JText::sprintf('TIENDA MONEYBOOKERS MESSAGE STATUS INVALID', $this->_getMBStatus($data['status']));
		}	
		
		return '';
	}
	
	/**
	 * Formatts the payment data before storing
	 * 
	 * @param array $data
	 * @return string
	 */
	function _getFormattedPaymentDetails($data)
	{
		$separator = "\n";
		$formatted = array();

		foreach ($data as $key => $value) {
			if ($key != 'view' && $key != 'layout') {
				$formatted[] = $key . ' = ' . $value;
			}
		}
		
		return count($formatted) ? implode("\n", $formatted) : '';		
	}
	    
	/**
	 * Gets the MoneyBookers gateway URL
	 * 
	 * @return string
	 * @access protected
	 */
	function _getActionUrl()
	{
		$url = 'https://www.moneybookers.com/app/payment.pl';
		return $url;
	}
	
	/**
	 * Logs the MB response data
	 * 
	 * @param array $data
	 * @return void
	 * @access protected
	 */
	function _logResponse($data)
	{
		if ($this->_isLog) {
			$f = fopen(JPATH_CACHE . "/".$this->_element.".txt", 'a');
			fwrite($f, "\n" . date('F j, Y, g:i a') . "\n");
			fwrite($f, print_r($data, true));
			fclose($f);
		}
	}
	
	/**
	 * Gets MB payment status title by its name
	 * 
	 * @param int $statusId
	 * @return string
	 * @access protected
	 */
	function _getMBStatus($statusId)
	{
		switch($statusId) {
			case  2 : return 'Complete';
			case  0 : return 'Pending';
			case -1 : return 'Cancelled';
			case -2 : return 'Failed';
			case -3 : return 'Chargeback';			
			
			default: return 'unknown_status';
		}
	}
	
	/**
	 * Wraps the given text in the HTML
	 *
	 * @param string $text
	 * @return string
	 * @access protected
	 */
	function _renderHtml($message = '')
	{
		$vars = new JObject();
		$vars->message = $message;

		$html = $this->_getLayout('message', $vars);

		return $html;
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

        $sandbox_param = "sendbox_".$name;
        $sb_value = $this->params->get($sandbox_param);
        if ($this->params->get('sandbox') && !empty($sb_value))
        {
            $return = $this->params->get($sandbox_param, $default);
        }

        return $return;
    }
	
	/**
	 * Converts the duration unit into the Moneybookers valid value
	 * 
	 * @param string $unit
	 * @return string|boolean
	 * @access protected
	 */
	function _getDurationUnit($unit)
	{
		switch ($unit) {
			case 'D' : return 'day';			
			case 'M' : return 'month';			
			case 'Y' : return 'year';
			
			default : return false;
		}
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
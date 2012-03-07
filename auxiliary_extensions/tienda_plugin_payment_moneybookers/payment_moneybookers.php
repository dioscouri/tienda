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
        $order = JTable::getInstance('Orders', 'TiendaTable');
		$order->load( $data['order_id'] );
	    $vars->is_recurring = $order->isRecurring();
	    $items = $order->getItems();
		
        $vars->pay_to_email = $this->_getParam( 'receiver_email' );
        $vars->transaction_id = TiendaConfig::getInstance()->get('order_number_prefix').$data['orderpayment_id'];
        $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=message&checkout=0";
        $vars->return_url_text = 'Exit Secure Payment'; //A
        $vars->cancel_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=cancel";
        $vars->status_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=process&tmpl=component";
        $vars->status_url2 = 'mailto:bojan.programer@gmail.com'; //$this->_getParam( 'receiver_email' );
        $vars->language = $this->_getParam( 'language', 'EN' );
        $vars->confirmation_note = JText::_( 'TIENDA MONEYBOOKERS CONFIRMATION NOTE' );
        $vars->logo_url = JURI::root().$this->_getParam( 'logo_image' );
        $vars->user_id = JFactory::getUser()->id;
		$vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
	    $vars->orderpayment_type = $this->_element;	
	    $vars->currency = $this->_getParam( 'currency', 'EUR' ); //A
	    $vars->detail1_description = $data['order_id'];
     	$vars->detail1_text = JText::_( 'TIENDA MONEYBOOKERS DETAIL1 DESCRIPTION' );
	    $vars->detail2_description = $data['orderpayment_id'];
	    $billing_address = TiendaHelperUser::getPrimaryAddress( $vars->user_id , 'billing' );
     	$vars->first_name       = $billing_address->first_name;
		$vars->last_name       	= $billing_address->last_name;
		$vars->phone_number      		= $billing_address->phone1;
		$vars->email      		= JFactory::getUser()->email;
		
		$vars->address       	= $billing_address->address_1;
		$vars->postal_code      = $billing_address->postal_code;
		$vars->city			    = $billing_address->city;
		$vars->country     		= $billing_address->country;
		$vars->state       		= $billing_address->zone_name;
		
     	//Currency Static ? 
	    
	    
	    JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'OrderItems', 'TiendaModel' );
        $model->setState( 'select', 'tbl.orderitem_id' );
        $model->setState( 'filter_orderid', $vars->order_id );
        $model->setState( 'filter_recurs', '1' );          	  	   	
        $recurring_orderitem_id = $model->getResult();        	
			
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $recurring_orderitem_table = JTable::getInstance( 'OrderItems', 'TiendaTable' );
		$recurring_orderitem_table->load( $recurring_orderitem_id );    
		$recurring_order_id = $recurring_orderitem_table->order_id;
		$recurring_orderitem_final_price = 0;
		if (is_null($recurring_order_id) == False)
		{    	
			$recurring_orderitem_final_price = $recurring_orderitem_table->orderitem_final_price + $recurring_orderitem_table->orderitem_tax;
		}
		$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $vars->orderpayment_id );
	    // if order has both recurring and non-recurring items
	    if ($vars->is_recurring && count($items) > '1')  //Mixed
		{
			$vars->mixed_cart = true;
			$recurring_orderitem_table->delete();
		
            $orderpayment->orderpayment_amount = $order->order_total - $recurring_orderitem_final_price;
            $orderpayment->save();
                        
			//$this->_sendErrorEmails($error, 'A0 -'.$orderpayment->orderpayment_amount.' = '.$order->order_total.' - '.$recurring_orderitem_final_price.' Recurring ID '.$recurring_order_id);
            $order = JTable::getInstance('Orders', 'TiendaTable');
			$order->load( $data['order_id'] );
            $order->calculateTotals(); 
            $order->save();
            
            $vars->is_recurring = false;
            $vars->amount = $orderpayment->orderpayment_amount;
            $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=message&checkout=1";                   
			}
			elseif ($vars->is_recurring && count($items) == '1') //Recurring Only
			{
				//Calls Second Prepayment fo
				$vars = $this->prepareSecondVars(  $order, $orderpayment  );	
			}
		    else //Non-Recurring Only
		    {
		    	$order->order_recurs = false;
		    	$vars->mixed_cart = false;
				$vars->amount = $data['orderpayment_amount'];
	    }
	
        $html = $this->_getLayout('prepayment', $vars);
        return $html;
    }

	/**
     * Second prepayment
     * 
     * @param object $order order to process
     * @return string html to display
     */
    function _secondPrePayment()
	{	
		//error_reporting(E_ALL);
		// Prepare order
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        
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
		
		$order = JTable::getInstance('Orders', 'TiendaTable');
        
        // set the currency
		$order->currency_id = TiendaConfig::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected
		// set the shipping method
		$order->shipping_method_id = TiendaConfig::getInstance()->get('defaultShippingMethod', '2');

		if (!$guest)
		{
			// set the order's addresses based on the form inputs
			// set to user defaults
			Tienda::load( 'TiendaHelperUser', 'helpers.user' );		
			$order->user_id = JFactory::getUser()->id;	
			$billing_address = TiendaHelperUser::getPrimaryAddress( $order->user_id , 'billing' );
			$shipping_address = TiendaHelperUser::getPrimaryAddress( $order->user_id, 'shipping' );
			$order->setAddress( $billing_address, 'billing' );
			$order->setAddress( $shipping_address, 'shipping' );
		}

		// get the items and add them to the order
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$items = TiendaHelperCarts::getProductsInfo();
		$order->ip_address        = $_SERVER['REMOTE_ADDR'];
		$order->order_state_id = '15';
		$order->addItem( $items[0] );
		$order->calculateTotals();
		$order->save();
			
			$items[0]->order_id = $order->order_id;
			$items[0]->orderitem_tax = $order->order_tax;
			$items[0]->save();
		
		// get the order totals
		
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$row = JTable::getInstance('OrderInfo', 'TiendaTable');
		$row->order_id = $order->order_id;
		$row->user_email = JFactory::getUser()->get('email');
		
		// Get Addresses
		$row->billing_first_name       	= $billing_address->first_name;
		$row->billing_last_name       	= $billing_address->last_name;
		$row->billing_middle_name       = $billing_address->middle_name;
		$row->billing_phone1       		= $billing_address->phone1;
		$row->billing_phone2       		= $billing_address->phone2;
		$row->billing_fax       		= $billing_address->fax;
		$row->billing_address_1       	= $billing_address->address_1;
		$row->billing_address_2       	= $billing_address->address_2;
		$row->billing_city       		= $billing_address->city;
		$row->billing_zone_name       	= $billing_address->zone_name;
		$row->billing_country_name      = $billing_address->country_name;
		
		$row->shipping_first_name       = $shipping_address->first_name;
		$row->shipping_last_name       	= $shipping_address->last_name;
		$row->shipping_middle_name      = $shipping_address->middle_name;
		$row->shipping_phone1       	= $shipping_address->phone1;
		$row->shipping_phone2       	= $shipping_address->phone2;
		$row->shipping_fax       		= $shipping_address->fax;
		$row->shipping_address_1       	= $shipping_address->address_1;
		$row->shipping_address_2       	= $shipping_address->address_2;
		$row->shipping_city       		= $shipping_address->city;
		$row->shipping_zone_name       	= $shipping_address->zone_name;
		$row->shipping_country_name     = $shipping_address->country_name;
		
		
		// set zones and countries
		$row->billing_zone_id       = $billing_address->zone_id;
		$row->billing_country_id    = $billing_address->country_id;
		$row->shipping_zone_id      = $shipping_address->zone_id;
		$row->shipping_country_id   = $shipping_address->country_id;
			
		if (!$row->save())
		{
			$this->setError( $row->getError() );
		}

		$order->orderinfo = $row;
		$order->save();
		
		$orderpayment_type = $this->_element;
		$transaction_status = JText::_("COM_TIENDA_INCOMPLETE");
		// in the case of orders with a value of 0.00, use custom values
		if ( (float) $order->order_total == (float)'0.00' )
		{
			$orderpayment_type = 'free';
			$transaction_status = JText::_("COM_TIENDA_COMPLETE");
		}

		// Save an orderpayment with an Incomplete status
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
		$orderpayment->order_id = $order->order_id;
		$orderpayment->orderpayment_type = $orderpayment_type; // this is the payment plugin selected
		$orderpayment->transaction_status = $transaction_status; // payment plugin updates this field onPostPayment
		$orderpayment->orderpayment_amount = $order->order_total; // this is the expected payment amount.  payment plugin should verify actual payment amount against expected payment amount
		$orderpayment->save();
		
        $vars = $this->prepareSecondVars( $order, $orderpayment );
		
		$html = $this->_getLayout('secondpayment', $vars);
        $html .= $this->_getLayout('prepayment', $vars);
        return $html;
    }
	
      
    
    /**
     * Prepares vars for the recurring prepayment
     * 
     * @param object vars
     * @return object vars
     */
    function prepareSecondVars( $order, $orderpayment ) 
    {
    	$vars = new JObject();        
        
        $vars->action_url = $this->_getActionUrl();        

        // properties as specified in moneybookers gateway manual
        $vars->pay_to_email = $this->_getParam( 'receiver_email' );
        $vars->transaction_id = TiendaConfig::getInstance()->get('order_number_prefix').$orderpayment->orderpayment_id; // this need to be subscription id??
        $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=message&checkout=0";
        $vars->return_url_text = 'Exit Secure Payment'; //A
        $vars->cancel_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=cancel";
        $vars->status_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=process&tmpl=component";
        $vars->status_url2 = 'mailto:bojan.programer@gmail.com';
        $vars->language = $this->_getParam( 'language', 'EN' );
        $vars->confirmation_note = JText::_( 'TIENDA MONEYBOOKERS CONFIRMATION NOTE' );
        $vars->logo_url = JURI::root().$this->_getParam( 'logo_image' );
        
	    $vars->currency = $this->_getParam( 'currency', 'EUR' );
	    $vars->detail1_description = $order->order_id;
     	$vars->detail1_text = JText::_( 'TIENDA MONEYBOOKERS DETAIL1 DESCRIPTION' );
	    $vars->detail2_description = $orderpayment->orderpayment_id;
	    $vars->detail2_text = JText::_( 'TIENDA MONEYBOOKERS DETAIL2 DESCRIPTION' );
	    
		//pay from email + All Customer Details to prefill MoneyBookers form
		//MD5 and other comparison
		$vars->user_id = JFactory::getUser()->id;
		$vars->order_id = $order->order_id;
        $vars->orderpayment_id = $orderpayment->orderpayment_id;
	    $vars->orderpayment_type = $this->_element;	
	    $vars->is_recurring = $order->isRecurring();
	    $vars->mixed_cart = false;
	    
	    $billing_address = TiendaHelperUser::getPrimaryAddress( $vars->user_id , 'billing' );
     	$vars->first_name       = $billing_address->first_name;
		$vars->last_name       	= $billing_address->last_name;
		$vars->phone_number     = $billing_address->phone1;
		$vars->email      		= JFactory::getUser()->email;
		
		$vars->address       	= $billing_address->address_1;
		$vars->postal_code      = $billing_address->postal_code;
		$vars->city			    = $billing_address->city;
		$vars->country     		= $billing_address->country;
		$vars->state       		= $billing_address->zone_name;
					
		$vars->rec_amount = $order->recurring_trial ? $order->recurring_trial_price : $order->order_total;
		$vars->rec_start_date = '';
		$vars->rec_period = $order->recurring_trial ? $order->recurring_trial_period_interval : $order->recurring_period_interval;
		$vars->rec_cycle = $this->_getDurationUnit($order->recurring_trial ? $order->recurring_trial_period_unit : $order->recurring_period_unit);// (day | week | month)
			
		// a period of days during which the customer can still process the transaction in case it originally failed.			
		$vars->rec_grace_period = 3;
		$vars->transaction_id = TiendaConfig::getInstance()->get('order_number_prefix').$orderpayment->orderpayment_id; // this need to be subscription id??
        
		return $vars;
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
        						// get the order_id from the session set by the prePayment
				                $mainframe =& JFactory::getApplication();
				                $order_id = (int) $mainframe->getUserState( 'tienda.order_id' );
				                $order = JTable::getInstance('Orders', 'TiendaTable');
				                $order->load( $order_id );
				                $items = $order->getItems();
        						// prepare payment for the recurring item Click Here to View and Print an Invoice 
        						//$html = $this->_secondPrePayment($order_id);
        						$html = $this->_secondPrePayment();      						
        					}        					
        				}        				
                    }
                    	else 
                   	{
                    	$text =  JText::_( 'TIENDA MONEYBOOKERS MESSAGE PAYMENT SUCCESS' );
						$html .= "<div align='center'><strong>".$this->_renderHtml( $text )."<strong></div>";
						$html .= $this->_displayArticle(); 	
                   	}									
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
            
    /**
     * Processes the payment
     * 
     * This method process only real time (simple) payments
     * The scheduled recurring payments are processed by the corresponding method
     * 
     * - Merchant creates a pending transaction or order for X amount in their system.
	 * - Merchant redirects the customer to the Moneybookers Payment Gateway where the customer completes the transaction.
     * - Moneybookers posts the confirmation for a transaction to the status_url, which includes the 'mb_amount' parameter.
     * - The Merchant's application at 'status_url' first validates the parameters by calculating the md5sig (see Annex III � MD5 Signature) and if successful, it should compare the value from the confirmation post (amount parameter) to the one from the pending transaction/order in their system. Merchants may also wish to compare other parameters such as transaction id and pay_from_email. Once everything is correct the Merchant can process the transaction in their system, crediting the money to their customer's account or dispatching the goods ordered.
     * 
     * @return string
     * @access protected
     */
    function _process()
    {
		
    	$errors = array();
    	$send_email = false;
    	
    	$data = JRequest::get('POST');  	
    	
    	$this->_logResponse($data);
		
    	// make some initial validations
		if ($error = $this->_validatePayment($data)) {
			$errors[] = $error;
		}
			
		// process the payment if this is not a cancellation (-1) or failed (-2) or charge back (-3)
		if ($data['status'] != '-1' && $data['status'] != '-2' && $data['status'] != '-3') {
			if ( ! isset($data['rec_payment_id'])) {
				$payment_error = $this->_processSale($data, $errors);
			}
			else {
				$payment_error = $this->_processSubscription($data, $errors);
			}
		}
		
		if ($payment_error) {
            // it seems like an error has occurred during the payment process
            $error .= $error ? "\n" . $payment_error : $payment_error;
        } 	

        if ($error) {   
            // send an emails to site's administrators with error messages
            foreach($error as $value)
			 	$this->_sendErrorEmails($error, 'A9 ='.$value);
            return $error;
        }
		
        // if here, all went well
        $error = 'processed';
		return $error;
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
    	echo $data[0];
     	// check that payment amount is correct for order_id 
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $data['orderpayment_id'] );
        
        if (empty($orderpayment->order_id))
        {
             $errors[] = JText::_('TIENDA MONEYBOOKERS MESSAGE INVALID ORDER');
			 $this->_sendErrorEmails($error, 'A1 -'.$orderpayment->order_id);
        }
		echo $orderpayment->order_id.'</br>';
        $orderpayment->transaction_details  = $payment_details;
        $orderpayment->transaction_id       = $data['mb_transaction_id'];
        $orderpayment->transaction_status   = $this->_getMBStatus($data['status']);

        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
        $respond_amount = TiendaHelperBase::number( $data['amount'], array( 'thousands'=>'' ) );
        if ($stored_amount != $respond_amount ) {
        	$errors[] = JText::_('TIENDA MONEYBOOKERS MESSAGE PAYMENT AMOUNT INVALID');
            $errors[] = $stored_amount . " != " . $respond_amount;
			$this->_sendErrorEmails($error, 'A2 -'.$stored_amount);
			$this->_sendErrorEmails($error, 'A3 -'.$respond_amount);
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
			$this->_sendErrorEmails($error, 'A4 -'.$orderpayment->getError()); 
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
		
        return $errors;	
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
			 $this->_sendErrorEmails($error, 'A18 -'.$data['orderpayment_id']);
        }
        $orderpayment->transaction_details  = $payment_details;
        $orderpayment->transaction_id       = $data['rec_payment_id'];
        $orderpayment->transaction_status   = $this->_getMBStatus($data['status']);

        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
        $respond_amount = TiendaHelperBase::number( $data['amount'], array( 'thousands'=>'' ) );
        if ($stored_amount != $respond_amount) {
        	$errors[] = JText::_('TIENDA MONEYBOOKERS MESSAGE PAYMENT AMOUNT INVALID');
            $errors[] = $stored_amount . " != " . $respond_amount;
			$this->_sendErrorEmails($error, 'A19 -'.$stored_amount . " != " . $respond_amount);
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
            $send_email = false;
            
            // make subscription
            $subscription = true;
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
            $order_email = $model->getItem();
            $helper->sendEmailNotices($order_email, 'new_order');
        }
        
        if( $subscription )
        {
	        // is the recipient correct?
	        if (empty($data['pay_to_email']) || $data['pay_to_email'] != $this->_getParam( 'receiver_email' )) {
	            $errors[] = JText::_('TIENDA MONEYBOOKERS MESSAGE RECEIVER INVALID');
	        }
	        // if no subscription exists for this subscr_id,
	        // create new subscription for the user
	        $subscription = JTable::getInstance('Subscriptions', 'TiendaTable');
	        $subscription->load( array('transaction_id'=>$data['rec_payment_id']));
	        if (empty($subscription->subscription_id))
	        {
	            $date = JFactory::getDate();
	            // create new subscription
	            // if recurring trial, set it 
	            // for the order's recurring_trial_period_interval
	            // using its recurring_trial_period_unit
	            // otherwise, do the normal recurring_period_interval
	            // and the recurring_period_unit
	            $recurring_period_unit = $order->recurring_period_unit;
	            $recurring_period_interval = $order->recurring_period_interval;
	            if (!empty($order->recurring_trial))
	            {
	                $recurring_period_unit = $order->recurring_trial_period_unit;
	                $recurring_period_interval = $order->recurring_trial_period_interval;
	            }      

				error_reporting(E_ALL);
				//$items = $order->getItems();
	           // $item = $items[0];
	            
	            
		        $items = $order->getItems();
		        
		        foreach ($items as $item)
				{
	            	$subscription->product_id = $item->product_id;
	            	$subscription->orderitem_id = $item->orderitem_id;
				}
		
	            $subscription->user_id = $order->user_id;
	            $subscription->order_id = $order->order_id;
	            $subscription->transaction_id = $data['rec_payment_id'];
	            $subscription->created_datetime = $date->toMySQL();
	            $subscription->subscription_enabled = '1';
	            
	        	switch($recurring_period_unit) 
	            {   
	                case "Y":
	                    $period_unit = "YEAR";
	                    break;
	                case "M":
	                    $period_unit = "MONTH";
	                    break;
	                case "W":
	                    $period_unit = "WEEK";
	                    break;
	                case "D":
	                default:
	                    $period_unit = "DAY";
	                    break;
	            }
	            $database = JFactory::getDBO();
	            $query = " SELECT DATE_ADD('{$subscription->created_datetime}', INTERVAL {$recurring_period_interval} $period_unit ) ";
	            $database->setQuery( $query );
	            $subscription->expires_datetime = $database->loadResult();
	            
	            if (!$subscription->save())
	            {
	                $this->setError( $subscription->getError() );
	                return false;
	            }
	            
	            // add a sub history entry, email the user?
	            $subscriptionhistory = JTable::getInstance('SubscriptionHistory', 'TiendaTable');
	            $subscriptionhistory->subscription_id = $subscription->subscription_id;
	            $subscriptionhistory->subscriptionhistory_type = 'creation';
	            $subscriptionhistory->created_datetime = $date->toMySQL();
	            $subscriptionhistory->notify_customer = '0'; // notify customer of new trial subscription?
	            $subscriptionhistory->comments = JText::_( 'TIENDA MONEYBOOKERS NEW SUBSCRIPTION CREATED' );
	            $subscriptionhistory->save();
	            
	        }
	            else
	        {
	            // subscription exists, just update its expiration date
	            // based on normal interval and period
	            switch($order->recurring_period_unit) 
	            {
	                case "Y":
	                    $period_unit = "YEAR";
	                    break;
	                case "M":
	                    $period_unit = "MONTH";
	                    break;
	                case "W":
	                    $period_unit = "WEEK";
	                    break;
	                case "D":
	                default:
	                    $period_unit = "DAY";
	                    break;
	            }
	            $database = JFactory::getDBO();
	            $today = $date = JFactory::getDate();
	            $query = " SELECT DATE_ADD('{$today}', INTERVAL {$order->recurring_period_interval} $period_unit ) ";
	            $database->setQuery( $query );
	            $subscription->expires_datetime = $database->loadResult();
	            
	            if (!$subscription->save())
	            {
	                $this->setError( $subscription->getError() );
	                return false;
	            }
	            
	            // add a sub history entry, email the user?
	            $subscriptionhistory = JTable::getInstance('SubscriptionHistory', 'TiendaTable');
	            $subscriptionhistory->subscription_id = $subscription->subscription_id;
	            $subscriptionhistory->subscriptionhistory_type = 'payment';
	            $subscriptionhistory->created_datetime = $date->toMySQL();
	            $subscriptionhistory->notify_customer = '0'; // notify customer of new trial subscription?
	            $subscriptionhistory->comments = JText::_( 'NEW SUBSCRIPTION PAYMENT RECEIVED' );
	            $subscriptionhistory->save();
	        }
        }
        
        
            // update order status
            Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
            Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
            $order->order_state_id = $this->params->get('payment_received_order_state', '17');; // PAYMENT RECEIVED
            
            // do post payment actions
            $setOrderPaymentReceived = true;
            
            // send email
            $send_email = true;
            
            // save the order
            if (!$order->save())
            {
                $errors[] = $order->getError();
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
        
        
        $error = count($errors) ? implode("\n", $errors) : '';
        if (!empty($error))
        {
            $this->setError( $error );
            return false;            
        }
        return true;
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
        
        $recipients = 'bojan.programer@gmail.com';
        $mailer =& JFactory::getMailer();
        
        $subject = $keyarray['status'];

        
            $mailer = JFactory::getMailer();        
            $mailer->addRecipient( $recipients );
        
            $mailer->setSubject( $subject );
            $mailer->setBody( JText::sprintf('TIENDA MONEYBOOKERS EMAIL PAYMENT FAILED BODY', $recipient->name, $sitename, $siteurl, $message, $paymentData) );          
            $mailer->setSender(array( $mailfrom, $fromname ));
            $sent = $mailer->send();
        

        return true;
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
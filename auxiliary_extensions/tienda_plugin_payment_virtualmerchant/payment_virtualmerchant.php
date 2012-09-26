<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2011 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_virtualmerchant extends TiendaPaymentPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element    = 'payment_virtualmerchant';

    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object $subject The object to observe
     * @param   array  $config  An array that holds the plugin configuration
     * @since 1.5
     */
    function plgTiendaPayment_virtualmerchant(& $subject, $config) {
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
        
        $vars->ssl_merchant_id = $this->params->get('ssl_merchant_id', '');
        $vars->ssl_user_id = $this->params->get('ssl_user_id', '');
        $vars->ssl_pin = $this->params->get('ssl_pin', '');
        $vars->test_mode = $this->params->get('test_mode', '0');
		$vars->merchant_demo_mode = $this->params->get('merchant_demo_mode', '0');
		$vars->inline_creditcard_form = $this->params->get('inline_creditcard_form', '0');
       
		$vars->ssl_customer_code = JFactory::getUser()->id;
        $vars->ssl_invoice_number = $data['orderpayment_id'];
        $vars->ssl_description = JText::_('Order Number: ').$data['order_id'];
        
        // Billing Info
        $vars->first_name   = $data['orderinfo']->billing_first_name;
        $vars->last_name    = $data['orderinfo']->billing_last_name;
        $vars->email        = $data['orderinfo']->user_email;
        $vars->address_1    = $data['orderinfo']->billing_address_1;
        $vars->address_2    = $data['orderinfo']->billing_address_2;
        $vars->city         = $data['orderinfo']->billing_city;
        $vars->country      = $data['orderinfo']->billing_country_name;
        $vars->state        = $data['orderinfo']->billing_zone_name;
        $vars->zip  		= $data['orderinfo']->billing_postal_code;
        
        $vars->amount = @$data['order_total'];

		if ($vars->merchant_demo_mode == 1)
		{
			$vars->payment_url = "https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do";
		}
		else
		{
			$vars->payment_url = "https://www.myvirtualmerchant.com/VirtualMerchant/process.do";
		}

		if ( $vars->inline_creditcard_form == 0)
		{
			$vars->receipt_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element;
			$vars->failed_url  = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element;
			$html = $this->_getLayout('prepayment', $vars);
		}
		elseif ( $vars->inline_creditcard_form == 1)
		{
			$vars->action_url  = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element;
			$vars->order_id	   = $data['order_id'];
			$html = $this->_getLayout('paymentcreditcard', $vars);
		}

        return $html;
    }

	/**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param  $data           array       creidt card data
     * @return $result_params array Result response from virtualpayment
     */
	function _getInlinePaymentResponse($data)
	{
		// prepare the payment form
        $vars = new JObject();

		$vars->ssl_merchant_id = $this->params->get('ssl_merchant_id', '');
        $vars->ssl_user_id = $this->params->get('ssl_user_id', '');
        $vars->ssl_pin = $this->params->get('ssl_pin', '');
        $vars->test_mode = $this->params->get('test_mode', '0');
		$vars->merchant_demo_mode = $this->params->get('merchant_demo_mode', '0');
		$vars->inline_creditcard_form = $this->params->get('inline_creditcard_form', '0');
		$vars->transaction_type = $this->params->get('transaction_type');
		$vars->order_id	= $data['order_id'];

		$orderinfo = JTable::getInstance('OrderInfo', 'TiendaTable');
        $orderinfo->load( $vars->order_id );

		$order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $vars->order_id );

		$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
		$orderpayment->load( $vars->order_id );

		$vars->address     = $orderinfo->billing_address_1;
		$vars->address    .= ($orderinfo->billing_address_2)?','.$orderinfo->billing_address_2:'';
		$vars->postal_code = $orderinfo->billing_postal_code;
		$vars->ssl_invoice_number = $orderpayment->orderpayment_id;

		$vars->order_total = $order->order_total;

		$vars->ssl_customer_code = JFactory::getUser()->id;

		if ($vars->merchant_demo_mode == 1)
		{
			$vars->payment_url = "https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do";
		}
		else
		{
			$vars->payment_url = "https://www.myvirtualmerchant.com/VirtualMerchant/process.do";
		}

		$vars->credit_card = $data['credit_card'];
		$expire_year = substr($data['expire_year'], 2, 2);
		$vars->expire_date = $data['expire_month'].$expire_year;
		$vars->card_cvv	   = $data['card_cvv'];

		$paymentParams = array();
		$paymentParams [] = 'ssl_merchant_id='.$vars->ssl_merchant_id;
		$paymentParams [] = 'ssl_user_id='.$vars->ssl_user_id;
		$paymentParams [] = 'ssl_pin='.$vars->ssl_pin;
		$paymentParams [] = 'ssl_transaction_type='.$vars->transaction_type;
		$paymentParams [] = 'ssl_amount='.sprintf('%0.2f', $vars->order_total);
		$paymentParams [] = 'ssl_customer_code='.$vars->ssl_customer_code;
		$paymentParams [] = 'ssl_show_form=FALSE';
		$paymentParams [] = 'ssl_result_format=ASCII';
		$paymentParams [] = 'ssl_card_number='.$vars->credit_card;
		$paymentParams [] = 'ssl_card_present=Y';
		$paymentParams [] = 'ssl_exp_date='.$vars->expire_date;
		$paymentParams [] = 'ssl_cvv2cvc2='.$vars->card_cvv;
		$paymentParams [] = 'ssl_cvv2cvc2_indicator=Y';
		$paymentParams [] = 'ssl_receipt_decl_method=REDG';
		$paymentParams [] = 'ssl_invoice_number='.$vars->ssl_invoice_number;
		$paymentParams [] = 'ssl_avs_address='.$vars->address;
		$paymentParams [] = 'ssl_avs_zip='.$vars->postal_code;

		$postParams = implode('&', $paymentParams);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $vars->payment_url); // set url to post to
		curl_setopt($ch,CURLOPT_POST, 1); // set POST method
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
		$result = curl_exec($ch); // run the whole process
		curl_close($ch);

		$response_params = explode("\n", $result);
		
		foreach ( $response_params as $param ) {
			list($key, $value) = split('=', $param);
			$result_params[trim($key)] = trim($value);
		}

		return $result_params;
	}

    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
	 * inline_creditcard_form
	 * value=1 : send curl request and get response from virtualpayment gateway
	 * value=0 : send web request to virtualpayment server
	 *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _postPayment( $data )
    {
		$vars->inline_creditcard_form = $this->params->get('inline_creditcard_form', '0');
		
		if ($vars->inline_creditcard_form == 1) 
		{
			$data = $this->_getInlinePaymentResponse($data);
			$result = $data['ssl_result'];
		} 
		else 
		{
			// Process the payment
			$result = JRequest::getVar('ssl_result');
		}

        $vars = new JObject();

        switch ($result)
        {
            case "0":
            	$errors = $this->_process( $data );

            	// No errors
            	if($errors == '')
            	{
	                $vars->message = JText::_('VIRTUALMERCHANT PAYMENT OK');
	                $html = $this->_getLayout('message', $vars);
	                $html .= $this->_displayArticle();
            	}
            	// Errors
            	else
            	{
            		$vars->message = $errors;
	                $html = $this->_getLayout('message', $vars);
	                $html .= $this->_displayArticle();
            	}
              break;
            default:
            case "1":
                $vars->message = JText::_('VIRTUALMERCHANT PAYMENT FAILED').': '.$data['ssl_result_message'];
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();
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
    
    function _process($data)
    {
		$post = JRequest::get('post');
    	
    	$orderpayment_id = @$data['ssl_invoice_number'];
    	
    	$errors = array();
    	$send_email = false;
    	
    	// load the orderpayment record and set some values
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $orderpayment_id );
        if (empty($orderpayment_id) || empty($orderpayment->orderpayment_id))
        {
            $errors[] = JText::_('VIRTUALMERCHANT MESSAGE INVALID ORDERPAYMENTID');
            return count($errors) ? implode("\n", $errors) : '';
        }
        $orderpayment->transaction_details  = $data['ssl_result_message'];
        $orderpayment->transaction_id       = $data['ssl_txn_id'];
        $orderpayment->transaction_status   = $data['ssl_result'];
       
        // check the stored amount against the payment amount        
    	Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
        $respond_amount = TiendaHelperBase::number( $data['ssl_amount'], array( 'thousands'=>'' ) );
        if ($stored_amount != $respond_amount ) {
        	$errors[] = JText::_('VIRTUALMERCHANT MESSAGE AMOUNT INVALID');
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

        return count($errors) ? implode("\n", $errors) : '';    

    	return true;
    }

}


<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2010 Dioscouri. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_2checkout extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_2checkout';

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
	function plgTiendaPayment_2checkout(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, null, true);
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
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper = TiendaHelperBase::getInstance();
        
        $vars = new JObject();
        $vars->cart_order_id = $data['order_id'].$helper->getToday();
        $vars->merchant_order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->total = $data['orderpayment_amount'];
        
        $vars->x_Receipt_Link_URL = JURI::base()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element;
        
        // Destination
        if($this->params->get('page', 'single') == 'single'){
        	$vars->url = 'https://www.2checkout.com/checkout/spurchase';
        } else{
        	$vars->url = 'https://www.2checkout.com/checkout/purchase';
        	// Payment type
        	$vars->pay_method = $this->params->get('pay_method', 'CC');
        }
        
        // Sandbox mode?
        if($this->params->get('sandbox', '0') == 1) 
        	$vars->url = "http://developers.2checkout.com/return_script/";
        
        // 2Checkout account number
        $vars->sid = $this->params->get('sid', '0');
        
        // Demo Mode?
   		if($this->params->get('demo', '0') == 1){
        	$vars->demo = true;
        } else{
        	$vars->demo = false;
        }
        
        // Language
   		if ($this->params->get('automatic_language', '0') == 1)
   		{
        	// automatic language from joomla
   			jimport('joomla.language.helper');
   			$lang = JLanguageHelper::detectLanguage();
            // TODO Use JFactory::getLanguage(); 
            // and explode the language's code by the '-' to get the var->lang for 2CO
   			switch($lang)
   			{
   				// Do more than these two
   				case "it-IT":
   					$vars->lang = "it";
   					break;
   				case "en-GB":
   				default: 	  
   					$vars->lang = 'en';
   					break;
   			}
   			
        } else{
        	$vars->lang = $this->params->get('default_language', 'en');
        }
        
        // Skip Landing
        if($this->params->get('skip_landing', '0') == 1)
        	$vars->skip_landing = true;
        else
        	$vars->skip_landing = false;
        	
        // Billing Address
        $vars->first_name   = $data['orderinfo']->billing_first_name;
        $vars->last_name    = $data['orderinfo']->billing_last_name;
        $vars->email        = $data['orderinfo']->user_email;
        $vars->street_address    = $data['orderinfo']->billing_address_1;
        $vars->street_address2    = $data['orderinfo']->billing_address_2;
        $vars->city         = $data['orderinfo']->billing_city;
        $vars->country      = $data['orderinfo']->billing_country_name;
        $vars->state       = $data['orderinfo']->billing_zone_name;
        $vars->zip  = $data['orderinfo']->billing_postal_code;
        
        // Shipping Address
        $vars->ship_name   = $data['orderinfo']->shipping_first_name. " " . $data['orderinfo']->shipping_last_name;
        $vars->ship_street_address    = $data['orderinfo']->shipping_address_1;
        $vars->ship_street_address2    = $data['orderinfo']->shipping_address_2;
        $vars->ship_city         = $data['orderinfo']->shipping_city;
        $vars->ship_country      = $data['orderinfo']->shipping_country_name;
        $vars->ship_state       = $data['orderinfo']->shipping_zone_name;
        $vars->ship_zip  = $data['orderinfo']->shipping_postal_code;
        
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
    	$values = JRequest::get('request');
	
    	$approved = $values['credit_card_processed'] == 'Y' ? true : false;
        	        	
    	$secret_word = $this->params->get('secret_word', '');
    	$vendor_number = $this->params->get('sid', '');
    	
    	//for testing purposes
    	//we set the order number to 1
    	$order_number = $this->params->get('demo', '0') == 1 ? 1 : $values['order_number'];       	
   
    	//$total = $data['orderpayment_amount'];// it is not defined
    	$total = $data['total'];
    	
    	$check = strtoupper(md5($secret_word.$vendor_number.$order_number.$total));
  	
    	$vars = new JObject();
    	// Check MD5 hash
    	if( ( $check == $values['key'] ) && ( $approved ) )
    	{
    		$vars->approved = true;
    	} 
    	else
    	{
    		$vars->approved = false;
    	}
    	
    	$data_temp = array_merge($values, $data);
    	
    	//we dont process the sale if we have inconsistent hash ?
    	if($vars->approved)
    	{    
    		$return = $this->_processSale($data_temp, $vars);
    	
    		if(empty($return))
    		{
    			$vars->message = JText::_('COM_TIENDA_TIENDA_2CHECKOUT_MESSAGE_PAYMENT_ACCEPTED_FOR_VALIDATION');  
    		}
    		else {
    			$vars->message = $return;
    		}  		
    	}
    	else 
    	{
    		$this->_processSale($data_temp, $vars);
    		$vars->message = JText::_('COM_TIENDA_TIENDA_2CHECKOUT_MESSAGE_PAYMENT_SECURITY_ERROR');
    	}
    	
    	// Process the payment
        $html = $this->_getLayout('message', $vars);
                
        return $html;
    }
    
    /**
     * Processes the form data 
     */
    function _processSale($data, $vars)
    {
    	$errors = array();
    	
    	// load the orderpayment record and set some values
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $orderpayment_id = JRequest::getVar('orderpayment_id');
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $orderpayment_id );
        $orderpayment->transaction_details  = $data['key'];
        $orderpayment->transaction_id       = $data['order_number'];
        $orderpayment->transaction_status   = $data['credit_card_processed'];
       
        // check the stored amount against the payment amount
    	Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
        $respond_amount = TiendaHelperBase::number( $data['total'], array( 'thousands'=>'' ) );
        if ($stored_amount != $respond_amount ) {
        	$errors[] = JText::_('COM_TIENDA_2CO_MESSAGE_AMOUNT_INVALID');
        	$errors[] = $stored_amount . " != " . $respond_amount;
        }   
        
        // set the order's new status and update quantities if necessary
        Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $orderpayment->order_id );
        $send_email = false;
        
        if(!$vars->approved)
        {
        	//incorrect hash
        	 $order->order_state_id = '14'; // Unspecified Error
        }
        else 
        {
        	if(count($errors))
        	{
        		// if an error occurred 
            	$order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED        	
        	}
        	else 
        	{
        		$order->order_state_id = $this->params->get('payment_received_order_state', '17'); // PAYMENT RECEIVED            
            	// do post payment actions
            	$setOrderPaymentReceived = true;            
            	// send email
            	$send_email = true;
        	}        
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
    }
    
    /**
     * Prepares variables and 
     * Renders the form for collecting payment info
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
	
}

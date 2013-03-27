<?php
/**
 * @version	1.1
 * @author 	Lewis Lin
 * @link 	http://www.Flo2Cash.co.nz
 * @copyright Copyright (C) 2011 Flo2Cash. All rights reserved.
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_flo2cash extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_flo2cash';
	
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
	function plgTiendaPayment_flo2cash(& $subject, $config)
	{
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

        // set flo2cash checkout type
        $order = DSCTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data['order_id'] );

        $items = $order->getItems();
        // Build a descriptive string to show on the email message Changed my Jimmy Anderson NZGeo
        $vars->item_list = '';
        foreach ($items as $item) {
           if (!empty($vars->item_list)) $vars->item_list .= ', ';
           $vars->item_list .= $item->orderitem_name;
        }
        
        $vars->order = $order;
        $vars->orderitems = $items;
        
        // set payment plugin variables   
        $vars->f2cAccid = $this->params->get( 'f2cAccid' );
        $vars->f2cReturnURL = JURI::base()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element;
        $vars->f2cHeaderImage = $this->params->get( 'f2cHeaderImage' );
        $vars->f2cHeaderBorderBottom = $this->params->get( 'f2cHeaderBorderBottom' );
        $vars->f2cHeaderBackgroundColor = $this->params->get( 'f2cHeaderBackgroundColor' );
        $vars->f2cStoreCard = $this->params->get( 'f2cStoreCard' );
        $vars->f2cCSCRequired = $this->params->get( 'f2cCSCRequired' );
        $vars->f2cTestMode = $this->params->get( 'f2cTestMode' );
        $vars->f2cDisplayEmail = $this->params->get( 'f2cDisplayEmail' );
        $vars->f2cLiveProcessURL = $this->params->get( 'f2cLiveProcessURL' );
        $vars->f2cTestProcessURL = $this->params->get( 'f2cTestProcessURL' );

		$vars->post_url = $this->params->get('f2cTestMode') ? $this->params->get('f2cTestProcessURL') : $this->params->get('f2cLiveProcessURL');

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
		$vars->storecard    = $data['orderinfo']->shipping_storecard;
        
        $html = $this->_getLayout('prepayment', $vars);
        return $html;
    }

    /**
     * Prepares the payment form
     * and returns HTML Form to be displayed to the user
     * for the second of two payments (when cart has both recurring and non-recurring items)
     * 
     * Submit button target for onsite payments & return URL for offsite payments should be:
     * index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=xxxxxx
     * where xxxxxxx = $_element = the plugin's filename 
     *  
     * @return string   HTML to display
     */
    function _postPayment( $data )
    {
		$values = JRequest::get('request');
		$vars = new JObject();
		
		$data_temp = array_merge($values, $data);
		
    	$vars->message = $this->_process($data_temp);

        $html = $this->_getLayout('postpayment', $vars);
        return $html;
    }   
    
    /*
     * Processes the form data 
     */
    function _process( $data )
    {
		$errors = array();
		
		// load the orderpayment record and set some values
		DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		$orderpayment = DSCTable::getInstance('OrderPayments', 'TiendaTable');
		$orderpayment->load( $data['reference'] );
		$orderpayment->transaction_details  = $data['key'];
		$orderpayment->transaction_id       = $data['txn_id'];
		
		// set the order's new status and update quantities if necessary
		Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$order = DSCTable::getInstance('Orders', 'TiendaTable');
		$order->load( $orderpayment->order_id );
		
		if($data['txn_status'] == '2')
		{
			$order->order_state_id = $this->params->get('payment_received_order_state', '17'); // PAYMENT RECEIVED            
			// do post payment actions
			$setOrderPaymentReceived = true;
		}
		else
		{
			$orderpayment->transaction_status   = 'Failed';
			$order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED
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
		
		$html = "";
		if($data['txn_status'] == '2')
		{
			// clear the shopping cart
			Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
			TiendaHelperCarts::removeOrderItems($orderpayment->order_id);
			
			$html = "<dl id='system-message'><dt class='message'>message</dt><dd class='message fade'><ul><li>Your order has been completed successfully. Your transaction no. is <strong>" .
			$data['txn_id'] .
			"</strong> and your receipt no. is <strong>" .
			$data['receipt_no'] .
			"</strong> <br />( <a href='index.php?option=com_tienda&view=orders&task=view&id=" .
			$orderpayment->order_id .
			"'>view invoice</a> )</li></ul></dd></dl>";
		}
		else
		{	
			$html = "<div style='padding-top: 10px;' id='validationmessage'><dl id='system-message'><dt class='notice'>notice</dt><dd class='notice message fade'><ul><li>Unable to complete order because : <i>" . 
			$data['response_text'] . 
			"</i></li></ul></dd></dl></div><style type='text/css'>.postpayment_article{display:none!important;visibility:hidden!important;}</style>";
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

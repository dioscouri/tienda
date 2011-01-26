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

class plgTiendaPayment_ambrapoints extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_ambrapoints';
    
	function plgTiendaPayment_ambrapoints(& $subject, $config) {
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
        $vars->url = JRoute::_( "index.php?option=com_tienda&view=checkout" );
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_type = $this->_element;
        $vars->amount_currency = $data['orderpayment_amount'];        
        $vars->points_rate = $this->_getParam('exchange_rate');
        $vars->amount_points = round( $vars->amount_currency * $vars->points_rate );
               
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
        
    	$data['amount_points'] = JRequest::getVar( 'amount_points' );
    	
    	$this->_process( $data );   	
    	
        $vars = new JObject();
        $vars->message = "Payment processed successfully.  Hooray!";
        
        $html = $this->_getLayout('postpayment', $vars);
        return $html;
    }
    
	/**
     * Prepares the 'view' tmpl layout
     * when viewing a payment record
     *
     * @param $orderPayment     object       a valid TableOrderPayment object
     * @return string   HTML to display
     */
    function _renderView( $orderPayment )
    {
        // Load the payment from _orderpayments and render its html
        
        $vars = new JObject();
        $vars->full_name        = "";
        $vars->email            = "";
        $vars->payment_method   = $this->_paymentMethods();
        
        $html = $this->_getLayout('view', $vars);
        return $html;
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
     * @return obj
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
     * The methods below here are 
     * specific to this payment plugin
     * 
     ************************************/  
    
    function _process( $data )
    {   	
    	// we'll check again if user have enough points
    	$user = JFactory::getUser();
        JLoader::register( "Ambra", JPATH_ADMINISTRATOR.DS."components".DS."com_ambra".DS."helpers".DS."user.php");
        $helper = Ambra::get( "AmbraHelperUser", 'helpers.user' );
        $usertotalpoints = $helper->getTotalPoints( $user->id );
        
        if( $data['amount_points'] <= $usertotalpoints )
        {
        	// substract spent points from user's ambra total points
        	
        	
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
	            $order->order_state_id = $this->params->get('payment_received_order_state', '17'); // PAYMENT RECEIVED
	            
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
        
        return count($errors) ? implode("\n", $errors) : '';
        
    }
}
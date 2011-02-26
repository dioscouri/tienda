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
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_type = $this->_element;
        $vars->orderpayment_amount = $data['orderpayment_amount'];        
        $vars->points_rate = $this->params->get('exchange_rate');
        $vars->amount_points = round( $vars->orderpayment_amount * $vars->points_rate );
               
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

    	$vars = new JObject();
    	
    	$errors = $this->_process( $data );
    	
    	if( empty( $errors ) )
    	{
    		$vars->message = JText::_('TIENDA AMBRAPOINTS PAYMENT SUCCESSUFUL');
            $html = $this->_getLayout('message', $vars);
            $html .= $this->_displayArticle(); 
    	}
    		else 
    	{
    		$vars->message = JText::_( 'TIENDA ALPHAUSERPOINTS PAYMENT ERROR MESSAGE' ) . $errors;
    		$vars->errors = $errors;
			$html = $this->_getLayout('message', $vars);
    	}
    	
    	return $html;
    	
    	/*$success = $this->_process( $data ); 
    	$display_article = $this->params->get('display_article_title');  	
    	
    	if( $success == '' )
    	{
    		if( $display_article )
    		{
    			$html = $this->_displayArticle();
    		}
    		else
      		{ 			
	        	$html = $this->_getLayout('postpayment');
    		}
    			        
	        return $html;
    	}
    	else
    	{       	
        	$vars->message = JText::_( 'Tienda Ambrapoints Payment Error Message' );
			$html = $this->_getLayout('message', $vars);
			return $html;
    	}*/
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
         
        $vars = new JObject();        
        $vars->message = JText::_( 'TIENDA AMBRAPOINTS PAYMENT MESSAGE' );
		
        $html = $this->_getLayout('message', $vars);
        
        return $html;
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
        $errors = array();
        
        $user = JFactory::getUser();
       
        // load the orderpayment record and set some values
	    JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
	    $orderpayment_id = $data['orderpayment_id'];
	    $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
	    $orderpayment->load( $orderpayment_id );
	    $orderpayment->transaction_details  = $data['orderpayment_type'];
	    $orderpayment->transaction_id       = $data['orderpayment_id'];
	    $orderpayment->transaction_status   = "Incomplete";
	       	        
	    // check the stored amount against the payment amount thousand
	    Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
        $respond_amount = TiendaHelperBase::number( $data['orderpayment_amount'], array( 'thousands'=>'' ) );	   
	    if ( $stored_amount != $respond_amount ) {
	    	$errors[] = JText::_('TIENDA AMBRAPOINTS PAYMENT MESSAGE AMOUNT INVALID');
	    }
	    
	    // set the order's new status and update quantities if necessary
	    Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
	    Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
	    $order = JTable::getInstance('Orders', 'TiendaTable');
	    $order->load( $orderpayment->order_id );
	    
    	// check if user has enough points
	    JLoader::import( 'com_ambra.helpers.user', JPATH_ADMINISTRATOR.DS.'components' );
		$current_points = AmbraHelperUser::getPoints( $order->user_id ); 
	    if( $data['amount_points'] > $current_points )
	    {
	    	$errors[] = JText::_('TIENDA AMBRAPOINTS PAYMENT MESSAGE NOT ENOUGH POINTS');
	    }	        
	   
	    if (count($errors)) 
	    {
	    	// if an error occurred 
	        $order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED
	        
	        $send_email = false;
	   	}
	        else 
	    {
	        $order->order_state_id = $this->params->get('payment_received_order_state', '17'); // PAYMENT RECEIVED
	        $orderpayment->transaction_status   = "Payment Received";  
	        
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
        	
    	// substract spent points from user's ambra total points
     	// successful payment
	 	// if here, all OK
		// create a pointhistory table object
	   	$pointhistory = JTable::getInstance('PointHistory', 'AmbraTable');
		// set properties
		$pointhistory->user_id = $user->id;
	  	$pointhistory->points = "-".$data['amount_points'];
	 	$pointhistory->points_updated = 0;
		$pointhistory->pointhistory_enabled = 1;
	 	$pointhistory->pointhistory_name = JText::_( "For making purchase in Tienda" );
		$pointhistory->pointhistory_description = 
		JText::_( "Payment ID" ) . ": " . $orderpayment_id . "\n" .
		JText::_( "Transaction ID" ) . ": " . $orderpayment->transaction_id;
	            
		// save it and move on
		if (!$pointhistory->save())
		{
			$errors[] = $pointhistory->getError();
	        	
			// if saving the record failed, disable sub?
		}
        
        return count($errors) ? implode("\n", $errors) : ''; 
        
    }
    
	/**
     * Determines if this payment option is valid for this order
     * 
     * @param $element
     * @param $order
     * @return unknown_type
     */
    function onGetPaymentOptions($element, $order)
    {       
        // Check if this is the right plugin
        if (!$this->_isMe($element)) 
        {
            return null;
        }
        
        // if this payment method should be available for this order, return true
        // if not, return false.
        // by default, all enabled payment methods are valid, so return true here,
        // but plugins may override this
    	
        $amount_points = round( $order->order_total * $this->params->get('exchange_rate') );
       	
        JLoader::import( 'com_ambra.helpers.user', JPATH_ADMINISTRATOR.DS.'components' );
		$current_points = AmbraHelperUser::getPoints( $order->user_id ); 
		
        if( $amount_points > $current_points )
        {
        	return false;
        }
        else 
        {
        	return true;
        }              
    }
}
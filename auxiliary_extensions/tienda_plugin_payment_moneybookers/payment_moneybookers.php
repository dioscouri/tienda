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
	var $_isLog = true;
    
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
        $vars->pay_to_email = $this->params->get( 'receiver_email' );
        $vars->transaction_id = $data['orderpayment_id'];
        $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=message";
        $vars->return_url_text = JText::_( 'TIENDA MONEYBOOKERS TEXT ON FINISH PAYMENT BUTTON' );
        $vars->cancel_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=cancel";
        $vars->status_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=process";
        $vars->status_url2 = $this->params->get( 'receiver_email' );
        $vars->language = $this->params->get( 'language', 'EN' );
        $vars->confirmation_note = JText::_( 'TIENDA MONEYBOOKERS CONFIRMATION NOTE' );
        $vars->logo_url = JURI::root().$this->params->get( 'logo_image' );
        $vars->user_id = JFactory::getUser()->id;
		$vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
	    $vars->orderpayment_type = $this->_element;	
	    $vars->amount = $data['orderpayment_amount'];
	    $vars->currency = $this->params->get( 'currency', 'USD' );
	    $vars->detail1_description = $data['order_id'];
     	$vars->detail1_text = JText::_( 'TIENDA MONEYBOOKERS DETAIL1 DESCRIPTION' );
	    $vars->detail2_description = $data['orderpayment_id'];
	    $vars->detail2_text = JText::_( 'TIENDA MONEYBOOKERS DETAIL2 DESCRIPTION' );
     	
	    
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
        
        $paction = JRequest::getVar( 'paction' );
		$html = "";
			
		switch ($paction) {
			case "message":
				$text = JText::_( 'TIENDA MONEYBOOKERS MESSAGE PAYMENT SUCCESS' );
				$html .= $this->_renderHtml( $text );
				$html .= $this->_displayArticle();
			  break;
			case "process":
				$html .= $this->_process();					
					echo $html;
					
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
    	
     	// check that payment amount is correct for order_id
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $data['orderpayment_id'] );
        if (empty($orderpayment->order_id))
        {
             $errors[] = JText::_('TIENDA MONEYBOOKERS MESSAGE INVALID ORDER');
        }
        $orderpayment->transaction_details  = $data['payment_type'];
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

        if (empty($errors))
        {
            $return = JText::_( "TIENDA MONEYBOOKERS MESSAGE PAYMENT SUCCESS" );
        }
         
        //return count($errors) ? implode("\n", $errors) : $return;
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
		$sig = $this->params->get('customer_id')
		     . $data['transaction_id']
		     . strtoupper(md5($this->params->get('secret_word')))
		     . $data['mb_amount']
		     . $data['mb_currency']
		     . $data['status']
		     ;
		$sig = strtoupper(md5($sig));
		
		if ($sig != $data['md5sig']) {
			return JText::_('TIENDA MONEYBOOKERS MESSAGE SIG INVALID');
		}
		
		// receiver
		if ($this->params->get('receiver_email') != $data['pay_to_email']) {
			return JText::_('TIENDA MONEYBOOKERS MESSAGE RECEIVER INVALID');
		}
		
		// payment status (processed (2) or pending (0))
		if ($data['status'] != '2' && $data['status'] != '0') {
			return JText::sprintf('TIENDA MONEYBOOKERS MESSAGE STATUS INVALID', $this->_getMBStatus($data['status']));
		}	
		
		return '';
	}
	
	/**
	 * Payment canceled
	 * 
	 */
	function _paymentCanceled()
	{
		// TODO make order cancelation
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
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
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_moneybookers';
    
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
        $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=display_message";
        $vars->return_url_text = JText::_( 'TIENDA MONEYBOOKERS TEXT ON FINISH PAYMENT BUTTON' );
        $vars->cancel_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=cancel";
        $vars->status_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type={$this->_element}&paction=process&tmpl=component";
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
     	$vars->detail2_description = $this->params->get( 'receiver_email' );
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
        
        $paction 	= JRequest::getVar( 'paction' );
		$html = "";
			
		switch ($paction) {
			case "display_message":					
 				$html .= $this->_renderHtml( JText::_('TIENDA MONEYBOOKERS MESSAGE PAYMENT ACCEPTED FOR VALIDATION') ); 
				$html .= $this->_displayArticle();					
			  break;
			case "process":
				$html .= $this->_process();					
				echo $html;
				
				$app =& JFactory::getApplication();
				$app->close();
			  break;
			case "cancel":
				$text = JText::_( 'Moneybookers Message Cancel' );
				$html .= $this->_renderHtml( $text );
			  break;				
			default:
				$text = JText::_( 'Moneybookers Message Invalid Action' );
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
    	// load the orderpayment record and set some values
	    JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
	    $orderpayment_id = $data['orderpayment_id'];
	    $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
	    $orderpayment->load( $orderpayment_id );
	    $orderpayment->transaction_details  = $data['orderpayment_type'];
	    $orderpayment->transaction_id       = $data['orderpayment_id'];
	    $orderpayment->transaction_status   = "Pending";
	    
    	// check the stored amount against the payment amount
	    $stored_amount = number_format( $orderpayment->get('orderpayment_amount'), '2' );
	    if ((float) $stored_amount !== (float) $data['orderpayment_amount']) {
	    	$errors[] = JText::_('TIENDA MONEYBOOKERS MESSAGE AMOUNT INVALID');
	    }
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
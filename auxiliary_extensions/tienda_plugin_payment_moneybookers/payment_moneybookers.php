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
        
    	$user = JFactory::getUser();
    	
        $vars = new JObject();
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_amount = $data['orderpayment_amount'];
        $vars->orderpayment_type = $this->_element;
        
      /*  $vars->action_url = $this->_getActionUrl();
		$vars->pay_to_email = $this->params->get( 'receiver_email' );
		$vars->return_url = JURI::root()."index.php?option=com_ambrasubs&controller=payment&task=process&ptype={$this->_payment_type}&paction=display_message";
		$vars->cancel_url = JURI::root()."index.php?option=com_ambrasubs&controller=payment&task=process&ptype={$this->_payment_type}&paction=cancel";
		$vars->status_url = JURI::root()."index.php?option=com_ambrasubs&controller=payment&task=process&ptype={$this->_payment_type}&paction=process&tmpl=component";
		$vars->status_url2 = $this->params->get( 'receiver_email' );
		$vars->language = $this->params->get( 'language', 'EN' );
		$vars->user_id = $user->get('id');
		$vars->type_id = $row->get('id');			
		$vars->currency = $this->params->get( 'currency', 'USD' );*/
        
        
        
        $vars->message = "Preprocessing successful. Double-check your entries.  Then, to complete your order, click Complete Order!";
        
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
        
        $app =& JFactory::getApplication();
        $paction = JRequest::getVar( 'paction' );
        
        switch ($paction)
        {
            case 'process_recurring':
                // TODO Complete this
                // $this->_processRecurringPayment();
                $app->close();                  
              break;
            case 'process':
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
              break;
            default:
                $vars->message = JText::_( 'Invalid Action' );
                $html = $this->_getLayout('message', $vars);
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
     * This method process only real time (simple and subscription create) payments
     * The scheduled recurring payments are processed by the corresponding method
     * 
     * @return string
     * @access protected
     */
    function _process()
    {
    	$data = JRequest::get('post');
        
    	// ajust this and _processSimplePayment() function to the moneybookers
        // get order information
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data['order_id'] );
        if ( empty($order->order_id) ) {
            return JText::_( 'Moneybookers Message Invalid Order' );
        }
         
        if ( empty($this->login_id)) {
            return JText::_( 'Moneybookers Message Missing Merchant Login ID' );
        }
        if ( empty($this->tran_key)) {
            return JText::_( 'Moneybookers Message Missing Transaction Key' );
        }
        
        // prepare the form for submission to auth.net
        $process_vars = $this->_getProcessVars($data);
        
        return $this->_processSimplePayment($process_vars);
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
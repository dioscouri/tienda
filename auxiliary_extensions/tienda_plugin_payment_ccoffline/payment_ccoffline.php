<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2011 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_ccoffline extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_ccoffline';

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
	function plgTiendaPayment_ccoffline(& $subject, $config) 
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
        
        $vars->cardtype = !empty($data['cardtype']) ? $data['cardtype'] : JRequest::getVar('cardtype');
        $vars->cardnum = !empty($data['cardnum']) ? $data['cardnum'] : JRequest::getVar('cardnum');      
        $vars->cardcvv = !empty($data['cardcvv']) ? $data['cardcvv'] : JRequest::getVar('cardcvv');
        $vars->cardnum_last4 = substr( $vars->cardnum, -4 );
        
        $exp_month = !empty($data['cardexp_month']) ? $data['cardexp_month'] : JRequest::getVar('cardexp_month');
        if ($exp_month < '10') { $exp_month = '0'.$exp_month; } 
        $exp_year = !empty($data['cardexp_year']) ? $data['cardexp_year'] : JRequest::getVar('cardexp_year');
        $exp_year = $exp_year - 2000;
        $cardexp = $exp_month.$exp_year;
        $vars->cardexp = $cardexp;
        
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
        // Process the payment        
        $vars = new JObject();
        $orderpayment_id = !empty($data['orderpayment_id']) ? $data['orderpayment_id'] : JRequest::getVar('orderpayment_id');
                
        $cardtype = !empty($data['cardtype']) ? $data['cardtype'] : JRequest::getVar('cardtype');
        $cardnum = !empty($data['cardnum']) ? $data['cardnum'] : JRequest::getVar('cardnum');
        $cardexp =  !empty($data['cardexp']) ? $data['cardexp'] : JRequest::getVar('cardexp');      
        $cardcvv = !empty($data['cardcvv']) ? $data['cardcvv'] : JRequest::getVar('cardcvv');
        
        $formatted = array( 
                        'cardtype' => $cardtype,
        				'cardnum' => $cardnum,
				        'cardexp' => $cardexp,
				        'cardcvv' => $cardcvv
                        ); 
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $orderpayment_id );
        $orderpayment->transaction_details = implode("\n", $formatted); 
        if ($orderpayment->save())
        {
        	// Don't remove order quantities until payment is actually received?
        	if ( $this->params->get('remove_quantities') )
        	{
	            Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
	            TiendaHelperOrder::updateProductQuantities( $orderpayment->order_id, '-' );
        	}
            
            // remove items from cart
            Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
            TiendaHelperCarts::removeOrderItems( $orderpayment->order_id );
            
            // send notice of new order
            Tienda::load( "TiendaHelperBase", 'helpers._base' );
            $helper = TiendaHelperBase::getInstance('Email');
            
            $model = Tienda::getClass("TiendaModelOrders", "models.orders");
            $model->setId( $orderpayment->order_id );
            $order = $model->getItem();
            
            $helper->sendEmailNotices($order, 'new_order');
        }
        
        // display the layout
        $html = $this->_getLayout('postpayment', $vars);
        
        // append the article with offline payment information
        $html .= $this->_displayArticle();
        
        return $html;
    }
    
    /**
     * Prepares variables and 
     * Renders the form for collecting payment info
     * 
     * @return unknown_type
     */
    function _renderForm( $data )
    {
    	$vars = new JObject();
        //$vars->prepop = array();
        $vars->cctype_input = $this->_cardTypesField();
        
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
        $user = JFactory::getUser();
 
        foreach ($submitted_values as $key=>$value) 
        {
            switch ($key) 
            {
                case "cardtype":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key])) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('TIENDA CC OFFLINE PAYMENT TYPE INVALID')."</li>";
                    }
                  break;
                case "cardnum":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key])) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('TIENDA CC OFFLINE PAYMENT NUMBER INVALID')."</li>";
                    } 
                  break;
                case "cardexp":
                    if (!isset($submitted_values[$key]) || JString::strlen($submitted_values[$key]) != 4) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('TIENDA CC OFFLINE PAYMENT EXPIRATION DATE INVALID')."</li>";
                    } 
                  break;
                case "cardcvv":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key])) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('TIENDA CC OFFLINE PAYMENT CVV INVALID')."</li>";
                    } 
                  break;
                default:
                  break;
            }
        }   
            
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
	
    /**
     * Generates a dropdown list of valid CC types
     * @param $fieldname
     * @param $default
     * @param $options
     * @return unknown_type
     */
    function _cardTypesField( $field='cardtype', $default='', $options='' )
    {       
        $types_array = $this->params->get('cc_list');
        $types_array = explode( ',' , $types_array );
        
        $types = array();
        foreach ($types_array as $type)
        {        
        	$types[] = JHTML::_('select.option', $type, $type );
        }
        
        $return = JHTML::_('select.genericlist', $types, $field, $options, 'value','text', $default);
        return $return;
    }
    
	/**
     * Shows the CVV popup
     * @return unknown_type
     */
    public function showCVV($row)
    {
        if (!$this->_isMe($row))
        {
            return null;
        }
        
        $vars = new JObject();
        echo $this->_getLayout('showcvv', $vars);
        return;
    }
    
    /**
     * Displays the article with payment info on the order page & email if the order is yet to pay
     *
     * @param TiendaModelOrders $order
     */
    function onBeforeDisplayOrderView($order)
    {
    	$orderpayments = $order->orderpayments;
    	$is_me = false;
    	
    	foreach($orderpayments as $p)
    	{
	    	if ($this->_isMe($p->orderpayment_type)) 
	        {
	            $is_me = true;
	        }
    	}
        
    	if($is_me)
    	{
    		
	    	$initial = Tienda::getInstance()->get('initial_order_state', '1');
	    	$pending = Tienda::getInstance()->get('pending_order_state', '1');
	    	
	    	if($order->order_state_id == $pending || $order->order_state_id == $initial)
	    	{
	    		echo '<div style="clear:both;">';
	    		echo $this->_displayArticle();
	    		echo '</div>';
	    	}
	    	
    	}
    	
    }
}

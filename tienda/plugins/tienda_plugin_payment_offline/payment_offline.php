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

class plgTiendaPayment_offline extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_offline';

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
	function plgTiendaPayment_offline(& $subject, $config) 
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
        
        $vars = new JObject();
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_amount = $data['orderpayment_amount'];
        $vars->orderpayment_type = $this->_element;
        $vars->offline_payment_method = !empty($data['offline_payment_method']) ? $data['offline_payment_method'] : JRequest::getVar('offline_payment_method');
        
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
        $orderpayment_id =  !empty($data['orderpayment_id']) ? $data['orderpayment_id'] : JRequest::getVar('orderpayment_id');
        $offline_payment_method = !empty($data['offline_payment_method']) ? $data['offline_payment_method'] : JRequest::getVar("offline_payment_method");
        $formatted = array( 
                        'offline_payment_method' => $offline_payment_method 
                        ); 
        
        DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $orderpayment = DSCTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $orderpayment_id );
        $orderpayment->transaction_details = implode("\n", $formatted); 
        if ($orderpayment->save())
        {
        	// Don't remove order quantities until payment is actually received?
        	// Or do we remove them now?
        	// TODO Make that a param in the offline payments plugin
            Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
            TiendaHelperOrder::updateProductQuantities( $orderpayment->order_id, '-' );
            
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
    function _renderForm( $data=null )
    {
    	$user = JFactory::getUser();  	
        $vars = new JObject();
        $vars->payment_method   = $this->_paymentMethods('offline_payment_method', $this->params->get('default'));
        
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
                case "offlinetype":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key])) 
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_('COM_TIENDA_OFFLINE_PAYMENT_TYPE_INVALID')."</li>";
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
     * Generates a dropdown list of valid payment methods
     * @param $fieldname
     * @param $default
     * @param $options
     * @return unknown_type
     */
    function _paymentMethods( $field='offline_payment_method', $default='', $options='' )
    {
        $types = array();
        if ($this->params->get('enable_check')) {
            $types[] = JHTML::_('select.option', 'check', JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_CHECK') );    
        }
        if ($this->params->get('enable_moneyorder')) {
            $types[] = JHTML::_('select.option', 'moneyorder', JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_MONEYORDER') ); 
        }
        if ($this->params->get('enable_cash')) {
            $types[] = JHTML::_('select.option', 'cash', JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_CASH') );    
        }
        if ($this->params->get('enable_wire')) {
            $types[] = JHTML::_('select.option', 'wire', JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_WIRE') ); 
        }
        if ($this->params->get('enable_invoice')) {
            $types[] = JHTML::_('select.option', 'invoice', JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_INVOICE') ); 
        }
        if ($this->params->get('enable_other')) {
            $types[] = JHTML::_('select.option', 'other', JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_OTHER') );    
        }       
        $return = JHTML::_('select.genericlist', $types, $field, $options, 'value','text', $default);
        return $return;
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
    
    /**
     * Payment plugins should override this function
     * to customize the one-line summary that is displayed
     * during the new OPC
     *
     * @param unknown_type $data
     * @return NULL
     */
    protected function _getSummary( $data )
    {
        $html = null;
        
        switch (@$data['offline_payment_method']) 
        {
            case "check":
                $html = JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_CHECK');
                break;
            case "moneyorder":
                $html = JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_MONEYORDER');
                break;
            case "cash":
                $html = JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_CASH');
                break;
            case "wire":
                $html = JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_WIRE');
                break;
            case "invoice":
                $html = JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_INVOICE');
                break;
            case "other":
                $html = JText::_('PLG_TIENDA_PAYMENT_OFFLINE_OFFLINE_PAYMENT_OTHER');
                break;
        }
        
        return $html;
    }
}

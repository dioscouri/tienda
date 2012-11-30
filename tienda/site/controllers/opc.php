<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaControllerCheckout', 'controllers.checkout', array( 'site'=>'site', 'type'=>'components', 'ext'=>'com_tienda' ) );

class TiendaControllerOpc extends TiendaControllerCheckout
{
    var $onepage_checkout = true;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->set('suffix', 'opc');
    }
    
    public function setMethod()
    {
        $this->setFormat();
        $method = JRequest::getVar('checkout_method');
        $session = JFactory::getSession();
        $session->set('tienda.opc.method', $method);
        $response = $this->getResponseObject();
        
        $post = JRequest::get('post');
        
        switch(strtolower($method)) {
            case "guest":
                $response->summary->html = JText::sprintf("COM_TIENDA_GUEST_CHECKOUT", $post['email_address']);
                break;
            case "register":
            default:
                $response->summary->html = JText::sprintf("COM_TIENDA_GUEST_REGISTERING_AS_NEW_USER", $post['email_address']);
                break;
        }
        
        $response->goto_section = 'billing';
        
        echo json_encode($response);
    }
    
    public function setBilling()
    {
        $this->setFormat();
        $session = JFactory::getSession();
        $response = $this->getResponseObject();
    
        $post = JRequest::get('post');
        
        $address_id = null; // TODO if logged in and selecting from a stored address, use its id from the post
        $user_id = $this->user->id ? $this->user->id : '-1'; 
        $prefix = $this->billing_input_prefix;
        $address_type = 1;
        
        $addressArray = $this->getAddressArray( $address_id, $prefix, $post );
        $address = $this->getAddress( $addressArray, $address_type, $user_id );
        $session->set('tienda.opc.billingAddress', serialize($address) );
       
        $order = &$this->_order;
        $order = $this->populateOrder();
        $order->setAddress( $address );
        
        $response->goto_section = 'payment';
        if ($order->isShippingRequired()) {
            $response->goto_section = 'shipping';
        }
        
        $response->summary->html = $this->getSummaryAddress( $address );
        
        if (!empty($post['billing_input_use_for_shipping'])) {
            $response->duplicateBillingInfo = 1;
            $response->goto_section = 'shipping-method';
        }
        
        $response->summaries = array();
        $summary = $this->getSummaryResponseObject();
        $summary->id = 'opc-payment-body';
        $summary->html = $this->getPaymentOptionsHtml( 'payment' );
        $response->summaries[] = $summary;
        
        echo json_encode($response);
    }
    
    public function setShipping()
    {
        $this->setFormat();
        $session = JFactory::getSession();
        $response = $this->getResponseObject();
    
        $post = JRequest::get('post');
        
        $address_id = null; // TODO if logged in and selecting from a stored address, use its id from the post
        $user_id = $this->user->id ? $this->user->id : '-1';
        $prefix = $this->shipping_input_prefix;
        $address_type = 2;
        
        $addressArray = $this->getAddressArray( $address_id, $prefix, $post );
        $address = $this->getAddress( $addressArray, $address_type, $user_id );
        $session->set('tienda.opc.shippingAddress', serialize($address) );
        
        $order = &$this->_order;
        $order = $this->populateOrder();
        $order->setAddress( $address, 'shipping' );
        $billingAddress = unserialize( $session->get('tienda.opc.billingAddress') );
        $order->setAddress( $billingAddress );

        $rates = $this->getShippingRates();
        $session->set('tienda.opc.shippingRates', serialize($rates) );
        
        $response->goto_section = 'shipping-method';
        $response->summary->html = $this->getSummaryAddress( $address );
    
        $response->summaries = array();
        $summary = $this->getSummaryResponseObject();
        $summary->id = 'opc-shipping-method-body';
        $summary->html = $this->getShippingHtml( 'shippingmethod' );
        $response->summaries[] = $summary; 
        
        echo json_encode($response);
    }
    
    public function setShippingMethod()
    {
        $this->setFormat();
        $session = JFactory::getSession();
        $response = $this->getResponseObject();
    
        $post = JRequest::get('post');
        
        $errorMessage = '';
        if (empty($post['shipping_plugin']))
        {
            $errorMessage = '<ul class="text-error">';
            $errorMessage .= "<li>" . JText::_("COM_TIENDA_PLEASE_SELECT_SHIPPING_METHOD") . "</li>";
            $errorMessage .= '</ul>';
            $response->goto_section = 'shipping-method';
            $response->summary->html = $errorMessage;
            echo json_encode($response);
            return;
        }
        
        $value = $post['shipping_plugin'];
        $parts = explode('.', $value); 
        $plugin = $parts[0];
        $key = $parts[1];
        
        $shippingRates = unserialize( $session->get('tienda.opc.shippingRates') );
        
        $currency = Tienda::getInstance()->get( 'default_currencyid', 1);
        $rate = !empty($shippingRates[$key]) ? $shippingRates[$key] : null; 
        $summary = $rate ? $rate['name'] . " (" . TiendaHelperBase::currency( $rate['total'], $currency ) . ")" : null;
        
        // TODO if $summary or $rate is null, fail
        
        $response->goto_section = 'payment';
        $response->summary->html = $summary;

        $session->set('tienda.opc.shippingMethod', serialize($rate) );
        
        echo json_encode($response);
    }
    
    public function setPayment()
    {
        $this->setFormat();
        $session = JFactory::getSession();
        $response = $this->getResponseObject();
    
        $post = JRequest::get('post');

        $errorMessage = '';
        if (empty($post['payment_plugin']))
        {
            $errorMessage = '<ul class="text-error">';
            $errorMessage .= "<li>" . JText::_("COM_TIENDA_PLEASE_SELECT_PAYMENT_METHOD") . "</li>";
            $errorMessage .= '</ul>';
            $response->goto_section = 'payment';
            $response->summary->html = $errorMessage;
            echo json_encode($response);
            return;
        }
        
        DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
        $model = DSCModel::getInstance('Payment', 'TiendaModel');
        $model->setState('limit', '1');
        $model->setState('filter_element', $post['payment_plugin']);
        if ($items = $model->getList()) 
        {
            $item = $items[0];
        }
        
        $response->summary->html = $item->name;
    
        //$session->set('tienda.opc.paymentMethod', serialize($paymentMethod) );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $dummyaddress = JTable::getInstance('Addresses', 'TiendaTable');
        
        $billingAddress = unserialize( $session->get('tienda.opc.billingAddress') );
        $shippingAddress = unserialize( $session->get('tienda.opc.shippingAddress') );
        
        $order = &$this->_order;
        $order = $this->populateOrder();
        $order->setAddress( $billingAddress );
        if (!empty($shippingAddress)) 
        {
            $order->setAddress( $shippingAddress, 'shipping' );
        }        
        
        $response->summaries = array();
        $summary = $this->getSummaryResponseObject();
        $summary->id = 'opc-review-body';
        $summary->html = $this->getOrderSummary( 'review' );
        $response->summaries[] = $summary;

        $response->goto_section = 'review';
        
        echo json_encode($response);
    }
    
    public function submitOrder()
    {
        $this->setFormat();
        $session = JFactory::getSession();
        $response = $this->getResponseObject();
    
        $values = JRequest::get('post');
        
        // Prep the post
        $userHelper = new TiendaHelperUser();
        
        // set user values appropriately
        if (!empty($this->user->id)) {
            $values["user_id"] = $this->user->id; 
            $values["email_address"] = $this->user->email;
            $values["checkout_method"] = null;
        } else {
            $values["user_id"] = $userHelper->getNextGuestUserId();
        }
        
        $values['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $values["sameasbilling"] = $values["shipping_input_same_as_billing"];
        
        if ($shippingMethod = unserialize( $session->get('tienda.opc.shippingMethod') )) 
        {
            $values['shipping_plugin'] = $shippingMethod['element'];
            $values['shipping_price'] = $shippingMethod['price'];
            $values['shipping_extra'] = $shippingMethod['extra'];
            $values['shipping_name'] = $shippingMethod['name'];
            $values['shipping_tax'] = $shippingMethod['tax'];
            $values['shipping_code'] = $shippingMethod['code'];
        }

        $model = $this->getModel( 'orders' );
        $errorMessage = '';
        if (!$validate = $model->validate( $values )) 
        {
            $errorMessage = '<ul class="text-error">';
            foreach ($model->getErrors() as $error)
            {
                $errorMessage .= "<li>" . $error . "</li>";
            }
            $errorMessage .= '</ul>';
        }
        
        if ($errorMessage) 
        {
            $response->goto_section = 'review';
            $response->summary->html = $errorMessage;
            echo json_encode($response);
            return;            
        }
        
        $options = array();
        $options["save_addresses"] = true;
        
        if (!$order = $model->save( $values, $options )) 
        {
            $errorMessage = '<ul class="text-error">';
            foreach ($model->getErrors() as $error)
            {
                $errorMessage .= "<li>" . $error . "</li>";
            }
            $errorMessage .= '</ul>';
        }
        
        if ($errorMessage)
        {
            $response->goto_section = 'review';
            $response->summary->html = $errorMessage;
            echo json_encode($response);
            return;
        }

        $this->_order = $order;
        
        if (!$html = $this->getPreparePaymentForm($values, $options, $order)) 
        {
            // TODO get and return the error for display to the user
        }
        
        if ($html == 'free') 
        {
            // TODO confirm order
        }
        
        $response->summary->id = 'opc-payment-prepayment';
        $response->summary->html = $html;
        $response->goto_section = 'prepare-payment';
        echo json_encode($response);
        
        return;
    }
    
    private function getPreparePaymentForm( $values, $options=array(), &$order=null ) 
    {
        $order = $this->_order;
        
        $orderpayment_type = @$values['payment_plugin'];
        $transaction_status = JText::_('COM_TIENDA_INCOMPLETE');
        
        // in the case of orders with a value of 0.00, use custom values
        if( $order->isRecurring() )
        {
            if( (float)$order->getRecurringItem()->recurring_price == (float)'0.00' )
            {
                $orderpayment_type = 'free';
                $transaction_status = JText::_('COM_TIENDA_COMPLETE');
            }
        }
        else 
        {
            if ( (float) $order->order_total == (float)'0.00' )
            {
                $orderpayment_type = 'free';
                $transaction_status = JText::_('COM_TIENDA_COMPLETE');
            }
        }
        
        // Save an orderpayment with an Incomplete status
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->order_id = $order->order_id;
        $orderpayment->orderpayment_type = $orderpayment_type; // this is the payment plugin selected
        $orderpayment->transaction_status = $transaction_status; // payment plugin updates this field onPostPayment
        $orderpayment->orderpayment_amount = $order->order_total; // this is the expected payment amount.  payment plugin should verify actual payment amount against expected payment amount
        if (!$orderpayment->save())
        {
            $this->setError( $orderpayment->getError() );
            return false;
        }
        
        // send the order_id and orderpayment_id to the payment plugin so it knows which DB record to update upon successful payment
        $values["order_id"]             = $order->order_id;
        $values["orderinfo"]            = $order->orderinfo;
        $values["orderpayment_id"]      = $orderpayment->orderpayment_id;
        $values["orderpayment_amount"]  = $orderpayment->orderpayment_amount;
        
        // IMPORTANT: Store the order_id in the user's session for the postPayment "View Invoice" link
        $mainframe = JFactory::getApplication();
        $mainframe->setUserState( 'tienda.order_id', $order->order_id );
        $mainframe->setUserState( 'tienda.orderpayment_id', $orderpayment->orderpayment_id );
        
        $html = "";
        if ($orderpayment_type == 'free') 
        {
            $html = $orderpayment_type;
        }
        elseif (!empty($values['payment_plugin']))
        {
            $dispatcher = JDispatcher::getInstance();
            $results = $dispatcher->trigger( "onPrePayment", array( $values['payment_plugin'], $values ) );
            for ($i=0; $i<count($results); $i++)
            {
                $html .= $results[$i];
            }
        }
        
        return $html;
    }
    
    private function getSummaryAddress( $address )
    {
        $lines = array();
        
        // TODO Get the fields enabled in config,
        $lines[] = $address->first_name . " " . $address->last_name;
        $lines[] = $address->address_1;
        if ($address->address_2) {
            $lines[] = $address->address_2;
        }
        $lines[] = $address->city;
        if ($zone = $address->getZone()) {
            $lines[] = $zone->zone_name;
        }
        $lines[] = $address->postal_code;
        if ($country = $address->getCountry()) {
            $lines[] = $country->country_name;
        }
                 
        $return = implode(', ', $lines);
        return $return;
    }
    
    private function setFormat( $set='raw' )
    {
        $format = JRequest::getVar('format');
        if ($format != $set) {
            JRequest::setVar('format', $set);
        }
    }
    
    private function getResponseObject()
    {
        $response = new stdClass();
        $response->summary = $this->getSummaryResponseObject();
        $response->error = null; // whether or not there was an error
        $response->goto_section = null; // the next section in the OPC to display
        $response->redirect = null; // force the browser to redirect
        $response->duplicateBillingInfo = null; // set all shipping input fields equal to their billing counterparts
        $response->allow_sections = array(); // set certain sections as editable
        $response->summaries = array(); // an array of summary objects to inject into the DOM 
        
        return $response;
    }
    
    private function getSummaryResponseObject()
    {
        $summary = new stdClass();
        $summary->id = ''; // [optional] the id of the html element to be updated 
        $summary->html = ''; // the content to be inserted into the html element
        
        return $summary;
    }
}
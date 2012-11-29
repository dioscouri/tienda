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
        $method = JRequest::getVar('method');
        $session = JFactory::getSession();
        $session->set('tienda.opc.method', $method);
        $response = $this->getResponseObject();
        
        switch(strtolower($method)) {
            case "guest":
                $response->summary->html = JText::_("COM_TIENDA_GUEST_CHECKOUT");
                break;
            case "register":
            default:
                $response->summary->html = JText::_("COM_TIENDA_GUEST_REGISTERING_AS_NEW_USER");
                break;
        }
        
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
        
        $response->summary->html = $this->getSummaryAddress( $address );
        
        if (!empty($post['billing_input_use_for_shipping'])) {
            $response->duplicateBillingInfo = 1;
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
        
        $value = $post['shipping_plugin'];
        $parts = explode('.', $value); 
        $plugin = $parts[0];
        $key = $parts[1];
        
        $shippingRates = unserialize( $session->get('tienda.opc.shippingRates') );
        
        $currency = Tienda::getInstance()->get( 'default_currencyid', 1);
        $rate = !empty($shippingRates[$key]) ? $shippingRates[$key] : null; 
        $summary = $rate ? $rate['name'] . " (" . TiendaHelperBase::currency( $rate['total'], $currency ) . ")" : null;
        
        // TODO if $summary or $rate is null, fail
        
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

        echo json_encode($response);
    }
    
    public function setReview()
    {
        $this->setFormat();
        $session = JFactory::getSession();
        $response = $this->getResponseObject();
    
        $post = JRequest::get('post');
        
        // TODO Prep the $post var
        
        //$model = $this->getModel('orders');
        //$result = $model->save( $post );
        
        return;
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
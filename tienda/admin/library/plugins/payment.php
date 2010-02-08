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

JLoader::import( 'com_tienda.library.plugins._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaPaymentPlugin extends TiendaPluginBase 
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename, 
     *                         forcing it to be unique 
     */
    var $_element    = '';

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
        
        $vars = new JObject();
        $vars->full_name        = "";
        $vars->email            = "";
        $vars->payment_method   = $this->_paymentMethods();
        
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
     * You shouldn't need to override
     * any of the methods below here
     * 
     ************************************/
    
    /**
     * Given an order_id, will remove the order's items from the user's cart
     * 
     * @param unknown_type $order_id
     * @return unknown_type
     */
    function removeOrderItemsFromCart( $order_id )
    {
	    JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );
	    TiendaHelperCarts::removeOrderItems( $order_id );
    }
    
    /**
     * Tells extension that this is a payment plugin
     * 
     * @param $element  string      a valid payment plugin element 
     * @return boolean
     */
    function onGetPaymentPlugins( $element )
    {
        $success = false;
        if ($this->_isMe($element)) 
        {
            $success = true;
        }
        return $success;    
    }
    
    /**
     * Wrapper for the internal _renderForm method
     * 
     * @param $element  string      a valid payment plugin element 
     * @param $data     array       form post data
     * @return html
     */
    function onGetPaymentForm( $element, $data )
    {
        if (!$this->_isMe($element)) 
        {
            return null;
        }

        $html = $this->_renderForm( $data );

        return $html;
    }
    
    /**
     * Wrapper for the internal _renderView method
     * 
     * @param $element  string      a valid payment plugin element
     * @param $orderPayment  object      a valid TableOrderPayment object
     * @return html
     */
    function onGetPaymentView( $element, $orderPayment )
    {
        if (!$this->_isMe($element)) 
        {
            return null;
        }

        $html = $this->_renderView( $orderPayment );

        return $html;
    }
    
    /**
     * Wrapper for the internal _prePayment method
     * which performs any necessary actions before payment
     *   
     * @param $element  string      a valid payment plugin element 
     * @param $data     array       form post data
     * @return html
     */
    function onPrePayment( $element, $data )
    {
        if (!$this->_isMe($element)) 
        {
            return null;
        }

        $html = $this->_prePayment( $data );

        return $html;
    }

    /**
     * Wrapper for the internal _postPayment method
     * that processes the payment after user submits
     *   
     * @param $element  string      a valid payment plugin element 
     * @param $data     array       form post data
     * @return html
     */
    function onPostPayment( $element, $data )
    {
        if (!$this->_isMe($element)) 
        {
            return null;
        }

        $html = $this->_postPayment( $data );

        return $html;
    }
}

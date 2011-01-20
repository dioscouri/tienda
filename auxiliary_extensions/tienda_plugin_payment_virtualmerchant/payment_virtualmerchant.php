<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2011 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_virtualmerchant extends TiendaPaymentPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element    = 'payment_virtualmerchant';

    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object $subject The object to observe
     * @param   array  $config  An array that holds the plugin configuration
     * @since 1.5
     */
    function plgTiendaPayment_virtualmerchant(& $subject, $config) {
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
        
        $vars->ssl_merchant_id = $this->params->get('ssl_merchant_id', '');
        $vars->ssl_user_id = $this->params->get('ssl_user_id', '');
        $vars->ssl_pin = $this->params->get('ssl_pin', '');
        $vars->test_mode = $this->params->get('test_mode', '0');
        
        $vars->ssl_customer_code = JFactory::getUser()->id;
        $vars->ssl_invoice_number = $data['order_id'];
        $vars->ssl_description = JText::_('Order Number: ').$data['order_id'];
        
        // Billing Info
        $vars->first_name   = $data['orderinfo']->billing_first_name;
        $vars->last_name    = $data['orderinfo']->billing_last_name;
        $vars->email        = $data['orderinfo']->user_email;
        $vars->address_1    = $data['orderinfo']->billing_address_1;
        $vars->address_2    = $data['orderinfo']->billing_address_2;
        $vars->city         = $data['orderinfo']->billing_city;
        $vars->country      = $data['orderinfo']->billing_country_name;
        $vars->state        = $data['orderinfo']->billing_zone_name;
        $vars->zip  		= $data['orderinfo']->billing_postal_code;
        
        $vars->amount = @$data['order_total'];
        $vars->amount = @$data['order_tax'];

        $vars->payment_url = "https://www.myvirtualmerchant.com/VirtualMerchant/process.do";
        $vars->receipt_url = JURI::base()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element;

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
        $paction = JRequest::getVar('paction');

        $vars = new JObject();

        switch ($paction)
        {
            case "display_message":
                $vars->message = JText::_('PAYPAL MESSAGE PAYMENT ACCEPTED FOR VALIDATION');
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();
              break;
            case "process":
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
                echo $html; // TODO Remove this
                $app =& JFactory::getApplication();
                $app->close();
              break;
            case "cancel":
                $vars->message = JText::_( 'Paypal Message Cancel' );
                $html = $this->_getLayout('message', $vars);
              break;
            default:
                $vars->message = JText::_( 'Paypal Message Invalid Action' );
                $html = $this->_getLayout('message', $vars);
              break;
        }

        return $html;
    }

    /**
     * Prepares variables for the payment form
     *
     * @return unknown_type
     */
    function _renderForm( $data )
    {
        $user = JFactory::getUser();
        $vars = new JObject();

        $html = $this->_getLayout('form', $vars);

        return $html;
    }

    /************************************
     * Note to 3pd:
     *
     * The methods between here
     * and the next comment block are
     * specific to this payment plugin
     *
     ************************************/

}


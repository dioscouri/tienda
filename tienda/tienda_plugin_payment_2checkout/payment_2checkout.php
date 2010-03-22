<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2010 Dioscouri. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.library.plugins.payment', JPATH_ADMINISTRATOR.DS.'components' );

class plgTiendaPayment_2checkout extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_2checkout';

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
	function plgTiendaPayment_2checkout(& $subject, $config) 
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
        JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );
        $helper = TiendaHelperBase::getInstance();
        
        $vars = new JObject();
        $vars->cart_order_id = $data['order_id'].$helper->getToday();
        $vars->merchant_order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->total = $data['orderpayment_amount'];
        
        $vars->x_Receipt_Link_URL = "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element;
        
        // Destination
        if($this->params->get('page', 'single') == 'single'){
        	$vars->url = 'https://www.2checkout.com/checkout/spurchase';
        } else{
        	$vars->url = 'https://www.2checkout.com/checkout/purchase';
        	// Payment type
        	$vars->pay_method = $this->params->get('pay_method', 'CC');
        }
        
        // 2Checkout account number
        $vars->sid = $this->params->get('sid', '0');
        
        // Demo Mode?
   		if($this->params->get('demo', '0') == 1){
        	$vars->demo = true;
        } else{
        	$vars->demo = false;
        }
        
        // Language
   		if($this->params->get('automatic_language', '0') == 1){
        	// automatic language from joomla
   			jimport('joomla.language.helper');
   			$lang = JLanguageHelper::detectLanguage();
   			
   			switch($lang){
   				
   				// Do more than these two
   				case "it-IT":
   					$vars->lang = "it";
   					break;
   				case "en-GB":
   				default: 	  
   					$vars->lang = 'en';
   					break;
   				
   			}
   			
        } else{
        	$vars->lang = $this->params->get('default_language', 'en');
        }
        
        // Skip Landing
        if($this->params->get('skip_landing', '0') == 1)
        	$vars->skip_landing = true;
        else
        	$vars->skip_landing = false;
        	
        // Billing Address
        $vars->first_name   = $data['orderinfo']->billing_first_name;
        $vars->last_name    = $data['orderinfo']->billing_last_name;
        $vars->email        = $data['orderinfo']->user_email;
        $vars->street_address    = $data['orderinfo']->billing_address_1;
        $vars->street_address2    = $data['orderinfo']->billing_address_2;
        $vars->city         = $data['orderinfo']->billing_city;
        $vars->country      = $data['orderinfo']->billing_country_name;
        $vars->state       = $data['orderinfo']->billing_zone_name;
        $vars->zip  = $data['orderinfo']->billing_postal_code;
        
        // Shipping Address
        $vars->ship_name   = $data['orderinfo']->shipping_first_name. " " . $data['orderinfo']->shipping_last_name;
        $vars->ship_street_address    = $data['orderinfo']->shipping_address_1;
        $vars->ship_street_address2    = $data['orderinfo']->shipping_address_2;
        $vars->ship_city         = $data['orderinfo']->shipping_city;
        $vars->ship_country      = $data['orderinfo']->shipping_country_name;
        $vars->ship_state       = $data['orderinfo']->shipping_zone_name;
        $vars->ship_zip  = $data['orderinfo']->shipping_postal_code;
        
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
        $credit_card_processed = JRequest::getVar('credit_card_processed');
        
        $vars = new JObject();
        
        if ($credit_card_processed == "Y") 
        {
                $vars->message = JText::_('Payment Successful');
                $html = $this->_getLayout('message', $vars);
        } else{
        	$vars->message = JText::_('Payment Denied');
            $html = $this->_getLayout('message', $vars);
        }
        
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
    	$user = JFactory::getUser();  	
        $vars = new JObject();
        
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
	
}

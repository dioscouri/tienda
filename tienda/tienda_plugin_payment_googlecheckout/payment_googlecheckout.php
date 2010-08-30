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

class plgTiendaPayment_googlecheckout extends TiendaPaymentPlugin
{
	/**
	 * @var string
	 * @access protected
	 */
	var $_payment_type = 'payment_googlecheckout';
	var $_element    = 'payment_googlecheckout';

	/**
	 * @var boolean
	 * @access protected
	 */
	var $_isLog = false;

	/**
	 * @var object
	 * @access protected
	 */
	var $_logObj;

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

	function plgTiendaPayment_googlecheckout(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
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
		/*
		* get all necessary data and prepare vars for assigning to the template
		*/
		$vars = new JObject();
        
		$vars->merchant_id = $this->_getParam('merchant_id');
		$vars->type_id = JRequest::getInt('id');
		$vars->action_url = JRoute::_("index.php?option=com_tienda&controller=payment&task=process&ptype={$this->_payment_type}&paction=proceed&tmpl=component");
		$vars->button_url = $this->_getActionUrl(false);
		$vars->note = JText::_( 'GoogleCheckout Note Default' );

		$uri =& JFactory::getURI();
		$url = $uri->toString(array('path', 'query', 'fragment'));
		$vars->r = base64_encode($url);
		
		$html = $this->_getLayout('prepayment', $vars);
        $text = array();
		$text[] = $html;
		$text[] = $this->params->get( 'title', 'Google Checkout' );
		return $text;

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
        
		$vars->merchant_id = $this->_getParam('merchant_id');
		$vars->type_id = JRequest::getInt('id');
		$vars->action_url = JRoute::_("index.php?option=com_tienda&controller=payment&task=process&ptype={$this->_payment_type}&paction=proceed&tmpl=component");
		$vars->button_url = $this->_getActionUrl(false);
		$vars->note = JText::_( 'GoogleCheckout Note Default' );

		$uri =& JFactory::getURI();
		$url = $uri->toString(array('path', 'query', 'fragment'));
		$vars->r = base64_encode($url);
     	$html = $this->_getLayout('form', $vars);

		return $html;
	}

	/**
     * Gets the value for the Paypal variable
     * 
     * @param string $name
     * @return string
     * @access protected
     */
    function _getParam( $name, $default='' )
    {
    	$return = $this->params->get($name, $default);
    	
    	$sandbox_param = "sandbox_$name";
    	$sb_value = $this->params->get($sandbox_param);
        if ($this->params->get('sandbox') && !empty($sb_value)) 
        {
            $return = $this->params->get($sandbox_param, $default);
        }
        
        return $return;
    }  
    
    /**
	 * Gets the gateway URL
	 * 
	 * @param boolean $full
	 * @return string
	 * @access protected
	 */
	function _getActionUrl($full = true)
	{
		if ($full) {
			$url  = $this->params->get('sandbox') ? 'https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/' : 'https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/';
			$url .= $this->_getParam('merchant_id');
		}
		else {
			$url = $this->params->get('sandbox') ? 'https://checkout.google.com' : 'https://sandbox.google.com/checkout';			
		}
		
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

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

JLoader::import( 'com_tienda.library.plugins.payment', JPATH_ADMINISTRATOR.DS.'components' );

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
        $vars->offline_payment_method = JRequest::getVar('offline_payment_method');
        
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
        $orderpayment_id = JRequest::getVar('orderpayment_id');
        $offline_payment_method = JRequest::getVar('offline_payment_method');
        $formatted = array( 
                        'offline_payment_method' => $offline_payment_method 
                        ); 
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $orderpayment_id );
        $orderpayment->transaction_details = implode("\n", $formatted); 
        if ($orderpayment->save())
        {
            // Update quantities
            JLoader::import( 'com_tienda.helpers.order', JPATH_ADMINISTRATOR.DS.'components' );
            TiendaHelperOrder::updateProductQuantities( $orderpayment->order_id, '-' );
            
            // remove items from cart
            JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );
            TiendaHelperCarts::removeOrderItems( $orderpayment->order_id );
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
    	$user = JFactory::getUser();  	
        $vars = new JObject();
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
                        $object->message .= "<li>".JText::_( "Offline Payment Type Invalid" )."</li>";
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
            $types[] = JHTML::_('select.option', 'check', JText::_( "Check" ) );    
        }
        if ($this->params->get('enable_moneyorder')) {
            $types[] = JHTML::_('select.option', 'moneyorder', JText::_( "Money Order" ) ); 
        }
        if ($this->params->get('enable_wire')) {
            $types[] = JHTML::_('select.option', 'wire', JText::_( "Wire Transfer" ) ); 
        }
        if ($this->params->get('enable_other')) {
            $types[] = JHTML::_('select.option', 'other', JText::_( "Other" ) );    
        }       
        $return = JHTML::_('select.genericlist', $types, $field, $options, 'value','text', $default);
        return $return;
    }
}

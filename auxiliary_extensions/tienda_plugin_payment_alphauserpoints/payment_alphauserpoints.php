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

class plgTiendaPayment_alphauserpoints extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_alphauserpoints';
    
	function plgTiendaPayment_alphauserpoints(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
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
    	// Prepare vars for the payment
    	
        $vars = new JObject();
        $vars->url = JRoute::_( "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element );
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_type = $this->_element;
        $vars->orderpayment_amount = $data['orderpayment_amount'];        
        $vars->points_rate = $this->params->get('exchange_rate');
        $vars->amount_points = round( $vars->orderpayment_amount * $vars->points_rate );
               
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
    	$vars = new JObject();
    	
    	if( $this->_process( $data ) )
    	{
    		$vars->message = JText::_('TIENDA ALPHAUSERPOINTS PAYMENT SUCCESSUFUL');
            $html = $this->_getLayout('message', $vars);
            $html .= $this->_displayArticle(); 
    	}
    		else 
    	{
    		$vars->message = JText::_( 'TIENDA ALPHAUSERPOINTS PAYMENT ERROR MESSAGE' );
			$html = $this->_getLayout('message', $vars);				
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
         
        $vars = new JObject();
        
        $vars->message = JText::_( 'TIENDA ALPHAUSERPOINTS PAYMENT MESSAGE' );
		$html = $this->_getLayout('message', $vars);
        
        return $html;
    }
    
    
    /**
     * Processing the payment
     * 
     * @param $data     array form post data
     * @return string   HTML to display
     */
    function _process( $data )
	{
		
	}
    
	/**
     * Determines if this payment option is valid for this order
     * 
     * @param $element
     * @param $order
     * @return unknown_type
     */
    function onGetPaymentOptions($element, $order)
    {       
        // Check if this is the right plugin
        if (!$this->_isMe($element)) 
        {
            return null;
        }
        
        // if this payment method should be available for this order, return true
        // if not, return false. AlphaUserPointsHelper
        // by default, all enabled payment methods are valid, so return true here,
        // but plugins may override this
    	
        $amount_points = round( $order->order_total * $this->params->get('exchange_rate') );
       	        
        JLoader::import( 'com_ambra.helpers.user', JPATH_ADMINISTRATOR.DS.'components' );
		$current_points = AmbraHelperUser::getPoints( $order->user_id );

	    $api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
		if ( file_exists($api_AUP))
		{			
			$referrerid = '';
			require_once ($api_AUP);
			$user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid, $order->user_id );			
		}
		
        if( $amount_points > $user_info->points )
        {
        	return false;
        }
        else 
        {
        	return true;
        }              
    }   
}
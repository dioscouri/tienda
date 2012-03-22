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
    	
    	$errors = $this->_process( $data );
    	
    	if( empty( $errors ) )
    	{
    		$vars->message = JText::_('TIENDA ALPHAUSERPOINTS PAYMENT SUCCESSUFUL');
            $html = $this->_getLayout('message', $vars);
            $html .= $this->_displayArticle(); 
    	}
    		else 
    	{
    		$vars->message = JText::_('TIENDA ALPHAUSERPOINTS PAYMENT ERROR MESSAGE') . $errors;
    		$vars->errors = $errors;
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
        
        $vars->message = JText::_('TIENDA ALPHAUSERPOINTS PAYMENT MESSAGE');
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
		$errors = array();
        
        // load the orderpayment record and set some values
	    JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
	    $orderpayment_id = $data['orderpayment_id'];
	    $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
	    $orderpayment->load( $orderpayment_id );
	    $orderpayment->transaction_details  = $data['orderpayment_type'];
	    $orderpayment->transaction_id       = $data['orderpayment_id'];
	    $orderpayment->transaction_status   = "Payment Incomplete";
	    
		// check the stored amount against the payment amount
	    Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
        $respond_amount = TiendaHelperBase::number( $data['orderpayment_amount'], array( 'thousands'=>'' ) );	   
	    if ( $stored_amount != $respond_amount )
	    {	    
	    	$errors[] = JText::_('TIENDA ALPHAUSERPOINTS PAYMENT MESSAGE AMOUNT INVALID');
	    	$errors[] = $stored_amount . " != " . $respond_amount;
	    }
	    	    
	    // check if user has enough points
	    $userpoints = $this->getUserpoints();
	    if( $data['amount_points'] > $userpoints )
	    {
	    	$errors[] = JText::_('TIENDA ALPHAUSERPOINTS PAYMENT MESSAGE NOT ENOUGH POINTS');
	    }
	    
	    // set the order's new status and update quantities if necessary
	    Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
	    Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
	    $order = JTable::getInstance('Orders', 'TiendaTable');
	    $order->load( $orderpayment->order_id );
	    
		if (count($errors)) 
	    {
	    	// if an error occurred 
	        $order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED
	        
	        $setOrderPaymentReceived = false;
	        
	        $send_email = false;
	   	}
	        else 
	    {
	        $order->order_state_id = $this->params->get('payment_received_order_state', '17'); // PAYMENT RECEIVED
	            
	        $orderpayment->transaction_status = "Payment Received";
	        
	        //reduce number of alphauserpoints
	        $errors[] = $this->reduceUserpoints( $data['amount_points'] );
	        
	        // do post payment actions
	        $setOrderPaymentReceived = true;
	            
	        // send email
	        $send_email = true;    
	    }
	    
		// save the order
	    if (!$order->save())
	    {
	       	$errors[] = $order->getError();
	    }
	        
	    // save the orderpayment
	    if (!$orderpayment->save())
	    {
	      	$errors[] = $orderpayment->getError(); 
	    }
	        
	    if (!empty($setOrderPaymentReceived))
	    {
	        $this->setOrderPaymentReceived( $orderpayment->order_id );
	    }
	        
	    if ($send_email)
	    {
	    	// send notice of new order
	    	Tienda::load( "TiendaHelperBase", 'helpers._base' );
	    	$helper = TiendaHelperBase::getInstance('Email');
	    	$model = Tienda::getClass("TiendaModelOrders", "models.orders");
	    	$model->setId( $orderpayment->order_id );
	   		$order = $model->getItem();
	    	$helper->sendEmailNotices($order, 'new_order');
	    }
	    
	    return count($errors) ? implode("\n", $errors) : '';
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
       	    
		$userpoints = $this->getUserpoints();
		
		JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );
		$guest = JRequest::getVar( 'guest', '0');
		
        if( $amount_points > $userpoints && $guest == '1' )
        {
        	return false;
        }
        else 
        {
        	return true;
        }              
    }  

    /**
     * Gets alpha user points
     * 
     * @param void
     * @return $userpoints int
     */
    function getUserpoints()
    {
    	// get alphauserpoints for the user
		$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';	    
		if ( file_exists($api_AUP))
		{
			require_once ($api_AUP);
					
			$referrerid = $this->getReferreid( JFactory::getUser()->id );
			
			$userpoints = AlphaUserPointsHelper::getCurrentTotalPoints( $referrerid );			
		}
		
		return $userpoints;
    }
    
    /**
     * Updates alpha user points
     * 
     * @param $assignpoints int
     * @return void
     */
    function reduceUserpoints( $amount_points )
    {
    	// reduce number of points for the payment
    	$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';	    
		if ( file_exists($api_AUP))
		{
			require_once ($api_AUP);
			
			// create the rule for Tienda payment if not exists
			$this->checkTiendaPaymentRule();
							
			$referrerid = $this->getReferreid( JFactory::getUser()->id );
			
			$jnow = & JFactory::getDate();
			$now  = $jnow->toMySQL();	
			
			//AlphaUserPointsHelper::newpoints( 'function_name', '', '', '', -$amount_points);
			
			AlphaUserPointsHelper::insertUserPoints( $referrerid, -$amount_points, $rule_expire='0000-00-00 00:00:00', AlphaUserPointsHelper::getRuleID( $this->_element ), 0, '', 'Tienda order paid by points' );
		}     
    }
    
    /** 
     * Gets $referrerid for the user and starts the session
     *  
     * @param int $userID
     */    
	function getReferreid( $userID )
	{	
		if ( !$userID ) return;	
		// get referre ID on login	
		$db	   =& JFactory::getDBO();
		$query = "SELECT referreid FROM #__alpha_userpoints WHERE `userid`='$userID'";
		$db->setQuery( $query );
		$referreid = $db->loadResult();
		if ( $referreid )
		{		
			@session_start('alphauserpoints');	
			$_SESSION['referrerid'] = $referreid;
		}	
		
		return $referreid;
	}
	
	/**
	 * Checks AlphaUserPoints for Tienda payment
	 * 
	 * @param void
	 * @return void
	 */
	function checkTiendaPaymentRule()
	{
		$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';	    
		if ( file_exists($api_AUP))
		{
			require_once ($api_AUP);
		
			$referrerid = $this->getReferreid( JFactory::getUser()->id );
			
			$rule_name = AlphaUserPointsHelper::getNameRule( $this->_element );
			
			if( empty( $rule_name ) )
			{
				JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_alphauserpoints'.DS.'tables');
				// save new points into alpha_userpoints_rules table
				$row =& JTable::getInstance('Rules');
				$row->id			   = NULL;
				$row->rule_name		   = 'Tienda Payment';
				$row->rule_description = 'Rule for substraction points during Tienda payment';
				$row->rule_plugin	   = 'Tienda Payment';
				$row->plugin_function  = $this->_element;			
				$row->component		   = 'com_tienda';
				$row->calltask		   = '';
				$row->taskid		   = '';
				$row->category		   = '';
				if ( !$row->store() )
				{
					JError::raiseError(500, $row->getError());
				}	
			}		
		}
	}
	
	
	
	
	
}
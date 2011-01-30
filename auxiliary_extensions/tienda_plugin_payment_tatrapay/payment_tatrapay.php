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

class plgTiendaPayment_tatrapay extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_tatrapay';
    
    /**
     * 
     * @param $subject
     * @param $config
     * @return unknown_type
     */
	function plgTiendaPayment_tatrapay(& $subject, $config) 
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
        
        $app =& JFactory::getApplication();
        $paction = JRequest::getVar( 'paction' );
        
        switch ($paction)
        {
            case 'redirect': // redirect to IB
                $vars->message = '';
                $this->_loadData( $data, 1 );
                $html = '';
                //$this->_generateSignature( $data, 1 );
                $app->redirect( $this->_getIbUri().'?'.$this->_generateRestUri( $data ) );
              break;
        	case 'process': // process respond from the server
                $this->_loadData( $data, 2 );
                $vars->message = $this->_process( $data );
                $html = $this->_getLayout('message', $vars);
              break;
            default:
                $vars->message = JText::_( 'Invalid Action' );
                $html = $this->_getLayout('message', $vars);
              break;
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

    /**
     * Gets a value of the plugin parameter
     * 
     * @param string $name
     * @param string $default
     * @return string
     * @access protected
     */
    function _getParam($name, $default = '') 
    {
        $sandbox_param = "sandbox_".$name;
        $sb_value = $this->params->get( $sandbox_param );
        
        if ( $this->params->get( 'sandbox' ) && !empty( $sb_value ) ) {
            $param = $this->params->get( $sandbox_param, $default );
        }
        else {
            $param = $this->params->get( $name, $default );
        }
        
        return $param;
    }
    
    /*
     * Processes data from bank
     */
    function _process( $data )
    {
    	Tienda::load( 'TiendaModelOrders', 'models.orders' );
    	$model = new TiendaModelOrders();

    	if( !$data['vs'] ) // order is not valid
    		return JText::_('TIENDA TATRAPAY MESSAGE INVALID ORDER').$this->_generateSignature( $data, 2);
    		
      $errors = array();
      
      $send_email = false;
    	if( $this->_generateSignature( $data, 2) == $data['sign'] )
    	{
    		switch( $data['res'] )
    		{
    			case 'OK' : // OK
						break;

    			case 'TOUT' : // Time out
						$errors[] = JText::_( "TIENDA TATRAPAY MESSAGE PAYMENT TIMEOUT" );
						break;

    			default :	 // something went wrong
    			case 'FAIL' : // transaction failed
						$errors[] = JText::_( "TIENDA TATRAPAY MESSAGE PAYMENT FAIL" );
						break;
    		}
    		$send_email = true; // send email!
    	}
    	else // invalid signature!
    	{
		  	$errors[] = JText::_('Tienda Tatrapay Message Invalid Signature');
    	}
    	
      // check that payment amount is correct for order_id
      JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
      $orderpayment = JTable::getInstance( 'OrderPayments', 'TiendaTable' );
      $orderpayment->load( array( 'order_id'=>$data['vs'] ) );

      $orderpayment->transaction_details  = Tienda::dump( $data );
      $orderpayment->transaction_id       = $data['vs'];
      $orderpayment->transaction_status   = $data['res'];
           
      // set the order's new status and update quantities if necessary
      Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
      $order = JTable::getInstance('Orders', 'TiendaTable');
      $order->load( $data['vs'] );
      if ( count( $errors ) ) 
      {
       	// if an error occurred 
       	$order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED
      }
      else 
      {
     		$order->order_state_id = $this->params->get('payment_received_order_state', '17');; // PAYMENT RECEIVED

     		// do post payment actions
     		$setOrderPaymentReceived = true;
     		// send email
     		$send_email = true;
			}
		    
			// save the order
			if( !$order->save() )
			{
				$errors[] = $order->getError();
			}
		            
			// save the orderpayment
			if( !$orderpayment->save() )
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
				$helper = TiendaHelperBase::getInstance( 'Email' );
				$model = Tienda::getClass("TiendaModelOrders", "models.orders");
				$model->setId( $orderpayment->order_id );
				$order = $model->getItem();
				$helper->sendEmailNotices($order, 'new_order');
			}
		
			if (empty($errors))
			{
				$return = JText::_( "TIENDA TATRAPAY MESSAGE PAYMENT SUCCESS" );
				return $return;                
			}
    	
    	return count($errors) ? implode("\n", $errors) : '';
    }
    
    /*
     * Gets additional data
     * 
     * $data Array with data
     * $type 1 - outcoming message; 2 - incoming message
     */
    function _loadData( &$data, $type)
    {
    	$session = JFactory::getSession();
      $order = JTable::getInstance( 'Orders', 'TiendaTable' );
    	$data['secure_key'] = $this->_getParam( 'secure_key' );
      switch( $type )
    	{
    		case 1:
    			$order->load( $data['order_id'] );
    			$data['order_total'] = $order->order_total;
    			$data['mid'] = 	$this->_getParam( 'mid' );
    			$data['curr'] = $this->_getParam('currency');
    			$data['rsms'] = $this->_getParam( 'rsms' );
    			$data['rem'] = 	$this->_getParam( 'rem' );
    			$data['cs'] = 	$this->_getParam( 'constant_symbol' );
    			$data['ss'] = 	$this->_getParam( 'special_symbol' );
    			$data['rurl'] = $this->_getReturnUri( $data );
    			
    			if( $data['ss'] == 'x' )
    				$data['ss'] = $data['order_id'];

    			$session->set( 'order_id', $data['order_id'] );
    		break;
    		
    		case 2:
    			$data['vs'] = JRequest::getCmd( 'VS' );
    			$data['ss'] = JRequest::getCmd( 'SS' );
    			$data['res'] = JRequest::getCmd( 'RES', 'FAIL' );
    			$data['sign'] = JRequest::getCmd( 'SIGN' );
    			$order->load( $data['vs'] );
    			if( !$order || ( $data['vs'] < 1 ) ||
    					( $data['vs'] != $session->get( 'order_id' ) ) ) // order is not valid
    				$data['vs'] = 0;
    				
    			$session->set( 'order_id', 0 );
    		break;
    	}
    }

    /*
     * Generate secure signature (for both incoming and outcoming message)
     * $data Request array
     * $type 1 - outcoming message; 2 - incomming message
     */
    function _generateSignature( $data , $type  )
    {
    	$res = '';

    	switch( $type )
    	{
    		case 1 : // outcoming message
  
    			$str = $data['mid'].sprintf('%01.2F', $data['order_total']).$data['curr'].
    			$data['order_id'].$data['ss'].$data['cs'].$data['rurl'];
    			break;
    		
    		case 2 : // incoming message
    			$str = $data['vs'].$data['ss'].$data['res'];
    			break;
    	}

     	$sign_hash = sha1( $str, true );
    	$des = mcrypt_module_open( MCRYPT_DES, '', MCRYPT_MODE_ECB, '' );
    	$iv = mcrypt_create_iv( mcrypt_enc_get_iv_size( $des ), MCRYPT_RAND );
    	mcrypt_generic_init( $des, $data['secure_key'], $iv );
    	$bytesSign = mcrypt_generic( $des, substr( $sign_hash, 0, 8 ) );
    	mcrypt_generic_deinit( $des );
    	mcrypt_module_close( $des );
    	return strtoupper( bin2hex( $bytesSign ) );
    }
    
    function _generateRestUri( $data )
    {
    	$url = array();
    	$url[] = 'PT=TatraPay';
    	$url[] = 'MID='.$data['mid'];
    	$url[] = 'CURR='.$data['curr'];
    	$url[] = 'AMT='.sprintf( '%01.2F', $data['order_total'] );
    	$url[] = 'VS='.$data['order_id'];
   		$url[] = 'CS='.$data['cs'];
    	
    	if( !empty( $data['ss'] ) )
    		$url[] = 'SS='.$data['ss'];

    				
    	$url[] = 'RURL='.urlencode( JRoute::_( $data['rurl'] ) );
    	$url[] = 'SIGN='.$this->_generateSignature( $data, 1 );
    	$url[] = 'RSMS='.$data['rsms'];
    	$url[] = 'REM='.$data['rem'];
    	$url[] = 'DESC='.JText::sprintf( 'DESC_OUTCOMING_MESSAGE', $data['order_id'] );
    	$url[] = 'AREDIR=1';
    	$url[] = 'LANG='.$this->_getParam( 'lang' );

    	return implode( '&', $url );
    }

    /*
     * Gets a proper URI of Tatrabanka's IB
     */
    function _getIbUri()
    {
    	if( $this->params->get( 'sandbox' ) ) // if sandbox mode
    		return 'http://epaymentsimulator.monogram.sk/TB_TatraPay.aspx';
    	else // real-life mode
    		return 'https://moja.tatrabanka.sk/cgi-bin/e-commerce/start/e-commerce.jsp';
    }

    /*
     * Gets a proper URI of Tatrabanka's IB
     */
    function _getReturnUri( $data )
    {
    	return JUri::base().'plugins/tienda/payment_tatrapay/tmpl/return.php';
    }
}

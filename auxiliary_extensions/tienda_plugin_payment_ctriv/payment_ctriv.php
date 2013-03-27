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

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_ctriv extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_ctriv';

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
	function plgTiendaPayment_ctriv(& $subject, $config) 
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
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper = TiendaHelperBase::getInstance();
        
        $params = $this->get('params', array());
        
        $vars = new JObject();
        $vars->action = $params->get('action', '1');
        $vars->track_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->total = number_format($data['orderpayment_amount'], 2, ".", "");
        $vars->email = JFactory::getUser()->email;
        
        $vars->response_url = JURI::base().'plugins/tienda/'.$this->_element.'/tmpl/response.php';
        $vars->error_url = JURI::base().'plugins/tienda/'.$this->_element.'/tmpl/error.php';
        
        
        // Is Demo or production?
        if($this->params->get('demo', '0') == 0){
        	$vars->url = 'https://www.constriv.com/cg/servlet/PaymentInitHTTPServlet';
        	$vars->account_id = $params->get('account_id', '');
        	$vars->password = $params->get('password', '');
        } else{
        	$vars->url = 'https://test4.constriv.com/cg301/servlet/PaymentInitHTTPServlet';
        	$vars->account_id = $params->get('account_id_demo', '');
        	$vars->password = $params->get('password_demo', '');
        }
        
        $vars->currency = $params->get('currency', '978');
        
        
        // Language
   		if ($this->params->get('language', 'auto') == 'auto')
   		{
        	// automatic language from joomla
   			jimport('joomla.language.helper');
   			$lang = JLanguageHelper::detectLanguage();
            // TODO Use JFactory::getLanguage(); 
            // and explode the language's code by the '-' to get the var->lang for 2CO
   			switch($lang)
   			{
   				case "es-ES":
   					$vars->language = "ESP";
   					break;
   				case "en-US":
   					$vars->language = "USA";
   					break;
   				case "de-DE":
   					$vars->language = "DEU";
   					break;
   				case "fr-FR":
   					$vars->language = "FRA";
   					break;
   				case "it-IT":
   					$vars->language = "ITA";
   					break;
   				case "en-GB":
   				default: 	  
   					$vars->language = 'ENG';
   					break;
   			}
   			
        } else{
        	$vars->language = $this->params->get('language', 'ENG');
        }
        
        $data = new JObject();
        
        // Check the parameters!!
		 //Apro la connessione
		 $ch=curl_init($vars->url);

		 $DataToSend ="id=$vars->account_id&password=$vars->password&action=$vars->action&amt=".$vars->total."&amp;currencycode=$vars->currency&langid=".$vars->language."&responseURL=$vars->response_url&errorURL=$vars->error_url&trackid=$vars->track_id&udf3=EMAILADDR:".$vars->email."&udf1=".$vars->orderpayment_id;
		 
		 //Imposto gli headers HTTP
		 //imposto curl per protocollo https
		 curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
		 curl_setopt($ch,CURLOPT_POST,1);
		 
		  //Invio i dati
		 curl_setopt($ch,CURLOPT_POSTFIELDS,$DataToSend);
		 
		 //imposta la variabile PHP  
		 curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1); 
		 
		 //Ricevo la risposta dal server
		 $varResponse=curl_exec($ch); 
		   
		 //chiudo la connessione 
		 curl_close($ch); 
		 
		 if (substr($varResponse,0,7) == '!ERROR!') {
		 
		 		$data->error = 1;
		 		$data->message =  $varResponse;
		 
		 } else {
		 
		 		//Separo il contenuto della stringa ricevuta (PaymentID:RedirectURL)
		 		 		
		 		$varPosiz= strpos($varResponse, ':http');
		 		$varPaymentId= substr($varResponse,0,$varPosiz);
		 		$nc=strlen($varResponse);
		 		$nc=($nc-17);
		 		$varRedirectURL=substr($varResponse,$varPosiz+1);
		 
				//Creo l'URL di redirezione
		  		$varRedirectURL ="$varRedirectURL?PaymentID=$varPaymentId";
		 		//echo $varRedirectURL;
				
				//Redirezione finale del browser sulla HPP
		 		$data->error = 0;
		 		$data->redirect = $varRedirectURL;
		}
             
		
        $html = $this->_getLayout('prepayment', $data);
        //$html = "<meta http-equiv=\"refresh\" content=\"0;URL=$varRedirectURL\">";
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
    	$values = JRequest::get('request');
    	
    	if($values['error'] == '0')
    		$vars->approved = true;
    	else
    		$vars->approved = false;
    	
    	$data_temp['error'] = $values['error'];
    	$data_temp['orderpayment_id'] = $values['udf1'];
    	$data_temp['trans_id'] = $values['TransID'];
    	$data_temp['result_code'] = $values['resultcode'];
    	$data_temp['key'] = $values['postdate'].' '.$values['resultcode'];
    	
    	$errors = $this->_processSale($data_temp);
    	
    	// Process the payment        
        $vars = new JObject();
        
        if($errors)
        {
        	$vars->message = $errors;
        }
        else
        {
        	$vars->message = JText::_('Payment Performed!');
        }
        
        $html = $this->_getLayout('message', $vars);
                
        return $html;
    }
    
    /**
     * Processes the form data 
     */
    function _processSale($data)
    {
    	$send_email = false;
    	$errors = array();    	
    	
    	// load the orderpayment record and set some values
        DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $orderpayment_id = $data['orderpayment_id'];
        $orderpayment = DSCTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $orderpayment_id );
        $orderpayment->transaction_details  = $data['key'];
        $orderpayment->transaction_id       = $data['trans_id'];
        $orderpayment->transaction_status   = $data['result_code'];
       
        // set the order's new status and update quantities if necessary
        Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $order = DSCTable::getInstance('Orders', 'TiendaTable');
        $order->load( $orderpayment->order_id );
        if ($data['error'] != "0") 
        {
            // if an error occurred 
            $order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED
            $errors[] = JRequest::getVar('ErrorText', JText::_('ERROR_WHILE_PAYING'));
        }
            else 
        {
            $order->order_state_id = $this->params->get('payment_received_order_state', '17'); // PAYMENT RECEIVED
            $this->setOrderPaymentReceived( $orderpayment->order_id );
            
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

        return count($errors) ? implode("<br />", $errors) : '';       
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

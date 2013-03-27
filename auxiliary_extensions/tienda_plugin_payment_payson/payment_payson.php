<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Henrik Hussfelt 
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_payson extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'payment_payson';
    
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
	function plgTiendaPayment_payson(& $subject, $config) {
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

        // set payment plugin variables
        $vars->post_url = $this->_getPostUrl();
        
        // TODO: Currency choice not Accepted Yet, but when it is use Tienda::getInstance()->get('currency');
        // TODO: Recalculate to SEK with something like:
		// $vars->Cost = TiendaCurrency::convert( $data['orderpayment_amount'], Tienda::getInstance()->get('currency'), "SEK" );
        
        $vars->currency_code = "SEK"; 

        // set variables for user info
        $vars->SellerEmail 		= $this->_getParam( 'payson_seller_email' );
        $vars->BuyerFirstName	= $data['orderinfo']->shipping_first_name;
        $vars->BuyerLastName	= $data['orderinfo']->shipping_last_name;
        $vars->BuyerEmail		= $data['orderinfo']->user_email;
        $vars->Cost				= str_replace(".", ",", TiendaHelperBase::number( $data['orderpayment_amount'], array( 'thousands' =>'', 'currency_decimal' => ',', 'num_decimals' => 2 ) ) );
		$vars->ExtraCost		= '0'; // TODO: Implement later, might be configured from plugin parameters to let the shop owner take an extra cost?
        $vars->RefNr			= $data['orderpayment_id'];
        $vars->PaymentMethod	= $this->_getParam( 'PaymentMethod' );
        $vars->OkUrl			= JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=display_message";
        $vars->CancelUrl		= JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=cancel";
        $vars->AgentId			= $this->_getParam( 'payson_agent_id', '' );
        $vars->GuaranteeOffered	= $this->_getParam( 'payson_guarantee', '' );
        $vars->payson_image		= $this->_getParam( 'payson_image', '' );

        // Create the MD5 to validate the request at Payson
        $Key = $this->_getParam( 'payson_md5' );
		$MD5string = $vars->SellerEmail . ":" . $vars->Cost . ":" . $vars->ExtraCost . ":" . $vars->OkUrl . ":" . $vars->GuaranteeOffered . $Key;

		$vars->MD5 = md5($MD5string);

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
                $vars->message = JText::_('PAYSON MESSAGE PAYMENT ACCEPTED FOR VALIDATION');
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();
              break;
            case "process":
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
                echo $html; // TODO Remove this
                $app = JFactory::getApplication();
                $app->close();
              break;
            case "cancel":
                $vars->message = JText::_('Payson Message Cancel');
                $html = $this->_getLayout('message', $vars);
              break;
            default:
                $vars->message = JText::_('Payson Message Invalid Action');
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

    /**
     * Gets the Payson gateway URL
     * 
     * @param boolean $full
     * @return string
     * @access protected
     */
    function _getPostUrl($full = true)
    {
        $url = $this->params->get('sandbox') ? 'www.payson.se/testagent/default.aspx' : 'www.payson.se/merchant/default.aspx';
        
        if ($full) 
        {
            $url = 'https://' . $url;
        }
        
        return $url;
    }
    
    
    /**
     * Gets the value for the Payson variable
     * 
     * @param string $name
     * @return string
     * @access protected
     */
    function _getParam( $name, $default='' )
    {
    	$return = $this->params->get($name, $default);        
        return $return;
    }  
    
    /**
     * Validates the Payson data
     * 
     * @param array $data
     * @return string Empty string if data is valid and an error message otherwise
     * @access protected
     */
    function _validateData( $data )
    {
		$strYourSecretKey	= $this->_getParam('payson_md5');
		$strOkURL			= $data['OkURL'];
		$strPaysonRef		= $data['Paysonref'];
		$strMD5				= $data['MD5'];

		$strTestMD5String = $strOkURL . $strPaysonRef . $strYourSecretKey;

		$strMD5Hash = md5($strTestMD5String);

		if($strMD5Hash == $strMD5){
			// Return true here, we have a success
			return '';
		} else {
			return JText::_('PAYSON ERROR IN VALIDATION');
		}
    }

	/**
	 *
	 * @return HTML
	 */
	function _process() 
	{
		$data = JRequest::get('post');
		// validate the request info
		$error = $this->_validateData( $data );

		$payment_error = $this->_processSale( $data, $error );

		return $payment_error;
	}
	
    /**
     * Processes the sale payment
     * 
     * @param array $data data
     * @return boolean Did the Payment Validate?
     * @access protected
     */
    function _processSale( $data, $error='' )
    {
        $send_email = false;
        
        /*
         * validate the payment data
         */
        $errors = array();
        
        if (!empty($error))
        {
        	$errors[] = $error;
        }

        // load the orderpayment record and set some values
        DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $orderpayment = DSCTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load( $data['RefNr'] );
        $orderpayment->transaction_details  = $data['PaysonRef'];

        // set the order's new status and update quantities if necessary
        JLoader::import( 'com_tienda.helpers.order', JPATH_ADMINISTRATOR.'/components' );
        JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.'/components' );
        $order = DSCTable::getInstance('Orders', 'TiendaTable');
        $order->load( $orderpayment->order_id );
        if (count($errors)) 
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

        // Send emails if error
        if (count($errors))
        {
        	$this->_sendErrorEmails(implode(",", $message));	
        }

        return count($errors) ? implode("\n", $errors) : '';        
    }

    /**
     * Sends error messages to site administrators
     * 
     * @param string $message
     * @param string $paymentData
     * @return boolean
     * @access protected
     */
    function _sendErrorEmails($message, $paymentData)
    {
        $mainframe = JFactory::getApplication();
                
        // grab config settings for sender name and email
        $config     = Tienda::getInstance();
        $mailfrom   = $config->get( 'emails_defaultemail', $mainframe->getCfg('mailfrom') );
        $fromname   = $config->get( 'emails_defaultname', $mainframe->getCfg('fromname') );
        $sitename   = $config->get( 'sitename', $mainframe->getCfg('sitename') );
        $siteurl    = $config->get( 'siteurl', JURI::root() );
        
        $recipients = $this->_getAdmins();
        $mailer = JFactory::getMailer();
        
        $subject = JText::sprintf('PAYSON EMAIL PAYMENT NOT VALIDATED SUBJECT', $sitename);

        foreach ($recipients as $recipient) 
        {
            $mailer = JFactory::getMailer();        
            $mailer->addRecipient( $recipient->email );
        
            $mailer->setSubject( $subject );
            $mailer->setBody( JText::sprintf('PAYSON EMAIL PAYMENT FAILED BODY', $recipient->name, $sitename, $siteurl, $message, $paymentData) );          
            $mailer->setSender(array( $mailfrom, $fromname ));
            $sent = $mailer->send();
        }

        return true;
    }

    /**
     * Gets admins data
     * 
     * @return array|boolean
     * @access protected 
     */
    function _getAdmins()
    {
        $db = JFactory::getDBO();
        $q = "SELECT name, email FROM #__users "
           . "WHERE LOWER(usertype) = \"super administrator\" "
           . "AND sendEmail = 1 "
           ;
        $db->setQuery($q);
        $admins = $db->loadObjectList();
            
        if ($error = $db->getErrorMsg()) {
            JError::raiseError(500, $error);
            return false;
        }
        
        return $admins;               
    }
    
}
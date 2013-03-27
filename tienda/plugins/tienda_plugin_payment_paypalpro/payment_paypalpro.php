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

class plgTiendaPayment_paypalpro extends TiendaPaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = 'payment_paypalpro';
	var $_payment_type = 'payment_paypalpro';
	/**
	 * @var array
	 * @access protected
	 */
	var $_renderers = array();

	/**
	 * @var array
	 * @access protected
	 */
	var $_processors = array();

	/**
	 * @var boolean
	 */
	var $_is_log = false;

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
	function plgTiendaPayment_paypalpro(& $subject, $config) {
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, null, true);
	}
  
  
  /**
     * Prepares variables for the payment form
     * 
     * @return unknown_type
     */
    function _renderForm( )
    {
        $user = JFactory::getUser();    
        $vars = new JObject();
        
        $html = $this->_getLayout('form', $vars);
        
        return $html;
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
        $directpayment_renderer = $this->_getRenderer('directpayment');		
		$expresscheckout_renderer = $this->_getRenderer('expresscheckout');
		$helper_renderer = $this->_getRenderer('helper');
		
		$vars = new JObject();
		$vars->note = JText::_('COM_TIENDA_PAYPALPRO_NOTE_DEFAULT');
		$vars->document = JFactory::getDocument();	

		$vars->expresscheckout_form = $expresscheckout_renderer->renderForm( $data, array() );
		$vars->directpayment_form = $directpayment_renderer->renderForm( $data, array(), '0', '0' );
		
		$vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_amount = $data['orderpayment_amount'];
        $vars->orderpayment_type = $this->_element;
       

        // set paypal checkout type
        $order = DSCTable::getInstance('Orders', 'TiendaTable');
        $order->load( $data['order_id'] );
        $items = $order->getItems();
        $vars->is_recurring = $order->isRecurring();
        
        // if order has both recurring and non-recurring items,
        if ($vars->is_recurring && count($items) > '1')
        {
            $vars->cmd = '_cart';
            $vars->mixed_cart = true;
            // Adjust the orderpayment amount since it's a mixed cart
            // first orderpayment is just the non-recurring items total
            // then upon return, ask user to checkout again for recurring items
            $orderpayment = DSCTable::getInstance('OrderPayments', 'TiendaTable');
            $orderpayment->load( $vars->orderpayment_id );
            $vars->amount = $order->recurring_trial ? $order->recurring_trial_price : $order->recurring_amount;
            $orderpayment->orderpayment_amount = $orderpayment->orderpayment_amount - $vars->amount; 
            $orderpayment->save();
            $vars->orderpayment_amount = $orderpayment->orderpayment_amount;
        }
            elseif ($vars->is_recurring && count($items) == '1')
        {
            // only recurring
            $vars->cmd = '_xclick-subscriptions';
            $vars->mixed_cart = false;
        }
            else
        {
            // do normal cart checkout
            $vars->cmd = '_cart';
            $vars->mixed_cart = false;
        } 
        $vars->order = $order;
        $vars->orderitems = $items;
        
        // set payment plugin variables        
        $vars->merchant_email = $this->_getParam( 'merchant_email' );
       // $vars->post_url = $this->_getPostUrl();
        
        // are there both recurring and non-recurring items in cart? 
        // if so, then user must perform two checkouts,
        // so store a flag in the return_url        
        $vars->return_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=display_message&checkout=1";
        $vars->cancel_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=cancel";
        $vars->notify_url = JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process&tmpl=component";
        $vars->currency_code = $this->_getParam( 'currency', 'USD' ); // TODO Eventually use: Tienda::getInstance()->get('currency');

        // set variables for user info
       
        $vars->first_name   = $data['orderinfo']->shipping_first_name;
        $vars->last_name    = $data['orderinfo']->shipping_last_name;
        $vars->email        = $data['orderinfo']->user_email;
        $vars->address_1    = $data['orderinfo']->shipping_address_1;
        $vars->address_2    = $data['orderinfo']->shipping_address_2;
        $vars->city         = $data['orderinfo']->shipping_city;
        
       	//get 2-character IS0-3166-1 country code
        $countryTable = DSCTable::getInstance('Countries', 'TiendaTable');       
        $countryTable->load( $data['orderinfo']->shipping_country_id );     
        
        $vars->country      = $countryTable->country_isocode_2;        
        //$vars->country      = $data['orderinfo']->shipping_country_name;
        $vars->region       = $data['orderinfo']->shipping_zone_name;
        $vars->postal_code  = $data['orderinfo']->shipping_postal_code;
       
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
       // $paction = JRequest::getVar('paction');
        $vars = new JObject();
        $ptype 		= JRequest::getVar( 'orderpayment_type' );
		if ($ptype == $this->_payment_type)
		{
			$app			  = JFactory::getApplication();
			$paction 		  = JRequest::getVar( 'paction' );
			$helper_renderer  = $this->_getRenderer('helper');	
			$html 			  = '';

			switch ($paction) {
				case "display_message":					
 					$html .= $helper_renderer->renderHtml( JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_ACCEPTED_FOR_VALIDATION') ); 
					$html .= $helper_renderer->displayArticle();			
				  break;
				case "process_express_checkout":
					$html .= $this->_processExpressCheckout();
					$html .= $helper_renderer->displayArticle();				
				  break;
				  
				case "process_doexpresscheckout":
					$html .= $this->_processDoExpressCheckout();
					$html .= $helper_renderer->displayArticle();
				  break;	
				  
				case "process_direct_payment":
					$html .= $this->_processDirectPayment();
					$html .= $helper_renderer->displayArticle();					
				  break;
				  
//			    case 'process_recurring':
//					$this->_processRecurringPayment();
//					$app->close();					
//				  break;
//				  
				case "cancel":
					$text = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_CANCEL');
					$html .= $helper_renderer->renderHtml( $text );
				  break;
				default:
					$text = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_INVALID_ACTION');
					$html .= $helper_renderer->renderHtml( $text );
				  break;
			}

			return $html;
        
//        switch ($paction) 
//        {
//            case "display_message":
//                $checkout = JRequest::getInt('checkout');
//                // get the order_id from the session set by the prePayment
//                $mainframe = JFactory::getApplication();
//                $order_id = (int) $mainframe->getUserState( 'tienda.order_id' );
//                $order = DSCTable::getInstance('Orders', 'TiendaTable');
//                $order->load( $order_id );
//                $items = $order->getItems();
//
//                // if order has both recurring and non-recurring items,
//                if ($order->isRecurring() && count($items) > '1' && $checkout == '1')
//                {
//                    $html = $this->_secondPayment( $order_id );
//                }
//                    else
//                {
//                    $vars->message = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_ACCEPTED_FOR_VALIDATION');
//                    $html = $this->_getLayout('message', $vars);
//                    $html .= $this->_displayArticle();                    
//                }     
//              break;
//            case "process":
//                $vars->message = $this->_process();
//                $html = $this->_getLayout('message', $vars);
//                echo $html; // TODO Remove this
//                $app = JFactory::getApplication();
//                $app->close();
//              break;
//            case "cancel":
//                $vars->message = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_CANCEL');
//                $html = $this->_getLayout('message', $vars);
//              break;
//            default:
//                $vars->message = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_INVALID_ACTION');
//                $html = $this->_getLayout('message', $vars);
//              break;
//        }
//        
//        return $html;
    	}
    }   
	/**
	 * Gets the requested renderer object
	 * 
	 * @param string $type Directpayment, Expresscheckout or Helper
	 * @return object
	 * @access protected
	 */
	function  _getRenderer($type)
	{
		if (!isset($this->_renderers[$type]) || $this->_renderers[$type] === null) {
			
			// Load helper
			$file = JPath::clean(dirname(__FILE__) . "/{$this->_payment_type}/library/renderer/$type.php");
			
			if (JFile::exists($file)) {	
				require_once $file;
				
				$name = 'plgTiendaPayment_Paypalpro_Renderer_' . ucfirst($type);
				$this->_renderers[$type] = new $name($this->params, $this->_payment_type);
			}			
		}
		
		return $this->_renderers[$type];
	}
	/**
     * Gets the value for the Paypal pro variable
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
     * Gets the Paypal gateway URL
     * 
     * @param boolean $full
     * @return string
     * @access protected
     */
    function _getPostUrl($full = true)
    {
        $url = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
        
        if ($full) 
        {
            $url = 'https://' . $url . '/cgi-bin/webscr';
        }
        
        return $url;
    } 
    /**
	 * Processes the Express Checkout payment
	 * 
	 * This methods will execute the SetExpressCheckout call
	 * 
	 * @return string
	 * @access protected
	 */
	function _processExpressCheckout()
	{
		
		$item_id = JRequest::getVar('item_number', '', 'post', 'int');
		
		$data = JRequest::get('post');
		
		$processor = $this->_getProcessor('expresscheckout');
		$processor->setSubscrTypeID($item_id);
		$processor->setData($data);
		$processor->setIsLog($this->_is_log);
		
		// validate the form data
		if (!$processor->validateData()) {			
			$text  = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_FAILED');
			$text .= $this->_getFormattedErrorMsg($processor->getErrors(), 'text');	
		}
		else {
			if (!$processor->processSetExpressCheckout()) {
				// payment wasn't processed, no order was created
				$text  = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_FAILED');
				$text .= $this->_getFormattedErrorMsg($processor->getErrors(), 'text');		
			}			
		}

		// if errors occurred, let's display them
		// otherwise we shouldn't get to this code because $processor->process()
		// will redirect to PayPal
		$helper_renderer = $this->_getRenderer('helper');
		return $helper_renderer->renderHtml($text);
	}
    /**
	 * Processes the Direct payment
	 * 
	 * @return string
	 * @access protected
	 */
	function _processDirectPayment()
	{
		$item_id = JRequest::getVar('item_number', '', 'post', 'int');
		$data = JRequest::get('post');
	
		$processor = $this->_getProcessor('directpayment');
		$processor->setSubscrTypeID($item_id);
		$processor->setData($data);
		$processor->setIsLog($this->_is_log);
		
		// validate the form data
		if (!$processor->validateData()) {			
			$error_msg = $this->_getFormattedErrorMsg($processor->getErrors());
			// display the form again, prepopulated, with an error message
			JError::raiseNotice('Invalid Form Values', $error_msg);
			
			$renderer = $this->_getRenderer('directpayment');
			//$helper = TiendaHelperBase::getInstance();
			//$SubscrArray = (array)($processor->getSubscrTypeObj());
			return $renderer->renderForm($data, $processor->getData(), '1', '1');			
		}
		
		if (!$processor->process()) {
			// payment wasn't processed, no subscription was created
			$text  = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_FAILED');
			$text .= $this->_getFormattedErrorMsg($processor->getErrors(), 'text');		
		}
		elseif (count($processor->getErrors())) {
			// payment was processed with warnings
			$text  = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_SUCCESS_WITH_WARNINGS');
			$text .= $this->_getFormattedErrorMsg($processor->getErrors(), 'text');
		}	
		else {
			$text = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_SUCCESS');
		}

		$helper_renderer = $this->_getRenderer('helper');
		return $helper_renderer->renderHtml($text);
	}
	/**
	 * Processes the Express Checkout payment
	 * 
	 * This methods will execute the DoExpressCheckout call
	 * 
	 * @return string
	 * @access protected
	 */
	function _processDoExpressCheckout()
	{
		
		$item_id = JRequest::getVar('item_number', '', 'get', 'int');
		$data = JRequest::get('get');
		
		$processor = $this->_getProcessor('expresscheckout');
		$processor->setSubscrTypeID($item_id);
		$processor->setData($data);
		$processor->setIsLog($this->_is_log);
		
		// validate the form data
		if (!$processor->validateData(false)) {
		
			$text  = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_FAILED');
			$text .= $this->_getFormattedErrorMsg($processor->getErrors(), 'text');	
		}
		else {
			if (!$processor->processDoExpressCheckout()) {
				// payment wasn't processed, no order was created
				$text  = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_FAILED');
				$text .= $this->_getFormattedErrorMsg($processor->getErrors(), 'text');		
			}
			elseif (count($processor->getErrors())) {
				// payment was processed with warnings
				$text  = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_SUCCESS_WITH_WARNINGS');
				$text .= $this->_getFormattedErrorMsg($processor->getErrors(), 'text');
			}	
			else {
				$text = JText::_('COM_TIENDA_PAYPALPRO_MESSAGE_PAYMENT_SUCCESS');
			}	
		}

		$helper_renderer = $this->_getRenderer('helper');
		return $helper_renderer->renderHtml($text);
	}
/**
	 * Gets the requested processor object
	 *
	 * @param string $type Directpayment or Expresscheckout 
	 * @return object
	 * @access protected
	 */
	function  _getProcessor($type)
	{
		if (!isset($this->_processors[$type]) || $this->_processors[$type] === null) {
			$file = JPath::clean(dirname(__FILE__) . "/{$this->_payment_type}/library/processor/$type.php");
			
			if (JFile::exists($file)) {	
				require_once $file;
				
				$name = 'plgTiendaPayment_Paypalpro_Processor_' . ucfirst($type);
				$this->_processors[$type] = new $name($this->params, $this->_payment_type);
			}			
		}
		
		return $this->_processors[$type];
	}
	
/**
	 * Generares an error string from an array
	 * 
	 * @param array $errors
	 * @param string $type html or text
	 * @return string
	 */
	function _getFormattedErrorMsg($errors, $type = 'html')
	{		
		if ($type == 'html') {
			return '<li>' . implode('</li><li>', $errors) . '</li>';		
		} 
		else {
			return implode("\n", $errors);
		}
	}
	
    
    

}
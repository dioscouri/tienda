<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if (!class_exists('TiendaHelperBase')) {
    JLoader::register( "TiendaHelperBase", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."helpers".DS."_base.php" );
}

class TiendaHelperEmail extends TiendaHelperBase
{
    /**
     * Protected! Use getInstance()
     */ 
    protected function TiendaHelperEmail() 
    {
        parent::__construct();
        $this->use_html = true;
    }
    
    /**
     * Returns 
     * @param mixed Data to send
     * @param type  Type of mail.
     * @return boolean
     */
    public function sendEmailNotices( $data, $type = 'order' ) 
    {
        $mainframe = JFactory::getApplication();
        $success = false;
        $done = array();

        // grab config settings for sender name and email
        $config     = &TiendaConfig::getInstance();
        $mailfrom   = $config->get( 'shop_email', '' );
        if( !strlen( $mailfrom ) )
        	$mailfrom = $mainframe->getCfg('mailfrom');
        	
        $fromname   = $config->get( 'shop_email_from_name', '' );
        if( !strlen( $fromname ) )
        	$fromname = $mainframe->getCfg('fromname');
        
        $sitename   = $config->get( 'sitename', $mainframe->getCfg('sitename') );
        $siteurl    = $config->get( 'siteurl', JURI::root() );
        switch( $type )
        {
        	case 'subscription_expiring':
        	case 'subscription_expired' :
        	case 'subscription_new':
        	case 'new_subscription':
        	case 'subscription':
        		$recipients = $this->getEmailRecipients( $data->subscription_id, $type );
        		break;
        	default :
        		$recipients = $this->getEmailRecipients( $data->order_id, $type );
        		break;
        }
        $content = $this->getEmailContent( $data, $type );
        
        // trigger event onAfterGetEmailContent 
        $dispatcher=& JDispatcher::getInstance(); 
        $dispatcher->trigger('onAfterGetEmailContent', array( $data, &$content) ); 
                
        //$this->results = array();
        for ($i=0; $i<count($recipients); $i++) 
        {
            $recipient = $recipients[$i];
            if (!isset($done[$recipient])) 
            {
                if ( $send = $this->_sendMail( $mailfrom, $fromname, $recipient, $content->subject, $content->body ) ) 
                {
                    $success = true;
                    $done[$recipient] = $recipient;
                }
                //$this->results[$recipient] = $send;
            }
        }
        
        //$this->recipients = $recipients;
        //$this->content = $content;
        
        return $success;
    }

    /**
     * Returns an array of user objects
     * of all users who should receive this email 
     *  
     * @param $data Object
     * @return array
     */
    private function getEmailRecipients( $id, $type = 'order' ) 
    {
      $recipients = array();
       
      switch ($type)
      {
      	    case 'subscription_expiring':
    		case 'subscription_expired' :
    		case 'subscription_new':
    		case 'new_subscription':
    		case 'subscription':
	    		$model = Tienda::getClass('TiendaModelSubscriptions', 'models.subscriptions');
	    		$model->setId( $id );
	    		$subscription = $model->getItem();
    			
	    		$model_order = Tienda::getClass('TiendaModelOrders', 'models.orders');
	    		$model_order->setId( $subscription->order_id );
	    		$order = $model_order->getItem();
	    			
	    		if( $subscription->user_id > 0 ) // not a guest account
	    		{
		    		$user = JUser::getInstance( $subscription->user_id );
	    			// string needle NOT found in haystack
	    			if (!in_array($user->email, $recipients) && JMailHelper::isEmailAddress($user->email))
	    			{
	    				$recipients[] = $user->email;    
	    			}
	    		}
	    		else 
	    		{
	    			// add the userinfo email to the list of recipients
	    			if (!in_array($order->userinfo_email, $recipients) && JMailHelper::isEmailAddress($order->userinfo_email))
	    			{
	    				$recipients[] = $order->userinfo_email;    
	    			}
	    		}
	   			// add the order user_email to the list of recipients
	   			if (!in_array($order->user_email, $recipients) && JMailHelper::isEmailAddress($order->user_email))
	   			{
	   				$recipients[] = $order->user_email;    
	   			}
	   			break;
	   		case "new_order":
	   			$system_recipients = $this->getSystemEmailRecipients();
	   			foreach ($system_recipients as $r)
	   			{
	   				if (!in_array($r->email, $recipients))
	   				{
	   					$recipients[] = $r->email;    
	   				}
	   			}
	   			
      	  $additional_recipients = $this->getAdditionalEmailRecipients();
	   			foreach ($additional_recipients as $r)
	   			{
	   				if (!in_array($r, $recipients))
	   				{
	   					$recipients[] = $r;    
	   				}
	   			}
	   			
	   			$model = Tienda::getClass('TiendaModelOrders', 'models.orders');
	   			$model->setId( $id );
	   			$order = $model->getItem();
	   			jimport('joomla.mail.helper');
	   			
	   			// add the userinfo user_email to the list of recipients
	   			if (!in_array($order->userinfo_email, $recipients) && JMailHelper::isEmailAddress($order->userinfo_email))
	   			{
	   				$recipients[] = $order->userinfo_email;    
	   			}
               
	   			// add the order user_email to the list of recipients
	   			if (!in_array($order->user_email, $recipients) && JMailHelper::isEmailAddress($order->user_email))
	   			{
	   				$recipients[] = $order->user_email;    
	   			}
	   		case 'order':
	   		default:                
	   			$model = Tienda::getClass('TiendaModelOrders', 'models.orders');
	   			$model->setId( $id );
	   			$order = $model->getItem();
	   			
	    		if( $order->user_id > 0 ) // not a guest account
	   			{
	   				$user = JUser::getInstance( $order->user_id );
	   				// string needle NOT found in haystack
	   				if (!in_array($user->email, $recipients))
	   				{
	   					$recipients[] = $user->email;    
	   				}
	   			}
	   			else 
	   			{
	   				// add the userinfo email to the list of recipients
	   				if (!in_array($order->userinfo_email, $recipients) && JMailHelper::isEmailAddress($order->userinfo_email))
	   				{
	   					$recipients[] = $order->userinfo_email;    
	   				}
	   			}
               
	   			// add the order user_email to the list of recipients
	   			if (!in_array($order->user_email, $recipients) && JMailHelper::isEmailAddress($order->user_email))
	   			{
	   				$recipients[] = $order->user_email;    
	   			}
	   			break;
      }
      // allow plugins to modify the order email recipient list
      $dispatcher = JDispatcher::getInstance();
      $dispatcher->trigger( 'onGetEmailRecipients', array( $id, $type, &$recipients ) );
        
      return $recipients;
    }

    /**
     * Returns 
     * 
     * @param object
     * @param mixed Boolean
     * @param mixed Boolean
     * @return array
     */
    private function getEmailContent( $data, $type = 'order' ) 
    {
        $mainframe = JFactory::getApplication();
        $type = strtolower($type);  

        $lang = &JFactory::getLanguage();
        $lang->load('com_tienda', JPATH_ADMINISTRATOR);
        
        $return = new stdClass();
        $return->body = '';
        $return->subject = '';

        // get config settings
        $config = &TiendaConfig::getInstance();
        $sitename = $config->get( 'sitename', $mainframe->getCfg('sitename') );
        $siteurl = $config->get( 'siteurl', JURI::root() );
        
        // get the placeholders array here so the switch statement can add to it
        $placeholders = $this->getPlaceholderDefaults();
        
        switch ($type) 
        {
            case "subscription_expiring":
                $return->subject    = JText::_( 'EMAIL_EXPIRING_SUBSCRIPTION_SUBJECT' );
                $return->body       = JText::_( 'EMAIL_EXPIRING_SUBSCRIPTION_BODY' );
                if ($this->use_html)
                {
                    $return->body = nl2br( $return->body );
                }
                $placeholders['user.name'] = $data->user_name;
                $placeholders['product.name'] = $data->product_name;
              break;

            case "subscription_expired":
                $return->subject    = JText::_( 'EMAIL_EXPIRED_SUBSCRIPTION_SUBJECT');
                $return->body       = JText::_( 'EMAIL_EXPIRED_SUBSCRIPTION_BODY' );
                if ($this->use_html)
                {
                    $return->body = nl2br( $return->body );
                }
                                
                $placeholders['user.name'] = $data->user_name;
                $placeholders['product.name'] = $data->product_name;
              break;
            
            case "subscription_new":
            case "new_subscription":
            case "subscription":
            		$user_name = JText::_( 'COM_TIENDA_GUEST' );
            		if( $data->user_id > 0 )
            		{
	                $user = JUser::getInstance($data->user_id);
	                $user_name = $user->name;
            		}
            		if( $data->user_id < Tienda::getGuestIdStart() )
            			$link = JURI::root()."index.php?option=com_tienda&view=orders&task=view&id=".$data->order_id.'&h='.$data->order_hash;
            		else
            			$link = JURI::root()."index.php?option=com_tienda&view=orders&task=view&id=".$data->order_id;
                $link = JRoute::_( $link, false );
                $link = "<a href='{$link}'>" . $link . "</a>";
                                
                if ( count($data->history) == 1 )
                {
                    // new order
                    $return->subject = sprintf( JText::_('EMAIL_NEW_ORDER_SUBJECT'), $data->order_id );

                    // set the email body
                    $text = sprintf(JText::_('EMAIL_DEAR'),$user_name).",\n\n";
                    $text .= JText::_("EMAIL_THANKS_NEW_SUBSCRIPTION")."\n\n";
                    $text .= sprintf(JText::_("EMAIL_CHECK"),$link)."\n\n";
                    $text .= JText::_("EMAIL_RECEIPT_FOLLOWS")."\n\n";
                    if ($this->use_html)
                    {
                        $text = nl2br( $text );
                    }
                    
                    // get the order body
                    Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
                    $text .= TiendaHelperOrder::getOrderHtmlForEmail( $data->order_id );
                }
                    else
                {
                    // Status Change
                    $return->subject = JText::_( 'EMAIL_SUBSCRIPTION_STATUS_CHANGE' );
                    $last_history = count($data->history) - 1;
                    
                    $text = sprintf(JText::_('EMAIL_DEAR'),$user_name).",\n\n";
                    $text .= sprintf( JText::_("EMAIL_ORDER_UPDATED"), $data->order_id );
                    if (!empty($data->history[$last_history]->comments))
                    {
                        $text .= sprintf( JText::_("EMAIL_ADDITIONAL_COMMENTS"), $data->history[$last_history]->comments );
                    }
                    $text .= sprintf(JText::_("EMAIL_CHECK"),$link)."\n\n";

                    if ($this->use_html)
                    {
                        $text = nl2br( $text );
                    }
                }
                
                $return->body = $text;
                
                $placeholders['user.name'] = $user_name;
                
                break;
                
            case "new_order":
            case "order":
            default:
								$user_name = JText::_( 'COM_TIENDA_GUEST' );
            		if( $data->user_id > 0 )
            		{
	                $user = JUser::getInstance($data->user_id);
	                $user_name = $user->name;
            		}
            		if( $data->user_id < Tienda::getGuestIdStart() )
            			$link = JURI::root()."index.php?option=com_tienda&view=orders&task=view&id=".$data->order_id.'&h='.$data->order_hash;
            		else
            			$link = JURI::root()."index.php?option=com_tienda&view=orders&task=view&id=".$data->order_id;
            			
                $link = JRoute::_( $link, false );
                $link = "<a href='{$link}'>" . $link . "</a>";
                
                if ( $type == 'new_order' )
                {
                    // new order
                    $return->subject = sprintf( JText::_('EMAIL_NEW_ORDER_SUBJECT'), $data->order_id );

                    // set the email body
                    $text = sprintf(JText::_('EMAIL_DEAR'),$user_name).",\n\n";
                    $text .= JText::_("EMAIL_THANKS_NEW_ORDER")."\n\n";
                    $text .= sprintf(JText::_("EMAIL_CHECK"),$link)."\n\n";
                    $text .= JText::_("EMAIL_RECEIPT_FOLLOWS")."\n\n";
                    if ($this->use_html)
                    {
                        $text = nl2br( $text );
                    }
                    
                    // get the order body
                    Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
                    $text .= TiendaHelperOrder::getOrderHtmlForEmail( $data->order_id );
                }
                    else
                {
                    // Status Change
                    $return->subject = JText::_( 'EMAIL_ORDER_STATUS_CHANGE' );
                    $last_history = count($data->orderhistory) - 1;

                    $text = sprintf(JText::_('EMAIL_DEAR'),$user_name).",\n\n";
                    $text .= sprintf( JText::_("EMAIL_ORDER_UPDATED"), $data->order_id );
                    $text .= JText::_("EMAIL_NEW_STATUS")." ".$data->orderhistory[$last_history]->order_state_name."\n\n";
                    if (!empty($data->orderhistory[$last_history]->comments))
                    {
                        $text .= sprintf( JText::_("EMAIL_ADDITIONAL_COMMENTS"), $data->orderhistory[$last_history]->comments );
                    }
                    $text .= sprintf(JText::_("EMAIL_CHECK"),$link)."\n\n";

                    if ($this->use_html)
                    {
                        $text = nl2br( $text );
                    }
                }
                
                $return->body = $text;
                
                $placeholders['user.name'] = $user_name;
              break;
        }        
        // replace placeholders in language strings - great idea, Oleg
        $return->subject = $this->replacePlaceholders($return->subject, $placeholders);
        $return->body = $this->replacePlaceholders($return->body, $placeholders);
        
        return $return;

    }

    /**
     * Prepares and sends the email
     * 
     * @param unknown_type $from
     * @param unknown_type $fromname
     * @param unknown_type $recipient
     * @param unknown_type $subject
     * @param unknown_type $body
     * @param unknown_type $actions
     * @param unknown_type $mode
     * @param unknown_type $cc
     * @param unknown_type $bcc
     * @param unknown_type $attachment
     * @param unknown_type $replyto
     * @param unknown_type $replytoname
     * @return unknown_type
     */
    private function _sendMail( $from, $fromname, $recipient, $subject, $body, $actions=NULL, $mode=NULL, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL ) 
    {
        $success = false;
        $mailer = JFactory::getMailer();
        $mailer->addRecipient( $recipient );
        $mailer->setSubject( $subject );
        
        // check user mail format type, default html
        $mailer->IsHTML($this->use_html);
        $body = htmlspecialchars_decode( $body );
        $mailer->setBody( $body );
            
        $sender = array( $from, $fromname );
        $mailer->setSender($sender);
        $sent = $mailer->send();
        if ($sent == '1') 
        {
            $success = true;
        }
        
        return $success;
    }
    
    /**
     * Gets all targets for system emails
     * 
     * return array of objects
     */
    function getSystemEmailRecipients()
    {
        $db =& JFactory::getDBO();
        $query = "
            SELECT tbl.email
            FROM #__users AS tbl
						WHERE tbl.sendEmail = 1 AND tbl.block = 0
				        "; 
        $db->setQuery( $query );
        $items = $db->loadObjectList();
        if (empty($items))
        {
            return array();
        }
        return $items;
    }
    
    /**
     * 
     * 
     * return array of emails
     */
    function getAdditionalEmailRecipients()
    {
        $items = array();
        
        $order_emails = TiendaConfig::getInstance()->get('order_emails');
        if (empty($order_emails))
        {
            return $items;
        }
        
        if ($csv = explode(',', $order_emails))
        {
            foreach ($csv as $email) 
            {
                $email = trim($email);
                if (!in_array($email, $items))
                {
                	if( strlen( $email ) )
                    $items[] = $email;
                }
            }
        }

        if ($nlsv = explode("\n", $order_emails))
        {
            foreach ($nlsv as $email) 
            {
                $email = trim($email);
                if (!in_array($email, $items))
                {
                	if( strlen( $email ) )
                		$items[] = $email;
                }
            }
        }
        
        return $items;
    }
    
    /**
     * Creates the placeholder array with the default site values
     * 
     * @return unknown_type
     */
    function getPlaceholderDefaults()
    {
        $mainframe = JFactory::getApplication();
        $config = &TiendaConfig::getInstance();
        $site_name              = $config->get( 'sitename', $mainframe->getCfg('sitename') );
        $site_url               = $config->get( 'siteurl', JURI::root() );
        $link_my_subscriptions  = $config->get( 'link_my_subscriptions', JURI::root()."/index.php?option=com_tienda&view=subscriptions" );
        $user_name              = JText::_( $config->get( 'default_email_user_name', 'Valued Customer' ) );
        
        // default placeholders
        $placeholders = array(
            'site.name'                 => $site_name,
            'site.url'                  => $site_url,
            'user.name'                 => $user_name,
            'link.my_subscriptions'     => $link_my_subscriptions
        );
        
        return $placeholders;
    }
    
    /**
     * Replaces placeholders with their values
     * 
     * @param string $text
     * @param array $placeholders
     * @return string
     * @access public
     */
    function replacePlaceholders($text, $placeholders)
    {
        $plPattern = '{%key%}';
        
        $plKeys = array();
        $plValues = array();
        
        foreach ($placeholders as $placeholder => $value) {
            $plKeys[] = str_replace('key', $placeholder, $plPattern);
            $plValues[] = $value;
        }
        
        $text = str_replace($plKeys, $plValues, $text);     
        return $text;
    }
    
    /**
     * Method to send the question ask by the customer to the site vendor
     * 
     * @param object $sendObject
     * @return boolean
     */
    function sendEmailToAskQuestionOnProduct($sendObject)
    {
    	$config = &TiendaConfig::getInstance();
		$lang = &JFactory::getLanguage();
        $lang->load('com_tienda', JPATH_ADMINISTRATOR);
    	//set the email subject
    	$subject = "[".$config->get('shop_name', 'SHOP')." - ".JText::_('Product')." #{$sendObject->item->product_id} ] ";
    	$subject .= JText::_('Product Inquiries!');
    	$sendObject->subject = $subject;
    	
    	$vendor_name = $config->get('shop_owner_name', 'Admin');
    	// set the email body
        $text = sprintf(JText::_('EMAIL_DEAR'),$vendor_name).",\n\n".$vendor_name;
     	$text .= $sendObject->namefrom.JText::_(' has some inquiries about the product')." ".$sendObject->item->product_name." #{$sendObject->item->product_id} ".JText::_("and here's what he has to say")."-\n\n";
		$text .= "------------------------------------------------------------------------------------------\n";
     	$text .= $sendObject->body;
     	$text .= "\n------------------------------------------------------------------------------------------";
		$text .= "\n\n";
		$text .= JText::_('Please use the link below to view the product.')."\n\n";		
		$text .= JText::_('Click this link to');
		$link = JURI::root().$sendObject->item->link;
		$text .= " <a href='{$link}'>";
		$text .= JText::_('view product').".";
		$text .= "</a>";
     	
        if ($this->use_html)
        {
        	$text = nl2br( $text );
        }

    	$success = false;    	
    	if ( $send = $this->_sendMail( $sendObject->mailfrom, $sendObject->namefrom, $sendObject->mailto, $sendObject->subject, $text ) ) 
        {
        	$success = true;                    
        }
        
        return $success;
    }

 /**
	 * Method to send a notice about low quantity of a product in the stock
	 *
	 * @param  string productquantity_id
	 * @return boolean
	 */
	function sendEmailLowQuanty( $productquantity_id )
	{
		$mainframe = JFactory::getApplication();
		$recipients = array();
		$done = array();
		$lang = JFactory::getLanguage();
		$lang->load( 'com_tienda', JPATH_ADMINISTRATOR );
		$system_recipients = $this->getSystemEmailRecipients();

		foreach ( $system_recipients as $r )
		{
			if( !in_array( $r->email, $recipients ) )
			{
				$recipients[] = $r->email;
			}
		}
		$config = TiendaConfig::getInstance();

		$fromname = $config->get('shop_name', 'SHOP');
		$mailfrom = $config->get('shop_email', '');
		if (!strlen($mailfrom))
		{
			$mailfrom = $mainframe->getCfg('mailfrom');
		}
		$vendor_name = $config->get('shop_owner_name', 'Admin');
		$ProductQuantities_model = JTable::getInstance('ProductQuantities', 'TiendaTable');
		$ProductQuantities_model->load( array( 'productquantity_id' => $productquantity_id ) );
		$quantity = $ProductQuantities_model -> quantity;
		$product_id = $ProductQuantities_model -> product_id;
		$product_attributes_csv = $ProductQuantities_model -> product_attributes;

		if (!empty($product_attributes_csv))
		{
			$productattributeoption_id_array = explode(',', $product_attributes_csv);
		}
		else
		{
			$productattributeoption_id_array = NULL;
		}

		$productsTable = JTable::getInstance('Products', 'TiendaTable');
		$productsTable -> load( $product_id, true, false );
		$product_name = $productsTable-> product_name;
		$subject = "[" . $config -> get('shop_name', 'SHOP') . " - " . JText::sprintf('LOW STOCK MAIL SUBJECT NAME AND ID', $product_name, $product_id) . "]";

		// set the email body
		$text = JText::sprintf('EMAIL_DEAR', $vendor_name) . ",\n\n";
		$text .= JText::sprintf("LOW STOCK MAIL PRODUCT NAME AND ID", $product_name, $product_id) . "\n";
		if (!empty($productattributeoption_id_array))
		{
			foreach ($productattributeoption_id_array as $productattributeoption_id)
			{
				$productattributeoptionsTable = JTable::getInstance('Productattributeoptions', 'TiendaTable');
				$productattributeoptionsTable -> load($productattributeoption_id);
				$productattribute_id = $productattributeoptionsTable -> productattribute_id;
				$productattributeoption_name = $productattributeoptionsTable -> productattributeoption_name;
				$productattributesTable = JTable::getInstance('Productattributes', 'TiendaTable');
				$productattributesTable -> load($productattribute_id);
				$productattribute_name = $productattributesTable -> productattribute_name;
				$text .= JText::sprintf( "LOW STOCK MAIL OPTION DETAILS", $productattribute_name, $productattributeoption_name ) . "\n";
			}
		}

		$text .= "\n------------------------------------------------------------------------------------------\n";
		$text .= JText::sprintf("LOW STOCK MAIL ITEMS AVAILABLE", $quantity) . "\n";
		$text .= "------------------------------------------------------------------------------------------";
		$text .= "\n\n";

		if ($this -> use_html)
		{
			$text = nl2br($text);
		}

		$success = false;
		for ($i = 0; $i < count($recipients); $i++)
		{
			$recipient = $recipients[$i];
			if (!isset($done[$recipient]))
			{
				if ($send = $this -> _sendMail($mailfrom, $fromname, $recipient, $subject, $text))
				{
					$success = true;
					$done[$recipient] = $recipient;
				}
			}
		}
		return $success;
	}

}

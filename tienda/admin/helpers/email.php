<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
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
	}
	
	/**
	 * Returns 
	 * @param mixed	Data to send
	 * @param type	Type of mail.
	 * @return boolean
	 */
	public function sendEmailNotices( $data, $type = 'order' ) 
	{
		$mainframe = JFactory::getApplication();
		$success = false;
		$done = array();

		// grab config settings for sender name and email
		$config 	= &TiendaConfig::getInstance();
		$mailfrom 	= $config->get( 'emails_defaultemail', $mainframe->getCfg('mailfrom') );
		$fromname 	= $config->get( 'emails_defaultname', $mainframe->getCfg('fromname') );
		$sitename 	= $config->get( 'sitename', $mainframe->getCfg('sitename') );
		$siteurl 	= $config->get( 'siteurl', JURI::root() );
		
		$recipients = $this->getEmailRecipients( $data->order_id );
		$content = $this->getEmailContent( $data, $type );
		
		// trigger event onAfterGetEmailContent 
		$dispatcher=& JDispatcher::getInstance(); 
		$dispatcher->trigger('onAfterGetEmailContent', array( $data, &$content) ); 
				
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
			}
		}		
		
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
		
		switch($type){
			case 'order':
			default:				
				$model = Tienda::getClass('TiendaModelOrders', 'models.orders');
				$model->setId( $id );
				$order = $model->getItem();

				$user = JUser::getInstance( $order->user_id );
				$email = $user->email;
				$needle="guest";
				$pos = strpos($email,$needle);
				
				if($pos === false) {
				 // string needle NOT found in haystack
				 $recipients[] = $email;
					   
				}
				else {
				 // string needle found in haystack
				 //getting the user info and sending the email on the correct email id 
					
 				Tienda::load( 'TiendaHelperUser', 'helpers.user' );
				$userHelper = TiendaHelperUser::getInstance('User', 'TiendaHelper');
			 	$userInfo=$userHelper->getBasicInfo($order->user_id);	
				$recipients[]=$userInfo->emailId;	
				}
								
				//$recipients[] = $email;
				
				// Add the order email only if they are different
				if( $email != $order->user_email){
					$recipients[] = $order->user_email;
				}
		    }
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
		
		$return = array();
		$link = JURI::root()."index.php?option=com_tienda&view=orders&task=view&id=".$data->order_id;
		$link = JRoute::_( $link, false );
		
		$return = new stdClass();
		$return->body = '';
		$return->subject = '';

		// get config settings
		$config = &TiendaConfig::getInstance();
		
		$sitename 						= $config->get( 'sitename', $mainframe->getCfg('sitename') );
		$siteurl 						= $config->get( 'siteurl', JURI::root() );
								
		switch ($type) 
		{
			case "order":
			default:
				$user = JUser::getInstance($data->user_id);
				$lang = JFactory::getLanguage();
				$lang->load('com_tienda', JPATH_ADMINISTRATOR);
				$link = JURI::root().JRoute::_("index.php?option=com_tienda&view=orders&task=view&id=".$data->order_id);
				
				if ( count($data->orderhistory) == 1 )
				{
                    // First Save, new order!
					$return->subject = JText::_('EMAIL_THANKS_NEW_ORDER');
					$text = $text  = JText::_('EMAIL_DEAR') ." ".$user->name.", \n ";
					$text .= sprintf( JText::_("EMAIL_NEW_ORDER_TEXT"), $data->order_id )."\n\n";;
					$text .= JText::_("EMAIL_CHECK")." ".$link;	
				}
				    else
				{
                    // Status Change
					$return->subject = JText::_( 'EMAIL_ORDER_STATUS_CHANGE' );
					$last_history = count($data->orderhistory) - 1;
					
					$text  = JText::_('EMAIL_DEAR') ." ".$user->name.", \n ";
					$text .= sprintf( JText::_("EMAIL_ORDER_UPDATED"), $data->order_id );
					$text .= JText::_("EMAIL_NEW_STATUS")." ".$data->orderhistory[$last_history]->order_state_name."\n\n";
					$text .= JText::_("EMAIL_CHECK")." ".$link;	
				}
				
				$return->body = $text;
				
			  break;
		}
		
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
		$mailer->setBody( ( $body ) );
        	
		$sender = array( $from, $fromname );
		$mailer->setSender($sender);
			
		//$sent = $mailer->send();
		 $header=$mailer->CreateHeader();
		 $header =  str_replace('>','',str_replace('<',' ',$header));
	 
		$sent=$this->Sendmail($mailer,$header, $body);
		
		if ($sent == '1') {
			$success = true;
		}
		return $success;
	}
	
	
	function Sendmail($mailer,$header, $body){
	
		switch($mailer->Mailer) {
	      case 'sendmail':
	        $sent = $mailer->SendmailSend($header, $body);
	        break;
	      case 'smtp':
	        $sent = $mailer->SmtpSend($header, $body);
	        break;
	      case 'mail':
	        $sent = $mailer->MailSend($header, $body);
	        break;
	      default:
	        $sent = $mailer->MailSend($header, $body);
	        break;
		 }

		 return $sent;
		
	}
	
	
}
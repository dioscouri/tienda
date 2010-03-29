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
	 * @param mixed Boolean
	 * @param array Email content
	 * @return boolean
	 */
	public function sendEmailNotices( $data, $type='1' ) 
	{
		$mainframe = JFactory::getApplication();
		$success = false;
		$done = array();

		// allows the system plugin respond by email to set the message's user
	    if (empty($data->user))
        {
            $data->user = JFactory::getUser();
        }
		$user = $data->user;
		$data->comment = $data->description;
				
		// grab config settings for sender name and email
		$config 	= &TiendaConfig::getInstance();
		$mailfrom 	= $config->get( 'emails_defaultemail', $mainframe->getCfg('mailfrom') );
		$fromname 	= $config->get( 'emails_defaultname', $mainframe->getCfg('fromname') );
		$sitename 	= $config->get( 'sitename', $mainframe->getCfg('sitename') );
		$siteurl 	= $config->get( 'siteurl', JURI::root() );
		
		$recipients = $this->getEmailRecipients( $data->id );
		$content = $this->getEmailContent( $data, $user, $type );

		// trigger event onAfterGetEmailContent 
		$dispatcher=& JDispatcher::getInstance(); 
		$dispatcher->trigger('onAfterGetEmailContent', array( $data, &$content) ); 
		
        // Reply Above line
        // needs to be flush left, i think, if it is one string
        // if we break this up and concatenate it, then we can indent
        $temp_body  = ""; 

		$content->body = $temp_body.$content->body;
		
		// Add signature
		if ($config->get('enable_email_signature'))
		{
			$content->body .= sprintf( JText::_( 'Email Signature' ), $sitename, $siteurl );
		}
				
		for ($i=0; $i<count($recipients); $i++) 
		{
			$recipient = $recipients[$i];
			if (!isset($done[$recipient->email]) && $recipient->email != $user->email) 
			{
				if ( $send = $this->_sendMail( $mailfrom, $fromname, $recipient->email, $content->subject, $content->body ) ) 
				{
					$success = true;
					$done[$recipient->email] = $recipient->email;
				}	
			}
		}		

		return $success;
	}

	/**
	 * Returns an array of user objects
	 * 
	 * @param $data Object
	 * @return array
	 */
	private function getEmailRecipients( $id ) 
	{
		$participants = array();
		return $participants;
	}

	/**
	 * Returns 
	 * 
	 * @param object
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	private function getEmailContent( $data, $user, $type ) 
	{
		$mainframe = JFactory::getApplication();
		$type = strtolower($type);
		
		$return = array();
		$link = JURI::root()."index.php?option=com_scriba&controller=messages&task=view&id=".$data->id;
		$link = JRoute::_( $link, false );
		
		$return = new stdClass();
		$return->body = '';
		$return->subject = '';

		// get config settings
		$config = &TiendaConfig::getInstance();

		$emails_includedescription 		= $config->get( 'emails_includedescription', '0' );
		$emails_descriptionmaxlength 	= $config->get( 'emails_descriptionmaxlength', '-1' );
		$emails_includecomments 		= $config->get( 'emails_includecomments', '0' );
		$emails_commentmaxlength		= $config->get( 'emails_commentmaxlength', '-1' );		
		$sitename 						= $config->get( 'sitename', $mainframe->getCfg('sitename') );
		$siteurl 						= $config->get( 'siteurl', JURI::root() );
								
		switch ($type) 
		{
			case "2": // addcomment
			case "comment":
				$return->subject =  sprintf( JText::_( 'New PM Comment Email Subject' ), $data->id, $user->name );
				$text = JText::_( "Subject" ).": ".$data->title." [#$data->id] \n\n"; 
				
				if ($emails_includecomments) 
				{
					// if include, trim and set body
					if ($emails_commentmaxlength > 0) {
						$text .= JString::substr( stripslashes( $data->comment ), 0, $emails_commentmaxlength );
					} else {
						$text .= stripslashes( $data->comment );
					}
					$return->body = $text."\n\n--\n";
					$return->body .= sprintf( JText::_( 'New PM Comment Email Body' ), $user->name, $sitename, $link);
				} 
					else 
				{
					$return->body = sprintf( JText::_( 'New PM Comment Email Body' ), $user->name, $sitename, $link);
				}
			  break;
			case "1": // new
			default:
				$return->subject =  sprintf( JText::_( 'New PM Message Email Subject' ), $data->id, $user->name );
				$text = JText::_( "Subject" ).": ".$data->title." [#$data->id] \n\n";
				
				if ($emails_includedescription) {
					// if include, trim and set body
					if ($emails_descriptionmaxlength > 0) {
						$text = JString::substr( stripslashes( $data->comment ), 0, $emails_descriptionmaxlength );
					} else {
						$text = stripslashes( $data->comment );
					}
					$return->body = $text."\n\n--\n";
					$return->body .= sprintf( JText::_( 'New PM Message Email Body' ), $user->name, $sitename, $link);
				} else {
					// else 
					$return->body = sprintf( JText::_( 'New PM Message Email Body' ), $user->name, $sitename, $link);
				}
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
		
        // convert bbcode if present
        $fulltext = $body;
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'onBBCode_RenderText', array(&$fulltext) );
        $body = $fulltext;
		
		// check user mail format type, default html
		$type = $this->checkEmailFormat();
		$mailer->IsHTML($type);
		if ($type)
		{
			$mailer->setBody( nl2br( $body ) ); // only do this if HTML, otherwise take out the nl2br
		} else {
			$mailer->setBody( ( $body ) );
		}

		$sender = array( $from, $fromname );
		$mailer->setSender($sender);
		$sent = $mailer->send();
		if ($sent == '1') {
			$success = true;
		}
		return $success;
	}
	
	/**
	 * Check if sendMail should be html or text
	 * @return boolean
	 */
	private function checkEmailFormat()
	{
		$success = true;
		$user = JFactory::getUser();
		JLoader::import( 'com_scriba.library.settings', JPATH_ADMINISTRATOR.DS.'components' );
		$settings = TiendaSettings::getInstance( $userid );
		$html = $settings->get('html_email');
		
		if ($n != '1')
		{ 
			$success = false;
		}
			
		// all else fails say yes to html!!
		return $success;
	}
}
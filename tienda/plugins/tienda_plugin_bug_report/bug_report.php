<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.plugin.plugin');

/**
 * plgTiendaCharts_fusioncharts class.
 *
 * @extends JPlugin
 */
class plgTiendaBug_report extends JPlugin
{
    /**
     * constructor function
     * 
     * @param $subject
     * @param $options
     * @return void
     */
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage( '', JPATH_ADMINISTRATOR );
    }

	/**
	 * onAfterFooter function.
	 * 
	 * @access public
	 * @return string
	 */
	function onAfterFooter()
    {
    	ob_start();
    	include(dirname(__FILE__).'/bug_report/tmpl/default.php');
    	$text = ob_get_contents();
    	ob_end_clean();
        
        return $text;
	}
	
	/**
	 * Displays the submit bug form
	 */
	function submitBug()
	{
		JRequest::setVar('tienda_display_submenu', 1 );
		Tienda::load( 'TiendaViewBase', 'views._base' );
		$view = new TiendaViewBase();
		$view->displayTitle(JText::_('COM_TIENDA_SUBMIT_BUG'));

		unset($view);
		
		ob_start();
    	include(dirname(__FILE__).'/bug_report/tmpl/submitbug.php');
    	$text = ob_get_contents();
    	ob_end_clean();
        
        return $text;
	}
	
	/**
	 * Submits a bug report to Dioscouri.
	 * Thanks so much!  They really help improve the product!
	 */
	function sendBug()
	{
		$mainframe = JFactory::getApplication();
		
		$body = JRequest::getVar('body');
		$name = JRequest::getVar('title');
		
		$body .= "\n\n Project: tienda";
		$body .= "\n Tracker: Bug";
		$body .= "\n Affected Version: ".Tienda::getVersion();
		
		$doc = JDocument::getInstance('raw');
		
		ob_start();
		$option = JRequest::getCmd('option');
		
		$db = JFactory::getDBO();
		$path = JPATH_ADMINISTRATOR.'/components/com_admin/';
		
		require_once($path.'admin.admin.html.php');
		
		$path .= 'tmpl/';
		
		require_once($path.'sysinfo_system.php');
		require_once($path.'sysinfo_directory.php');
		require_once($path.'sysinfo_phpinfo.php');
		require_once($path.'sysinfo_phpsettings.php');
		require_once($path.'sysinfo_config.php');
		jimport('joomla.filesystem.file');
		
		$contents = ob_get_contents();
		
		
		ob_end_clean();
		
		$doc->setBuffer($contents);
		$contents = $doc->render();
		
		$sitename 	= $config->get( 'sitename', $mainframe->getCfg('sitename') );
		
		// write file with info
		$config = JFactory::getConfig();
		$filename = 'system_info_'.$sitename.'.html';
		$file = JPATH_SITE.'/tmp/'.$filename;
		JFile::write($file, $contents);
		
		$mailer = JFactory::getMailer();
		
		$success = false;
		
        // For now, bug submission goes to info@dioscouri.com,
        // but in the future, it will go to projects@dioscouri.com
        // (once we get the Redmine auto-create working properly
        // and format the subject/body of the email properly)
        
		$mailer->addRecipient( 'projects@dioscouri.com' );
		$mailer->setSubject( $name );
		
		$mailfrom 	= $config->get( 'emails_defaultemail', $mainframe->getCfg('mailfrom') );
		$fromname 	= $config->get( 'emails_defaultname', $mainframe->getCfg('fromname') );
		
		// check user mail format type, default html
		$mailer->setBody( $body );
		$mailer->addAttachment($file);
		
		$sender = array( $mailfrom, $fromname );
		$mailer->setSender($sender);
		$sent = $mailer->send();
		if ($sent == '1') {
			$success = true;
		}
		JFile::delete($file);
		
		if($success ){
			$msg = JText::_('COM_TIENDA_BUG_SUBMIT_OK');
			$msgtype = 'message'; 
		} else{
			$msg = JText::_('COM_TIENDA_BUG_SUBMIT_FAIL');
			$msgtype = 'notice';
		}
		
		$mainframe->redirect( JRoute::_('index.php?option=com_tienda&view=dashboard'), $msg, $msgtype );
		
	}

}

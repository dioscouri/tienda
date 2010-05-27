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

jimport('joomla.html.toolbar.button');

class JButtonTienda extends JButton{
	
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Tienda';

	function fetchButton( $type='Tienda', $name = '', $text = '', $task = '', $list = true, $hideMenu = false, $taskName = 'shippingTask' )
	{
		$i18n_text	= JText::_($text);
		$class	= $this->fetchIconClass($name);
		$doTask	= $this->_getCommand($text, $task, $list, $hideMenu, $taskName);

		$html	= "<a href=\"#\" onclick=\"$doTask\" class=\"toolbar\">\n";
		$html .= "<span class=\"$class\" title=\"$i18n_text\">\n";
		$html .= "</span>\n";
		$html	.= "$i18n_text\n";
		$html	.= "</a>\n";

		return $html;
	}
	/**
	 * Get the JavaScript command for the button
	 *
	 * @access	private
	 * @param	string	$name	The task name as seen by the user
	 * @param	string	$task	The task used by the application
	 * @param	???		$list
	 * @param	boolean	$hide
	 * @param	string	$taskName	the task field name
	 * @return	string	JavaScript command string
	 * @since	1.5
	 */
	function _getCommand($name, $task, $list, $hide, $taskName)
	{
		$todo		= JString::strtolower(JText::_( $name ));
		$message	= JText::sprintf( 'Please make a selection from the list to', $todo );
		$message	= addslashes($message);
		$hidecode	= $hide ? 'hideMainMenu();' : '';

		if ($list) {
			$cmd = "javascript:if(document.adminForm.boxchecked.value==0){alert('$message');}else{ $hidecode submitTiendabutton('$task', '$taskName')}";
		} else {
			$cmd = "javascript:$hidecode submitTiendabutton('$task', '$taskName')";
		}


		return $cmd;
	}
}

class TiendaToolBarHelper extends JToolBarHelper {
	
	/**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	* @param boolean True if required to include callinh hideMainMenu()
	* @since 1.0
	*/
	function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true, $x = false, $taskName = 'shippingTask')
	{
		$bar = & JToolBar::getInstance('toolbar');

		//strip extension
		$icon	= preg_replace('#\.[^.]*$#', '', $icon);

		// Add a standard button
		$bar->appendButton( 'Tienda', $icon, $alt, $task, $listSelect, $x, $taskName );
	}
	
	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function addNew($task = 'add', $alt = 'New', $taskName = 'shippingTask')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a new button
		$bar->appendButton( 'Tienda', 'new', $alt, $task, false, false, $taskName );
	}
}
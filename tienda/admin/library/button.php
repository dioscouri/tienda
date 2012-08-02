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

class JButtonTienda extends DSCButton{
	
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	 
	//public $_name = 'Tienda';
	
	
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
		$message	= JText::sprintf( 'COM_TIENDA_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO', $todo );
		$message	= addslashes($message);

		if ($list) {
			$cmd = "javascript:if(document.adminForm.boxchecked.value==0){alert('$message');}else{ submitTiendabutton('$task', '$taskName')}";
		} else {
			$cmd = "submitTiendabutton('$task', '$taskName')";
		}


		return $cmd;
	}
}

class TiendaToolBarHelper extends DSCToolBarHelper {
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	//protected $_name = 'Tienda';
	
	
	/* IN Joomla 1.5 STATIC::$_name Causes a fatal error, so extending directly here to avoid that.
	 * ALSO in JOOMLA 1.5 $_name is public so that causes fatal error too, so  for now I 
	 *  */
	
	public static  function _custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true, $x = false, $taskName = 'shippingTask')
	{
		
		
		$bar = JToolBar::getInstance('toolbar');

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
	public static  function _addNew($task = 'add', $alt = 'New', $taskName = 'shippingTask')
	{
		$bar = JToolBar::getInstance('toolbar');
		// Add a new button
		$bar->appendButton( 'Tienda', 'new', $alt, $task, false, false, $taskName );
	}
	
}
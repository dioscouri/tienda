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

class TiendaViewBase extends DSCViewAdmin 
{
	/**
	 * Displays a layout file
	 *
	 * @param unknown_type $tpl
	 * @return unknown_type
	 */
	function display($tpl = null) 
	{
		//including core JS because it needs to be included in modals and since we have so many including here keeps that from failing. 
		JHTML::_('behavior.modal');
		JHTML::_('script', 'core.js', 'media/system/js/');
		DSC::loadBootstrap(1,'2.2.1');
		
		parent::display($tpl);

	}

	/**
	 * The default toolbar for a list
	 * @return unknown_type
	 */
	function _defaultToolbar() 
	{
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_('COM_TIENDA_VALID_DELETE_ITEMS'));
		JToolBarHelper::addnew();
	}

	/**
	 * The default toolbar for editing an item
	 * @param $isNew
	 * @return unknown_type
	 */
	function _formToolbar($isNew = null) 
	{
		$divider = false;
		$surrounding = (!empty($this -> surrounding)) ? $this -> surrounding : array();
		if (!empty($surrounding['prev'])) {
			$divider = true;
			JToolBarHelper::custom('saveprev', "saveprev", "saveprev", 'COM_TIENDA_SAVE_PLUS_PREV', false);
		}
		if (!empty($surrounding['next'])) {
			$divider = true;
			JToolBarHelper::custom('savenext', "savenext", "savenext", 'COM_TIENDA_SAVE_PLUS_NEXT', false);
		}
		if ($divider) {
			JToolBarHelper::divider();
		}

		JToolBarHelper::custom('savenew', "savenew", "savenew", 'COM_TIENDA_SAVE_PLUS_NEW', false);
		JToolBarHelper::save('save');
		JToolBarHelper::apply('apply');

		if ($isNew) {
			JToolBarHelper::cancel();
		} else {
			JToolBarHelper::cancel('close', 'COM_TIENDA_CLOSE');
		}
	}

	/**
	 * The default toolbar for viewing an item
	 * @param $isNew
	 * @return unknown_type
	 */
	function _viewToolbar($isNew = null) 
	{
		$divider = false;
		$surrounding = (!empty($this -> surrounding)) ? $this -> surrounding : array();
		if (!empty($surrounding['prev'])) {
			$divider = true;
			JToolBarHelper::custom('prev', "prev", "prev", JText::_('COM_TIENDA_PREV'), false);
		}
		if (!empty($surrounding['next'])) {
			$divider = true;
			JToolBarHelper::custom('next', "next", "next", JText::_('COM_TIENDA_NEXT'), false);
		}
		if ($divider) {
			JToolBarHelper::divider();
		}

		JToolBarHelper::cancel('close', JText::_('COM_TIENDA_CLOSE'));
	}

}

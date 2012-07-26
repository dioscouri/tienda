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

class TiendaViewBase extends DSCViewAdmin {
	/**
	 * Displays a layout file
	 *
	 * @param unknown_type $tpl
	 * @return unknown_type
	 */
	function display($tpl = null) {
		JHTML::_('stylesheet', 'admin.css', 'media/com_tienda/css/');
		//including core JS because it needs to be included in modals and since we have so many including here keeps that from failing. 
		 JHTML::_('behavior.modal');
		JHTML::_('script', 'core.js', 'media/system/js/');
		DSC::loadBootstrap();
		
		parent::display($tpl);

	}

	/**
	 * Basic commands for displaying a list
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function _default($tpl = '') {
		$model = $this -> getModel();

		// set the model state
		$state = $model -> getState();
		JFilterOutput::objectHTMLSafe($state);
		$this -> assign('state', $state);

		if (empty($this -> hidemenu)) {
			// add toolbar buttons
			$this -> _defaultToolbar();
		}

		// page-navigation
		$this -> assign('pagination', $model -> getPagination());

		// list of items
		$this -> assign('items', $model -> getList());

		// form
		$validate = JUtility::getToken();
		$form = array();
		$controller = strtolower($this -> get('_controller', JRequest::getVar('controller', JRequest::getVar('view'))));
		$view = strtolower($this -> get('_view', JRequest::getVar('view')));
		$action = $this -> get('_action', "index.php?option=com_tienda&controller={$controller}&view={$view}");
		$form['action'] = $action;
		$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
		$this -> assign('form', $form);
	}

	/**
	 * Basic methods for displaying an item from a list
	 * @param $tpl
	 * @return unknown_type
	 */
	/*function _form($tpl='')
	 {
	 $model = $this->getModel();

	 // set the model state
	 $state = $model->getState();
	 JFilterOutput::objectHTMLSafe( $state );
	 $this->assign( 'state', $state );

	 // get the data
	 // not using getItem here to enable ->checkout (which requires JTable object)
	 $row = $model->getTable();
	 $row->load( (int) $model->getId() );
	 // TODO Check if the item is checked out and if so, setlayout to view

	 if (empty($this->hidemenu))
	 {
	 // set toolbar
	 $layout = $this->getLayout();
	 $isNew = ($row->id < 1);
	 switch(strtolower($layout))
	 {
	 case "view":
	 $this->_viewToolbar($isNew);
	 break;
	 case "form":
	 default:
	 // Checkout the item if it isn't already checked out
	 $row->checkout( JFactory::getUser()->id );
	 $this->_formToolbar($isNew);
	 break;
	 }
	 $view = strtolower( JRequest::getVar('view') );
	 $this->displayTitle( 'Edit '.$view );
	 }

	 // form
	 $validate = JUtility::getToken();
	 $form = array();
	 $controller = strtolower( $this->get( '_controller', JRequest::getVar('controller', JRequest::getVar('view') ) ) );
	 $view = strtolower( $this->get( '_view', JRequest::getVar('view') ) );
	 $action = $this->get( '_action', "index.php?option=com_tienda&controller={$controller}&view={$view}&layout=form&id=".$model->getId() );
	 $form['action'] = $action;
	 $form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
	 $form['id'] = $model->getId();
	 $this->assign( 'form', $form );
	 $this->assign('row', $model->getItem() );

	 // set the required image
	 // TODO Fix this
	 $required = new stdClass();
	 $required->text = JText::_('COM_TIENDA_REQUIRED');
	 $required->image = "<img src='".JURI::root()."/media/com_tienda/images/required_16.png' alt='{$required->text}'>";
	 $this->assign('required', $required );
	 }*/

	/**
	 * The default toolbar for a list
	 * @return unknown_type
	 */
	function _defaultToolbar() {
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_('COM_TIENDA_VALID_DELETE_ITEMS'));
		JToolBarHelper::addnew();
	}

	/**
	 * The default toolbar for editing an item
	 * @param $isNew
	 * @return unknown_type
	 */
	function _formToolbar($isNew = null) {
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
	function _viewToolbar($isNew = null) {
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

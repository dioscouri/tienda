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

Tienda::load('TiendaViewBase', 'views._base');
JLoader::import('com_tienda.library.select', JPATH_ADMINISTRATOR . DS . 'components');
JLoader::import('com_tienda.library.grid', JPATH_ADMINISTRATOR . DS . 'components');
JLoader::import('com_tienda.library.url', JPATH_ADMINISTRATOR . DS . 'components');

class TiendaViewOrders extends TiendaViewBase
{
	/**
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function getLayoutVars($tpl=null)
	{
		$layout = $this->getLayout();
		switch(strtolower($layout))
		{
			case "confirmdelete":
				JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'));
				JToolBarHelper::cancel('close', JText::_('Close'));
				$validate = JUtility::getToken();
				$form = array();
				$controller = strtolower($this->get('_controller', JRequest::getVar('controller', JRequest::getVar('view'))));
				$view = strtolower($this->get('_view', JRequest::getVar('view')));
				$action = $this->get('_action', "index.php?option=com_tienda&controller={$controller}&view={$view}");
				$form['action'] = $action;
				$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
				$this->assign('form', $form);
				break;
			case "print":

			case "view":
				$this->_form($tpl);
				break;
			case "form_addresses":
				JRequest::setVar('hidemainmenu', '1');
				$this->_formAddresses($tpl);
				break;
			case "form":
				JRequest::setVar('hidemainmenu', '1');
				$this->_form($tpl);
				break;
			case "batchedit":
				JRequest::setVar('hidemainmenu', '1');
				$this->_batchedit($tpl);
				break;
			case "default":

			default:
				$this->set('leftMenu', 'leftmenu_orders');
				$this->_default($tpl);
				break;
		}
	}

	/**
	 * The default toolbar for a list
	 * @return unknown_type
	 */
	function _defaultToolbar()
	{
		JToolBarHelper::custom('batchedit', "forward", "forward", JText::_('Batch Edit'), false);
		JToolBarHelper::divider();
		JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'));		
		$class_name = 'new';
		$text = JText::_('COM_TIENDA_NEW');
		$url = "index.php?option=com_tienda&view=pos";
		$bar = &JToolBar::getInstance('toolbar');
		$bar->appendButton('link', $class_name, $text, $url);
	}

	/**
	 * Process the data for the convert view
	 * @return void
	 **/
	function _batchedit($tpl=null)
	{
		// Import necessary helpers + library files
		JLoader::import('com_tienda.library.select', JPATH_ADMINISTRATOR . DS . 'components');
		JLoader::import('com_tienda.library.grid', JPATH_ADMINISTRATOR . DS . 'components');
		JLoader::import('com_tienda.library.url', JPATH_ADMINISTRATOR . DS . 'components');
		$model = $this->getModel();

		// set the model state
		$this->assign('state', $model->getState());

		// page-navigation
		$this->assign('pagination', $model->getPagination());

		// list of items
		$items = $model->getList();
		$this->assign('items', $items);

		// set toolbar
		$this->_batcheditToolbar();

		// form
		$validate = JUtility::getToken();
		$form = array();
		$controller = strtolower($this->get('_controller', JRequest::getVar('controller', JRequest::getVar('view'))));
		$view = strtolower($this->get('_view', JRequest::getVar('view')));
		$action = $this->get('_action', "index.php?option=com_tienda&controller={$controller}&view={$view}");
		$form['action'] = $action;
		$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
		$this->assign('form', $form);
	}

	function _batcheditToolbar()
	{
		$this->set('title', "Order Batch Edit");
		JToolBarHelper::save('updatebatch');
		JToolBarHelper::cancel();
	}

	function _formAddresses($tpl=null)
	{
		$model = $this->getModel();

		// set the model state
		$state = $model->getState();
		JFilterOutput::objectHTMLSafe($state);
		$this->assign('state', $state);

		// get the data
		// not using getItem here to enable ->checkout (which requires JTable object)
		$row = $model->getTable();
		$row->load((int)$model->getId());
		// TODO Check if the item is checked out and if so, setlayout to view

		$this->displayTitle('Edit Addresses');
		JToolBarHelper::save('saveAddresses');
		JToolBarHelper::cancel('closeEditAddresses', JText::_('Close'));

		// form
		$validate = JUtility::getToken();
		$form = array();
		$controller = strtolower($this->get('_controller', JRequest::getVar('controller', JRequest::getVar('view'))));
		$view = strtolower($this->get('_view', JRequest::getVar('view')));
		$action = $this->get('_action', "index.php?option=com_tienda&controller={$controller}&view={$view}&layout=form&id=" . $model->getId());
		$form['action'] = $action;
		$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
		$form['id'] = $model->getId();
		$this->assign('form', $form);
		$this->assign('row', $model->getItem());
	}

}

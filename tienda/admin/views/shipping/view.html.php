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

JLoader::import( 'com_tienda.views._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaViewShipping extends TiendaViewBase
{
	function _form($tpl=null)
	{
		$model = $this->getModel();

		// set the model state
		$this->assign( 'state', $model->getState() );

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

		$this->assign('row', $model->getItem() );

		// load the plugin
		$row = $this->getModel()->getItem();
		$import = JPluginHelper::importPlugin( 'tienda', $row->element );
	}

	function _defaultToolbar()
	{
	}

	function _viewToolbar()
	{
	}
}

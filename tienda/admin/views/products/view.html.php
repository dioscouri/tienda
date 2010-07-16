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

Tienda::load( 'TiendaViewBase', 'views._base' );

class TiendaViewProducts extends TiendaViewBase
{
	function _form($tpl=null)
	{
		parent::_form($tpl);

		$model = $this->getModel();

		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'onGetProductView', array( $model->getItem()  ) );

		$shippingHtml = implode('<hr />', $results);
		$this->assign('shippingHtml', $shippingHtml);
		
		// Getting the event HTML for mapping
		$dispatcher = JDispatcher::getInstance();
		$html = $dispatcher->trigger( 'onGetProductJeventView', array( $model) );
		$this->assign('eventHtml', $html);
	}

	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/views/TiendaViewBase#_defaultToolbar()
	 */
	function _defaultToolbar()
	{
		JToolBarHelper::publishList( 'product_enabled.enable' );
		JToolBarHelper::unpublishList( 'product_enabled.disable' );
		JToolBarHelper::divider();
		parent::_defaultToolbar();
	}

	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/views/TiendaViewBase#_formToolbar($isNew)
	 */
	function _formToolbar( $isNew=null )
	{
		$model = $this->getModel();
		if ($model->getId())
		{
			JToolBarHelper::custom( 'view', 'edit', 'edit', JText::_( 'Dashboard' ), false);
			JToolBarHelper::divider();
		}
		 
		$divider = false;
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		$surrounding = TiendaHelperProduct::getSurrounding( $model->getId() );
		if (!empty($surrounding['prev']))
		{
			$divider = true;
			JToolBarHelper::custom('saveprev', "saveprev", "saveprev", JText::_( 'Save + Prev' ), false);
		}
		if (!empty($surrounding['next']))
		{
			$divider = true;
			JToolBarHelper::custom('savenext', "savenext", "savenext", JText::_( 'Save + Next' ), false);
		}
		if ($divider)
		{
			JToolBarHelper::divider();
		}
		parent::_formToolbar($isNew);
	}

	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/views/TiendaViewBase#_viewToolbar($isNew)
	 */
	function _viewToolbar( $isNew=null )
	{
		$model = $this->getModel();
		$divider = false;
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		$surrounding = TiendaHelperProduct::getSurrounding( $model->getId() );
		if (!empty($surrounding['prev']))
		{
			$divider = true;
			JToolBarHelper::custom('prev', "prev", "prev", JText::_( 'Prev' ), false);
		}
		if (!empty($surrounding['next']))
		{
			$divider = true;
			JToolBarHelper::custom('next', "next", "next", JText::_( 'Next' ), false);
		}
		if ($divider)
		{
			JToolBarHelper::divider();
		}
		$this->assign('surrounding', $surrounding);
		JToolBarHelper::custom( 'edit', 'edit', 'edit', JText::_( 'Edit' ), false);
		JToolBarHelper::cancel( 'close', JText::_( 'Close' ) );
	}
}

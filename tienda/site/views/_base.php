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

jimport( 'joomla.filter.filteroutput' );
jimport( 'joomla.application.component.view' );

class TiendaViewBase extends DSCViewSite
{
	

	/**
	 * First displays the submenu, then displays the output
	 * but only if a valid _doTask is set in the view object
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function display($tpl=null, $perform = true )
	{
		//JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
		
		$parentPath = JPATH_ADMINISTRATOR . '/components/com_tienda/helpers';
		DSCLoader::discover('TiendaHelper', $parentPath, true);
		
		$parentPath = JPATH_ADMINISTRATOR . '/components/com_tienda/library';
		DSCLoader::discover('Tienda', $parentPath, true);
	
			if( $perform )
			{
				$this->getLayoutVars($tpl);
	
				Tienda::load( 'TiendaMenu', 'library.menu' );
	
				if (!JRequest::getInt('hidemainmenu') && empty($this->hidemenu))
				{
					$this->displaySubmenu();
				}
			}
	
		parent::display($tpl);
	}

	/**
	 * Displays a submenu if there is one and if hidemainmenu is not set
	 *
	 * @param $selected
	 * @return unknown_type
	 */
	function displaySubmenu($selected='')
	{
		if (!JRequest::getInt('hidemainmenu') && empty($this->hidemenu))
		{
			$menu =& TiendaMenu::getInstance();
		}
	}

	


	/**
	 * Basic commands for displaying a list
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function _default($tpl='', $onlyPagination = false )
	{
		Tienda::load( 'TiendaSelect', 'library.select' );
		Tienda::load( 'TiendaGrid', 'library.grid' );
		$model = $this->getModel();

		// set the model state
		$state = $model->getState();
		JFilterOutput::objectHTMLSafe( $state );
		$this->assign( 'state', $state );

		// page-navigation
		$this->assign( 'pagination', $model->getPagination() );

		// list of items
		if( !$onlyPagination )
			$this->assign('items', $model->getList());

		// form
		$validate = JUtility::getToken();
		$form = array();
		$view = strtolower( JRequest::getVar('view') );
		$form['action'] = "index.php?option=com_tienda&controller={$view}&view={$view}";
		$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
		$this->assign( 'form', $form );
	}

	/**
	 * Basic methods for a form
	 * @param $tpl
	 * @return unknown_type
	 */
	function _form($tpl='')
	{
		Tienda::load( 'TiendaSelect', 'library.select' );
		$model = $this->getModel();
		if( isset( $this->row ) ) 
			JFilterOutput::objectHTMLSafe( $this->row );
		else
		{
	
			// get the data
			$row = $model->getItem();
			JFilterOutput::objectHTMLSafe( $row );
			$this->assign('row', $row );
		}

		// form
		$form = array();
		$controller = strtolower( $this->get( '_controller', JRequest::getVar('controller', JRequest::getVar('view') ) ) );
		$view = strtolower( $this->get( '_view', JRequest::getVar('view') ) );
		$task = strtolower( $this->get( '_task', 'edit' ) );
		$form['action'] = $this->get( '_action', "index.php?option=com_tienda&controller={$controller}&view={$view}&task={$task}&id=".$model->getId() );
		$form['validation'] = $this->get( '_validation', "index.php?option=com_tienda&controller={$controller}&view={$view}&task=validate&format=raw" );
		$form['validate'] = "<input type='hidden' name='".JUtility::getToken()."' value='1' />";
		$form['id'] = $model->getId();
		$this->assign( 'form', $form );

		// set the required image
		// TODO Fix this
		$required = new stdClass();
		$required->text = JText::_('COM_TIENDA_REQUIRED');
		$required->image = "<img src='".JURI::root()."/media/com_tienda/images/required_16.png' alt='{$required->text}'>";
		$this->assign('required', $required );
	}

	/**
	 * Basic commands for displaying a auxiliaty layout
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function _default_light($tpl='')
	{
		Tienda::load( 'TiendaSelect', 'library.select' );
		Tienda::load( 'TiendaGrid', 'library.grid' );

		// form
		$validate = JUtility::getToken();
		$form = array();
		$view = strtolower( JRequest::getVar('view') );
		$form['action'] = "index.php?option=com_tienda&controller={$view}&view={$view}";
		$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
		$this->assign( 'form', $form );
	}
}
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
    function __construct( $config=array() )
    {
        parent::__construct( $config );
    
        $this->defines = Tienda::getInstance();
    
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $this->router = new TiendaHelperRoute();
        
        $this->user = JFactory::getUser();
        $this->session = JFactory::getSession();
    }
    
	/**
	 * First displays the submenu, then displays the output
	 * but only if a valid _doTask is set in the view object
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function display($tpl=null, $perform = true )
	{
	    // these need to load before jquery to prevent joomla from crying
	    JHTML::_('behavior.modal');
	    JHTML::_('script', 'core.js', 'media/system/js/');
	    
	    DSC::loadJQuery('latest', true, 'tiendaJQ');
	    
	    if ($this->defines->get('use_bootstrap', '0'))
	    {
	    	DSC::loadBootstrap();
	    }
	    
	    JHTML::_('stylesheet', 'common.css', 'media/dioscouri/css/');
	    
	    if ($this->defines->get('include_site_css', '0'))
	    {
	        JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
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
			$menu = TiendaMenu::getInstance();
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

		if ($onlyPagination || !empty($this->items)) {
		    $this->no_items = true;
		}
		
		parent::_default($tpl);
	}

	/**
	 * Basic methods for a form
	 * @param $tpl
	 * @return unknown_type
	 */
	function _form($tpl='')
	{
		Tienda::load( 'TiendaSelect', 'library.select' );
		Tienda::load( 'TiendaGrid', 'library.grid' );
		$model = $this->getModel();
		if( isset( $this->row ) ) {
			JFilterOutput::objectHTMLSafe( $this->row );
		}
		else
		{
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
		$required->image = TiendaGrid::required( $required->text );
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

		$this->no_items = true;
		$this->no_state = true;
		$this->no_pagination = true;
		
		parent::_default($tpl);
	}
}
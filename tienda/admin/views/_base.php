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

class TiendaViewBase extends JView
{
	/**
	 * Displays a layout file 
	 * 
	 * @param unknown_type $tpl
	 * @return unknown_type
	 */
	function display($tpl=null)
	{
		JHTML::_('stylesheet', 'tienda_admin.css', 'media/com_tienda/css/');
		
        Tienda::load( 'TiendaUrl', 'library.url' );
        Tienda::load( 'TiendaSelect', 'library.select' );
        Tienda::load( 'TiendaGrid', 'library.grid' );
        Tienda::load( 'TiendaMenu', 'library.menu' );
                
        $this->getLayoutVars($tpl);
        if (empty($this->hidemenu))
        {        
            $this->displayTitle( $this->get('title') );
        }
        
		if (!JRequest::getInt('hidemainmenu') && empty($this->hidemenu))
		{
		    Tienda::load( 'TiendaHelperMenu', 'helpers.menu' );
		    $helper = new TiendaHelperMenu();
		    $helper->display( 'submenu' );
		}
        
        jimport( 'joomla.application.module.helper' );		
		$modules = JModuleHelper::getModules("tienda_left");
		if ($modules && !JRequest::getInt('hidemainmenu') || !empty($this->leftMenu) )
		{
			if(@$this->hideleftmenu)
			{
				parent::display($tpl);
			}
			else
			{
				$this->displayWithLeftMenu($tpl=null, @$this->leftMenu);
			}
		}
			else
		{
			parent::display($tpl);
		}
	}

	/**
	 * Displays text as the title of the page
	 * 
	 * @param $text
	 * @return unknown_type
	 */
	function displayTitle( $text = '' )
	{
		$title = $text ? JText::_($text) : JText::_( ucfirst(JRequest::getVar('view')) );
		JToolBarHelper::title( $title, Tienda::getName() );
	}

	/**
	 * Displays a layout file with room for a left menu bar
	 * @param $tpl
	 * @return unknown_type
	 */
    public function displayWithLeftMenu($tpl=null, $menuname=null)
    {
    	// TODO This is an ugly, quick hack - fix it
    	echo "<table width='100%'>";
    		echo "<tr>";
	    		echo "<td style='width: 180px; padding-right: 5px; vertical-align: top;' >";

	    		    Tienda::load( 'TiendaMenu', 'library.menu' );
					if ($menu =& TiendaMenu::getInstance($menuname)) {
					    $menu->display('leftmenu');
					}
					
					$modules = JModuleHelper::getModules("tienda_left");
					$document	= &JFactory::getDocument();
					$renderer	= $document->loadRenderer('module');
					$attribs 	= array();
					$attribs['style'] = 'xhtml';
					foreach ( @$modules as $mod )
					{
						echo $renderer->render($mod, $attribs);
					}

	    		echo "</td>";
	    		echo "<td style='vertical-align: top;' >";
	    			parent::display($tpl);
	    		echo "</td>";
    		echo "</tr>";
    	echo "</table>";
    }

    /**
     * Gets layout vars for the view
     * 
     * @return unknown_type
     */
    function getLayoutVars($tpl=null)
    {
        $layout = $this->getLayout();
        switch(strtolower($layout))
        {
            case "view":
                $this->_form($tpl);
              break;
            case "form":
                JRequest::setVar('hidemainmenu', '1');
                $this->_form($tpl);
              break;
            case "default":
            default:
                $this->_default($tpl);
              break;
        }
    }
    
	/**
	 * Basic commands for displaying a list
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function _default($tpl='')
	{
		$model = $this->getModel();

		// set the model state
            $state = $model->getState();
            JFilterOutput::objectHTMLSafe( $state );
			$this->assign( 'state', $state );

		if (empty($this->hidemenu))
		{
	        // add toolbar buttons
	            $this->_defaultToolbar();
		}

		// page-navigation
			$this->assign( 'pagination', $model->getPagination() );

		// list of items
			$this->assign('items', $model->getList());

		// form
			$validate = JUtility::getToken();
			$form = array();
			$controller = strtolower( $this->get( '_controller', JRequest::getVar('controller', JRequest::getVar('view') ) ) );
			$view = strtolower( $this->get( '_view', JRequest::getVar('view') ) );
			$action = $this->get( '_action', "index.php?option=com_tienda&controller={$controller}&view={$view}" );
			$form['action'] = $action;
			$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
			$this->assign( 'form', $form );
	}

	/**
	 * Basic methods for displaying an item from a list
	 * @param $tpl
	 * @return unknown_type
	 */
	function _form($tpl='')
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
	}

	/**
	 * The default toolbar for a list
	 * @return unknown_type
	 */
	function _defaultToolbar()
	{
		JToolBarHelper::editList();
		JToolBarHelper::deleteList( JText::_('VALIDDELETEITEMS') );
		JToolBarHelper::addnew();
	}

	/**
	 * The default toolbar for editing an item
	 * @param $isNew
	 * @return unknown_type
	 */
	function _formToolbar( $isNew=null )
	{
	    $divider = false;
        $surrounding = (!empty($this->surrounding)) ? $this->surrounding : array();
        if (!empty($surrounding['prev']))
        {
            $divider = true;
            JToolBarHelper::custom('saveprev', "saveprev", "saveprev", JText::_('Save + Prev'), false);
        }
        if (!empty($surrounding['next']))
        {
            $divider = true;
            JToolBarHelper::custom('savenext', "savenext", "savenext", JText::_('Save + Next'), false);
        }
        if ($divider)
        {
            JToolBarHelper::divider();
        }
	    
		JToolBarHelper::custom('savenew', "savenew", "savenew", JText::_('Save + New'), false);
		JToolBarHelper::save('save');
		JToolBarHelper::apply('apply');

		if ($isNew)
		{
			JToolBarHelper::cancel();
		}
			else
		{
			JToolBarHelper::cancel( 'close', JText::_('COM_TIENDA_CLOSE') );
		}
	}

	/**
	 * The default toolbar for viewing an item
	 * @param $isNew
	 * @return unknown_type
	 */
	function _viewToolbar( $isNew=null )
	{
        $divider = false;
        $surrounding = (!empty($this->surrounding)) ? $this->surrounding : array();
        if (!empty($surrounding['prev']))
        {
            $divider = true;
            JToolBarHelper::custom('prev', "prev", "prev", JText::_('Prev'), false);
        }
        if (!empty($surrounding['next']))
        {
            $divider = true;
            JToolBarHelper::custom('next', "next", "next", JText::_('Next'), false);  
        }
        if ($divider)
        {
            JToolBarHelper::divider();
        }
        
        JToolBarHelper::cancel( 'close', JText::_('COM_TIENDA_CLOSE') );
	}
}
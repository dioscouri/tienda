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
		
        JLoader::import( 'com_tienda.library.url', JPATH_ADMINISTRATOR.DS.'components' );
        JLoader::import( 'com_tienda.library.select', JPATH_ADMINISTRATOR.DS.'components' );
        JLoader::import( 'com_tienda.library.grid', JPATH_ADMINISTRATOR.DS.'components' );
        
        $this->getLayoutVars($tpl);
        
		$this->displayTitle( $this->get('title') );

		if (!JRequest::getInt('hidemainmenu') && empty($this->hidemenu))
		{
			$this->displayMenubar();
		}
        
        jimport( 'joomla.application.module.helper' );		
		$modules = JModuleHelper::getModules("tienda_left");
		if ($modules && !JRequest::getInt('hidemainmenu') || !empty($this->leftMenu))
		{
			$this->displayWithLeftMenu($tpl=null);
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
	 * Gets the default submenu bar
	 * @return array
	 */
	function getMenubar()
	{
		$views = array();
		$views['dashboard']			= 'Dashboard';
		$views['users']				= 'Users';
		$views['orders']			= 'Orders';
		$views['products']  		= 'Products';
		$views['categories']		= 'Categories';
		$views['manufacturers']		= 'Manufacturers';
		$views['localization']		= 'Localization';
		$views['reports']           = 'Reports';
		$views['config']			= 'Configuration';
		return $views;
	}

	/**
	 * Displays the default submenu bar 
	 * @return void
	 */
	function displayMenubar()
	{
		$views = $this->getMenubar();

		$left = array();
		if (isset($this->leftMenu)) { $left = $this->getLeftMenubar(); }

		foreach($views as $view => $title)
		{
			$current = strtolower( JRequest::getVar('view') );
			$active = ($view == $current );
			if (array_key_exists($current, $left) && $view == 'localization' ) { $active = true; }
			JSubMenuHelper::addEntry(JText::_($title), 'index.php?option=com_tienda&view='.$view, $active );
		}
	}

	/**
	 * Gets the selected left menu bar
	 * TODO This can be consolidated with getMenuBar
	 * 
	 * @param unknown_type $type
	 * @return unknown_type
	 */
	function getLeftMenubar( $type='' )
	{
		$views  = array();
		$views['currencies']		= 'Currencies';
		$views['countries']			= 'Countries';
		$views['zones']				= 'Zones';
		$views['geozones']			= 'Geo Zones';
		$views['orderstates']		= 'Order States';
		$views['taxclasses']		= 'Tax Classes';
		$views['shippingmethods']   = 'Shipping Methods';

		return $views;
	}

	/**
	 * Displays the left menu bar
	 * @param $name
	 * @return unknown_type
	 */
	function displayLeftMenubar($name='leftmenu')
	{
		JLoader::import( 'com_tienda.library.menu', JPATH_ADMINISTRATOR.DS.'components' );

		$views = $this->getLeftMenubar();

		foreach($views as $view => $title)
		{
			$active = ($view == strtolower( JRequest::getVar('view') ) );
			TiendaMenu::addEntry(JText::_($title), 'index.php?option=com_tienda&view='.$view, $active, $name );
		}

		echo TiendaMenu::display( $name, "{$name}_admin.css" );
	}

	/**
	 * Displays a layout file with room for a left menu bar
	 * @param $tpl
	 * @return unknown_type
	 */
    public function displayWithLeftMenu($tpl=null)
    {
    	// TODO This is an ugly, quick hack - fix it
    	echo "<table width='100%'>";
    		echo "<tr>";
	    		echo "<td style='width: 180px; padding-right: 5px; vertical-align: top;' >";

					$this->displayLeftMenubar();

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
			$this->assign( 'state', $model->getState() );

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
			$required->text = JText::_( 'Required' );
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
		JToolBarHelper::deleteList( JText::_( 'VALIDDELETEITEMS' ) );
		JToolBarHelper::addnew();
	}

	/**
	 * The default toolbar for editing an item
	 * @param $isNew
	 * @return unknown_type
	 */
	function _formToolbar( $isNew=null )
	{
		JToolBarHelper::custom('savenew', "savenew", "savenew", JText::_( 'Save + New' ), false);
		JToolBarHelper::save('save');
		JToolBarHelper::apply('apply');

		if ($isNew)
		{
			JToolBarHelper::cancel();
		}
			else
		{
			JToolBarHelper::cancel( 'close', JText::_( 'Close' ) );
		}
	}

	/**
	 * The default toolbar for viewing an item
	 * @param $isNew
	 * @return unknown_type
	 */
	function _viewToolbar( $isNew=null )
	{
		JToolBarHelper::cancel( 'close', JText::_( 'Close' ) );
	}
}
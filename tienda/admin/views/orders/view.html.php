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
        	case "print":
            case "view":
                $this->_form($tpl);
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
                $this->set( 'leftMenu', 'leftmenu_orders' );
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
        JToolBarHelper::custom('batchedit', "forward", "forward", JText::_( 'Batch Edit' ), false);
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/views/TiendaViewBase#_viewToolbar($isNew)
     */
    function _viewToolbar( $isNew=null )
    {
        $divider = false;
        $model = $this->getModel();
        Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
        $surrounding = TiendaHelperOrder::getSurrounding( $model->getId() );
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
        parent::_viewToolbar($isNew);
    }
    
    
    /**
	 * Process the data for the convert view
	 * @return void
	 **/
	function _batchedit($tpl=null)
	{
		// Import necessary helpers + library files
		JLoader::import( 'com_tienda.library.select', JPATH_ADMINISTRATOR.DS.'components' );
		JLoader::import( 'com_tienda.library.grid', JPATH_ADMINISTRATOR.DS.'components' );
		JLoader::import( 'com_tienda.library.url', JPATH_ADMINISTRATOR.DS.'components' );
		$model = $this->getModel();
		
		// set the model state
			$this->assign( 'state', $model->getState() );
			
		// page-navigation
			$this->assign( 'pagination', $model->getPagination() );
		
		// list of items
			$items = $model->getList();	
			$this->assign('items', $items);
			
		// set toolbar
			$this->_batcheditToolbar();
			
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
	
	function _batcheditToolbar()
	{
		$this->set('title', "Order Batch Edit" );
		JToolBarHelper::save('updatebatch');
		JToolBarHelper::cancel();
	}
    
    
    
    
    
}

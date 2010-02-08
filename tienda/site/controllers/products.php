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
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerProducts extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'products');
	}
	
	/**
	 * Sets the model's state
	 * 
	 * @return array()
	 */
	function _setModelState()
	{    	
		$state = parent::_setModelState();   	
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		$state['filter_enabled']  = 1;
        $state['search']          = $app->getUserStateFromRequest($ns.'.search', 'search', '', '');
        $state['filter_category'] = $app->getUserStateFromRequest($ns.'.category', 'filter_category', '', '');
        
        if ($state['search']) {
            $state['filter']      = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');   
        } else {
            $state['filter']      = '';
        }
        
		if ($state['filter_category']) {
			JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
			$cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
			$cmodel->setId($state['filter_category']);
			$item = $cmodel->getItem();
			$state['category_name'] = $item->category_name;
		} elseif (!$state['search']) {
            $state['filter_category'] = '0';
		}
		    			
		foreach (@$state as $key=>$value) {
			$model->setState( $key, $value );	
		}
		
  		return $state;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/TiendaController#display($cachable)
	 */
	function display()
	{
		JRequest::setVar( 'view', $this->get('suffix') );
		JRequest::setVar( 'layout', 'default' );
		JRequest::setVar( 'search', false );
		parent::display();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tienda/site/TiendaController#view()
	 */
	function view() 
	{
		JRequest::setVar( 'view', $this->get('suffix') );
		$model 	= $this->getModel( $this->get('suffix') );
	    $model->getId();
	    $row = $model->getItem();
        if (empty($row->product_enabled))
        {
            $redirect = "index.php?option=com_tienda&view=products";
            $redirect = JRoute::_( $redirect, false );
            $this->message = JText::_( "Invalid Product" );
            $this->messagetype = 'notice';
            JFactory::getApplication()->redirect( $redirect, $this->message, $this->messagetype );
            return;
        }
        
		JLoader::import( 'com_tienda.library.article', JPATH_ADMINISTRATOR.DS.'components' );
		$product_description = TiendaArticle::fromString( $row->product_description );
        
		$filter_category = $model->getState('filter_category', JRequest::getVar('filter_category'));
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
        $cat = $cmodel->getTable();
        $cat->load( $filter_category );       
		
        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
        $view->set('_doTask', true);
        $view->assign('row', $row);
        $view->assign( 'cat', $cat );
        $view->assign('product_description', $product_description );
        $view->setModel( $model, true );
        $view->setLayout('view');
        $view->display();
        $this->footer();
        return;
	}
	
//	/**
//	 * 
//	 * @return unknown_type
//	 */
//    function quickadd() 
//    {
//        $model  = $this->getModel( $this->get('suffix') );
//        $model->getId();
//        $row = $model->getItem();
//        if (empty($row->product_enabled))
//        {
//            $redirect = "index.php?option=com_tienda&view=products";
//            $redirect = JRoute::_( $redirect, false );
//            $this->message = JText::_( "Invalid Product" );
//            $this->messagetype = 'notice';
//            JFactory::getApplication()->redirect( $redirect, $this->message, $this->messagetype );
//            return;
//        }
//        
//        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
//        $view->set('hidemenu', true);
//        $view->set('_doTask', true);
//        $view->setModel( $model, true );
//        $view->setLayout('quickadd');
//        $view->display();
//        $this->footer();
//        return;
//    }

    /**
     * 
     * @return void
     */
    function search() 
    {
        JRequest::setVar( 'view', $this->get('suffix') );
        JRequest::setVar( 'layout', 'search' );
        JRequest::setVar( 'search', true );
        parent::display();
    }
}

?>
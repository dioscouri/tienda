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

class TiendaControllerOrders extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct()
	{
        if (empty(JFactory::getUser()->id))
        {
            $url = JRoute::_( "index.php?option=com_tienda&view=orders" );
            $redirect = "index.php?option=com_user&view=login&return=".base64_encode( $url );
            $redirect = JRoute::_( $redirect, false );
            JFactory::getApplication()->redirect( $redirect );
            return;
        }
		
		parent::__construct();
		$this->set('suffix', 'orders');
		$this->registerTask( 'print', 'printOrder' );
	}
	
    /**
     * 
     * @return unknown_type
     */
    function _setModelState()
    {
        $state = parent::_setModelState();      
        $app = JFactory::getApplication();
        $model = $this->getModel( $this->get('suffix') );
        $ns = $this->getNamespace();
        
        $config = TiendaConfig::getInstance();
        // adjust offset for when filter has changed
        if (
            $app->getUserState( $ns.'orderstate' ) != $app->getUserStateFromRequest($ns.'orderstate', 'filter_orderstate', '', '') 
        )
        {
            $state['limitstart'] = '0';
        }

        $state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.created_date', 'cmd');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word');
        
        $state['filter_orderstate'] = $app->getUserStateFromRequest($ns.'orderstate', 'filter_orderstate', '', '');
        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        
        $state['filter_userid']     = JFactory::getUser()->id;
        $filter_userid = $app->getUserStateFromRequest($ns.'userid', 'filter_userid', JFactory::getUser()->id, '');
        
        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_total']      = $app->getUserStateFromRequest($ns.'total', 'filter_total', '', '');
        
        $state['filter_date_from']  = $app->getUserStateFromRequest($ns.'date_from', 'filter_date_from', '', '');
        $state['filter_date_to']    = $app->getUserStateFromRequest($ns.'date_to', 'filter_date_to', '', '');
        $state['filter_datetype']   = $app->getUserStateFromRequest($ns.'datetype', 'filter_datetype', '', '');
        
        
        foreach (@$state as $key=>$value)
        {
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
        $model  = $this->getModel( $this->get('suffix') );
        $this-> _setModelState();
        
        $view = $this->getView( 'orders', 'html' );
        $view->set( '_controller', 'orders' );
        $view->set( '_view', 'orders' );
        $view->set( '_doTask', true);
        $view->set( 'hidemenu', false);
        $view->setModel( $model, true );
        $view->setLayout( 'view' );
        JRequest::setVar( 'view', $this->get('suffix') );
        JRequest::setVar( 'layout', 'default' );
        parent::display();
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/site/TiendaController#view()
     */
    function view() 
    {
    	// if the user cannot view order, fail
        $model  = $this->getModel( $this->get('suffix') );
        $model->getId();
        $row = $model->getItem();
        $user_id = JFactory::getUser()->id;
        if (empty($user_id) || $user_id != $row->user_id)
        {
        	$this->messagetype  = 'notice';
        	$this->message      = JText::_( 'Invalid Order' );
            $redirect = "index.php?option=com_tienda&view=".$this->get('suffix');
            $redirect = JRoute::_( $redirect, false );
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;
        }
        
        Tienda::load( 'TiendaUrl', 'library.url' );
        
        $view = $this->getView( 'orders', 'html' );
        $view->set( '_controller', 'orders' );
        $view->set( '_view', 'orders' );
        $view->set( '_doTask', true);
        $view->set( 'hidemenu', false);
        $view->setModel( $model, true );
        $view->setLayout( 'view' );
        JRequest::setVar( 'view', $this->get('suffix') );
        JRequest::setVar( 'layout', 'view' );
        parent::display();
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/site/TiendaController#view()
     */
    function printOrder() 
    {
        // if the user cannot view order, fail
        $model  = $this->getModel( $this->get('suffix') );
        $model->getId();
        $row = $model->getItem();
        $user_id = JFactory::getUser()->id;
        if (empty($user_id) || $user_id != $row->user_id)
        {
            $this->messagetype  = 'notice';
            $this->message      = JText::_( 'Invalid Order' );
            $redirect = "index.php?option=com_tienda&view=".$this->get('suffix');
            $redirect = JRoute::_( $redirect, false );
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;
        }
        Tienda::load( 'TiendaUrl', 'library.url' );
        JRequest::setVar( 'view', $this->get('suffix') );
        JRequest::setVar( 'layout', 'print' );   
        parent::display();
    }
}
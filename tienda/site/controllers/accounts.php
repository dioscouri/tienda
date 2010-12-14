<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerAccounts extends TiendaController 
{
    /**
     * constructor
     */
    function __construct() 
    {
        if (empty(JFactory::getUser()->id))
        {
            $url = JRoute::_( "index.php?option=com_tienda&view=accounts" );
            $redirect = "index.php?option=com_user&view=login&return=".base64_encode( $url );
            $redirect = JRoute::_( $redirect, false );
            JFactory::getApplication()->redirect( $redirect );
            return;
        }
    	
        parent::__construct();
        $this->set('suffix', 'accounts');
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/TiendaController#_setModelState()
     */
    function _setModelState()
    {
        $state = parent::_setModelState();      
        $app = JFactory::getApplication();
        $model = $this->getModel( $this->get('suffix') );
        $ns = $this->getNamespace();
        
        $state['filter_userid']     = JFactory::getUser()->id;
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }
    
    function display()
    {
        $uri = JURI::getInstance();
        
        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setLayout('default');
        
        $url = "index.php?option=com_user&view=user&task=edit";
        
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $helper = TiendaHelperBase::getInstance( 'Ambra' );
        if ($helper->isInstalled())
        {
            $url = "index.php?option=com_ambra&view=users&task=edit&return=" . base64_encode( $uri->toString() );
        }
        $view->assign( 'url_profile', $url );
        
        parent::display();
    }
        
    /**
     * @return void
     */
    function edit() 
    {
        $model  = $this->getModel( $this->get('suffix') );
        $row = $model->getTable();
        $row->load( array( 'user_id' => JFactory::getUser()->id ) );
        
        JRequest::setVar('id', $row->user_info_id );
    	JRequest::setVar('view', 'accounts');
    	JRequest::setVar('layout', 'form');
        parent::display();
    }
    
    /**
     * Saves an item and redirects based on task
     * @return void
     */
    function save()
    {
        $model  = $this->getModel( $this->get('suffix') );
        $row = $model->getTable();
        $row->load( array( 'user_id' => JFactory::getUser()->id ) );
        $row->bind( $_POST );
        $row->user_id = JFactory::getUser()->id;

        if ( $row->save() )
        {
            $model->setId( $row->user_id );
            $this->messagetype  = 'message';
            $this->message      = JText::_( 'Saved' );

            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
        }
            else
        {
            $this->messagetype  = 'notice';
            $this->message      = JText::_( 'Save Failed' )." - ".$row->getError();
        }

        $redirect = "index.php?option=com_tienda";
        $task = JRequest::getVar('task');
        switch ($task)
        {
            case "save":
            default:
                $redirect .= "&view=".$this->get('suffix');
              break;
        }

        $redirect = JRoute::_( $redirect, false );
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
}
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

class TiendaControllerTaxclasses extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'taxclasses');
	}

    /*
     * Creates a popup where rates can be edited & created
     */
    function setrates()
    {
        $this->set('suffix', 'taxrates');
        $state = parent::_setModelState();
        $app = JFactory::getApplication();
        $model = $this->getModel( $this->get('suffix') );
        $ns = $this->getNamespace();
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }

        $row = DSCTable::getInstance('TaxClasses', 'TiendaTable');
        $row->load($model->getId());
        $model->setState('filter_taxclassid', $model->getId());

        $view   = $this->getView( 'taxrates', 'html' );
        $view->set( '_controller', 'taxclasses' );
        $view->set( '_view', 'taxclasses' );
        $view->set( '_action', "index.php?option=com_tienda&controller=taxclasses&task=setrates&id={$model->getId()}&tmpl=component" );
        $view->setModel( $model, true );
        $view->assign( 'state', $model->getState() );
        $view->assign( 'row', $row );
        $view->setLayout( 'default' );
		$view->setTask(true);
        $view->display();
    }
    
    /**
     * Creates a rate and redirects
     * 
     * @return unknown_type
     */
    function createrate()
    {
        $this->set('suffix', 'taxrates');
        $model  = $this->getModel( $this->get('suffix') );
        
        $row = $model->getTable();
        $row->bind( $_POST );
        
        if ( $row->save() ) 
        {
            $model->clearCache();
            
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
            $this->messagetype  = 'notice';
            $this->message = JText::_('COM_TIENDA_SAVED');
        } 
            else 
        {
            $this->messagetype  = 'notice';         
            $this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
        }
        
        $redirect = "index.php?option=com_tienda&controller=taxclasses&task=setrates&id={$row->tax_class_id}&tmpl=component";
        $redirect = JRoute::_( $redirect, false );
        
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
    
    /**
     * Saves the properties for all rates in list
     * 
     * @return unknown_type
     */
    function saverates()
    {
        $error = false;
        $this->messagetype  = '';
        $this->message      = '';
                
        $model = $this->getModel('taxrates');
        
        $row = $model->getTable();
        
        $cids = JRequest::getVar('cid', array(0), 'request', 'array');
        $rates = JRequest::getVar('rate', array(0), 'request', 'array');
        $levels = JRequest::getVar('levels', array(0), 'request', 'array');
        $descriptions = JRequest::getVar('description', array(0), 'request', 'array');
        
        foreach (@$cids as $cid)
        {
            $row->load( $cid );
            $row->tax_rate = $rates[$cid];
            $row->tax_rate_description = $descriptions[$cid];
            $row->level = $levels[$cid];

            if (!$row->save())
            {
                $this->message .= $row->getError();
                $this->messagetype = 'notice';
                $error = true;
            }
        }
        
        $model->clearCache();
        
        if ($error)
        {
            $this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
        }
            else
        {
            $this->message = "";
        }

        $redirect = "index.php?option=com_tienda&view=taxclasses&task=setrates&id={$row->tax_class_id}&tmpl=component";
        $redirect = JRoute::_( $redirect, false );
        
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
	
}

?>
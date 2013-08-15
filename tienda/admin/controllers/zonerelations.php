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

class TiendaControllerZonerelations extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'zonerelations');
	}
	
	/**
	 * Saves an item and redirects based on task
	 * @return void
	 */
	function save() 
	{
		$model 	= $this->getModel( $this->get('suffix') );
		
	    $row = $model->getTable();
	    $row->load( $model->getId() );
		$row->bind( $_POST );
		$geozoneid = $row->geozone_id;
		
		if ( $row->save() ) 
		{
			$model->setId( $row->id );
			$model->clearCache();
			$this->messagetype 	= 'message';
			$this->message  	= JText::_('COM_TIENDA_SAVED');
			
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		} 
			else 
		{
			$this->messagetype 	= 'notice';			
			$this->message 		= JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}
		
    	$redirect = "index.php?option=com_tienda&tmpl=component&geozoneid=$geozoneid";
    	$task = JRequest::getVar('task');
    	switch ($task)
    	{
    		case "savenew":
    			$redirect .= '&view='.$this->get('suffix').'&layout=form';
    		  break;
    		case "apply":
    			$redirect .= '&view='.$this->get('suffix').'&layout=form&id='.$model->getId();
    		  break;
    		case "save":
    		default:
    			$redirect .= "&task=configzones&view=".$this->get('suffix');
    		  break;
    	}

    	$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Cancels operation and redirects to default page
	 * If item is checked out, releases it
	 * @return void
	 */
	function cancel() 
	{
		$model 	= $this->getModel( $this->get('suffix') );
	    $row = $model->getTable();
	    $row->load( $model->getId() );
		$geozoneid = $row->geozone_id;
		
		$this->redirect = "index.php?option=com_tienda&task=configzones&tmpl=component&geozoneid=$geozoneid&view=".$this->get('suffix');
        parent::cancel();		
	}

	/**
	 * Deletes record(s) and redirects to default layout
	 */
	function delete()
	{
		$model 	= $this->getModel( $this->get('suffix') );
	    $row = $model->getTable();
	    $row->load( $model->getId() );
		$geozoneid = $row->geozone_id;
		
		$this->redirect = JRequest::getVar( 'return' )
		  ? base64_decode( JRequest::getVar( 'return' ) )
		  : "index.php?option=com_tienda&task=configzones&tmpl=component&geozoneid=$geozoneid&view==".$this->get('suffix');
		$this->redirect = JRoute::_( $this->redirect, false );
        parent::delete();
	}

	/*
	 * Creates a popup where fields can be selected and associated with this category.
	 * Basically a reverse of the category popup on the fields screen
	 */
	function configzones()
    {
    	$this->set('suffix', 'zonerelations');
    	$state = parent::_setModelState();
    	$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
        $ns = $this->getNamespace();

      	$state['filter_typeid'] 	= $app->getUserStateFromRequest($ns.'typeid', 'filter_typeid', '', '');
      	
      	$geozoneid = JRequest::getVar ( 'geozoneid' );
		$state['filter_geozoneid'] = $geozoneid;

    	foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );	
		}
		
		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$row = $model->getTable( 'zonerelations' );
		$row->load( $id );

		$view	= $this->getView( 'zonerelations', 'html' );
		$view->set( '_controller', 'zonerelations' );
		$view->set( '_view', 'zonerelations' );
		$view->set( '_action', "index.php?option=com_tienda&controller=zonerelations&task=configzones&tmpl=component&geozoneid=$geozoneid" );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'default' );
		$view->setTask(true);
		$view->display();
    }

}

?>
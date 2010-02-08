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

class TiendaControllerManufacturers extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'manufacturers');
		$this->registerTask( 'manufacturer_enabled.enable', 'boolean' );
		$this->registerTask( 'manufacturer_enabled.disable', 'boolean' );
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

    	$state['filter_id_from'] 	= $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
    	$state['filter_id_to'] 		= $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
    	$state['filter_name'] 		= $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
		$state['filter_enabled'] 	= $app->getUserStateFromRequest($ns.'enabled', 'filter_enabled', '', '');
    			
    	foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );	
		}
  		return $state;
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
		
		$fieldname = 'manufacturer_image_new';
		$userfile = JRequest::getVar( $fieldname, '', 'files', 'array' );
		if (!empty($userfile['size']))
		{
			if ($upload = $this->addfile( $fieldname ))
			{
				$row->manufacturer_image = $upload->getPhysicalName();	
			}
				else
			{
				$error = true;	
			}
		}
		
		if ( $row->save() ) 
		{
			$model->setId( $row->id );
			$this->messagetype 	= 'message';
			$this->message  	= JText::_( 'Saved' );
			if ($error)
			{
				$this->messagetype 	= 'notice';
				$this->message .= " :: ".$this->getError();	
			}
			
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		} 
			else 
		{
			$this->messagetype 	= 'notice';			
			$this->message 		= JText::_( 'Save Failed' )." - ".$row->getError();
		}
		
    	$redirect = "index.php?option=com_tienda";
    	$task = JRequest::getVar('task');
    	switch ($task)
    	{
    		case "savenew":
    			$redirect .= '&view='.$this->get('suffix').'&task=add';
    		  break;
    		case "apply":
    			$redirect .= '&view='.$this->get('suffix').'&task=edit&id='.$model->getId();
    		  break;
    		case "save":
    		default:
    			$redirect .= "&view=".$this->get('suffix');
    		  break;
    	}

    	$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}
    
	/**
	 * Adds a thumbnail image to item
	 * @return unknown_type
	 */
	function addfile( $fieldname = 'manufacturer_image_new' )
	{
		JLoader::import( 'com_tienda.library.file', JPATH_ADMINISTRATOR.DS.'components' );
		$upload = new TiendaFile();
		// handle upload creates upload object properties
		$upload->handleUpload( $fieldname );
		// then save image to appropriate folder
		$upload->setDirectory( Tienda::getPath('manufacturers_images') );
		$dest = $upload->getDirectory().DS.$upload->getPhysicalName();
		// delete the file if dest exists
		if ($fileexists = JFile::exists( $dest ))
		{
			JFile::delete($dest);
		}
		// save path and filename or just filename
		if (!JFile::upload($upload->file_path, $dest))
		{
        	$this->setError( sprintf( JText::_("Move failed from"), $upload->file_path, $dest) );
        	return false;			
		}
		
		// TODO Make thumbnail also
		
		$upload->full_path = $dest;    	
    	return $upload;
	}
	
}

?>
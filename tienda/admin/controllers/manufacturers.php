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
		$error = false;
		$task = JRequest::getVar('task');
		$model 	= $this->getModel( $this->get('suffix') );

		$row = $model->getTable();
		$row->load( $model->getId() );
		$row->bind( JRequest::get('POST') );
		$row->manufacturer_description = JRequest::getVar( 'manufacturer_description', '', 'post', 'string', JREQUEST_ALLOWRAW);

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

		// set the id as 0 for new entry
		if ($task=="save_as")
		{
			$pk=$row->getKeyName();
			$row->$pk= 0;
		}
		
		if ( $row->save() )
		{
			$model->setId( $row->id );
			$this->messagetype 	= 'message';
			$this->message  	= JText::_('COM_TIENDA_SAVED');
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
			$this->message 		= JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
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
		Tienda::load( 'TiendaImage', 'library.image' );
		$upload = new TiendaImage();
		// handle upload creates upload object properties
		$upload->handleUpload( $fieldname );
		// then save image to appropriate folder
		$upload->setDirectory( Tienda::getPath('manufacturers_images') );

		// Do the real upload!
		$upload->upload();

		// Thumb
		Tienda::load( 'TiendaHelperImage', 'helpers.image' );
		$imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
		$imgHelper->resizeImage( $upload, 'manufacturer');

		return $upload;
	}

	/**
	 * Batch resize of thumbs
	 * @author Skullbock
	 */
	function recreateThumbs(){
			
		$per_step = 100;
		$from_id = JRequest::getInt('from_id', 0);
		$to =  $from_id + $per_step;
			
		Tienda::load( 'TiendaHelperManufacturer', 'helpers.manufacturer' );
		Tienda::load( 'TiendaImage', 'library.image' );
		$width = TiendaConfig::getInstance()->get('manufacturer_img_width', '0');
		$height = TiendaConfig::getInstance()->get('manufacturer_img_height', '0');

		$model = $this->getModel('Manufacturers', 'TiendaModel');
		$model->setState('limistart', $from_id);
		$model->setState('limit', $to);
			
		$row = $model->getTable();
			
		$count = $model->getTotal();
			
		$manufacturers = $model->getList();
			
		$i = 0;
		$last_id = $from_id;
		foreach($manufacturers as $p){
			$i++;
			$image = $p->manufacturer_full_image;

			if($image != ''){
					
				$img = new TiendaImage($image, 'manufacturer');
				$img->setDirectory( Tienda::getPath('manufacturers_images'));

				// Thumb
				Tienda::load( 'TiendaHelperImage', 'helpers.image' );
				$imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
				$imgHelper->resizeImage( $img, 'manufacturer');
			}

			$last_id = $p->manufacturer_id;
		}
			
		if($i < $count)
		$redirect = "index.php?option=com_tienda&controller=manufacturers&task=recreateThumbs&from_id=".($last_id+1);
		else
		$redirect = "index.php?option=com_tienda&view=config";
			
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, JText::_('COM_TIENDA_DONE'), 'notice' );
		return;
	}

}

?>
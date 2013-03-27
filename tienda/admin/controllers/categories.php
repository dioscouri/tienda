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

class TiendaControllerCategories extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->set('suffix', 'categories');
		$this->registerTask( 'category_enabled.enable', 'boolean' );
		$this->registerTask( 'category_enabled.disable', 'boolean' );
		$this->registerTask( 'selected_enable', 'selected_switch' );
		$this->registerTask( 'selected_disable', 'selected_switch' );
		$this->registerTask( 'saveprev', 'save' );
		$this->registerTask( 'savenext', 'save' );
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

		$state['order']             = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.lft', 'cmd');
		$state['filter_id_from'] 	= $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
		$state['filter_id_to'] 		= $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
		$state['filter_name'] 		= $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
		$state['filter_parentid'] 	= $app->getUserStateFromRequest($ns.'parentid', 'filter_parentid', '', '');
		$state['filter_enabled'] 	= $app->getUserStateFromRequest($ns.'enabled', 'filter_enabled', '', '');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}
		return $state;
	}

    /**
     * Reorders multiple items (based on form input from list) and redirects to default layout
     * @return void
     */
    function ordering()
    {
        parent::ordering();                
        $this->rebuild();
        $row->reorder();
    }
	
	/**
	 * Rebuilds the tree using a recursive loop on the parent_id
	 * Useful after importing categories (from other shopping carts)
	 * Or for when tree becomes corrupted
	 *
	 * @return unknown_type
	 */
	function rebuild()
	{
		DSCModel::getInstance('Categories', 'TiendaModel')->getTable()->updateParents();
		DSCModel::getInstance('Categories', 'TiendaModel')->getTable()->rebuildTreeOrdering();
			
		$redirect = "index.php?option=com_tienda&view=".$this->get('suffix');
		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves an item and redirects based on task
	 * @return void
	 */
	function save()
	{
		if (!$row = parent::save()) 
		{
		    return $row;
		} 
		
		$model 	= $this->getModel( $this->get('suffix') );
		$error = false;
		
		$row->category_description = JRequest::getVar( 'category_description', '', 'post', 'string', JREQUEST_ALLOWRAW);
		
		$fieldname = 'category_full_image_new';
		$userfile = JRequest::getVar( $fieldname, '', 'files', 'array' );
		if (!empty($userfile['size']))
		{
			if ($upload = $this->addfile( $fieldname ))
			{
				$row->category_full_image = $upload->getPhysicalName();
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
	}

	/**
	 * Adds a thumbnail image to item
	 * @return unknown_type
	 */
	function addfile( $fieldname = 'category_full_image_new' )
	{
		Tienda::load( 'TiendaImage', 'library.image' );
		$upload = new TiendaImage();
		// handle upload creates upload object properties
		$upload->handleUpload( $fieldname );
		// then save image to appropriate folder
		$upload->setDirectory( Tienda::getPath('categories_images'));

		// do upload!
		$upload->upload();

		// Thumb
		Tienda::load( 'TiendaHelperImage', 'helpers.image' );
		$imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
		if (!$imgHelper->resizeImage( $upload, 'category'))
		{
		    JFactory::getApplication()->enqueueMessage( $imgHelper->getError(), 'notice' );
		}

		return $upload;
	}

	/**
	 * Loads view for assigning products to categories
	 *
	 * @return unknown_type
	 */
	function selectproducts()
	{
		$this->set('suffix', 'products');
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$row = $model->getTable( 'categories' );
		$row->load( $id );

		$view   = $this->getView( 'categories', 'html' );
		$view->set( '_controller', 'categories' );
		$view->set( '_view', 'categories' );
		$view->set( '_action', "index.php?option=com_tienda&controller=categories&task=selectproducts&tmpl=component&id=".$model->getId() );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'selectproducts' );
		$view->setTask(true);
		$view->display();
	}

	/**
	 *
	 * @return unknown_type
	 */
	function selected_switch()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';

		$model = $this->getModel($this->get('suffix'));
		$row = $model->getTable();

		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$cids = JRequest::getVar('cid', array (0), 'request', 'array');
		$task = JRequest::getVar( 'task' );
		$vals = explode('_', $task);

		$field = $vals['0'];
		$action = $vals['1'];

		switch (strtolower($action))
		{
			case "switch":
				$switch = '1';
				break;
			case "disable":
				$enable = '0';
				$switch = '0';
				break;
			case "enable":
				$enable = '1';
				$switch = '0';
				break;
			default:
				$this->messagetype  = 'notice';
				$this->message      = JText::_('COM_TIENDA_INVALID_TASK');
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
				break;
		}

		$keynames = array();
		foreach (@$cids as $cid)
		{
			$table = DSCTable::getInstance('ProductCategories', 'TiendaTable');
			$keynames["category_id"] = $id;
			$keynames["product_id"] = $cid;
			$table->load( $keynames );
			if ($switch)
			{
				if (isset($table->product_id))
				{
					if (!$table->delete())
					{
						$this->message .= $cid.': '.$table->getError().'<br/>';
						$this->messagetype = 'notice';
						$error = true;
					}
				}
				else
				{
					$table->product_id = $cid;
					$table->category_id = $id;
					if (!$table->save())
					{
						$this->message .= $cid.': '.$table->getError().'<br/>';
						$this->messagetype = 'notice';
						$error = true;
					}
				}
			}
			else
			{
				switch ($enable)
				{
					case "1":
						$table->product_id = $cid;
						$table->category_id = $id;
						if (!$table->save())
						{
							$this->message .= $cid.': '.$table->getError().'<br/>';
							$this->messagetype = 'notice';
							$error = true;
						}
						break;
					case "0":
					default:
						if (!$table->delete())
						{
							$this->message .= $cid.': '.$table->getError().'<br/>';
							$this->messagetype = 'notice';
							$error = true;
						}
						break;
				}
			}
		}
		
		$model->clearCache();

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . ": " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = JRequest::getVar( 'return' ) ?
		base64_decode( JRequest::getVar( 'return' ) ) : "index.php?option=com_tienda&controller=categories&task=selectproducts&tmpl=component&id=".$id;
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Batch resize of thumbs
	 * @author Skullbock
	 */
	function recreateThumbs(){
			
		$per_step = 100;
		$from_id = JRequest::getInt('from_id', 0);
		$to =  $from_id + $per_step;
			
		Tienda::load( 'TiendaHelperCategory', 'helpers.category' );
		Tienda::load( 'TiendaImage', 'library.image' );
		$width = Tienda::getInstance()->get('category_img_width', '0');
		$height = Tienda::getInstance()->get('category_img_height', '0');

		$model = $this->getModel('Categories', 'TiendaModel');
		$model->setState('limistart', $from_id);
		$model->setState('limit', $to);
			
		$row = $model->getTable();
			
		$count = $model->getTotal();
			
		$categories = $model->getList();
			
		$i = 0;
		$last_id = $from_id;
		foreach($categories as $p){
			$i++;
			$image = $p->category_full_image;
			$path = Tienda::getPath('categories_images');

			if($image != ''){
					
				$img = new TiendaImage($path.'/'.$image);
				$img->setDirectory( Tienda::getPath('categories_images'));

				// Thumb
				Tienda::load( 'TiendaHelperImage', 'helpers.image' );
				$imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
				$imgHelper->resizeImage( $img, 'category');
			}

			$last_id = $p->category_id;
		}
			
		if($i < $count)
		$redirect = "index.php?option=com_tienda&controller=categories&task=recreateThumbs&from_id=".($last_id+1);
		else
		$redirect = "index.php?option=com_tienda&view=config";
			
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, JText::_('COM_TIENDA_DONE'), 'notice' );
		return;
	}
}

?>
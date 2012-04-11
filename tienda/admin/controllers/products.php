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
		$this->registerTask( 'product_enabled.enable', 'boolean' );
		$this->registerTask( 'product_enabled.disable', 'boolean' );
		$this->registerTask( 'selected_enable', 'selected_switch' );
		$this->registerTask( 'selected_disable', 'selected_switch' );
		$this->registerTask( 'saveprev', 'save' );
		$this->registerTask( 'savenext', 'save' );
		$this->registerTask( 'prev', 'jump' );
		$this->registerTask( 'next', 'jump' );
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
		$state['filter_quantity_from'] 	= $app->getUserStateFromRequest($ns.'quantity_from', 'filter_quantity_from', '', '');
		$state['filter_quantity_to'] 		= $app->getUserStateFromRequest($ns.'quantity_to', 'filter_quantity_to', '', '');
		$state['filter_category'] 		= $app->getUserStateFromRequest($ns.'category', 'filter_category', '', '');
		$state['filter_sku'] 		= $app->getUserStateFromRequest($ns.'sku', 'filter_sku', '', '');
		$state['filter_price_from'] 	= $app->getUserStateFromRequest($ns.'price_from', 'filter_price_from', '', '');
		$state['filter_price_to'] 		= $app->getUserStateFromRequest($ns.'price_to', 'filter_price_to', '', '');
		$state['filter_taxclass']   = $app->getUserStateFromRequest($ns.'taxclass', 'filter_taxclass', '', '');
		$state['filter_ships']   = $app->getUserStateFromRequest($ns.'ships', 'filter_ships', '', '');
		$state['filter_group']   = TiendaConfig::getInstance()->get('default_user_group', '1'); 
		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.ordering', 'cmd');
		
		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}
		return $state;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function edit()
	{
		$view   = $this->getView( $this->get('suffix'), 'html' );
		$model  = $this->getModel( $this->get('suffix') );
		$view->set( 'hidemenu', false);
		$view->assign( 'product_relations', $this->getRelationshipsHtml($view, $model->getId()) );
		$view->setLayout( 'form' );

		parent::edit();
	}

	/**
	 * Checks in the current item and displays the previous/next one in the list
	 * @return unknown_type
	 */
	function jump()
	{
		$model  = $this->getModel( $this->get('suffix') );
		$row = $model->getTable();
		$row->load( $model->getId() );
		if (isset($row->checked_out) && !JTable::isCheckedOut( JFactory::getUser()->id, $row->checked_out) )
		{
			$row->checkin();
		}
		$task = JRequest::getVar( "task" );
		$redirect = "index.php?option=com_tienda&view=products";
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		$surrounding = TiendaHelperProduct::getSurrounding( $model->getId() );
		switch ($task)
		{
			case "prev":
				if (!empty($surrounding['prev']))
				{
					$redirect .= "&task=view&id=".$surrounding['prev'];
				}
				break;
			case "next":
				if (!empty($surrounding['next']))
				{
					$redirect .= "&task=view&id=".$surrounding['next'];
				}
				break;
		}
		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves an item and redirects based on task
	 * @return void
	 */
	function save()
	{
		$task = JRequest::getVar('task');
		$model 	= $this->getModel( $this->get('suffix') );
        $isSaveAs = false;
		$row = $model->getTable();
		$row->load( $model->getId() );
		$row->bind( JRequest::get('POST') );
		$row->product_description = JRequest::getVar( 'product_description', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$row->product_description_short = JRequest::getVar( 'product_description_short', '', 'post', 'string', JREQUEST_ALLOWRAW);

		// set the id as 0 for new entry
		if ( $task == "save_as" )
		{
			unset($row);
			// load WITHOUT EAV! otherwise the save will fail
			$row = $model->getTable();
			$row->load( $model->getId(), true, false );
			$row->bind( JRequest::get('POST') );
			$row->product_description = JRequest::getVar( 'product_description', '', 'post', 'string', JREQUEST_ALLOWRAW);
			$row->product_description_short = JRequest::getVar( 'product_description_short', '', 'post', 'string', JREQUEST_ALLOWRAW);
			
		    $isSaveAs = true;
			$oldProductImagePath = $row->getImagePath();
			$pk = $row->getKeyName();
			$oldPk = $row->$pk;
			
            // these get reset
			$row->$pk = 0;
			$row->product_images_path = '';
			$row->product_rating = '';
			$row->product_comments = '';
			
		}
		$row->_isNew = empty($row->product_id);

		$fieldname = 'product_full_image_new';
		$userfiles = JRequest::getVar( $fieldname, '', 'files', 'array' );

		// save the integrations
		$row = $this->prepareParameters( $row );

		//check if normal price exists
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		
        if ($isSaveAs)
        {        	
        	// and the prices
        	$prices = TiendaHelperProduct::getPrices($oldPk);
        }
            else 
        {
        	$prices = TiendaHelperProduct::getPrices( $row->product_id );
        }
		
		if ( $row->save() )		
		{
			$row->product_id = $row->id;
			$model->setId( $row->id );
			$this->messagetype 	= 'message';
			$this->message  	= JText::_('COM_TIENDA_SAVED');

			// check it's new entry or empty price but not save as 
			if (( $row->_isNew || empty($prices) ) && ! $isSaveAs)
			{   
				// set price if new or no prices set
				$price = JTable::getInstance( 'Productprices', 'TiendaTable' );
				$price->product_id = $row->id;
				$price->product_price = JRequest::getVar( 'product_price' );
				$price->group_id = TiendaConfig::getInstance()->get('default_user_group', '1');
				if (!$price->save())
				{
					$this->messagetype 	= 'notice';
					$this->message .= " :: ".$price->getError();
				}
			}
			

			if ($row->_isNew && !$isSaveAs)
			{
				// set category
				$category = JTable::getInstance( 'Productcategories', 'TiendaTable' );
				$category->product_id = $row->id;
				$category->category_id = JRequest::getVar( 'category_id' );
				if (!$category->save())
				{
					$this->messagetype 	= 'notice';
					$this->message .= " :: ".$category->getError();
				}
               
				// save default quantity
				$quantity = JTable::getInstance( 'Productquantities', 'TiendaTable' );
				$quantity->product_id = $row->id;
				$quantity->quantity = JRequest::getInt( 'product_quantity' );
				if (!$quantity->save())
				{
					$this->messagetype  = 'notice';
					$this->message .= " :: ".$quantity->getError();
				}

			}
			 
			if ($isSaveAs)
			{
				// set price when cloning
				$priceTable = JTable::getInstance( 'Productprices', 'TiendaTable' );
				foreach($prices as $price)
				{
    				$priceTable->product_id = $row->id;
    				$priceTable->product_price = $price->product_price;
    				$priceTable->product_price_startdate = $price->product_price_startdate;
    				$priceTable->product_price_enddate = $price->product_price_enddate;
    				$priceTable->created_date = $price->created_date;
    				$priceTable->modified_date = $price->modified_date;
    				$priceTable->group_id = $price->group_id;
    				$priceTable->price_quantity_start = $price->price_quantity_start;
    				$priceTable->price_quantity_end = $price->price_quantity_end;
    				if (!$priceTable->save())
    				{
    					$this->messagetype 	= 'notice';
    					$this->message .= " :: ".$priceTable->getError();
    				}
				}
				
			    // set category
			    $categoryTable = JTable::getInstance( 'Productcategories', 'TiendaTable' );
			    $categories = TiendaHelperProduct::getCategories($oldPk);
			    foreach ($categories as $category)
			    {
    			   	$categoryTable->product_id = $row->id;
    				$categoryTable->category_id = $category;
    			    if (!$categoryTable->save())
    				{
    					$this->messagetype 	= 'notice';
    					$this->message .= " :: ".$categoryTable->getError();
    				}
     		    }
    		    
			   	// TODO Save Attributes
				
				  // An array to map attribute id  old attribute id  are as key and new attribute id are as value
				  $attrbuteMappingArray = array();
				  $attrbuteParentMappingArray = array();

			    $attributes  = TiendaHelperProduct::getAttributes($oldPk);
			   
			    foreach ($attributes as $attribute)
			    {
			    	$attributeTable = JTable::getInstance( 'ProductAttributes', 'TiendaTable' );
    			  $attributeTable->productattribute_name = $attribute->productattribute_name;
    			  $attributeTable->product_id = $row->id;
    			  $attributeTable->ordering = $attribute->ordering;

            if ($attributeTable->save())
    			  {
              $attrbuteMappingArray[$attribute->productattribute_id] = $attributeTable->productattribute_id;
    			    $attrbuteParentMappingArray[$attributeTable->productattribute_id] = $attribute->parent_productattributeoption_id;
            }
        		else 
    			  {
    			    $this->messagetype 	= 'notice';
    					$this->message .= " :: ".$attributeTable->getError();
    			  }
			    }
          
				
			    // set Attribute options
				
				$attrbuteOptionsMappingArray = array();
				foreach ($attrbuteMappingArray as $oldAttrbuteId => $newAttributeId) 
				{
            // set Attribute options
    				$options  = TiendaHelperProduct::getAttributeOptionsObjects($oldAttrbuteId);
    				foreach ($options as $option)
    				{
    					$attributeOptionsTable = JTable::getInstance( 'ProductAttributeOptions', 'TiendaTable' );
    					$attributeOptionsTable->productattribute_id   = $newAttributeId ;
    			    	$attributeOptionsTable->productattributeoption_name   = $option->productattributeoption_name ; 
    			    	$attributeOptionsTable->productattributeoption_price   = $option->productattributeoption_price ;
    			    	$attributeOptionsTable->productattributeoption_prefix   = $option->productattributeoption_prefix ;
    			    	$attributeOptionsTable->productattributeoption_code   = $option->productattributeoption_code ;
    			    	$attributeOptionsTable->ordering   = $option->ordering ; 
    			    			    	
        			    if ($attributeOptionsTable->save())
    			    	{
    			    		$attrbuteOptionsMappingArray[$option->productattributeoption_id] = $attributeOptionsTable->productattributeoption_id;
    			    	}
        			    else
        			    {
        			       	$this->messagetype 	= 'notice';
        					$this->message .= " :: ".$attributeOptionsTable->getError();
        				}
    			    }

            // save parent relationship
            if( $attrbuteParentMappingArray [ $newAttributeId ] )
            {
  			    	$attributeTable = JTable::getInstance( 'ProductAttributes', 'TiendaTable' );
              $attributeTable->load( $newAttributeId );
              $attributeTable->parent_productattributeoption_id = $attrbuteOptionsMappingArray[ $attrbuteParentMappingArray [ $newAttributeId ] ];
              if (!$attributeTable->save())
      			  {
      			    $this->messagetype 	= 'notice';
      					$this->message .= " :: ".$attributeTable->getError();
      			  }
            }
				}

				// set quantity
			    $quantityTable = JTable::getInstance( 'Productquantities', 'TiendaTable' );
			    $quantities  = TiendaHelperProduct::getProductQuantitiesObjects($oldPk );
			    foreach ($quantities as $quantity)
			    {
    			   	$quantityTable->product_attributes = $quantity->product_attributes ;
    			   	$quantityTable->product_id= $row->id;
    			   	$quantityTable->vendor_id = $quantity->vendor_id ;
    			   	$quantityTable->quantity = $quantity->quantity ;
    			   	
    			   	$optionsCSV=$quantity->product_attributes;
    			   	
    			   	$options= explode(",",$optionsCSV);
    			   	$newOptions=array();
    			   	foreach ($options as $option)
    			   	{
    			   		$newOptions[]=$attrbuteOptionsMappingArray[$option];
    			   	} 
    			    $optionsCSV =implode(",",$newOptions);
    			    $quantityTable->product_attributes=$optionsCSV;
    			   	
    			    if (!$quantityTable->save())
    				{
    					$this->messagetype 	= 'notice';
    					$this->message .= " :: ".$quantityTable->getError();
    				}
		      
				}
				
				// copy all gallery files
				jimport( 'joomla.filesystem.folder' );
				jimport( 'joomla.filesystem.file' );
				$galleryFiles = JFolder::files( $oldProductImagePath ); // get all gallery images
				if( count( $galleryFiles ) )// if there are any
				{
					JFolder::create( $row->getImagePath() ); // create folder for images
					JFolder::create( $row->getImagePath().'thumbs' ); // create folder for thumbnails images
					for( $i = 0, $c = count( $galleryFiles ); $i < $c; $i++ )
					{
						// copy only images with both original file and a corresponding thumbnail
						if( JFile::exists( $oldProductImagePath.'thumbs'.DS.$galleryFiles[$i] ) && JFile::exists( $oldProductImagePath.$galleryFiles[$i] ) )
						{
							JFile::copy( $oldProductImagePath.$galleryFiles[$i], $row->getImagePath().DS.$galleryFiles[$i] );
							JFile::copy( $oldProductImagePath.'thumbs'.DS.$galleryFiles[$i], $row->getImagePath().DS.'thumbs'.DS.$galleryFiles[$i] );
						}
					}
				}
				
				// duplicate product files (only in db)
				$modelFiles = $this->getModel( 'productfiles' );
				$modelFiles->setState( 'filter_product', $oldPk );
				$listFiles = $modelFiles->getList();
				if( count( $listFiles ) ) // if there are files attached to the first product, we should duplicate the record in db
				{
					$row_file = JTable::getInstance( 'Productfiles', 'TiendaTable' );
					for( $i = 0, $c = count( $listFiles ); $i < $c; $i++ )
					{
						$row_file->bind( $listFiles[$i] ); // bind old data
						$row_file->productfile_id = 0; // will be set
						$row_file->product_id = $row->product_id; // use clone's ID 
						$row_file->save(); // save the data
					}
				}
				
			// create duplicate connections for EAV custom fields
            JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
	    	$model = JModel::getInstance('EavAttributes', 'TiendaModel');
	    	$model->setState('filter_entitytype', 'products' );
	    	$model->setState('filter_entityid', $oldPk);    	
	    	$listEAV = $model->getList();
	    	$teav = $model->getTable();
				
				if( is_array( $listEAV ) ) // are there custom fields to clone?
				{
					for( $i = 0, $c = count( $listEAV ); $i < $c; $i++ )
					{
						$tblEAV = JTable::getInstance( 'EavAttributeEntities', 'TiendaTable' );
						$tblEAV->eaventity_id = $row->product_id;
						$tblEAV->eaventity_type = 'products';
						$tblEAV->eavattribute_id = $listEAV[$i]->eavattribute_id;
						$tblEAV->save();
						
						// Clone the values too!
						$teav->load($listEAV[$i]->eavattribute_id); 
						$value = TiendaHelperEav::getAttributeValue($teav, 'products', $row->product_id);
						
						$newValue = JTable::getInstance('EavValues', 'TiendaTable');
						$newValue->setType($teav->eavattribute_type);
		    			$newValue->eavattribute_id = $listEAV[$i]->eavattribute_id;
		    			$newValue->eaventity_id = $row->product_id;
		    		
			    		// Store the value
			    		$newValue->eavvalue_value = $value;
			    		$newValue->eaventity_type = 'products';
			    		$newValue->store();
					}
				}
			 }
			 
		
			// Multiple images processing
			$i = 0;
			$error = false;
			while (!empty($userfiles['size'][$i]))
			{
				$dir = $row->getImagePath(true);

				if ($upload = $this->addimage( $fieldname, $i, $dir ))
				{
					// The first One is the default (if there is no default yet)
					if ($i == 0 && (empty($row->product_full_image) || $row->product_full_image == ''))
					{
						$row->product_full_image = $upload->getPhysicalName();
						// need to re-save in this instance
						// should we be storing or saving?
						$row->save();
					}
				}
				else
				{
					$error = true;
				}
				$i++;
			}

			if ($error)
			{
				$this->messagetype  = 'notice';
				$this->message .= " :: ".$this->getError();
			}

			$helper = new TiendaHelperProduct();
			$helper->onAfterSaveProducts( $row );
			
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype 	= 'notice';
			$this->message 		= JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda";
		switch ($task)
		{
            case "save_as":
                $redirect .= '&view='.$this->get('suffix').'&task=edit&id='.$row->product_id;
                $this->message .= " - " . JText::_('COM_TIENDA_YOU_ARE_NOW_EDITING_NEW_PRODUCT');
                break;
			case "saveprev":
				$redirect .= '&view='.$this->get('suffix');
				// get prev in list
				Tienda::load( "TiendaHelperProduct", 'helpers.product' );
				$surrounding = TiendaHelperProduct::getSurrounding( $model->getId() );
				if (!empty($surrounding['prev']))
				{
					$redirect .= '&task=edit&id='.$surrounding['prev'];
				}
				break;
			case "savenext":
				$redirect .= '&view='.$this->get('suffix');
				// get next in list
				Tienda::load( "TiendaHelperProduct", 'helpers.product' );
				$surrounding = TiendaHelperProduct::getSurrounding( $model->getId() );
				if (!empty($surrounding['next']))
				{
					$redirect .= '&task=edit&id='.$surrounding['next'];
				}
				break;
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
	 *
	 * A separate space for working through all the different integrations
	 */
	function prepareParameters( &$row )
	{
		// this row's product_params has already been set from the textarea's POST
		// so we need to add to it
		$params = new JParameter( trim($row->product_params) );
		$params->set( 'amigos_commission_override', JRequest::getVar('amigos_commission_override') );
		$params->set( 'billets_ticket_limit_increase', JRequest::getVar('billets_ticket_limit_increase') );
		$params->set( 'billets_ticket_limit_exclusion', JRequest::getVar('billets_ticket_limit_exclusion') );
		$params->set( 'billets_hour_limit_increase', JRequest::getVar('billets_hour_limit_increase') );
		$params->set( 'billets_hour_limit_exclusion', JRequest::getVar('billets_hour_limit_exclusion') );
		$params->set( 'juga_group_csv_add', JRequest::getVar('juga_group_csv_add') );
		$params->set( 'juga_group_csv_remove', JRequest::getVar('juga_group_csv_remove') );
        $params->set( 'juga_group_csv_add_expiration', JRequest::getVar('juga_group_csv_add_expiration') );
        $params->set( 'juga_group_csv_remove_expiration', JRequest::getVar('juga_group_csv_remove_expiration') );
		$params->set( 'core_user_change_gid', JRequest::getVar('core_user_change_gid') );
		$params->set( 'core_user_new_gid', JRequest::getVar('core_user_new_gid') );
        $params->set( 'ambrasubs_type_id', JRequest::getVar('ambrasubs_type_id') );
        $params->set( 'hide_quantity_input', JRequest::getVar('param_hide_quantity_input') );
        $params->set( 'default_quantity', JRequest::getVar('param_default_quantity') );
        $params->set( 'hide_quantity_cart', JRequest::getVar('param_hide_quantity_cart') );
        $params->set( 'show_product_compare', JRequest::getVar('param_show_product_compare', '1') );
                
		$row->product_params = trim( $params->toString() );
		return $row;
	}

	/**
	 * Adds a thumbnail image to item
	 * @return unknown_type
	 */
	function addimage( $fieldname = 'product_full_image_new', $num = 0, $path = 'products_images' )
	{
		Tienda::load( 'TiendaImage', 'library.image' );
		$upload = new TiendaImage();
		// handle upload creates upload object properties
		$upload->handleMultipleUpload( $fieldname, $num );
		// then save image to appropriate folder
		if ($path == 'products_images') { $path = Tienda::getPath( 'products_images' ); }
		$upload->setDirectory( $path );

		// Do the real upload!
		$upload->upload();

		Tienda::load( 'TiendaHelperImage', 'helpers.image' );
		$imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
		if (!$imgHelper->resizeImage( $upload, 'product'))
		{
			JFactory::getApplication()->enqueueMessage( $imgHelper->getError(), 'notice' );
		}

		return $upload;
	}

	/**
	 * Loads view for assigning product to categories
	 *
	 * @return unknown_type
	 */
	function selectcategories()
	{
		$this->set('suffix', 'categories');
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		$state['filter_parentid'] 	= $app->getUserStateFromRequest($ns.'parentid', 'filter_parentid', '', '');
		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.lft', 'cmd');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$row = $model->getTable( 'products' );
		$row->load( $id );

		$view	= $this->getView( 'products', 'html' );
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_tienda&view=products&task=selectcategories&tmpl=component&id=".$model->getId() );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'selectcategories' );
		$view->display();
	}

	/**
	 * Loads view to show the gallery
	 *
	 * @return unknown_type
	 */
	function viewGallery()
	{
		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$row = JTable::getInstance('Products', 'TiendaTable');
		$row->load( $id );

		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		$helper = TiendaHelperBase::getInstance('Product', 'TiendaHelper');
		$gallery_path = $helper->getGalleryPath($row->product_id);
		$gallery_url = $helper->getGalleryUrl($row->product_id);
		$images = $helper->getGalleryImages($gallery_path);

		$view	= $this->getView( 'products', 'html' );
		$model = $this->getModel($this->get('suffix'));

		$view->setModel($model, true);
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_tienda&view=products&task=viewGallery&tmpl=component&id=".$id);
		$view->assign( 'row', $row );
		$view->assign( 'images', $images );
		$view->assign( 'url', $gallery_url );
		$view->setLayout( 'gallery' );

		$view->display();
	}

	/**
	 *
	 * @return unknown_type
	 */
	function selected_switch()
	{
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';

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
				$this->messagetype 	= 'notice';
				$this->message 		= JText::_('COM_TIENDA_INVALID_TASK');
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
				break;
		}

		$keynames = array();
		foreach (@$cids as $cid)
		{
			$table = JTable::getInstance('ProductCategories', 'TiendaTable');
			$keynames["product_id"] = $id;
			$keynames["category_id"] = $cid;
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
					$table->product_id = $id;
					$table->category_id = $cid;
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
						$table->product_id = $id;
						$table->category_id = $cid;
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

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . ": " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = JRequest::getVar( 'return' ) ?
		base64_decode( JRequest::getVar( 'return' ) ) : "index.php?option=com_tienda&view=products&task=selectcategories&tmpl=component&id=".$id;
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/*
	 * Creates a popup where quantities can be set
	 */
	function setquantities()
	{
		$this->set('suffix', 'productquantities');

		$model = $this->getModel( $this->get('suffix') );
		$model->setState('filter_productid', $model->getId());
		$model->setState('filter_vendorid', '0');
		$items = $model->getAll();

		$row = JTable::getInstance('Products', 'TiendaTable');
		$row->load($model->getId());

		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		// $csvs = TiendaHelperProduct::getProductAttributeCSVs( $row->product_id );
		// $items = TiendaHelperProduct::reconcileProductAttributeCSVs( $row->product_id, '0', $items, $csvs );
		TiendaHelperProduct::doProductQuantitiesReconciliation( $row->product_id );

		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$ns = $this->getNamespace();

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$view   = $this->getView( 'products', 'html' );
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_tienda&view=products&task=setquantities&id={$model->getId()}&tmpl=component" );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->assign( 'items', $model->getList() );
		$view->setLayout( 'setquantities' );
		$view->display();
	}

	/**
	 * Saves the quantities for all product attributes in list
	 *
	 * @return unknown_type
	 */
	function savequantities()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';
		$model = $this->getModel('productquantities');
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array(0), 'request', 'array');
		$quantities = JRequest::getVar('quantity', array(0), 'request', 'array');

		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->quantity = $quantities[$cid];

			if (!$row->save())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setquantities&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/*
	 * Creates a popup where prices can be edited & created
	 */
	function setprices()
	{
		$this->set('suffix', 'productprices');
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();
		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$row = JTable::getInstance('Products', 'TiendaTable');
		$row->load($model->getId());

		$model->setState('filter_id', $model->getId());

		$view	= $this->getView( 'productprices', 'html' );
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_tienda&view=products&task=setprices&id={$model->getId()}&tmpl=component" );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'default' );
		$view->display();
	}

	/**
	 * Creates a price and redirects
	 *
	 * @return unknown_type
	 */
	function createprice()
	{
		$this->set('suffix', 'productprices');
		$model 	= $this->getModel( $this->get('suffix') );

		$row = $model->getTable();
		$row->product_id = JRequest::getVar( 'id' );
		$row->product_price = JRequest::getVar( 'createprice_price' );
		$row->product_price_startdate = JRequest::getVar( 'createprice_date_start' );
		$row->product_price_enddate = JRequest::getVar( 'createprice_date_end' );
		$row->price_quantity_start = JRequest::getVar( 'createprice_quantity_start' );
		$row->price_quantity_end = JRequest::getVar( 'createprice_quantity_end' );
		$row->group_id = JRequest::getVar( 'createprice_group_id' );
		
		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype 	= 'notice';
			$this->message 		= JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setprices&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves the properties for all prices in list
	 *
	 * @return unknown_type
	 */
	function saveprices()
	{
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';

		$model = $this->getModel('productprices');
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array(0), 'request', 'array');
		$prices = JRequest::getVar('price', array(0), 'request', 'array');
		$date_starts = JRequest::getVar('date_start', array(0), 'request', 'array');
		$date_ends = JRequest::getVar('date_end', array(0), 'request', 'array');
		$quantity_starts = JRequest::getVar('quantity_start', array(0), 'request', 'array');
		$quantity_ends = JRequest::getVar('quantity_end', array(0), 'request', 'array');
		$user_groups = JRequest::getVar('price_group_id', array(0), 'request', 'array');
		
		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->product_price = $prices[$cid];
			$row->product_price_startdate = $date_starts[$cid];
			$row->product_price_enddate = $date_ends[$cid];
			$row->price_quantity_start = $quantity_starts[$cid];
			$row->price_quantity_end = $quantity_ends[$cid];
			$row->group_id = $user_groups[$cid];

			if (!$row->save())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setprices&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}
	
	/*
	 * Creates a popup where issues can be edited & created
	 */
	function setissues()
	{
		$this->set('suffix', 'productissues');
		$ns = $this->getNamespace();
		$app = JFactory::getApplication();
		
		$app->setUserState( $ns.'.filter_order', $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.publishing_date', 'cmd') );
		$app->setUserState( $ns.'.filter_direction', $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word') );
		$state = parent::_setModelState();
		$model = $this->getModel( $this->get('suffix') );
		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}
		
		$row = JTable::getInstance('Products', 'TiendaTable');
		$row->load( $model->getId() );
		$model->setState('filter_product_id', $model->getId());
		$view	= $this->getView( 'productissues', 'html' );
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_tienda&view=products&task=setissues&id={$model->getId()}&tmpl=component" );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'default' );
		$view->display();
	}

	/**
	 * Creates an issue and redirects
	 *
	 * @return unknown_type
	 */
	function createissue()
	{
		$this->set('suffix', 'productissues');
		$model 	= $this->getModel( $this->get('suffix') );

		$row = $model->getTable();
		$row->product_id = JRequest::getVar( 'id' );
		$row->issue_num = JRequest::getVar( 'issue_num' );
		$row->volume_num = JRequest::getVar( 'volume_num' );
		$row->publishing_date = JRequest::getVar( 'publishing_date'  );
		
		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype 	= 'notice';
			$this->message 		= JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setissues&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves the properties for all issues in list
	 *
	 * @return unknown_type
	 */
	function saveissues()
	{
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';

		$model = $this->getModel('productissues');
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array(0), 'request', 'array');
		$issues_num = JRequest::getVar( 'issues_num', array(0), 'request', 'array' );
		$volumes_num = JRequest::getVar( 'volumes_num', array(0), 'request', 'array' );
		$publishing_dates = JRequest::getVar( 'publishing_dates', array(0), 'request', 'array' );
				
		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->issue_num = $issues_num[$cid];
			$row->volume_num = $volumes_num[$cid];
			$row->publishing_date = $publishing_dates[$cid];

			if (!$row->save())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setissues&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Loads view for assigning product attributes
	 *
	 * @return unknown_type
	 */
	function setattributes()
	{
		$this->set('suffix', 'productattributes');
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		$state['filter_product'] = $model->getId();
		$state['order'] = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.ordering', 'cmd');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$row = JTable::getInstance('Products', 'TiendaTable');
		$row->load($model->getId());

		$view   = $this->getView( 'productattributes', 'html' );
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_tienda&view=products&task=setattributes&tmpl=component&id=".$model->getId() );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'default' );
		$view->display();
	}

	/**
	 * Creates a price and redirects
	 *
	 * @return unknown_type
	 */
	function createattribute()
	{
		$this->set('suffix', 'productattributes');
		$model  = $this->getModel( $this->get('suffix') );

		$row = $model->getTable();
		$row->product_id = JRequest::getVar( 'id' );
		$row->productattribute_name = JRequest::getVar( 'createproductattribute_name' );
        $row->ordering = '99';
        
		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setattributes&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves the properties for all attributes in list
	 *
	 * @return unknown_type
	 */
	function saveattributes()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';

		$model = $this->getModel('productattributes');
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array(0), 'request', 'array');
		$name = JRequest::getVar('name', array(0), 'request', 'array');
		$parent = JRequest::getVar('parent', array(0), 'request', 'array');
		$ordering = JRequest::getVar('ordering', array(0), 'request', 'array');

		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->productattribute_name = $name[$cid];
			$row->parent_productattributeoption_id = $parent[$cid];
			$row->ordering = $ordering[$cid];

			if (!$row->check() || !$row->store())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}
		$row->reorder();

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setattributes&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Loads view for assigning product attribute options
	 *
	 * @return unknown_type
	 */
	function setattributeoptions()
	{
		$this->set('suffix', 'productattributeoptions');
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		$state['filter_attribute']   = $model->getId();
		$state['order'] = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.ordering', 'cmd');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$row = JTable::getInstance('ProductAttributes', 'TiendaTable');
		$row->load($model->getId());

		$view   = $this->getView( 'productattributeoptions', 'html' );
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_tienda&view=products&task=setattributeoptions&tmpl=component&id=".$model->getId() );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'default' );
		$view->display();
	}
	
	/**
	 * Loads view for assigning product attribute option values
	 *
	 * @return unknown_type
	 */
	function setattributeoptionvalues()
	{
		$this->set('suffix', 'productattributeoptionvalues');
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		$state['filter_option']   = $model->getId();

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$row = JTable::getInstance('ProductAttributeOptions', 'TiendaTable');
		$row->load($model->getId());

		$view   = $this->getView( 'productattributeoptionvalues', 'html' );
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_tienda&view=products&task=setattributeoptionvalues&tmpl=component&id=".$model->getId() );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'default' );
		$view->display();
	}

	/**
	 * Creates an option and redirects
	 *
	 * @return unknown_type
	 */
	function createattributeoption()
	{
		$this->set('suffix', 'productattributeoptions');
		$model  = $this->getModel( $this->get('suffix') );

		$row = $model->getTable();
		$row->productattribute_id = JRequest::getVar( 'id' );
		$row->productattributeoption_name = JRequest::getVar( 'createproductattributeoption_name' );
		$row->productattributeoption_price = JRequest::getVar( 'createproductattributeoption_price' );
		$row->productattributeoption_code = JRequest::getVar( 'createproductattributeoption_code' );
		$row->productattributeoption_prefix = JRequest::getVar( 'createproductattributeoption_prefix' );
		$row->productattributeoption_weight = JRequest::getVar( 'createproductattributeoption_weight' );
		$row->productattributeoption_prefix_weight = JRequest::getVar( 'createproductattributeoption_prefix_weight' );
		$row->is_blank = JRequest::getVar( 'createproductattributeoption_blank' );
		$row->ordering = '99';
        
		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setattributeoptions&id={$row->productattribute_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Creates an option value and redirects
	 *
	 * @return unknown_type
	 */
	function createattributeoptionvalue()
	{
		$this->set('suffix', 'productattributeoptionvalues');
		$model  = $this->getModel( $this->get('suffix') );

		$row = $model->getTable();
		$row->productattributeoption_id = JRequest::getVar( 'id' );
		$row->productattributeoptionvalue_field = JRequest::getVar( 'createproductattributeoptionvalue_field' );
		$row->productattributeoptionvalue_operator = JRequest::getVar( 'createproductattributeoptionvalue_operator' );
		$row->productattributeoptionvalue_value = JRequest::getVar( 'createproductattributeoptionvalue_value' );
		
		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setattributeoptionvalues&id={$row->productattributeoption_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves the properties for all attribute options in list
	 *
	 * @return unknown_type
	 */
	function saveattributeoptions()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';

		$model = $this->getModel('productattributeoptions');
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array(0), 'request', 'array');
		$name = JRequest::getVar('name', array(0), 'request', 'array');
		$prefix = JRequest::getVar('prefix', array(0), 'request', 'array');
		$price = JRequest::getVar('price', array(0), 'request', 'array');
		$prefix_weight = JRequest::getVar('prefix_weight', array(0), 'request', 'array');
		$weight = JRequest::getVar('weight', array(0), 'request', 'array');
		$code = JRequest::getVar('code', array(0), 'request', 'array');
		$parent = JRequest::getVar('parent', array(0), 'request', 'array');
		$ordering = JRequest::getVar('ordering', array(0), 'request', 'array');
		$blank = JRequest::getVar( 'blank', array( 0 ), 'request', 'array' );
		
		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->productattributeoption_name = $name[$cid];
			$row->productattributeoption_prefix = $prefix[$cid];
			$row->productattributeoption_price = $price[$cid];
			$row->productattributeoption_prefix_weight = $prefix_weight[$cid];
			$row->productattributeoption_weight = $weight[$cid];
			$row->productattributeoption_code = @$code[$cid];
			$row->parent_productattributeoption_id = $parent[$cid];
			$row->ordering = $ordering[$cid];
			$row->is_blank = $blank[$cid];

			if (!$row->check() || !$row->store())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}
		$row->reorder();

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setattributeoptions&id={$row->productattribute_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves the properties for all attribute option values in list
	 *
	 * @return unknown_type
	 */
	function saveattributeoptionvalues()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';

		$model = $this->getModel('productattributeoptionvalues');
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array(0), 'request', 'array');
		$field = JRequest::getVar('field', array(0), 'request', 'array');
		$operator = JRequest::getVar('operator', array(0), 'request', 'array');
		$value = JRequest::getVar('value', array(0), 'request', 'array');

		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->productattributeoptionvalue_field = $field[$cid];
			$row->productattributeoptionvalue_operator = $operator[$cid];
			$row->productattributeoptionvalue_value = $value[$cid];

			if (!$row->check() || !$row->store())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}
		$row->reorder();

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setattributeoptionvalues&id={$row->productattributeoption_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Loads view for managing product files
	 *
	 * @return unknown_type
	 */
	function setfiles()
	{
		$this->set('suffix', 'productfiles');
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		$state['filter_product'] = $model->getId();
		//$state['order'] = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.ordering', 'cmd');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$row = JTable::getInstance('Products', 'TiendaTable');
		$row->load($model->getId());

		$view   = $this->getView( 'productfiles', 'html' );
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_tienda&view=products&task=setfiles&tmpl=component&id=".$model->getId() );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'default' );
		$view->display();
	}

	/**
	 * Creates a file and redirects
	 *
	 * @return unknown_type
	 */
	function createfile()
	{
		$this->set('suffix', 'productfiles');
		$model  = $this->getModel( $this->get('suffix') );

		$row = $model->getTable();
		$row->product_id = JRequest::getVar( 'id' );
		$row->productfile_name = JRequest::getVar( 'createproductfile_name' );
		$row->productfile_enabled = JRequest::getVar( 'createproductfile_enabled' );
		$row->purchase_required = JRequest::getVar( 'createproductfile_purchaserequired' );
		$row->max_download = JRequest::getInt( 'createproductfile_max_download', -1 );
		
		$fieldname = 'createproductfile_file';
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		$path = TiendaHelperProduct::getFilePath( $row->product_id );
		$userfile = JRequest::getVar( $fieldname, '', 'files', 'array' );
		if (!empty($userfile['size']))
		{
			if ($upload = $this->addfile( $fieldname, $path ))
			{
				if (empty($row->productfile_name)) { $row->productfile_name = $upload->proper_name; }
				$row->productfile_extension = $upload->getExtension();
				$row->productfile_path = $upload->full_path;
			}
			else
			{
				$error = true;
			}
		}
		// TODO Enable remotely-stored files with file_url

		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_UPLOAD_WAS_SUCCESSFULL');
					}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setfiles&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Creates a file from disk and redirects
	 *
	 * @return unknown_type
	 */
	function createfilefromdisk()
	{
		$this->set('suffix', 'productfiles');
		$model  = $this->getModel( $this->get('suffix') );

		$file = JRequest::getVar( 'createproductfileserver_file' );

		$row = $model->getTable();
		$row->product_id = JRequest::getVar( 'id' );
		$row->productfile_name = JRequest::getVar( 'createproductfileserver_name' );
		$row->productfile_enabled = JRequest::getVar( 'createproductfileserver_enabled' );
		$row->purchase_required = JRequest::getVar( 'createproductfileserver_purchaserequired' );
		$row->max_download = JRequest::getInt( 'createproductfileserver_max_download', -1 );
		
		if(empty($row->productfile_name))
		$row->productfile_name = $file;

		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		$path = TiendaHelperProduct::getFilePath( $row->product_id ) . DS . $file;
		$namebits = explode('.', $file);
		$extension = $namebits[count($namebits)-1];

		$row->productfile_extension = $extension;
		$row->productfile_path = $path;

		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_UPLOAD_WAS_SUCCESSFULL');
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setfiles&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}


	/**
	 * Uploads a file to associate to an item
	 *
	 * @return unknown_type
	 */
	function addfile( $fieldname = 'createproductfile_file', $path = 'products_files' )
	{
		Tienda::load( 'TiendaFile', 'library.file' );
		$upload = new TiendaFile();
		// handle upload creates upload object properties
		$upload->handleUpload( $fieldname );
		// then save image to appropriate folder
		if ($path == 'products_files') { $path = Tienda::getPath( 'products_files' ); }
		$upload->setDirectory( $path );
		$dest = $upload->getDirectory().DS.$upload->getPhysicalName();
		// delete the file if dest exists
		if ($fileexists = JFile::exists( $dest ))
		{
			JFile::delete($dest);
		}
		// save path and filename or just filename
		if (!JFile::upload($upload->file_path, $dest))
		{
			$this->setError( sprintf( JText::_('COM_TIENDA_MOVE_FAILED_FROM'), $upload->file_path, $dest) );
			return false;
		}

		$upload->full_path = $dest;
		return $upload;
	}

	/**
	 * Saves the properties for all files in list
	 *
	 * @return unknown_type
	 */
	function savefiles()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';

		$model = $this->getModel('productfiles');
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array(0), 'request', 'array');
		$name = JRequest::getVar('name', array(0), 'request', 'array');
		$ordering = JRequest::getVar('ordering', array(0), 'request', 'array');
		$enabled = JRequest::getVar('enabled', array(0), 'request', 'array');
		$purchaserequired = JRequest::getVar('purchaserequired', array(0), 'request', 'array');
		$max_download = JRequest::getVar('max_download', array(0), 'request', 'array');

		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->productfile_name = $name[$cid];
			$row->ordering = $ordering[$cid];
			$row->productfile_enabled = $enabled[$cid];
			$row->purchase_required = $purchaserequired[$cid];
			$row->max_download = $max_download[$cid];
     		if (!$row->check() || !$row->store())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}
		$row->reorder();

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setfiles&id={$row->product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Delete a product Image.
	 * Expected to be called via Ajax
	 */
	function deleteImage()
	{
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );

		$product_id = JRequest::getInt( 'product_id', 0, 'request');
		$image = JRequest::getVar('image', '', 'request');
		$image = html_entity_decode($image);

		// Find and delete the product image
		$helper = TiendaHelperBase::getInstance('Product', 'TiendaHelper');
		$path = $helper->getGalleryPath($product_id);

		$redirect = JRequest::getVar( 'return' ) ?
		base64_decode( JRequest::getVar( 'return' ) ) : "index.php?option=com_tienda&view=products&task=viewGallery&id={$product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		// Check if the data is ok
		if (empty($product_id) || empty($image))
		{
			$msg = JText::_('COM_TIENDA_INPUT_DATA_NOT_VALID');

			$redirect = "index.php?option=com_tienda&view=products";
			$redirect = JRoute::_( $redirect, false );

			$this->setRedirect( $redirect, $msg, 'notice' );
			return;
		}

		// Delete the image if it exists
		if(JFile::exists($path.$image)){
			$success = JFile::delete($path.$image);

			// Try to delete the thumb, too
			if ($success)
			{
				if (JFile::exists($path.'thumbs'.DS.$image))
				{
					JFile::delete($path.'thumbs'.DS.$image);
					$msg = JText::_('COM_TIENDA_IMAGE_DELETED');
				}
				else
				{
					$msg = JText::_("COM_TIENDA_CANNOT_DELETE_IMAGE_THUMBNAIL".$path.'thumbs'.DS.$image);
				}

				// if it is the primary image, let's clear the product_image field in the db
				$model = $this->getModel('products');
				$row = $model->getTable();
				$row->load($product_id);

				if ($row->product_full_image == $image)
				{
					$row->product_full_image = '';
				}
				// TODO Save or store here?
				$row->store();
			}
			else
			{
				$msg = JText::_('Cannot Delete the Image: '.$path.$image);
			}
		}
		else
		{
			$msg = JText::_("COM_TIENDA_CANNOT_DELETE_IMAGE".$path.$image);
		}
		$this->setRedirect( $redirect, $msg, 'notice' );
		return;
	}

	function setDefaultImage()
	{
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
			
		$product_id = JRequest::getInt( 'product_id', 0, 'request');
		$image = JRequest::getVar('image', '', 'request');
		$image = html_entity_decode($image);

		// Find the product image
		$helper = TiendaHelperBase::getInstance('Product');
		$path = $helper->getGalleryPath($product_id);

		// Check if the data is ok
		if (empty($product_id) || empty($image))
		{
			$msg = JText::_('COM_TIENDA_INPUT_DATA_NOT_VALID');
			$redirect = "index.php?option=com_tienda&view=products&task=viewGallery&id={$product_id}&tmpl=component";
			$redirect = JRoute::_( $redirect, false );
			$this->setRedirect( $redirect, $msg, 'notice' );
			return;
		}

		// Check if the image exists
		if (JFile::exists($path.$image) || JFile::exists($path.DS.$image))
		{
			// Update
			$model = $this->getModel('products');
			$row = $model->getTable();
			$row->load( array( 'product_id'=>$product_id ) );
			$row->product_full_image = $image;
			if (!$row->store())
			{
				JFactory::getApplication()->enqueueMessage( $row->getError(), 'notice' );
			}

			$this->message = JText::_('COM_TIENDA_UPDATE_SUCCESSFUL');
			$this->messagetype = 'message';
		}
		else
		{
			$this->message = JText::_("COM_TIENDA_IMAGE_DOES_NOT_EXIST".$path.$image);
			$this->messagetype = 'notice';
		}
			
		$redirect = "index.php?option=com_tienda&view=products&task=viewGallery&id={$product_id}&tmpl=component&update_parent=1";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
		return;

	}

	/**
	 * Batch resize of thumbs
	 * @author Skullbock
	 */
	function recreateThumbs(){
			
		// this will only be if there is only 1 image per product
		$per_step = 100;
		$from_id = JRequest::getInt('from_id', 0);
		$to_id =  $from_id + $per_step;
		$done = JRequest::getInt('done', 0);
			
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		Tienda::load( 'TiendaImage', 'library.image' );
		$width = TiendaConfig::getInstance()->get('product_img_width', '0');
		$height = TiendaConfig::getInstance()->get('product_img_height', '0');
			
		$helper = TiendaHelperBase::getInstance('Product', 'TiendaHelper');

		$model = $this->getModel('Products', 'TiendaModel');
		$model->setState('filter_id_from', $from_id);
		$model->setState('filter_id_to', $to_id);
			
		$row = $model->getTable();
			
		$count = $model->getTotal();
			
		$products = $model->getList();
			
		// Explanation: $i contains how many images we have processed till now
		// $k contains how many products we have checked.
		// Max $per_step images resized per call.
		// So we continue to cicle on this controller call until $done, which contains
		// how many products we have passed till now (in total), does not reach the
		// total number of products in the db.
		$i = 0;
		$k = 0;
		$last_id = $from_id;
		foreach ($products as $p)
		{
			$k++;
			$path = $helper->getGalleryPath($p->product_id);
			$images = $helper->getGalleryImages($path);

			foreach ($images as $image)
			{
				$i++;
				if ($image != '')
				{
					$img = new TiendaImage($path.$image);
					$img->setDirectory( $path );

					// Thumb
					Tienda::load( 'TiendaHelperImage', 'helpers.image' );
					$imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
					$imgHelper->resizeImage( $img );
				}
			}
			$last_id = $p->product_id;

			if ($i >= $per_step)
			break;
		}
			
		$done += $k;
			
		if ($done < $count)
		$redirect = "index.php?option=com_tienda&view=products&task=recreateThumbs&from_id=".($last_id+1)."&done=".$done;
		else
		$redirect = "index.php?option=com_tienda&view=config";
			
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, JText::_('COM_TIENDA_DONE'), 'notice' );
		return;
	}

	/**
	 * Gets an address formatted for display
	 *
	 * @param int $address_id
	 * @return string html
	 */
	function getRelationshipsHtml( $view, $product_id )
	{
		$html = '';
		$model = JModel::getInstance( 'ProductRelations', 'TiendaModel' );
		$model->setState('filter_product', $product_id);

		if ($items = $model->getList())
		{
			if( $view === null )
			{
				$view   = $this->getView( 'products', 'html' );
				$view->set( '_controller', 'products' );
        $view->set( '_view', 'products' );
        $view->set( '_doTask', true);
				$view->set( 'hidemenu', true);
				$view->setModel( $model, true );
			}
			$view->setLayout( 'form_relations' );
			$view->set('items', $items);
			$view->set('product_id', $product_id);

			ob_start();
			echo $view->loadTemplate( null );
			$html = ob_get_contents();
			ob_end_clean();
		}

		return $html;
	}
}

?>
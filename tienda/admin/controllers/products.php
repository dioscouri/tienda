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
		$view->assign( 'product_relations', $this->getRelationshipsHtml($model->getId()) );
		$view->set( 'hidemenu', false);

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
		if($task=="save_as"){
			$pk=$row->getKeyName();
			$oldPk=$row->$pk;
			$row->$pk= 0;
			$isSaveAs = true ;
		}
		$row->_isNew = empty($row->product_id);

		$fieldname = 'product_full_image_new';
		$userfiles = JRequest::getVar( $fieldname, '', 'files', 'array' );

		// save the integrations
		$row = $this->prepareParameters( $row );

		//check if normal price exists
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		
        if($isSaveAs){
        	$prices = TiendaHelperProduct::getPrices($oldPk );
        }
        else {
        	$prices = TiendaHelperProduct::getPrices( $row->product_id );
        	 }
		
		if ( $row->save() )
		
		{
			$row->product_id = $row->id;
			$model->setId( $row->id );
			$this->messagetype 	= 'message';
			$this->message  	= JText::_( 'Saved' );

			// check it's new entry or empty price but not save as 
			if (( $row->_isNew || empty($prices) ) && ! $isSaveAs)
			{   
				// set price if new or no prices set
				$price = JTable::getInstance( 'Productprices', 'TiendaTable' );
				$price->product_id = $row->id;
				$price->product_price = JRequest::getVar( 'product_price' );
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
			
			// old category, price  and quantity will enter with 
			if($isSaveAs){
			   
				// set price to whome doing the cloning
				$priceTable = JTable::getInstance( 'Productprices', 'TiendaTable' );
				foreach($prices as $price){
				$priceTable->product_id = $row->id;
				$priceTable->product_price = $price->product_price;
				$priceTable->product_price_startdate = $price->product_price_startdate;
				$priceTable->product_price_enddate = $price->product_price_enddate;
				$priceTable->created_date = $price->created_date;
				$priceTable->modified_date = $price->modified_date;
				$priceTable->user_group_id = $price->user_group_id;
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
			    $categories = TiendaHelperProduct::getCategories($oldPk );
			    foreach ($categories as $category){
			   	$categoryTable->product_id = $row->id;
				$categoryTable->category_id = $category;
			    if (!$categoryTable->save())
				{
					$this->messagetype 	= 'notice';
					$this->message .= " :: ".$categoryTable->getError();
				}
     		    }

     		   
    		    
			   	// set Attribute 
				
				// An array to map attribute id  old attribute id  are as key and new attribute id are as value
				$attrbuteMappingArray = array();
				
				$attributeTable = JTable::getInstance( 'ProductAttributes', 'TiendaTable' );
			    $attributes  = TiendaHelperProduct::getAttributes($oldPk );
			    foreach ($attributes as $attribute){
			    $attributeTable->productattribute_id =0;
			   	$attributeTable->productattribute_name = $attribute->productattribute_name;
			   	$attributeTable->product_id =$row->id;
			   	$attributeTable->ordering = $attribute->ordering;
			   
			    if($attributeTable->save()){
			    	 	$attrbuteMappingArray[$attribute->productattribute_id]=$attributeTable->productattribute_id;
			    }
			    else {
			    	$this->messagetype 	= 'notice';
					$this->message .= " :: ".$attributeTable->getError();
			    }
			    }
				
			    // set Attribute options
				
				$attrbuteOptionsMappingArray = array();
				foreach($attrbuteMappingArray as $oldAttrbuteId => $newAttributeId) {
				$options  = TiendaHelperProduct::getAttributeOptionsObjects($oldAttrbuteId);
				foreach ($options as $option){
					$attributeOptionsTable = JTable::getInstance( 'ProductAttributeOptions', 'TiendaTable' );
					$attributeOptionsTable->productattribute_id   = $newAttributeId ;
			    	$attributeOptionsTable->productattributeoption_name   = $option->productattributeoption_name ; 
			    	$attributeOptionsTable->productattributeoption_price   = $option->productattributeoption_price ;
			    	$attributeOptionsTable->productattributeoption_prefix   = $option->productattributeoption_prefix ;
			    	$attributeOptionsTable->ordering   = $option->ordering ; 
			    			    	
			    if($attributeOptionsTable->save())
			    	{
			    		$attrbuteOptionsMappingArray[$option->productattributeoption_id]=$attributeOptionsTable->productattributeoption_id;
			    	}
			    else{
			       	$this->messagetype 	= 'notice';
					$this->message .= " :: ".$attributeOptionsTable->getError();
					}
			    }
				}

				// set quantity
			    $quantityTable = JTable::getInstance( 'Productquantities', 'TiendaTable' );
			    $quantities  = TiendaHelperProduct::getProductQuantitiesObjects($oldPk );
			    foreach ($quantities as $quantity){
			   	$quantityTable->product_attributes = $quantity->product_attributes ;
			   	$quantityTable->product_id= $row->id;
			   	$quantityTable->vendor_id = $quantity->vendor_id ;
			   	$quantityTable->quantity = $quantity->quantity ;
			   	
			   	$optionsCSV=$quantity->product_attributes;
			   	
			   	$options= explode(",",$optionsCSV);
			   	$newOptions=array();
			   	foreach($options as $option){
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

			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype 	= 'notice';
			$this->message 		= JText::_( 'Save Failed' )." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda";
		switch ($task)
		{
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
		$params->set( 'juga_group_csv_add', JRequest::getVar('juga_group_csv_add') );
		$params->set( 'juga_group_csv_remove', JRequest::getVar('juga_group_csv_remove') );
		$params->set( 'core_user_change_gid', JRequest::getVar('core_user_change_gid') );
		$params->set( 'core_user_new_gid', JRequest::getVar('core_user_new_gid') );

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
			JFactory::getApplication()->enqueueMessage( JText::_("Could Not Create Thumb"), 'notice' );
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
				$this->message 		= JText::_( "Invalid Task" );
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
			$this->message = JText::_('Error') . ": " . $this->message;
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
			$this->message = JText::_('Error') . " - " . $this->message;
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

		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype 	= 'notice';
			$this->message 		= JText::_( 'Save Failed' )." - ".$row->getError();
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

		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->product_price = $prices[$cid];
			$row->product_price_startdate = $date_starts[$cid];
			$row->product_price_enddate = $date_ends[$cid];
			$row->price_quantity_start = $quantity_starts[$cid];
			$row->price_quantity_end = $quantity_ends[$cid];

			if (!$row->save())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('Error') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setprices&id={$row->product_id}&tmpl=component";
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

		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_( 'Save Failed' )." - ".$row->getError();
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
		$ordering = JRequest::getVar('ordering', array(0), 'request', 'array');

		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->productattribute_name = $name[$cid];
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
			$this->message = JText::_('Error') . " - " . $this->message;
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
		$row->productattributeoption_prefix = JRequest::getVar( 'createproductattributeoption_prefix' );

		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_( 'Save Failed' )." - ".$row->getError();
		}

		$redirect = "index.php?option=com_tienda&view=products&task=setattributeoptions&id={$row->productattribute_id}&tmpl=component";
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
		$ordering = JRequest::getVar('ordering', array(0), 'request', 'array');

		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->productattributeoption_name = $name[$cid];
			$row->productattributeoption_prefix = $prefix[$cid];
			$row->productattributeoption_price = $price[$cid];
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
			$this->message = JText::_('Error') . " - " . $this->message;
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
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_( 'Save Failed' )." - ".$row->getError();
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

		$file = JRequest::getVar( 'createproductfile_file' );

		$row = $model->getTable();
		$row->product_id = JRequest::getVar( 'id' );
		$row->productfile_name = JRequest::getVar( 'createproductfile_name' );
		$row->productfile_enabled = JRequest::getVar( 'createproductfile_enabled' );
		$row->purchase_required = JRequest::getVar( 'createproductfile_purchaserequired' );

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
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_( 'Save Failed' )." - ".$row->getError();
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
			$this->setError( sprintf( JText::_("Move failed from"), $upload->file_path, $dest) );
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
			$this->message = JText::_('Error') . " - " . $this->message;
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
			$msg = JText::_('Input Data not Valid');

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
					$msg = JText::_('Image Deleted');
				}
				else
				{
					$msg = JText::_('Cannot Delete the Image Thumbnail: '.$path.'thumbs'.DS.$image);
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
			$msg = JText::_('Image does not Exist: '.$path.$image);
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
			$msg = JText::_('Input Data not Valid');
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

			$this->message = JText::_('Update Successful');
			$this->messagetype = 'message';
		}
		else
		{
			$this->message = JText::_('Image does not Exist: '.$path.$image);
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

		$this->setRedirect( $redirect, JText::_('Done'), 'notice' );
		return;
	}

	/**
	 *
	 * Adds a product relationship
	 * @return unknown_type
	 */
	function removeRelationship()
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();

		// get elements from post
		$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		$submitted_values = $helper->elementsToArray( $elements );

		$product_id = $submitted_values['new_relationship_productid_from'];
		$productrelation_id = JRequest::getInt('productrelation_id');

		$table = JTable::getInstance('ProductRelations', 'TiendaTable');
		$table->delete( $productrelation_id );

		$response['error'] = '0';
		$response['msg'] = $this->getRelationshipsHtml( $product_id );

		echo ( json_encode( $response ) );
	}

	/**
	 *
	 * Adds a product relationship
	 * @return unknown_type
	 */
	function addRelationship()
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();

		// get elements from post
		$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		$submitted_values = $helper->elementsToArray( $elements );

		$product_id = $submitted_values['new_relationship_productid_from'];
		$product_to = $submitted_values['new_relationship_productid_to'];
		$relation_type = $submitted_values['new_relationship_type'];

		// verify product id exists
		$product = JTable::getInstance('Products', 'TiendaTable');
		$product->load(array('product_id'=>$product_to));
		if (empty($product->product_id) || $product_id == $product_to)
		{
			$response['error'] = '1';
			$response['msg'] = $helper->generateMessage( JText::_( "Invalid Product" ) );
			$response['msg'] .= $this->getRelationshipsHtml( $product_id );
			echo ( json_encode( $response ) );
			return;
		}

		// and that relationship doesn't already exist
		$producthelper = TiendaHelperBase::getInstance( 'Product' );
		if ($producthelper->relationshipExists( $product_id, $product_to, $relation_type ))
		{
			$response['error'] = '1';
			$response['msg'] = $helper->generateMessage( JText::_( "Relationship Already Exists" ) );
			$response['msg'] .= $this->getRelationshipsHtml( $product_id );
			echo ( json_encode( $response ) );
			return;
		}

		switch ($relation_type)
		{
			case "child":
			case "required_by":
				// for these two, we must flip to/from
				switch ($relation_type)
				{
					case "child":
						$rtype = 'parent';
						break;
					case "required_by":
						$rtype = 'requires';
						break;
				}

				// check existence of required_by relationship
				if ($producthelper->relationshipExists( $product_to, $product_id, $rtype ))
				{
					$response['error'] = '1';
					$response['msg'] = $helper->generateMessage( JText::_( "Relationship Already Exists" ) );
					$response['msg'] .= $this->getRelationshipsHtml( $product_id );
					echo ( json_encode( $response ) );
					return;
				}

				// then add it, need to flip to/from
				$table = JTable::getInstance('ProductRelations', 'TiendaTable');
				$table->product_id_from = $product_to;
				$table->product_id_to = $product_id;
				$table->relation_type = $rtype;
				$table->save();
				break;
			default:
				$table = JTable::getInstance('ProductRelations', 'TiendaTable');
				$table->product_id_from = $product_id;
				$table->product_id_to = $product_to;
				$table->relation_type = $relation_type;
				$table->save();
				break;
		}

		$response['error'] = '0';
		$response['msg'] = $this->getRelationshipsHtml( $product_id );

		echo ( json_encode( $response ) );
		return;
	}

	/**
	 * Gets an address formatted for display
	 *
	 * @param int $address_id
	 * @return string html
	 */
	function getRelationshipsHtml( $product_id )
	{
		$html = '';
		$model = JModel::getInstance( 'ProductRelations', 'TiendaModel' );
		$model->setState('filter_product', $product_id);

		if ($items = $model->getList())
		{
			$view   = $this->getView( 'products', 'html' );
			$view->set( '_controller', 'products' );
			$view->set( '_view', 'products' );
			$view->set( '_doTask', true);
			$view->set( 'hidemenu', true);
			$view->setModel( $model, true );
			$view->setLayout( 'form_relations' );
			$view->set('items', $items);
			$view->set('product_id', $product_id);

			ob_start();
			$view->display();
			$html = ob_get_contents();
			ob_end_clean();
		}

		return $html;
	}

	/**
	 *
	 * Adds a product relationship
	 * @return unknown_type
	 */
	function updateDefaultImage()
	{
		$response = array();
		$response['default_image'] = '';
		$response['default_image_name'] = '';

		$product_id = JRequest::getInt('product_id');
		Tienda::load( 'TiendaUrl', 'library.url' );
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );

		$row = JTable::getInstance('Products', 'TiendaTable');
		$row->load($product_id);

		$response['default_image'] = TiendaHelperProduct::getImage($row->product_id, 'id', $row->product_name, 'full', false, false, array( 'height'=>80 ) );
		$response['default_image_name'] = $row->product_full_image;

		echo ( json_encode( $response ) );
		return;
	}
}

?>
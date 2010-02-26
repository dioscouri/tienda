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
        JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
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
		$model 	= $this->getModel( $this->get('suffix') );
		
	    $row = $model->getTable();
	    $row->load( $model->getId() );
		$row->bind( $_POST );
		$isNew = empty($row->product_id);
		
		$fieldname = 'product_full_image_new';
		$userfile = JRequest::getVar( $fieldname, '', 'files', 'array' );
		if (!empty($userfile['size']))
		{
			if ($upload = $this->addfile( $fieldname, Tienda::getPath( 'products_images' ) ))
			{
				$row->product_full_image = $upload->getPhysicalName();	
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
			
			if ($isNew)
			{
				// set price
				$price = JTable::getInstance( 'Productprices', 'TiendaTable' );
				$price->product_id = $row->id;
				$price->product_price = JRequest::getVar( 'product_price' );
				if (!$price->save())
				{
					$this->messagetype 	= 'notice';
					$this->message .= " :: ".$price->getError();
				}
				
				// set category
				$category = JTable::getInstance( 'Productcategories', 'TiendaTable' );
				$category->product_id = $row->id;
				$category->category_id = JRequest::getVar( 'category_id' );
				if (!$category->save())
				{
					$this->messagetype 	= 'notice';
					$this->message .= " :: ".$category->getError();
				}
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
            case "saveprev":
            	$redirect .= '&view='.$this->get('suffix');
                // get prev in list
		        JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
		        $surrounding = TiendaHelperProduct::getSurrounding( $model->getId() );
		        if (!empty($surrounding['prev']))
		        {
                    $redirect .= '&task=edit&id='.$surrounding['prev'];
		        }
              break;
            case "savenext":
    	        $redirect .= '&view='.$this->get('suffix');
                // get next in list
                JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
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
	 * Adds a thumbnail image to item
	 * @return unknown_type
	 */
	function addfile( $fieldname = 'product_full_image_new', $path = 'products_images' )
	{
		JLoader::import( 'com_tienda.library.file', JPATH_ADMINISTRATOR.DS.'components' );
		$upload = new TiendaFile();
		// handle upload creates upload object properties
		$upload->handleUpload( $fieldname );
		// then save image to appropriate folder
		if ($path == 'products_images') { $path = Tienda::getPath( 'products_images' ); }
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
		
		// TODO Make thumbnail also
		
		$upload->full_path = $dest;
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
		$view->set( '_action', "index.php?option=com_tienda&controller=products&task=selectcategories&tmpl=component&id=".$model->getId() );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'selectcategories' );
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
			base64_decode( JRequest::getVar( 'return' ) ) : "index.php?option=com_tienda&controller=products&task=selectcategories&tmpl=component&id=".$id;
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
        
        JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
        $csvs = TiendaHelperProduct::getProductAttributeCSVs( $row->product_id );
        $items = TiendaHelperProduct::reconcileProductAttributeCSVs( $row->product_id, '0', $items, $csvs );
                
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
        $view->set( '_action', "index.php?option=com_tienda&controller=products&task=setquantities&id={$model->getId()}&tmpl=component" );
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

        $redirect = "index.php?option=com_tienda&controller=products&task=setquantities&id={$row->product_id}&tmpl=component";
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
		$view->set( '_action', "index.php?option=com_tienda&controller=products&task=setprices&id={$model->getId()}&tmpl=component" );
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
		
		$redirect = "index.php?option=com_tienda&controller=products&task=setprices&id={$row->product_id}&tmpl=component";
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

		$redirect = "index.php?option=com_tienda&controller=products&task=setprices&id={$row->product_id}&tmpl=component";
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
        $view->set( '_action', "index.php?option=com_tienda&controller=products&task=setattributes&tmpl=component&id=".$model->getId() );
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
        
        $redirect = "index.php?option=com_tienda&controller=products&task=setattributes&id={$row->product_id}&tmpl=component";
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

        $redirect = "index.php?option=com_tienda&controller=products&task=setattributes&id={$row->product_id}&tmpl=component";
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
        $view->set( '_action', "index.php?option=com_tienda&controller=products&task=setattributeoptions&tmpl=component&id=".$model->getId() );
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
        
        $redirect = "index.php?option=com_tienda&controller=products&task=setattributeoptions&id={$row->productattribute_id}&tmpl=component";
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

        $redirect = "index.php?option=com_tienda&controller=products&task=setattributeoptions&id={$row->productattribute_id}&tmpl=component";
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
        $view->set( '_action', "index.php?option=com_tienda&controller=products&task=setfiles&tmpl=component&id=".$model->getId() );
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
        $row->file_name = JRequest::getVar( 'createproductfile_name' );
        $row->file_enabled = JRequest::getVar( 'createproductfile_enabled' );
        $row->purchase_required = JRequest::getVar( 'createproductfile_purchaserequired' );

        $fieldname = 'createproductfile_file';
        JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
        $path = TiendaHelperProduct::getFilePath( $row->product_id );
        $userfile = JRequest::getVar( $fieldname, '', 'files', 'array' );
        if (!empty($userfile['size']))
        {
            if ($upload = $this->addfile( $fieldname, $path ))
            {
            	if (empty($row->file_name)) { $row->file_name = $upload->proper_name; }
                $row->file_extension = $upload->getExtension();
                $row->file_path = $upload->full_path;
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
        
        $redirect = "index.php?option=com_tienda&controller=products&task=setfiles&id={$row->product_id}&tmpl=component";
        $redirect = JRoute::_( $redirect, false );
        
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
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
        
        foreach (@$cids as $cid)
        {
            $row->load( $cid );
            $row->file_name = $name[$cid];
            $row->ordering = $ordering[$cid];
            $row->file_enabled = $enabled[$cid];
            $row->purchase_required = $purchaserequired[$cid];

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

        $redirect = "index.php?option=com_tienda&controller=products&task=setfiles&id={$row->product_id}&tmpl=component";
        $redirect = JRoute::_( $redirect, false );
        
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
}

?>
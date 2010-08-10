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

class TiendaControllerProducts extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->set('suffix', 'products');
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

		$date = JFactory::getDate();
		$state['order'] = 'tbl.ordering';
		$state['direction'] = 'ASC';
		$state['filter_published'] = 1;
		$state['filter_published_date'] = $date->toMySQL();
		$state['filter_enabled']  = 1;
		$state['search']          = $app->getUserStateFromRequest($ns.'.search', 'search', '', '');
		$state['filter_category'] = $app->getUserStateFromRequest($ns.'.category', 'filter_category', '', 'int');

		if ($state['search']) {
			$state['filter']      = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
		} else {
			$state['filter']      = '';
		}

		if ($state['filter_category'])
		{
			JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
			$cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
			$cmodel->setId($state['filter_category']);
			if ($item = $cmodel->getItem())
			{
				$state['category_name'] = $item->category_name;
			}

		}
    		elseif (!$state['search'])
		{
			$state['filter_category'] = '0';
		}
		
		if ($state['search'] && $state['filter_category'] == '1')
		{
			$state['filter_category'] = '';
		}

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		return $state;
	}

	/**
	 * Displays a product category
	 *
	 * (non-PHPdoc)
	 * @see tienda/admin/TiendaController#display($cachable)
	 */
	function display()
	{
		JRequest::setVar( 'view', $this->get('suffix') );
		JRequest::setVar( 'search', false );
		$view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
		$model  = $this->getModel( $this->get('suffix') );
		$this->_setModelState();

		if (!TiendaConfig::getInstance()->get('display_out_of_stock'))
		{
		    $model->setState('filter_quantity_from', '1');
		}
		
		// get the category we're looking at
		$filter_category = $model->getState('filter_category', JRequest::getVar('filter_category'));
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
		$cat = $cmodel->getTable();
		$cat->load( $filter_category );

		// set the title based on the selected category
		$title = (empty($cat->category_name)) ? JText::_( "All Categories" ) : JText::_($cat->category_name);
		$level = (!empty($filter_category)) ? $filter_category : '1';

        // breadcrumb support
        $app = JFactory::getApplication();
        $pathway =& $app->getPathway();
        $category_itemid = JRequest::getInt('Itemid', Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $filter_category, true ) );
        $items = Tienda::getClass( "TiendaHelperCategory", 'helpers.category' )->getPathName( $filter_category, 'array' );
        if (!empty($items))
        {
            // add the categories to the pathway
            Tienda::getClass( "TiendaHelperPathway", 'helpers.pathway' )->insertCategories( $items, $category_itemid );
        }
        // add the item being viewed to the pathway
        $pathway_values = $pathway->getPathway();
	    $pathway_names = Tienda::getClass( "TiendaHelperBase", 'helpers._base' )->getColumn( $pathway_values, 'name' );
        $pathway_links = Tienda::getClass( "TiendaHelperBase", 'helpers._base' )->getColumn( $pathway_values, 'link' );
        $cat_url = "index.php?Itemid=$category_itemid";
        if (!in_array($cat->category_name, $pathway_names))
        {
            $pathway->addItem( $title );
        }
		
		// get the category's sub categories
		$cmodel->setState('filter_level', $level);
		$cmodel->setState('filter_enabled', '1');
		$cmodel->setState('order', 'tbl.lft');
		$cmodel->setState('direction', 'ASC');
		if ($citems =& $cmodel->getList())
		{
		    foreach ($citems as $item)
            {
                $itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $item->category_id, true );
                $item->itemid = JRequest::getInt('Itemid', $itemid);
            }
		}

		// get the products to be displayed in this category
		if ($items =& $model->getList())
		{
		    foreach ($items as $item)
		    {
                $itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->product( $item->product_id, $filter_category, true );
                $item->itemid = JRequest::getInt('Itemid', $itemid);		        
		    }
		}

		$view->assign( 'level', $level);
		$view->assign( 'title', $title );
		$view->assign( 'cat', $cat );
		$view->assign( 'citems', $citems );
		$view->assign( 'items', $items );
		$view->set('_doTask', true);
		$view->setModel( $model, true );

		// using a helper file, we determine the category's layout
		$layout = Tienda::getClass( 'TiendaHelperCategory', 'helpers.category' )->getLayout( $cat->category_id );
		$view->setLayout($layout);

		$view->display();
		$this->footer();
		return;
	}

	/**
	 * Displays a single product
	 * (non-PHPdoc)
	 * @see tienda/site/TiendaController#view()
	 */
	function view()
	{
		JRequest::setVar( 'view', $this->get('suffix') );
		$model  = $this->getModel( $this->get('suffix') );
		$model->getId();
		$row = $model->getItem();
		
		$filter_category = $model->getState('filter_category', JRequest::getVar('filter_category'));
	    if (empty($filter_category)) 
        { 
            $categories = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getCategories( $row->product_id );
            if (!empty($categories))
            {
                $filter_category = $categories[0];
            }
        }
        		
		if (empty($row->product_enabled))
		{
			$redirect = "index.php?option=com_tienda&view=products&task=display&filter_category=".$filter_category;
			$redirect = JRoute::_( $redirect, false );
			$this->message = JText::_( "CANNOT VIEW DISABLED PRODUCT" );
			$this->messagetype = 'notice';
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		
		Tienda::load( 'TiendaArticle', 'library.article' );
		$product_description = TiendaArticle::fromString( $row->product_description );


		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
		$cat = $cmodel->getTable();
		$cat->load( $filter_category );

		$view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
		$view->set('_doTask', true);
		$view->assign( 'row', $row );
		$view->assign( 'cat', $cat );
		
        // breadcrumb support
        $app = JFactory::getApplication();
        $pathway =& $app->getPathway();
        $category_itemid = JRequest::getInt('Itemid', Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $filter_category, true ) );
        $items = Tienda::getClass( "TiendaHelperCategory", 'helpers.category' )->getPathName( $filter_category, 'array' );
        if (!empty($items))
        {
            // add the categories to the pathway
            Tienda::getClass( "TiendaHelperPathway", 'helpers.pathway' )->insertCategories( $items, $category_itemid );
        }
        // add the item being viewed to the pathway
        $pathway->addItem( $row->product_name );

		// Check If the inventroy is set then it will go for the inventory product quantities
		if ($row->product_check_inventory)
		{
			$inventoryList = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getProductQuantities( $row->product_id );
        
            if (!TiendaConfig::getInstance()->get('display_out_of_stock') && empty($inventoryList))
            {
                // redirect
                $redirect = "index.php?option=com_tienda&view=products&task=display&filter_category=".$filter_category;
                $redirect = JRoute::_( $redirect, false );
                $this->message = JText::_( "CANNOT VIEW PRODUCT" );
                $this->messagetype = 'notice';
                $this->setRedirect( $redirect, $this->message, $this->messagetype );
                return;
            }
			
			// if there is no entry of product in the productquantities
			if (count($inventoryList) == 0)
			{
				$inventoryList[''] = '0';
			}
			$view->assign( 'inventoryList', $inventoryList );
		}

		$view->assign('product_description', $product_description );
		$view->assign( 'files', $this->getFiles( $row->product_id ) );
		$view->assign( 'product_buy', $this->getAddToCart( $row->product_id ) );
		$view->assign( 'product_relations', $this->getRelationshipsHtml( $row->product_id ) );
		$view->setModel( $model, true );

		// using a helper file, we determine the product's layout
		$layout = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getLayout( $row->product_id, array( 'category_id'=>$cat->category_id ) );
		$view->setLayout($layout);

        $dispatcher =& JDispatcher::getInstance();
        
        ob_start();
        $dispatcher->trigger( 'onDisplayProductAttributeOptions', array( $row->product_id ) );
        $view->assign( 'onDisplayProductAttributeOptions', ob_get_contents() );
        ob_end_clean();
        
        ob_start();
        $dispatcher->trigger( 'onBeforeDisplayProduct', array( $row->product_id ) );
        $view->assign( 'onBeforeDisplayProduct', ob_get_contents() );
        ob_end_clean();
        
        ob_start();
        $dispatcher->trigger( 'onAfterDisplayProduct', array( $row->product_id ) );
        $view->assign( 'onAfterDisplayProduct', ob_get_contents() );
        ob_end_clean();
		
		$view->display();
		$this->footer();
		return;
	}
	
    /**
     * Gets a product's add to cart section
     * formatted for display
     *
     * @param int $address_id
     * @return string html
     */
    function getAddToCart( $product_id, $values=array() )
    {
        $html = '';

        $view   =& $this->getView( 'products', 'html' );
        $model  = $this->getModel( $this->get('suffix') );
        $model->setId( $product_id );
        $row = $model->getItem();
        
        $view->set( '_controller', 'products' );
        $view->set( '_view', 'products' );
        $view->set( '_doTask', true);
        $view->set( 'hidemenu', true);
        $view->setModel( $model, true );
        $view->setLayout( 'product_buy' );        
        $view->assign( 'item', $row );
        $view->assign('product_id', $product_id);
        $view->assign('values', $values);
        $filter_category = $model->getState('filter_category', JRequest::getInt('filter_category', (int) @$values['filter_category'] ));
        $view->assign('filter_category', $filter_category);
        $view->assign('validation', "index.php?option=com_tienda&view=products&view=products&task=validate&format=raw" );
        
        $config = TiendaConfig::getInstance();
        $show_tax = $config->get('display_prices_with_tax');
        $view->assign( 'show_tax', $show_tax );
        $view->assign( 'tax', 0 );
        $view->assign( 'taxtotal', '' );
        $view->assign( 'shipping_cost_link', '' );
        
        if ($show_tax)
        {
            // finish TiendaHelperUser::getGeoZone -- that's why this isn't working
            Tienda::load('TiendaHelperUser', 'helpers.user');
            $geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
            if (empty($geozones))
            {
                // use the default
                $table = JTable::getInstance('Geozones', 'TiendaTable');
                $table->load(array('geozone_id'=>TiendaConfig::getInstance()->get('default_tax_geozone')));
                $geozones = array( $table );
            }
            
            $taxtotal = TiendaHelperProduct::getTaxTotal($product_id, $geozones);
            $tax = $taxtotal->tax_total;
            $view->assign( 'taxtotal', $taxtotal );
            $view->assign( 'tax', $tax );
        }
        
        // TODO What about this??
        $show_shipping = $config->get('display_prices_with_shipping');
        if ($show_shipping)
        {
            $article_link = $config->get('article_shipping', '');
            $shipping_cost_link = JRoute::_('index.php?option=com_content&view=article&id='.$article_link);
            $view->assign( 'shipping_cost_link', $shipping_cost_link );
        }
        
        $invalidQuantity = '0';
        if (empty($values))
        {
            $product_qty = '1';
            // get the default set of attribute_csv
            $default_attributes = TiendaHelperProduct::getDefaultAttributes( $product_id );
            sort($default_attributes);
            $attributes_csv = implode( ',', $default_attributes );
            $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $attributes_csv );
            if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity ) 
            {
                $invalidQuantity = '1';
            }
        }
        
        if (!empty($values))
        {
            $product_id = !empty( $values['product_id'] ) ? (int) $values['product_id'] : JRequest::getInt( 'product_id' );
            $product_qty = !empty( $values['product_qty'] ) ? (int) $values['product_qty'] : '1';
            
            // TODO only display attributes available based on the first selected attribute?
            $attributes = array();
            foreach ($values as $key=>$value)
            {
                if (substr($key, 0, 10) == 'attribute_')
                {
                    $attributes[] = $value;
                }
            }
            sort($attributes);
            $attributes_csv = implode( ',', $attributes );
            
            // Integrity checks on quantity being added
            if ($product_qty < 0) { $product_qty = '1'; } 
    
            // using a helper file to determine the product's information related to inventory     
            $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $attributes_csv );    
            if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity ) 
            {
                $invalidQuantity = '1';
            }
        }
        
        $view->assign( 'availableQuantity', $availableQuantity );
        $view->assign( 'invalidQuantity', $invalidQuantity );
        
        $dispatcher =& JDispatcher::getInstance();
        
        ob_start();
        $dispatcher->trigger( 'onDisplayProductAttributeOptions', array( $row->product_id ) );
        $view->assign( 'onDisplayProductAttributeOptions', ob_get_contents() );
        ob_end_clean();

        ob_start();
        $view->display();
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * 
     */
    function updateAddToCart()
    {
        $response = array();
        $response['msg'] = '';
        $response['error'] = '';
            
        // get elements from post
        $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
        
        // convert elements to array that can be binded
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );             
        $values = TiendaHelperBase::elementsToArray( $elements );
        
        // now get the summary
        $html = $this->getAddToCart( $values['product_id'], $values );
        
        $response['msg'] = $html;
        // encode and echo (need to echo to send back to browser)
        echo json_encode($response);
        return;
    }
    
	/**
	 * Gets a product's files list
	 * formatted for display
	 *
	 * @param int $address_id
	 * @return string html
	 */
	function getFiles( $product_id )
	{
		$html = '';

		// get the product's files
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'ProductFiles', 'TiendaModel' );
		$model->setState( 'filter_product', $product_id );
		$model->setState( 'filter_enabled', 1 );
		//$model->setState( 'filter_purchaserequired', 1 );
		$items = $model->getList();

		if (!empty($items))
		{
            Tienda::load( 'TiendaHelperBase', 'helpers._base' );
            $helper = TiendaHelperBase::getInstance( 'ProductDownload', 'TiendaHelper' );
            $filtered_items = $helper->filterRestricted( $items, JFactory::getUser()->id );
        
			$view   = $this->getView( 'products', 'html' );
			$view->set( '_controller', 'products' );
			$view->set( '_view', 'products' );
			$view->set( '_doTask', true);
			$view->set( 'hidemenu', true);
			$view->setModel( $model, true );
			$view->setLayout( 'product_files' );
			$view->set('downloadItems', $filtered_items[0]);
			$view->set('nondownloadItems', $filtered_items[1]);
			$view->set('product_id', $product_id);

			ob_start();
			$view->display();
			$html = ob_get_contents();
			ob_end_clean();
		}

		return $html;
	}
	
    /**
     * Gets a product's related items
     * formatted for display
     *
     * @param int $address_id
     * @return string html
     */
    function getRelationshipsHtml( $product_id )
    {
        $html = '';

        // get the list
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductRelations', 'TiendaModel' );
        $model->setState( 'filter_product', $product_id );
        $model->setState( 'filter_relation', 'relates' );
        if ($items = $model->getList())
        {
            foreach ($items as $item)
            {
                if ($item->product_id_from == $product_id)
                {
                    // display the _product_to
                    $item->product_id = $item->product_id_to;
                    $item->product_name = $item->product_name_to;
                    $item->product_model = $item->product_model_to;
                    $item->product_sku = $item->product_sku_to;
                    $item->product_price = $item->product_price_to;
                } 
                    else 
                { 
                    // display the _product_from
                    $item->product_id = $item->product_id_from;
                    $item->product_name = $item->product_name_from;
                    $item->product_model = $item->product_model_from;
                    $item->product_sku = $item->product_sku_from;
                    $item->product_price = $item->product_price_from;
                }
            }
        }

        if (!empty($items))
        {
            $view   = $this->getView( 'products', 'html' );
            $view->set( '_controller', 'products' );
            $view->set( '_view', 'products' );
            $view->set( '_doTask', true);
            $view->set( 'hidemenu', true);
            $view->setModel( $model, true );
            $view->setLayout( 'product_relations' );
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
	 * downloads a file
	 *
	 * @return void
	 */
	function downloadFile()
	{
		$user =& JFactory::getUser();
		$productfile_id = intval( JRequest::getvar( 'id', '', 'request', 'int' ) );
		$product_id = intval( JRequest::getvar( 'product_id', '', 'request', 'int' ) );
		$link = 'index.php?option=com_tienda&controller=products&view=products&task=view&id='.$product_id;

		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance( 'ProductDownload', 'TiendaHelper' );

		if ( !$canView = $helper->canDownload( $productfile_id, JFactory::getUser()->id ) )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_( 'Not Authorized to Download File' );
			$this->setRedirect( $link, $this->message, $this->messagetype );
			return false;
		}

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$productfile = JTable::getInstance( 'ProductFiles', 'TiendaTable' );
		$productfile->load( $productfile_id );
		if (empty($productfile->productfile_id))
		{
			$this->messagetype = 'notice';
			$this->message = JText::_( 'Invalid File' );
			$this->setRedirect( $link, $this->message, $this->messagetype );
			return false;
		}

		// log and download
		Tienda::load( 'TiendaFile', 'library.file' );
		// Log the download
		$productfile->logDownload( $user->id );
		if ($downloadFile = TiendaFile::download( $productfile ))
		{
			$link = JRoute::_( $link, false );
			$this->setRedirect( $link );
		}
	}

	//  /**
	//   *
	//   * @return unknown_type
	//   */
	//    function quickadd()
	//    {
	//        $model  = $this->getModel( $this->get('suffix') );
	//        $model->getId();
	//        $row = $model->getItem();
	//        if (empty($row->product_enabled))
	//        {
	//            $redirect = "index.php?option=com_tienda&view=products";
	//            $redirect = JRoute::_( $redirect, false );
	//            $this->message = JText::_( "Invalid Product" );
	//            $this->messagetype = 'notice';
	//            JFactory::getApplication()->redirect( $redirect, $this->message, $this->messagetype );
	//            return;
	//        }
	//
	//        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
	//        $view->set('hidemenu', true);
	//        $view->set('_doTask', true);
	//        $view->setModel( $model, true );
	//        $view->setLayout('quickadd');
	//        $view->display();
	//        $this->footer();
	//        return;
	//    }

	/**
	 *
	 * @return void
	 */
	function search()
	{
		JRequest::setVar( 'view', $this->get('suffix') );
		JRequest::setVar( 'layout', 'search' );
		JRequest::setVar( 'search', true );
		parent::display();
	}
	
    /**
     * Verifies the fields in a submitted form.  Uses the table's check() method.
     * Will often be overridden. Is expected to be called via Ajax 
     * 
     * @return unknown_type
     */
    function validate()
    {
        $response = array();
        $response['msg'] = '';
        $response['error'] = '';
            
        // get elements from post
            $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

            // validate it using table's ->check() method
            if (empty($elements))
            {
                // if it fails check, return message
                $response['error'] = '1';
                $response['msg'] = '
                    <dl id="system-message">
                    <dt class="notice">notice</dt>
                    <dd class="notice message fade">
                        <ul style="padding: 10px;">'.
                        JText::_("Could not process form")                        
                        .'</ul>
                    </dd>
                    </dl>
                    ';
                echo ( json_encode( $response ) );
                return;
            }

        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper = TiendaHelperBase::getInstance();
        if (!TiendaConfig::getInstance()->get('shop_enabled', '1'))
        {
            $response['msg'] = $helper->generateMessage( "Shop Disabled" );
            $response['error'] = '1';
            echo ( json_encode( $response ) );
            return false;    
        }
            
        // convert elements to array that can be binded             
        $values = TiendaHelperBase::elementsToArray( $elements );
        $product_id = !empty( $values['product_id'] ) ? (int) $values['product_id'] : JRequest::getInt( 'product_id' );
        $product_qty = !empty( $values['product_qty'] ) ? (int) $values['product_qty'] : '1';
        
        $attributes = array();
        foreach ($values as $key=>$value)
        {
            if (substr($key, 0, 10) == 'attribute_')
            {
                $attributes[] = $value;
            }
        }
        sort($attributes);
        $attributes_csv = implode( ',', $attributes );

        // Integrity checks on quantity being added
        if ($product_qty < 0) { $product_qty = '1'; } 

        // using a helper file to determine the product's information related to inventory     
        $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $attributes_csv );    
        if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity ) 
        {
            $response['msg'] = $helper->generateMessage( JText::sprintf( 'NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $product_qty ) );
            $response['error'] = '1';
            echo ( json_encode( $response ) );
            return false;
        }
        
        // create cart object out of item properties
        $item = new JObject;
        $item->user_id     = JFactory::getUser()->id;
        $item->product_id  = (int) $product_id;
        $item->product_qty = (int) $product_qty;
        $item->product_attributes = $attributes_csv;
        $item->vendor_id   = '0'; // vendors only in enterprise version
        
        // no matter what, fire this validation plugin event for plugins that extend the checkout workflow
        $results = array();
        $dispatcher =& JDispatcher::getInstance();
        $results = $dispatcher->trigger( "onValidateAddToCart", array( $item, $values ) );

        for ($i=0; $i<count($results); $i++)
        {
            $result = $results[$i];
            if (!empty($result->error))
            {
                Tienda::load( 'TiendaHelperBase', 'helpers._base' );
                $helper = TiendaHelperBase::getInstance();
                $response['msg'] = $helper->generateMessage( $result->message );
                $response['error'] = '1';
                echo ( json_encode( $response ) );
                return;
            }
                else
            {
                // if here, all is OK
                $response['error'] = '0';
            }
        }
        echo ( json_encode( $response ) );
        return;
    }
    
    /**
     * Verifies the fields in a submitted form.
     * Then adds the item to the users cart 
     * 
     * @return unknown_type
     */
    function addToCart()
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );
        $product_id = JRequest::getInt( 'product_id' );
        $product_qty = JRequest::getInt( 'product_qty' );
        $filter_category = JRequest::getInt( 'filter_category' );
        
        // set the default redirect URL
        $redirect = "index.php?option=com_tienda&view=products&task=view&id=$product_id&filter_category=$filter_category";
        $redirect = JRoute::_( $redirect, false );
        
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper = TiendaHelperBase::getInstance();
        if (!TiendaConfig::getInstance()->get('shop_enabled', '1'))
        {
            $this->messagetype  = 'notice';         
            $this->message      = JText::_( "Shop Disabled" );
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;
        }
            
        // convert elements to array that can be binded             
        $values = JRequest::get('post');
        
        $attributes = array();
        foreach ($values as $key=>$value)
        {
            if (substr($key, 0, 10) == 'attribute_')
            {
                $attributes[] = $value;
            }
        }
        sort($attributes);
        $attributes_csv = implode( ',', $attributes );
        
        // Integrity checks on quantity being added
        if ($product_qty < 0) { $product_qty = '1'; } 

        // using a helper file to determine the product's information related to inventory     
        $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $attributes_csv );    
        if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity ) 
        {
            $this->messagetype  = 'notice';         
            $this->message      = JText::_( JText::sprintf( 'NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $product_qty ) );
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;            
        }
        
        // do the item's charges recur? does the cart already have a subscription in it?  if so, fail with notice
        $product = JTable::getInstance('Products', 'TiendaTable');
        $product->load( array( 'product_id'=>$product_id ) );
        
        $user = JFactory::getUser();
        $cart_id = $user->id;
        $id_type = "user_id";
        if (empty($user->id))
        {
            $session =& JFactory::getSession();
            $cart_id = $session->getId();
            $id_type = "session";
        }
        
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $carthelper = new TiendaHelperCarts();
        
        $cart_recurs = $carthelper->hasRecurringItem( $cart_id, $id_type );
        if ($product->product_recurs && $cart_recurs)
        {
            $this->messagetype  = 'notice';         
            $this->message      = JText::_( "Cart Already Recurs" );
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;            
        }
        
        if ($product->product_recurs)
        {
            $product_qty = '1';
        }
        
        // create cart object out of item properties
        $item = new JObject;
        $item->user_id     = JFactory::getUser()->id;
        $item->product_id  = (int) $product_id;
        $item->product_qty = (int) $product_qty;
        $item->product_attributes = $attributes_csv;
        $item->vendor_id   = '0'; // vendors only in enterprise version

        // does the user/cart match all dependencies?
        $canAddToCart = $carthelper->canAddItem( $item, $cart_id, $id_type );
        if (!$canAddToCart)
        {
            $this->messagetype  = 'notice';         
            $this->message      = JText::_( "Cannot Add Item to Cart" ) . " - " . $carthelper->getError();
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;            
        }
        
        // no matter what, fire this validation plugin event for plugins that extend the checkout workflow
        $results = array();
        $dispatcher =& JDispatcher::getInstance();
        $results = $dispatcher->trigger( "onBeforeAddToCart", array( $item, $values ) );

        for ($i=0; $i<count($results); $i++)
        {
            $result = $results[$i];
            if (!empty($result->error))
            {
                $this->messagetype  = 'notice';         
                $this->message      = $result->message;
                $this->setRedirect( $redirect, $this->message, $this->messagetype );
                return;
            }
        }
        
        // if here, add to cart
        
        // After login, session_id is changed by Joomla, so store this for reference
        $session =& JFactory::getSession(); 
        $session->set( 'old_sessionid', $session->getId() );
        
        // add the item to the cart
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        TiendaHelperCarts::updateCart( array( $item ) );
        
        // fire plugin event
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterAddToCart', array( $item, $values ) );
        
        // get the 'success' redirect url
        // TODO Enable redirect via base64_encoded urls?
        switch (TiendaConfig::getInstance()->get('addtocartaction', 'redirect')) 
        {
            case "redirect":
                $returnUrl = base64_encode( $redirect );
                $redirect = JRoute::_( "index.php?option=com_tienda&view=carts", false );
                if (strpos($redirect, '?') === false) { $redirect .= "?return=".$returnUrl; } else { $redirect .= "&return=".$returnUrl; }
                break;
            case "0":
            case "none":
                break;
            case "lightbox":
            default:
                // TODO Figure out how to get the lightbox to display even after a redirect
//                $lightbox_attribs = array(); 
//                $lightbox['update'] = false; 
//                if ($lightbox_width = TiendaConfig::getInstance()->get( 'lightbox_width' )) { $lightbox_attribs['width'] = $lightbox_width; };
//                echo TiendaUrl::popup( "index.php?option=com_tienda&view=carts&task=confirmAdd&tmpl=component", $text, $lightbox_attribs );
                break;
        }
        
        $this->messagetype  = 'message';
        $this->message      = JText::_( "Item Added to Your Cart" );
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
        return;
        
    }
}

?>
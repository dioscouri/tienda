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

        Tienda::load('TiendaHelperUser', 'helpers.user');       
        $user_id = JFactory::getUser()->id;
		$state['filter_group']   = TiendaHelperUser::getUserGroup($user_id);
		 
		$date = JFactory::getDate();
		$state['order'] = 'tbl.ordering';
		$state['direction'] = 'ASC';
		$state['filter_published'] = 1;
		$state['filter_published_date'] = $date->toMySQL();
		$state['filter_enabled']  = 1;
		$state['search']          = $app->getUserStateFromRequest($ns.'.search', 'search', '', '');
		$state['search_type']          = $app->getUserStateFromRequest($ns.'.search_type', 'search_type', '', '');
		$state['filter_category'] = $app->getUserStateFromRequest($ns.'.category', 'filter_category', '', 'int');
        $state['filter_price_from']     = $app->getUserStateFromRequest($ns.'price_from', 'filter_price_from', '0', 'int');
        $state['filter_price_to']       = $app->getUserStateFromRequest($ns.'price_to', 'filter_price_to', '', '');

		if ($state['search']) 
		{
		    $filter = $state['filter'] = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
		    
		    // apply additional 'AND' filter if requested by module and unset filter state
    		switch ($state['search_type'])
            {
                case "4":
                    $state['filter_name'] = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
                    $state['filter'] = '';
                    break;
                case "3":
                    $state['filter_namedescription'] = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
                    $state['filter'] = '';
                    break;
                case "2":
                    $state['filter_sku'] = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
                    $state['filter'] = '';
                    break;
                case "1":
                    $state['filter_model'] = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
                    $state['filter'] = '';
                    break;
                case "0":
                default:
                    break;
            }
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
                $item->itemid = (!empty($itemid)) ? $itemid : JRequest::getInt('Itemid', $itemid);
            }
		}

		// get the products to be displayed in this category
		if ($items =& $model->getList())
		{
		    $this->display_cartbutton = TiendaConfig::getInstance()->get('display_category_cartbuttons', '1');
		    foreach ($items as $item)
		    {
                $itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->product( $item->product_id, $filter_category, true );
                $item->itemid = JRequest::getInt('Itemid', $itemid);

                $item->product_buy = $this->getAddToCart($item->product_id);
		    }
		}
		
		if (($model->getState('filter_price_from') > '0') || ($model->getState('filter_price_to') > '0'))
		{
            $url = "index.php?option=com_tienda&view=products&filter_category=$filter_category&filter_price_from=&filter_price_to=";
            $from = TiendaHelperBase::currency( $model->getState('filter_price_from') );  
            $to = ($model->getState('filter_price_to') > 0) ? TiendaHelperBase::currency( $model->getState('filter_price_to') ) : JText::_( 'MAXIMUM PRICE' );
            $view->assign( 'remove_pricefilter_url', $url);
            $view->assign( 'pricefilter_applied', true);
            $view->assign( 'filterprice_from', $from);
            $view->assign( 'filterprice_to', $to);
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
	    $this->display_cartbutton = true;
	    
		JRequest::setVar( 'view', $this->get('suffix') );
		$model  = $this->getModel( $this->get('suffix') );
		$model->getId();
  
		Tienda::load('TiendaHelperUser', 'helpers.user');       
        $user_id = JFactory::getUser()->id;
        $filter_group = TiendaHelperUser::getUserGroup($user_id, $model->getId());
        $model->setState('filter_group', $filter_group);
		$row = $model->getItem( false ); // use the state

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
		$view->assign( 'product_comments', $this->getComments($row->product_id) );
		$view->assign('product_description', $product_description );
		$view->assign( 'files', $this->getFiles( $row->product_id ) );
		$view->assign( 'product_buy', $this->getAddToCart( $row->product_id ) );
		$view->assign( 'product_relations', $this->getRelationshipsHtml( $row->product_id, 'relates' ) );
		$view->assign( 'product_children', $this->getRelationshipsHtml( $row->product_id, 'parent' ) );
		$view->assign( 'product_requirements', $this->getRelationshipsHtml( $row->product_id, 'requires' ) );
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

        $view   = $this->getView( 'products', 'html' );
        //$model  = $this->getModel( $this->get('suffix') );
        $model = JModel::getInstance('Products', 'TiendaModel');
        $model->setId( $product_id );
        
        Tienda::load('TiendaHelperUser', 'helpers.user');       
        $user_id = JFactory::getUser()->id;
        $filter_group = TiendaHelperUser::getUserGroup($user_id, $product_id);
        $model->setState('filter_group', $filter_group);
        
        //$model->_item = '';
        $row = $model->getItem( false );
        if ($row->product_notforsale || TiendaConfig::getInstance()->get('shop_enabled') == '0')
        {
            return $html;
        }
        
        $view->set( '_controller', 'products' );
        $view->set( '_view', 'products' );
        $view->set( '_doTask', true);
        $view->set( 'hidemenu', true);
        $view->setModel( $model, true );
        $view->setLayout( 'product_buy' );        
        $view->assign('product_id', $product_id);
        $view->assign('values', $values);
        $filter_category = $model->getState('filter_category', JRequest::getInt('filter_category', (int) @$values['filter_category'] ));
        $view->assign('filter_category', $filter_category);
        $view->assign('validation', "index.php?option=com_tienda&view=products&task=validate&format=raw" );
        
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
            $row->taxtotal = $taxtotal;
            $row->tax = $tax;
            // @v0.6.1, we're leaving these here for 2 more versions, so as not to break existing templates
            // @v0.8.0, these are gone
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

        $quantity_min = 1;
        if ($row->quantity_restriction) { $quantity_min = $row->quantity_min; }
        
        $invalidQuantity = '0';
        if (empty($values))
        {
            $product_qty = $quantity_min;
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
            $product_qty = !empty( $values['product_qty'] ) ? (int) $values['product_qty'] : $quantity_min;
            
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
            
            // adjust the displayed price based on the selected attributes
            $table = JTable::getInstance('ProductAttributeOptions', 'TiendaTable');
            foreach ($attributes as $attrib_id)
            {
                // load the attrib's object
                $table->load( $attrib_id );
                // update the price
                //$row->price = $row->price + floatval( "$table->productattributeoption_prefix"."$table->productattributeoption_price");
                
                // is not + or -
                if($table->productattributeoption_prefix == '=')
                {
                	$row->price = floatval( $table->productattributeoption_price );
                }
                else
                {
                    $row->price = $row->price + floatval( "$table->productattributeoption_prefix"."$table->productattributeoption_price");
                }
                $row->sku .=  $table->productattributeoption_code;
            }
        }
        
        $row->_product_quantity = $product_qty;
        
        $view->assign( 'display_cartbutton', $this->display_cartbutton );
        $view->assign( 'availableQuantity', $availableQuantity );
        $view->assign( 'invalidQuantity', $invalidQuantity );
        $view->assign( 'item', $row );
        
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
     * Used whenever an attribute selection is changed,
     * to update the price and/or attribute selectlists
     * 
     * @return unknown_type
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
        $this->display_cartbutton = true;
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

		// get the user's active subscriptions to this product, if possible
		$submodel = JModel::getInstance( 'Subscriptions', 'TiendaModel' );
		$submodel->setState('filter_userid', JFactory::getUser()->id);
		$submodel->setState('filter_productid', $product_id);
		$subs = $submodel->getList(); 
		
		if (!empty($items))
		{
		    // reconcile the list of files to the date the sub's files were last checked 
		    Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
		    $subhelper = new TiendaHelperSubscription();
		    $subhelper->reconcileFiles($subs);
		    
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
    function getRelationshipsHtml( $product_id, $relation_type='relates' )
    {
        $html = '';
        $validation = "";

        // get the list
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductRelations', 'TiendaModel' );
        $model->setState( 'filter_relation', $relation_type );
        
        switch ($relation_type)
        {
            case "requires":
                $model->setState( 'filter_product_from', $product_id );
                $check_quantity = false;
                $layout = 'product_requirements';
                break;
            case "parent":
            case "child":
            case "children":
                $model->setState( 'filter_product_from', $product_id );
                $check_quantity = true;
                $validation = "index.php?option=com_tienda&view=products&task=validateChildren&format=raw";
                $layout = 'product_children';
                break;
            case "relates":
                $model->setState( 'filter_product', $product_id );
                $check_quantity = false;
                $layout = 'product_relations';
                break;
            default:
                return $html;
                break;
        }

        if ($items = $model->getList())
        {
            $filter_category = $model->getState('filter_category', JRequest::getVar('filter_category'));
            if (empty($filter_category)) 
            { 
                $categories = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getCategories( $product_id );
                if (!empty($categories))
                {
                    $filter_category = $categories[0];
                }
            }
            $userId = JFactory::getUser()->id;
        	$config = TiendaConfig::getInstance();
        	$show_tax = $config->get('display_prices_with_tax'); 
            foreach ($items as $key=>$item)
            {
                if ($check_quantity)
                {
                    // TODO Unset $items[$key] if 
                    // this is out of stock && 
                    // check_inventory && 
                    // item for sale
                }
                
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
                
                $itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->product( $item->product_id, $filter_category, true );
                $item->itemid = JRequest::getInt('Itemid', $itemid);
             	if ($show_tax)
		        {		           
		            Tienda::load('TiendaHelperUser', 'helpers.user');
		            $geozones = TiendaHelperUser::getGeoZones( $userId );
		            if (empty($geozones))
		            {
		                // use the default
		                $table = JTable::getInstance('Geozones', 'TiendaTable');
		                $table->load(array('geozone_id'=>$config->get('default_tax_geozone')));
		                $geozones = array( $table );
		            }
		            
		            $taxtotal = TiendaHelperProduct::getTaxTotal($item->product_id, $geozones);
		            $tax = $taxtotal->tax_total;
		            $item->taxtotal = $taxtotal;
		            $item->tax = $tax;		            
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
            $view->setLayout( $layout );
            $view->set('items', $items);
            $view->set('product_id', $product_id);
            $view->assign('filter_category', $filter_category);
            $view->assign('validation', $validation );

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
		$link = 'index.php?option=c	om_tienda&controller=products&view=products&task=view&id='.$product_id;

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
		
		// After download complete it will update the productdownloads on the basis of the user 
		
        // geting the ProductDownloadId to updated for which productdownload_max  is greater then 0
	    $productToDownload = $helper->getProductDownloadInfo($productfile->productfile_id, $user->id);;
        
        if (!empty($productToDownload))
        {
            $productDownload = JTable::getInstance('ProductDownloads', 'TiendaTable');
            $productDownload->load($productToDownload->productdownload_id);
            $productDownload->productdownload_max = $productDownload->productdownload_max-1;
            if (!$productDownload->save()) 
            {
                // TODO in case product Download is not updating properly .
            }
        }
     
        if ($downloadFile = TiendaFile::download( $productfile ))
		{
			$link = JRoute::_( $link, false );
			$this->setRedirect( $link );
		}
	}

	/**
	 *
	 * @return void
	 */
	function search()
	{
		JRequest::setVar( 'view', $this->get('suffix') );
		JRequest::setVar( 'layout', 'search' );
		JRequest::setVar( 'search', true );
		
		$model  = $this->getModel( $this->get('suffix') );
		$this->_setModelState();

		if (!TiendaConfig::getInstance()->get('display_out_of_stock'))
		{
		    $model->setState('filter_quantity_from', '1');
		}
		parent::display();
        
		// TODO In the future, make "Redirect to Advanced Search from Search Module?" an option in Tienda Config
		
        //        $query = array();
        //        // now that we have it, let's clean the post and redirect to the advanced search page
        //        // use the itemid from the request, so the user stays on the same menu item as they previously were on
        //
        //        $query['Itemid'] = JRequest::getInt('Itemid');
        //        if (empty($query['Itemid'])) 
        //        {
        //            // TODO Use Tienda Router to get the item_id for a tienda shop link
        //            //$item_id = 0;
        //            //$query['Itemid'] = $item_id;
        //        }
        //
        //        $badchars = array('#','>','<','\\'); 
        //        $filter = trim(str_replace($badchars, '', JRequest::getString('filter', null, 'post')));
        //        $query['filter'] = $filter;        
        //        
        //        $query['view'] = 'search'; 
        //        
        //	    $uri = JURI::getInstance();
        //        $uri->setQuery($query);
        //        $uri->setVar('option', 'com_tienda');
        //
        //        $this->setRedirect(JRoute::_('index.php'.$uri->toString(array('query', 'fragment')), false));
	}
	
    /**
     * Verifies the fields in a submitted form.  Uses the table's check() method.
     * Will often be overridden. Is expected to be called via Ajax 
     * 
     * @return unknown_type
     */
    function validate()
    {
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper = new TiendaHelperBase();
        
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
            $response['msg'] = $helper->generateMessage(JText::_("Could not process form"));
            echo ( json_encode( $response ) );
            return;
        }

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
        
        $product = JTable::getInstance('Products', 'TiendaTable');
        $product->load( array( 'product_id'=>$product_id ) );
        
        // if product notforsale, fail
        if ($product->product_notforsale)
        {
            $response['msg'] = $helper->generateMessage( "Product Not For Sale" );
            $response['error'] = '1';
            echo ( json_encode( $response ) );
            return false;
        }
        
        $user = JFactory::getUser();
        $keynames = array();
        $keynames['user_id'] = $user->id;
        if (empty($user->id))
        {
            $session =& JFactory::getSession();
            $keynames['session_id'] = $session->getId();
        }
        $keynames['product_id'] = $product_id;
        
        $cartitem = JTable::getInstance( 'Carts', 'TiendaTable' );
        $cartitem->load($keynames);
        if ($product->quantity_restriction)
        {
        	if( $product->quantity_restriction )
               {
               		$error = false;
                    $min = $product->quantity_min;
                    $max = $product->quantity_max;
                    
                    if( $max )
                    {
                    	$remaining = $max - $cartitem->product_qty;
                    	if ($product_qty > $remaining )
                    	{
                    		$error = true;
                    		$msg = $helper->generateMessage( JText::_("You have reached the maximum quantity for this item. You can order another")." ".$remaining );
                    	}
                    }
                    if( $min )
                    {
                    	if ($product_qty < $min )
                    	{
                    		$error = true;
                    		$msg = $helper->generateMessage( JText::_("You have not reached the miminum quantity for this item. You have to order at least")." ".$min );
                    	}
                    }
                }
                if($error)
                {
		            $response['msg'] = $msg;
		            $response['error'] = '1';
		            echo ( json_encode( $response ) );
		            return false;
                }
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

        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute();
        if (!$itemid = $router->product( $product_id, $filter_category, true ))
        {
            $itemid = $router->category( 1, true );
        }
        
        // set the default redirect URL
        $redirect = "index.php?option=com_tienda&view=products&task=view&id=$product_id&filter_category=$filter_category&Itemid=".$itemid;
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
        $files = JRequest::get('files');

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
        
        // if product notforsale, fail
        if ($product->product_notforsale)
        {
            $this->messagetype  = 'notice';         
            $this->message      = JText::_( "Product Not For Sale" );
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;
        }
        
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
        
        // if ther is another product_url, put it into the cartitem_params, to allow custom redirect
       	if(array_key_exists('product_url', $values))
       	{
       		$params = new JParameter('');
       		$params->set('product_url', $values['product_url']);
       		$item->cartitem_params = trim( $params->toString() );
       	}
        
		// onAfterCreateItemForAddToCart: plugin can add values to the item before it is being validated /added
        // once the extra field(s) have been set, they will get automatically saved
        $dispatcher =& JDispatcher::getInstance();
        $results = $dispatcher->trigger( "onAfterCreateItemForAddToCart", array( $item, $values, $files ) );
        foreach ($results as $result)
        {
            foreach($result as $key=>$value)
            {
            	$item->set($key,$value);
            }
        }	        

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
        $cart_helper = new TiendaHelperCarts();
        $cartitem = $cart_helper->addItem( $item );

        // fire plugin event
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterAddToCart', array( $cartitem, $values ) );

        // get the 'success' redirect url
        switch (TiendaConfig::getInstance()->get('addtocartaction', 'redirect')) 
        {
            case "0":
            case "none":
                // redirects back to product page
                break;
            case "samepage":
                // redirects back to the page it came from (category, content, etc)
                // Take only the url without the base domain (index.php?option.....)
                $uri = JURI::getInstance();
                $redirect = $uri->toString( array('path', 'query', 'fragment') );
                $redirect = JRoute::_( $redirect, false );
                break;
            case "lightbox":
            case "redirect":
            default:
                // if a base64_encoded url is present as return, use that as the return url
                // otherwise return == the product view page
                $returnUrl = base64_encode( $redirect );
                if ($return_url = JRequest::getVar('return', '', 'method', 'base64')) 
                {
                    $return_url = base64_decode($return_url);
                    if (JURI::isInternal($return_url)) 
                    {
                        $returnUrl = base64_encode( $return_url );
                    }
                }
                
                // if a base64_encoded url is present as redirect, redirect there,
                // otherwise redirect to the cart
                $itemid = $router->findItemid( array('view'=>'checkout') );
                $redirect = JRoute::_( "index.php?option=com_tienda&view=carts&Itemid=".$itemid, false );
                if ($redirect_url = JRequest::getVar('redirect', '', 'method', 'base64')) 
                {
                    $redirect_url = base64_decode($redirect_url);
                    if (JURI::isInternal($redirect_url)) 
                    {
                        $redirect = $redirect_url;
                    }
                }
                
                //$returnUrl = base64_encode( $redirect );
                //$itemid = $router->findItemid( array('view'=>'checkout') );
                //$redirect = JRoute::_( "index.php?option=com_tienda&view=carts&Itemid=".$itemid, false );
                if (strpos($redirect, '?') === false) { $redirect .= "?return=".$returnUrl; } else { $redirect .= "&return=".$returnUrl; }
                
                break;
        }
        
        $this->messagetype  = 'message';
        $this->message      = JText::_( "Item Added to Your Cart" );
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
        return;
        
    }

    /**
     * Gets all the product's user reviews
     * @param $product_id
     * @return unknown_type
     */
	function getComments($product_id)
    {
        $html = '';
        $view   =& $this->getView( 'products', 'html' );
        
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'productcomments', 'TiendaModel' );
        $selectsort = JRequest::getVar('default_selectsort', '');
        $model->setstate('order', $selectsort );
        $limitstart = JRequest::getInt('limitstart', 0);
        $model->setId( $product_id );
        $model->setstate('limitstart', $limitstart );
        $model->setstate('filter_product', $product_id );
        $model->setstate('filter_enabled', '1' );
        $reviews = $model->getList();
        
        $count = count($reviews);

        $view->set( '_controller', 'products' );
        $view->set( '_view', 'products' );
        $view->set( '_doTask', true);
        $view->set( 'hidemenu', true);
        $view->setModel( $model, true );
        $view->setLayout( 'product_comments' );
        $view->assign('product_id', $product_id);
        $view->assign('count', $count);
       	$view->assign('reviews', $reviews);
       	
       	$user_id = JFactory::getUser()->id;
       	$productreview = TiendaHelperProduct::getUserAndProductIdForReview($product_id, $user_id);
       	$purchase_enable = TiendaConfig::getInstance()->get('purchase_leave_review_enable', '0');
       	$login_enable = TiendaConfig::getInstance()->get('login_review_enable', '0');
       	$product_review_enable=TiendaConfig::getInstance()->get('product_review_enable', '0');
       	
       	$result = 1;
       	if($product_review_enable=='1')
       	{
       		$review_enable=1;
       	}
       	else
       	{
       		$review_enable=0;
       	}
       	if (($login_enable == '1'))
       	{
       		if ($user_id)
       		{
       		    $order_enable = '1';
       		   
           		if ($purchase_enable == '1')
           		{ 
                    $orderexist = TiendaHelperProduct::getOrders($product_id);
           	 		if (!$orderexist)
       	 			{	
       	 				$order_enable = '0';
       	 				
       	 			}
           		}

           		if (($order_enable != '1') || !empty($productreview) )
           		{
           			$result = 0;
           		}
       		}
           		else
       		{
       			$result = 0;
       		}
       	}  
       	
       	 
       	$view->assign('review_enable',$review_enable);
       	$view->assign('result', $result);
        $view->assign('click','index.php?option=com_tienda&controller=products&view=products&task=addReview');
        $view->assign('selectsort', $selectsort);
        ob_start();
        $view->display();
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    /**
     * Verifies the fields in a submitted form.  Uses the table's check() method.
     * Will often be overridden. Is expected to be called via Ajax 
     * 
     * @return unknown_type
     */
    function validateChildren()
    {
        $response = array();
        $response['msg'] = '';
        $response['error'] = '';

        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper = TiendaHelperBase::getInstance();
        
        // get elements from post
        $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

        // validate it using table's ->check() method
        if (empty($elements))
        {
            // if it fails check, return message
            $response['error'] = '1';
            $response['msg'] = $helper->generateMessage( "Could not process form" );
            echo ( json_encode( $response ) );
            return;
        }

        if (!TiendaConfig::getInstance()->get('shop_enabled', '1'))
        {
            $response['msg'] = $helper->generateMessage( "Shop Disabled" );
            $response['error'] = '1';
            echo ( json_encode( $response ) );
            return false;    
        }
            
        // convert elements to array that can be binded             
        $values = TiendaHelperBase::elementsToArray( $elements );
        $attributes_csv = '';
        $product_id = !empty( $values['product_id'] ) ? (int) $values['product_id'] : JRequest::getInt( 'product_id' );
        $quantities = !empty( $values['quantities'] ) ? $values['quantities'] : array();

        $items = array(); // this will collect the items to add to the cart 
        $attributes_csv = '';
        
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
        
        // TODO get the children
        // loop thru each child,
        // get the list
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductRelations', 'TiendaModel' );
        $model->setState( 'filter_product', $product_id );
        $model->setState( 'filter_relation', 'parent' );
        if ($children = $model->getList())
        {
            foreach ($children as $child)
            {
                $product_qty = $quantities[$child->product_id_to];
                
                // Integrity checks on quantity being added
                if ($product_qty < 0) { $product_qty = '1'; } 
        
                // using a helper file to determine the product's information related to inventory     
                $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $child->product_id_to, $attributes_csv );    
                if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity ) 
                {
                    $response['msg'] = $helper->generateMessage( JText::sprintf( 'NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $product_qty ) );
                    $response['error'] = '1';
                    echo ( json_encode( $response ) );
                    return false;
                }
                
                // do the item's charges recur? does the cart already have a subscription in it?  if so, fail with notice
                $product = JTable::getInstance('Products', 'TiendaTable');
                $product->load( array( 'product_id'=>$child->product_id_to ) );
                
                // if product notforsale, fail
                if ($product->product_notforsale)
                {
                    $response['msg'] = $helper->generateMessage( "Product Not For Sale" );
                    $response['error'] = '1';
                    echo ( json_encode( $response ) );
                    return false;
                }
                
                if ($product->product_recurs && $cart_recurs)
                {
                    $response['msg'] = $helper->generateMessage( "Cart Already Recurs" );
                    $response['error'] = '1';
                    echo ( json_encode( $response ) );
                    return false;
                }
                
                if ($product->product_recurs)
                {
                    $product_qty = '1';
                }

                // create cart object out of item properties
                $item = new JObject;
                $item->user_id     = JFactory::getUser()->id;
                $item->product_id  = (int) $child->product_id_to;
                $item->product_qty = (int) $product_qty;
                $item->product_attributes = $attributes_csv;
                $item->vendor_id   = '0'; // vendors only in enterprise version
        
                // does the user/cart match all dependencies?
                $canAddToCart = $carthelper->canAddItem( $item, $cart_id, $id_type );
                if (!$canAddToCart)
                {
                    $response['msg'] = $helper->generateMessage( JText::_( "Cannot Add Item to Cart" ) . " - " . $carthelper->getError() );
                    $response['error'] = '1';
                    echo ( json_encode( $response ) );
                    return false;
                }
                
                // no matter what, fire this validation plugin event for plugins that extend the checkout workflow
                $results = array();
                $dispatcher =& JDispatcher::getInstance();
                $results = $dispatcher->trigger( "onValidateAddToCart", array( $item, $values ) );
        
                for ($i=0; $i<count($results); $i++)
                {
                    $result = $results[$i];
                    if (!empty($result->error))
                    {
                        $response['msg'] = $helper->generateMessage( $result->message );
                        $response['error'] = '1';
                        echo ( json_encode( $response ) );
                        return false;
                    }
                }
                
                // if here, add to cart                
                $items[] = $item;
            }
        }
        
        if (!empty($items))
        {
            $response['error'] = '0';                
        }
            else
        {
            $response['msg'] = $helper->generateMessage( "No Items Passed Validity Check" );
            $response['error'] = '1';
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
    function addChildrenToCart()
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );
        $product_id = JRequest::getInt( 'product_id' );
        $quantities = JRequest::getVar('quantities', array(0), 'request', 'array');
        $filter_category = JRequest::getInt( 'filter_category' );

        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute();
        if (!$itemid = $router->product( $product_id, $filter_category, true ))
        {
            $itemid = $router->category( 1, true );
        }
        
        // set the default redirect URL
        $redirect = "index.php?option=com_tienda&view=products&task=view&id=$product_id&filter_category=$filter_category&Itemid=".$itemid;
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

        $items = array(); // this will collect the items to add to the cart 
        
        // convert elements to array that can be binded             
        $values = JRequest::get('post');
        $attributes_csv = '';
        
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
        
        // TODO get the children
        // loop thru each child,
        // get the list
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductRelations', 'TiendaModel' );
        $model->setState( 'filter_product', $product_id );
        $model->setState( 'filter_relation', 'parent' );
        if ($children = $model->getList())
        {
            foreach ($children as $child)
            {
                $product_qty = $quantities[$child->product_id_to];
                
                // Integrity checks on quantity being added
                if ($product_qty < 0) { $product_qty = '1'; } 
        
                // using a helper file to determine the product's information related to inventory     
                $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $child->product_id_to, $attributes_csv );    
                if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity ) 
                {
                    $this->messagetype  = 'notice';         
                    $this->message      = JText::_( JText::sprintf( 'NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $product_qty ) );
                    $this->setRedirect( $redirect, $this->message, $this->messagetype );
                    return;            
                }
                
                // do the item's charges recur? does the cart already have a subscription in it?  if so, fail with notice
                $product = JTable::getInstance('Products', 'TiendaTable');
                $product->load( array( 'product_id'=>$child->product_id_to ) );
                
                // if product notforsale, fail
                if ($product->product_notforsale)
                {
                    $this->messagetype  = 'notice';         
                    $this->message      = JText::_( "Product Not For Sale" );
                    $this->setRedirect( $redirect, $this->message, $this->messagetype );
                    return;
                }
                
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
                $item->product_id  = (int) $child->product_id_to;
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
                $items[] = $item;
            }
        }
        
        if (!empty($items))
        {
            // add the items to the cart
            Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
            TiendaHelperCarts::updateCart( $items );
            
            // fire plugin event
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterAddToCart', array( $items, $values ) );
            
            $this->messagetype  = 'message';
            $this->message      = JText::_( "Items Added to Your Cart" );                
        }
        
        // After login, session_id is changed by Joomla, so store this for reference
        $session =& JFactory::getSession(); 
        $session->set( 'old_sessionid', $session->getId() );
        
        // get the 'success' redirect url
        // TODO Enable redirect via base64_encoded urls?
        switch (TiendaConfig::getInstance()->get('addtocartaction', 'redirect')) 
        {
            case "redirect":
                $returnUrl = base64_encode( $redirect );
                $itemid = $router->findItemid( array('view'=>'checkout') );
                $redirect = JRoute::_( "index.php?option=com_tienda&view=carts&Itemid=".$itemid, false );
                if (strpos($redirect, '?') === false) { $redirect .= "?return=".$returnUrl; } else { $redirect .= "&return=".$returnUrl; }
                break;
            case "0":
            case "none":
                break;
            case "lightbox":
            default:
                // TODO Figure out how to get the lightbox to display even after a redirect
                break;
        }

        $this->setRedirect( $redirect, $this->message, $this->messagetype );
        return;        
    }
    
     
    /**
     * Add review
     *
     */     
	function addReview()
	{		
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
		$productreviews = JTable::getInstance('productcomments', 'TiendaTable');
		$post = JRequest::get('post');		
		$product_id = $post['product_id'];
		$Itemid= $post['Itemid'];
		$user = & JFactory::getUser();	
		$valid = true;	
		$this->messagetype  = 'message';	
		
		//set encase validation fails
		$linkAdd = '';	
		$linkAdd .= '&rn='.base64_encode($post['user_name']);
		$linkAdd .= '&re='.base64_encode($post['user_email']);
        $linkAdd .= '&rc='.base64_encode($post['productcomment_text']);       
        
		if (!$user->id) 
		{			
			if(empty($post['user_name']) && $valid)
			{
				$valid = false;
				$this->message = JText::_( "Name field is required." ); 				
				$this->messagetype = 'notice';
			}
		
			jimport('joomla.mail.helper');
			if(!JMailHelper::isEmailAddress($post['user_email']) && $valid)
			{
				$valid = false;				
            	$this->message = JText::_( "Please enter a correct email address." ); 
            	$this->messagetype = 'notice';
			}			
			
			if( in_array( $post['user_email'],TiendaHelperProduct::getUserEmailForReview( $post['product_id'] ) ) && $valid )
			{
				$valid = false;				
            	$this->message = JText::_( "You already submitted a review. You can only submit a review once." );	
            	$this->messagetype = 'notice';
			}	
		}
		else 
		{
			if( in_array( $user->email,TiendaHelperProduct::getUserEmailForReview( $post['product_id'] ) ) && $valid )
			{
				$valid = false;				
            	$this->message = JText::_( "You already submitted a review. You can only submit a review once." );
            	$this->messagetype = 'notice';
			}
		}
		
		if(empty($post['productcomment_text']) && $valid) 
		{
			$valid = false;		
            $this->message = JText::_( "Comment field is required." );	   
            $this->messagetype = 'notice';         
		}
	
		$captcha=true;
		if (TiendaConfig::getInstance()->get('use_captcha', '0') && $valid)
        {
        	$privatekey = "6LcAcbwSAAAAANZOTZWYzYWRULBU_S--368ld2Fb";
        	$captcha=false;          
           
			if ($_POST["recaptcha_response_field"]) 
			{
				Tienda::load( 'TiendaRecaptcha', 'library.recaptcha' );
           		$recaptcha = new TiendaRecaptcha();
                $resp = $recaptcha->recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $post['recaptcha_challenge_field'], $post['recaptcha_response_field']);
                if ($resp->is_valid) 
                {
                    $captcha=true;
                } 
			}		
        }
       	 		
		if (!$captcha && $valid)
		{
			$valid = false;			
            $this->message = JText::_( "Incorrect Captcha" );     
            $this->messagetype = 'notice';      
		}
		 		     	 		
		if($valid)
		{
			$date = JFactory::getDate();
 			$productreviews->bind($post);	
 			$productreviews->created_date = $date->toMysql();
            $productreviews->productcomment_enabled = TiendaConfig::getInstance()->get('product_reviews_autoapprove', '0');
 			
 			if (!$productreviews->save())
	     	{	     	
	            $this->message      = JText::_( "Unable to Save Review" )." :: ".$productreviews->getError();  
	            $this->messagetype = 'notice';      	
	     	}
	        else
	     	{
	     		$dispatcher =& JDispatcher::getInstance();
	            $dispatcher->trigger( 'onAfterSaveProductComments', array( $productreviews ) );	     		
	            $this->message      = JText::_( "Successfully Submitted Review" );
	            
	            //successful
	            $linkAdd = '';
	     	}	
		}					
			$redirect = "index.php?option=com_tienda&view=products&task=view&id=".$product_id.$linkAdd."&Itemid=".$Itemid;
 			$redirect = JRoute::_( $redirect );
 			$this->setRedirect( $redirect, $this->message, $this->messagetype ); 		
	}
	
	/**
	 * Adding helpfulness of review 
	 *
	 */
	function reviewHelpfullness()
	{		
		$user_id = JFactory::getUser()->id;
		$Itemid = JRequest::getInt('Itemid', '');
		$id = JRequest::getInt('product_id', '');		
		$url = "index.php?option=com_tienda&view=products&task=view&Itemid=".$Itemid."&id=".$id;
	
		if ($user_id)
		{
    		$productcomment_id = JRequest::getInt('productcomment_id', '');	
    		Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
            $producthelper = new TiendaHelperProduct();
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
    		$productcomment = JTable::getInstance('productcomments', 'TiendaTable');
    		$productcomment->load( $productcomment_id );
            
            $helpful_votes_total = $productcomment->helpful_votes_total;
            $helpful_votes_total = $helpful_votes_total + 1;
    		$helpfulness = JRequest::getInt('helpfulness', '');
    		if ($helpfulness == 1)
    		{
                $helpful_vote = $productcomment->helpful_votes;
                $helpful_vote_new = $helpful_vote + 1;
        		$productcomment->helpful_votes = $helpful_vote_new;
    		}
    		$productcomment->helpful_votes_total = $helpful_votes_total;
    		
    		$report = JRequest::getInt('report', '');
		    if ($report == 1)
            {
                $productcomment->reported_count = $productcomment->reported_count + 1;
            }
            
    		$help = array();
    		$help['productcomment_id'] = $productcomment_id;
    		$help['helpful'] = $helpfulness;
    		$help['user_id'] = $user_id;
    		$help['reported'] = $report;
    		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
    		$reviewhelpfulness = JTable::getInstance('ProductCommentsHelpfulness', 'TiendaTable');
    		$reviewhelpfulness->load(array('user_id'=>$user_id, 'productcomment_id'=>$productcomment_id));
 		
			$application =& JFactory::getApplication();
    		if ($report == 1 && !empty($reviewhelpfulness->productcommentshelpfulness_id) && empty($reviewhelpfulness->reported))
    		{
    		    $reviewhelpfulness->reported = 1;
    		    $reviewhelpfulness->save();
    		    
                $productcomment->save();
                $application->enqueueMessage( JText::sprintf( 'Thanks for reporting this review.' ));
                $application->redirect($url);
                return;
    		}
    			
    		if($report)
    		{
    			$application->enqueueMessage( JText::sprintf( 'You already reported this review.' ));
                $application->redirect($url);
    		}
           
    		
    		$reviewhelpfulness->bind($help);
 			if (!empty($reviewhelpfulness->productcommentshelpfulness_id))
 			{
     			$application->enqueueMessage( JText::sprintf( 'You have already given your feedback on this review.' ));
     			$application->redirect($url);
     			return;
 			}
     		else
 			{
 			    $reviewhelpfulness->save();
 				$productcomment->save();
     			$application->enqueueMessage( JText::sprintf( 'Thanks for your feedback on this comment.' ));
      			$application->redirect($url);
      			return;
 			}
	    }
    		else
		{
    		$redirect = "index.php?option=com_user&view=login&return=".base64_encode( $url );
    		$redirect = JRoute::_( $redirect, false );
            JFactory::getApplication()->redirect( $redirect );
            return;
        }
	}
}

?>
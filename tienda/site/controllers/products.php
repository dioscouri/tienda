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
	function __construct( )
	{
		parent::__construct( );
		
		$this->set( 'suffix', 'products' );
	}
	
	/**
	 * Sets the model's state
	 *
	 * @return array()
	 */
	function _setModelState( )
	{
		$state = parent::_setModelState( );
		$app = JFactory::getApplication( );
		$model = $this->getModel( $this->get( 'suffix' ) );
		$ns = $this->getNamespace( );
		
		Tienda::load( 'TiendaHelperUser', 'helpers.user' );
		$user_id = JFactory::getUser( )->id;
		$state['filter_group'] = TiendaHelperUser::getUserGroup( $user_id );
		
		$date = JFactory::getDate( );
		$state['order'] = 'tbl.ordering';
		$state['direction'] = 'ASC';
		$state['filter_published'] = 1;
		$state['filter_published_date'] = $date->toMySQL( );
		$state['filter_enabled'] = 1;
		$state['filter_category'] = $app->getUserStateFromRequest( $ns . '.category', 'filter_category', '', 'int' );
    $prev_cat_id = $app->getUserState( $ns . 'prev_cat_id' );
    if( $prev_cat_id && $prev_cat_id != $state['filter_category'] ) // drop all filters
    {
  		$app->setUserState( $ns . 'price_from', 0 );
  		$app->setUserState( $ns . 'price_to', '' );
  		$app->setUserState( $ns . 'attribute', '' );
  		$app->setUserState( $ns . 'manufacturer', 0 );
  		$app->setUserState( $ns . 'manufacturer_set', '' );
  		$app->setUserState( $ns . 'attributeoptionname', array( ) );		
  		$app->setUserState( $ns . 'rating', '' );
    }

		$state['search'] = $app->getUserStateFromRequest( $ns . '.search', 'search', '', '' );
		$state['search_type'] = $app->getUserStateFromRequest( $ns . '.search_type', 'search_type', '', '' );
		$state['filter_price_from'] = $app->getUserStateFromRequest( $ns . 'price_from', 'filter_price_from', '0', 'int' );
		$state['filter_price_to'] = $app->getUserStateFromRequest( $ns . 'price_to', 'filter_price_to', '', '' );
		$state['filter_attribute_set'] = $app->getUserStateFromRequest( $ns . 'attribute', 'filter_attribute_set', '', '' );
		$state['filter_manufacturer'] = $app->getUserStateFromRequest( $ns . 'manufacturer', 'filter_manufacturer', '', 'int' );
		$state['filter_manufacturer_set'] = $app->getUserStateFromRequest( $ns . 'manufacturer_set', 'filter_manufacturer_set', '', '' );
		$state['filter_attributeoptionname'] = $app
				->getUserStateFromRequest( $ns . 'attributeoptionname', 'filter_attributeoptionname', array( ), 'array' );		
		$state['filter_rating'] = $app->getUserStateFromRequest( $ns . 'rating', 'filter_rating', '', '' );
		
		$state['filter_sortby'] = $app->getUserStateFromRequest( $ns . 'sortby', 'filter_sortby', '', '' );
		$state['filter_dir'] = $app->getUserStateFromRequest( $ns . 'dir', 'filter_dir', 'asc', '' );
		
		if ( strlen( $state['filter_sortby'] ) && Tienda::getInstance( )->get( 'display_sort_by', '1' ) )
		{
			$state['order'] = $state['filter_sortby'];
			$state['direction'] = strtoupper($state['filter_dir']);
		}
				
		if ( $state['search'] )
		{
			$filter = $state['filter'] = $app->getUserStateFromRequest( $ns . '.filter', 'filter', '', 'string' );
			
			// apply additional 'AND' filter if requested by module and unset filter state
			switch ( $state['search_type'] )
			{
				case "4":
					$state['filter_name'] = $app->getUserStateFromRequest( $ns . '.filter', 'filter', '', 'string' );
					$state['filter'] = '';
					break;
				case "3":
					$state['filter_namedescription'] = $app->getUserStateFromRequest( $ns . '.filter', 'filter', '', 'string' );
					$state['filter'] = '';
					break;
				case "2":
					$state['filter_sku'] = $app->getUserStateFromRequest( $ns . '.filter', 'filter', '', 'string' );
					$state['filter'] = '';
					break;
				case "1":
					$state['filter_model'] = $app->getUserStateFromRequest( $ns . '.filter', 'filter', '', 'string' );
					$state['filter'] = '';
					break;
				case "0":
				default:
					break;
			}
		}
		else
		{
			$state['filter'] = '';
		}
		
		if ( $state['filter_category'] )
		{
			JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
			$cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
			$cmodel->setId( $state['filter_category'] );
			if ( $item = $cmodel->getItem( ) )
			{
				$state['category_name'] = $item->category_name;
			}
			
		}
		elseif ( !$state['search'] )
		{
			$state['filter_category'] = '0';
		}
		
		if ( $state['search'] && $state['filter_category'] == '1' )
		{
			$state['filter_category'] = '';
		}
		
		foreach ( @$state as $key => $value )
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
	function display($cachable=false, $urlparams = false )
	{
		JRequest::setVar( 'view', $this->get( 'suffix' ) );
		JRequest::setVar( 'search', false );
		$view = $this->getView( $this->get( 'suffix' ), JFactory::getDocument( )->getType( ) );
		$model = $this->getModel( $this->get( 'suffix' ) );
		$this->_setModelState( );
		
		if ( !Tienda::getInstance( )->get( 'display_out_of_stock' ) )
		{
			$model->setState( 'filter_quantity_from', '1' );
		}
		
		// get the category we're looking at
		$filter_category = $model->getState( 'filter_category', JRequest::getVar( 'filter_category' ) );
		JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
		$cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
		$cat = $cmodel->getTable( );
		$cat->load( $filter_category );
		
		// set the title based on the selected category
		$title = ( empty( $cat->category_name ) ) ? JText::_('COM_TIENDA_ALL_CATEGORIES') : JText::_( $cat->category_name );
		$level = ( !empty( $filter_category ) ) ? $filter_category : '1';
		
		// breadcrumb support
		$app = JFactory::getApplication( );
		$pathway = $app->getPathway( );
		$category_itemid = JRequest::getInt( 'Itemid', Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $filter_category, true ) );
		$items = Tienda::getClass( "TiendaHelperCategory", 'helpers.category' )->getPathName( $filter_category, 'array' );
		if ( !empty( $items ) )
		{
			// add the categories to the pathway
			Tienda::getClass( "TiendaHelperPathway", 'helpers.pathway' )->insertCategories( $items, $category_itemid );
		}
		// add the item being viewed to the pathway
		$pathway_values = $pathway->getPathway( );
		$pathway_names = Tienda::getClass( "TiendaHelperBase", 'helpers._base' )->getColumn( $pathway_values, 'name' );
		$pathway_links = Tienda::getClass( "TiendaHelperBase", 'helpers._base' )->getColumn( $pathway_values, 'link' );
		$cat_url = "index.php?Itemid=$category_itemid";
		if ( !in_array( $cat->category_name, $pathway_names ) )
		{
			$pathway->addItem( $title );
		}
		$cat->itemid = $category_itemid;
		
		// get the category's sub categories
		$cmodel->setState( 'filter_level', $level );
		$cmodel->setState( 'filter_enabled', '1' );
		$cmodel->setState( 'order', 'tbl.lft' );
		$cmodel->setState( 'direction', 'ASC' );
		if ( $citems = $cmodel->getList( ) )
		{
			foreach ( $citems as $item )
			{
				$itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $item->category_id, true );
				$item->itemid = ( !empty( $itemid ) ) ? $itemid : JRequest::getInt( 'Itemid', $itemid );
			}
		}
		
		$this->_list = true; // if you want to display a slightly differen add-to-cart area for list view, check this boolean
		// get the products to be displayed in this category
		if ( $items = $model->getList( ) )
		{
			JRequest::setVar( 'page', 'category' ); // for "getCartButton"
			$this->display_cartbutton = Tienda::getInstance( )->get( 'display_category_cartbuttons', '1' );
			foreach ( $items as $item )
			{
				$itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->product( $item->product_id, $filter_category, true );
				$item->itemid = JRequest::getInt( 'Itemid', $itemid );
			}
		}

		if ( ( $model->getState( 'filter_price_from' ) > '0' ) || ( $model->getState( 'filter_price_to' ) > '0' ) )
		{
			$url = "index.php?option=com_tienda&view=products&filter_category=$filter_category&filter_price_from=&filter_price_to=";
			$from = TiendaHelperBase::currency( $model->getState( 'filter_price_from' ) );
			$to = ( $model->getState( 'filter_price_to' ) > 0 ) ? TiendaHelperBase::currency( $model->getState( 'filter_price_to' ) )
					: JText::_('COM_TIENDA_MAXIMUM_PRICE');
			$view->assign( 'remove_pricefilter_url', $url );
			$view->assign( 'pricefilter_applied', true );
			$view->assign( 'filterprice_from', $from );
			$view->assign( 'filterprice_to', $to );
		}
		
		if(Tienda::getInstance()->get('enable_product_compare', '1'))
		{
			Tienda::load( "TiendaHelperProductCompare", 'helpers.productcompare' );
			$compareitems = TiendaHelperProductCompare::getComparedProducts();		
			$view->assign( 'compareitems',  $compareitems); 
		}
			
		$view->assign( 'level', $level );
		$view->assign( 'title', $title );
		$view->assign( 'cat', $cat );
		$view->assign( 'citems', $citems );
		$view->assign( 'items', $items );
		$view->set( '_doTask', true );
		$view->setModel( $model, true );
		
		// add the media/templates folder as a valid path for templates
		$view->addTemplatePath( Tienda::getPath( 'categories_templates' ) );
		// but add back the template overrides folder to give it priority
		$template_overrides = JPATH_BASE . DS . 'templates' . DS . $app->getTemplate( ) . DS . 'html' . DS . 'com_tienda' . DS . $view->getName( );
		$view->addTemplatePath( $template_overrides );
		
		// using a helper file, we determine the category's layout
		$layout = Tienda::getClass( 'TiendaHelperCategory', 'helpers.category' )->getLayout( $cat->category_id );
		$view->setLayout( $layout );
		
		$view->display( );
		$this->footer( );
		return;
	}
	
	/**
	 * Displays a single product
	 * (non-PHPdoc)
	 * @see tienda/site/TiendaController#view()
	 */
	function view( )
	{
		$this->display_cartbutton = true;
		
		JRequest::setVar( 'view', $this->get( 'suffix' ) );
		$model = $this->getModel( $this->get( 'suffix' ) );
		$model->getId( );
		Tienda::load( 'TiendaHelperUser', 'helpers.user' );
		$user_id = JFactory::getUser( )->id;
		$filter_group = TiendaHelperUser::getUserGroup( $user_id, $model->getId( ) );
		$model->setState( 'filter_group', $filter_group );
		$row = $model->getItem( false, false ); // use the state
		
		$filter_category = $model->getState( 'filter_category', JRequest::getVar( 'filter_category' ) );
		if ( empty( $filter_category ) )
		{
			$categories = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getCategories( $row->product_id );
			if ( !empty( $categories ) )
			{
				$filter_category = $categories[0];
			}
		}
		$unpublished = false;
		if( $row->unpublish_date != JFactory::getDbo()->getNullDate() )
		{
			$unpublished = strtotime( $row->unpublish_date ) < time();
		}
		if( !$unpublished && $row->publish_date != JFactory::getDbo()->getNullDate() )
		{
			$unpublished = strtotime( $row->publish_date ) > time();
		}
		
		if ( empty( $row->product_enabled ) || $unpublished )
		{
			$redirect = "index.php?option=com_tienda&view=products&task=display&filter_category=" . $filter_category;
			$redirect = JRoute::_( $redirect, false );
			$this->message = JText::_('COM_TIENDA_CANNOT_VIEW_DISABLED_PRODUCT');
			$this->messagetype = 'notice';
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		
		Tienda::load( 'TiendaArticle', 'library.article' );
		$product_description = TiendaArticle::fromString( $row->product_description );
		
		JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
		$cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
		$cat = $cmodel->getTable( );
		$cat->load( $filter_category );
		
		$view = $this->getView( $this->get( 'suffix' ), JFactory::getDocument( )->getType( ) );
		$view->set( '_doTask', true );
		$view->assign( 'row', $row );
		
		// breadcrumb support
		$app = JFactory::getApplication( );
		$pathway = $app->getPathway( );
		$category_itemid = JRequest::getInt( 'Itemid', Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $filter_category, true ) );
		$items = Tienda::getClass( "TiendaHelperCategory", 'helpers.category' )->getPathName( $filter_category, 'array' );
		if ( !empty( $items ) )
		{
			// add the categories to the pathway
			Tienda::getClass( "TiendaHelperPathway", 'helpers.pathway' )->insertCategories( $items, $category_itemid );
		}
		// add the item being viewed to the pathway
		$pathway->addItem( $row->product_name );
		$cat->itemid = $category_itemid;
		$view->assign( 'cat', $cat );
		
		// Check If the inventroy is set then it will go for the inventory product quantities
		if ( $row->product_check_inventory )
		{
			$inventoryList = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getProductQuantities( $row->product_id );
			
			if ( !Tienda::getInstance( )->get( 'display_out_of_stock' ) && empty( $inventoryList ) )
			{
				// redirect
				$redirect = "index.php?option=com_tienda&view=products&task=display&filter_category=" . $filter_category;
				$redirect = JRoute::_( $redirect, false );
				$this->message = JText::_('COM_TIENDA_CANNOT_VIEW_PRODUCT');
				$this->messagetype = 'notice';
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
			}
			
			// if there is no entry of product in the productquantities
			if ( count( $inventoryList ) == 0 )
			{
				$inventoryList[''] = '0';
			}
			$view->assign( 'inventoryList', $inventoryList );
		}

		$view->product_comments = $this->getComments( $view, $row->product_id );
		$view->files = $this->getFiles( $view, $row->product_id );
		$view->product_relations = $this->getRelationshipsHtml( $view, $row->product_id, 'relates' );
		$view->product_children = $this->getRelationshipsHtml( $view, $row->product_id, 'parent' );
		$view->product_requirements = $this->getRelationshipsHtml( $view, $row->product_id, 'requires' );
		$view->product_description = $product_description;
		$view->setModel( $model, true );
		
		// add the media/templates folder as a valid path for templates
		$view->addTemplatePath( Tienda::getPath( 'products_templates' ) );
		// but add back the template overrides folder to give it priority
		$template_overrides = JPATH_BASE . DS . 'templates' . DS . $app->getTemplate( ) . DS . 'html' . DS . 'com_tienda' . DS . $view->getName( );
		$view->addTemplatePath( $template_overrides );
		
		// using a helper file, we determine the product's layout
		$layout = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )
				->getLayout( $row->product_id, array(
					'category_id' => $cat->category_id
				) );
		$view->setLayout( $layout );
		
		$dispatcher = JDispatcher::getInstance( );
				
		ob_start( );
		$dispatcher->trigger( 'onBeforeDisplayProduct', array(
					$row->product_id
				) );
		$view->assign( 'onBeforeDisplayProduct', ob_get_contents( ) );
		ob_end_clean( );
		
		ob_start( );
		$dispatcher->trigger( 'onAfterDisplayProduct', array(
					$row->product_id
				) );
		$view->assign( 'onAfterDisplayProduct', ob_get_contents( ) );
		ob_end_clean( );
		
		$view->display( );
		$this->footer( );
		return;
	}
	
	/**
	 * Gets a product's add to cart section
	 * formatted for display
	 *
	 * @param int $address_id
	 * @return string html
	 */
	function getAddToCart( $product_id, $values = array( ) )
	{
	    $layout = 'product_buy';
	    
		Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
		if( isset( $values['layout'] ) ) {
			$layout = $values['layout'];
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance('Products', 'TiendaModel');
        $model->setId( $product_id );
        $row = $model->getItem( false );
        $buy_layout_override = $row->product_parameters->get('product_buy_layout_override');
        if (!empty($buy_layout_override))
        {
            $layout = $buy_layout_override;
        }
        		
		$html = TiendaHelperProduct::getCartButton( $product_id, $layout, $values );
		
		return $html;
	}
	
	/**
	 * Used whenever an attribute selection is changed,
	 * to update the price and/or attribute selectlists
	 * 
	 * @return unknown_type
	 */
	function updateAddToCart( )
	{
		$response = array( );
		$response['msg'] = '';
		$response['error'] = '';

		// get elements from post
		$elements = json_decode( preg_replace( '/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$values = TiendaHelperBase::elementsToArray( $elements );
		
		// merge current elements with post
		$request_arr = JRequest::get();
		unset( $request_arr['elements'] );
		JRequest::setVar( 'elements', null );
		$values = array_merge( $values, $request_arr );
		JRequest::set( $values, 'POST' );

		if ( empty( $values['product_id'] ) )
		{
			$values['product_id'] = JRequest::getInt( 'product_id', 0 );
		}
		
		// now get the summary
		$this->display_cartbutton = true;
		$html = $this->getAddToCart( $values['product_id'], $values );
		
		$response['msg'] = $html;
		// encode and echo (need to echo to send back to browser)
		echo json_encode( $response );
		return;
	}
	
	/**
	 * Gets a product's files list
	 * formatted for display
	 *
	 * @param int $address_id
	 * @return string html
	 */
	function getFiles( $view, $product_id )
	{
		$html = '';
		
		// get the product's files
		JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
		$model = JModel::getInstance( 'ProductFiles', 'TiendaModel' );
		$model->setState( 'filter_product', $product_id );
		$model->setState( 'filter_enabled', 1 );
		//$model->setState( 'filter_purchaserequired', 1 );
		$items = $model->getList( );
		
		// get the user's active subscriptions to this product, if possible
		$submodel = JModel::getInstance( 'Subscriptions', 'TiendaModel' );
		$submodel->setState( 'filter_userid', JFactory::getUser( )->id );
		$submodel->setState( 'filter_productid', $product_id );
		$subs = $submodel->getList( );
		
		if ( !empty( $items ) )
		{
			// reconcile the list of files to the date the sub's files were last checked 
			Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
			$subhelper = new TiendaHelperSubscription( );
			$subhelper->reconcileFiles( $subs );
			
			Tienda::load( 'TiendaHelperBase', 'helpers._base' );
			$helper = TiendaHelperBase::getInstance( 'ProductDownload', 'TiendaHelper' );
			$filtered_items = $helper->filterRestricted( $items, JFactory::getUser( )->id );
			
			$view->setModel( $model, true );
			$product_file_data = new stdClass;
			$product_file_data->downloadItems = $filtered_items[0];
			$product_file_data->nondownloadItems = $filtered_items[1];
			$product_file_data->product_id = $product_id;
			$lyt = $view->getLayout();
			$view->setLayout( 'product_files' );
			$view->product_file_data = $product_file_data;
			
			ob_start( );
			echo $view->loadTemplate( null );
			$html = ob_get_contents( );
			ob_end_clean( );
			$view->setLayout( $lyt );
			unset( $view->product_file_data );
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
	function getRelationshipsHtml( $view, $product_id, $relation_type = 'relates' )
	{
		$html = '';
		$validation = "";
		
		// get the list
		JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
		$model = JModel::getInstance( 'ProductRelations', 'TiendaModel' );
		$model->setState( 'filter_relation', $relation_type );
		$user = JFactory::getUser( );
		$model->setState( 'filter_group', $relation_type );
		
		switch ( $relation_type )
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
		$query = $model->getQuery();
		$query->order( 'p_from.ordering ASC, p_to.ordering ASC' );
		
		if ( $items = $model->getList( ) )
		{
			$filter_category = $model->getState( 'filter_category', JRequest::getVar( 'filter_category' ) );
			if ( empty( $filter_category ) )
			{
				$categories = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getCategories( $product_id );
				if ( !empty( $categories ) )
				{
					$filter_category = $categories[0];
				}
			}
			$userId = JFactory::getUser( )->id;
			$config = Tienda::getInstance( );
			$show_tax = $config->get( 'display_prices_with_tax' );
			Tienda::load('TiendaHelperTax', 'helpers.tax');
			if ( $show_tax )
	    	$taxes = TiendaHelperTax::calculateTax( $items, 2 );
			
			foreach ( $items as $key => $item )
			{
				if ( $check_quantity )
				{
					// TODO Unset $items[$key] if 
					// this is out of stock && 
					// check_inventory && 
					// item for sale
				}
				
				if ( $item->product_id_from == $product_id )
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
				$item->itemid = JRequest::getInt( 'Itemid', $itemid );
				$item->tax = 0;
				if ( $show_tax )
				{
					$tax = $taxes->product_taxes[$item->product_id];
					$item->taxtotal = $tax;
					$item->tax = $tax;
				}
			}
		}
		else
		{
			return '';
		}
		$view->setModel( $model, true );
		$lyt = $view->getLayout();
		$view->setLayout( $layout );
		$product_relations = new stdClass;
		$product_relations->items = $items;
		$product_relations->product_id = $product_id;
		$product_relations->show = $product_id;
		$product_relations->filter_category = $filter_category;
		$product_relations->validation = $validation;
		$product_relations->show_tax = $show_tax;
		$view->product_relations_data = $product_relations;
		
		ob_start( );
		$view->display( );
		$html = ob_get_contents( );
		ob_end_clean( );	
		unset( $view->product_relations_data );
		$view->setLayout( $lyt );
		
		return $html;
	}
	
	/**
	 * downloads a file
	 *
	 * @return void
	 */
	function downloadFile( )
	{
		$user = &JFactory::getUser( );
		$productfile_id = intval( JRequest::getvar( 'id', '', 'request', 'int' ) );
		$product_id = intval( JRequest::getvar( 'product_id', '', 'request', 'int' ) );
		$link = 'index.php?option=com_tienda&controller=products&view=products&task=view&id=' . $product_id;
		
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance( 'ProductDownload', 'TiendaHelper' );
		
		if ( !$canView = $helper->canDownload( $productfile_id, JFactory::getUser( )->id ) )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_NOT_AUTHORIZED_TO_DOWNLOAD_FILE');
			$this->setRedirect( $link, $this->message, $this->messagetype );
			return false;
		}
		JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
		$productfile = JTable::getInstance( 'ProductFiles', 'TiendaTable' );
		$productfile->load( $productfile_id );
		if ( empty( $productfile->productfile_id ) )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_INVALID_FILE');
			$this->setRedirect( $link, $this->message, $this->messagetype );
			return false;
		}
		
		// log and download
		Tienda::load( 'TiendaFile', 'library.file' );
		
		// Log the download
		$productfile->logDownload( $user->id );
		
		// After download complete it will update the productdownloads on the basis of the user 
		
		// geting the ProductDownloadId to updated for which productdownload_max  is greater then 0
		$productToDownload = $helper->getProductDownloadInfo( $productfile->productfile_id, $user->id );
		;
		
		if ( !empty( $productToDownload ) )
		{
			$productDownload = JTable::getInstance( 'ProductDownloads', 'TiendaTable' );
			$productDownload->load( $productToDownload->productdownload_id );
			$productDownload->productdownload_max = $productDownload->productdownload_max - 1;
			if ( !$productDownload->save( ) )
			{
				// TODO in case product Download is not updating properly .
			}
		}
		
		if ( $downloadFile = TiendaFile::download( $productfile ) )
		{
			$link = JRoute::_( $link, false );
			$this->setRedirect( $link );
		}
	}
	
	/**
	 *
	 * @return void
	 */
	function search( )
	{
		JRequest::setVar( 'view', $this->get( 'suffix' ) );
		JRequest::setVar( 'layout', 'search' );
		JRequest::setVar( 'search', true );
		
		$model = $this->getModel( $this->get( 'suffix' ) );
		$this->_setModelState( );
		
		if ( !Tienda::getInstance( )->get( 'display_out_of_stock' ) )
		{
			$model->setState( 'filter_quantity_from', '1' );
		}
		parent::display( );
		
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
	function validate( )
	{
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = new TiendaHelperBase( );
		
		$response = array( );
		$response['msg'] = '';
		$response['error'] = '';
		
		// get elements from post
		$elements = json_decode( preg_replace( '/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
		
		// validate it using table's ->check() method
		if ( empty( $elements ) )
		{
			// if it fails check, return message
			$response['error'] = '1';
			$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_COULD_NOT_PROCESS_FORM') );
			echo ( json_encode( $response ) );
			return;
		}
		
		if ( !Tienda::getInstance( )->get( 'shop_enabled', '1' ) )
		{
			$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_SHOP_DISABLED') );
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return false;
		}
		
		// convert elements to array that can be binded             
		$values = TiendaHelperBase::elementsToArray( $elements );
		$product_id = !empty( $values['product_id'] ) ? ( int ) $values['product_id'] : JRequest::getInt( 'product_id' );
		$product_qty = !empty( $values['product_qty'] ) ? ( int ) $values['product_qty'] : '1';
		
		$attributes = array( );
		foreach ( $values as $key => $value )
		{
			if ( substr( $key, 0, 10 ) == 'attribute_' )
			{
				$attributes[] = $value;
				if(  !( int )@$value )
				{
					$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_ALL_PRODUCT_ATTRIBUTES_REQUIRED') );
					$response['error'] = '1';
					echo ( json_encode( $response ) );
					return false;
				}
			}
		}
		sort( $attributes );
		$attributes_csv = implode( ',', $attributes );
		
		// Integrity checks on quantity being added
		if ( $product_qty < 0 )
		{
			$product_qty = '1';
		}
		
		// using a helper file to determine the product's information related to inventory     
		$availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity( $product_id, $attributes_csv );
		if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
		{
			$response['msg'] = $helper->generateMessage( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $product_qty ) );
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return false;
		}
		
		$product = JTable::getInstance( 'Products', 'TiendaTable' );
		$product->load( array(
					'product_id' => $product_id
				) );
		
		// if product notforsale, fail
		if ( $product->product_notforsale )
		{
			$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_PRODUCT_NOT_FOR_SALE') );
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return false;
		}
		
		$user = JFactory::getUser( );
		$keynames = array( );
		$keynames['user_id'] = $user->id;
		if ( empty( $user->id ) )
		{
			$session = &JFactory::getSession( );
			$keynames['session_id'] = $session->getId( );
		}
		$keynames['product_id'] = $product_id;
		
		$cartitem = JTable::getInstance( 'Carts', 'TiendaTable' );
		$cartitem->load( $keynames );
		if ( $product->quantity_restriction )
		{
			if ( $product->quantity_restriction )
			{
				$error = false;
				$min = $product->quantity_min;
				$max = $product->quantity_max;
				
				if ( $max )
				{
					$remaining = $max - $cartitem->product_qty;
					if ( $product_qty > $remaining )
					{
						$error = true;
						$msg = $helper
								->generateMessage( 
										JText::_('COM_TIENDA_YOU_HAVE_REACHED_THE_MAXIMUM_QUANTITY_YOU_CAN_ORDER_ANOTHER') . " " . $remaining );
					}
				}
				if ( $min )
				{
					if ( $product_qty < $min )
					{
						$error = true;
						$msg = $helper
								->generateMessage( 
										JText::_('COM_TIENDA_YOU_HAVE_NOT_REACHED_THE_MIMINUM_QUANTITY_YOU_HAVE_TO_ORDER_AT_LEAST') . " "
												. $min );
					}
				}
			}
			if ( $error )
			{
				$response['msg'] = $msg;
				$response['error'] = '1';
				echo ( json_encode( $response ) );
				return false;
			}
		}
		
		// create cart object out of item properties
		$item = new JObject;
		$item->user_id = JFactory::getUser( )->id;
		$item->product_id = ( int ) $product_id;
		$item->product_qty = ( int ) $product_qty;
		$item->product_attributes = $attributes_csv;
		$item->vendor_id = '0'; // vendors only in enterprise version
		
		// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
		$results = array( );
		$dispatcher = &JDispatcher::getInstance( );
		$results = $dispatcher->trigger( "onValidateAddToCart", array(
					$item, $values
				) );
		
		for ( $i = 0; $i < count( $results ); $i++ )
		{
			$result = $results[$i];
			if ( !empty( $result->error ) )
			{
				Tienda::load( 'TiendaHelperBase', 'helpers._base' );
				$helper = TiendaHelperBase::getInstance( );
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
	function addToCart( )
	{
		JRequest::checkToken( ) or jexit( 'Invalid Token' );
		$product_id = JRequest::getInt( 'product_id' );
		$product_qty = JRequest::getInt( 'product_qty' );
		$filter_category = JRequest::getInt( 'filter_category' );
		
		Tienda::load( "TiendaHelperRoute", 'helpers.route' );
		$router = new TiendaHelperRoute( );
		if ( !$itemid = $router->product( $product_id, $filter_category, true ) )
		{
			$itemid = $router->category( 1, true );
			if( !$itemid )
				$itemid = JRequest::getInt( 'Itemid', 0 );
		}
		
		// set the default redirect URL
		$redirect = "index.php?option=com_tienda&view=products&task=view&id=$product_id&filter_category=$filter_category&Itemid=" . $itemid;
		$redirect = JRoute::_( $redirect, false );
		
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance( );
		if ( !Tienda::getInstance( )->get( 'shop_enabled', '1' ) )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_SHOP_DISABLED');
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		
		// convert elements to array that can be binded             
		$values = JRequest::get( 'post' );
		if( isset( $values['elements'] ) ) // ajax call! -> decode elements and merge them with the request array
		{
			$elements = json_decode( preg_replace( '/[\n\r]+/', '\n', $values['elements'] ) );	
			unset( $values['elements'] );	
			// convert elements to array that can be binded
			$values = array_merge( TiendaHelperBase::elementsToArray( $elements ), $values );
			JRequest::set( $values, 'POST' );
		}
		
		$files = JRequest::get( 'files' );
		
		$attributes = array( );
		foreach ( $values as $key => $value )
		{
			if ( substr( $key, 0, 10 ) == 'attribute_' )
			{
				$attributes[] = $value;
			}
		}
		sort( $attributes );
		$attributes_csv = implode( ',', $attributes );
		
		// Integrity checks on quantity being added
		if ( $product_qty < 0 )
		{
			$product_qty = '1';
		}
		
		// using a helper file to determine the product's information related to inventory     
		$availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity( $product_id, $attributes_csv );
		if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $product_qty ) );
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		
		// do the item's charges recur? does the cart already have a subscription in it?  if so, fail with notice
		$product = JTable::getInstance( 'Products', 'TiendaTable' );
		$product->load( array(
					'product_id' => $product_id
				), true, false );
		
		// if product notforsale, fail
		if ( $product->product_notforsale )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_PRODUCT_NOT_FOR_SALE');
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		
		$user = JFactory::getUser( );
		$cart_id = $user->id;
		$id_type = "user_id";
		if ( empty( $user->id ) )
		{
			$session = &JFactory::getSession( );
			$cart_id = $session->getId( );
			$id_type = "session";
		}
		
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$carthelper = new TiendaHelperCarts( );
		
		$cart_recurs = $carthelper->hasRecurringItem( $cart_id, $id_type );
		if ( $product->product_recurs && $cart_recurs )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_CART_ALREADY_RECURS');
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		
		if ( $product->product_recurs )
		{
			$product_qty = '1';
		}
		
		// create cart object out of item properties
		$item = new JObject;
		$item->user_id = JFactory::getUser( )->id;
		$item->product_id = ( int ) $product_id;
		$item->product_qty = ( int ) $product_qty;
		$item->product_attributes = $attributes_csv;
		$item->vendor_id = '0'; // vendors only in enterprise version
		
		// if ther is another product_url, put it into the cartitem_params, to allow custom redirect
		if ( array_key_exists( 'product_url', $values ) )
		{
			$params = new DSCParameter( '');
			$params->set( 'product_url', $values['product_url'] );
			$item->cartitem_params = trim( $params->toString( ) );
		}
		
		// onAfterCreateItemForAddToCart: plugin can add values to the item before it is being validated /added
		// once the extra field(s) have been set, they will get automatically saved
		$dispatcher = &JDispatcher::getInstance( );
		$results = $dispatcher->trigger( "onAfterCreateItemForAddToCart", array(
					$item, $values, $files
				) );
		foreach ( $results as $result )
		{
			foreach ( $result as $key => $value )
			{
				$item->set( $key, $value );
			}
		}
		
		// does the user/cart match all dependencies?
		$canAddToCart = $carthelper->canAddItem( $item, $cart_id, $id_type );
		if ( !$canAddToCart )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_CANNOT_ADD_ITEM_TO_CART') . " - " . $carthelper->getError( );
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		
		// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
		$results = array( );
		$dispatcher = &JDispatcher::getInstance( );
		$results = $dispatcher->trigger( "onBeforeAddToCart", array(
					&$item, $values
				) );
				
		for ( $i = 0; $i < count( $results ); $i++ )
		{
			$result = $results[$i];
			if ( !empty( $result->error ) )
			{
				$this->messagetype = 'notice';
				$this->message = $result->message;
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
			}
		}
		
		// if here, add to cart
		
		// After login, session_id is changed by Joomla, so store this for reference
		$session = &JFactory::getSession( );
		$session->set( 'old_sessionid', $session->getId( ) );
		
		// add the item to the cart
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$cart_helper = new TiendaHelperCarts( );
		$cartitem = $cart_helper->addItem( $item );
		
		// fire plugin event
		$dispatcher = JDispatcher::getInstance( );
		$dispatcher->trigger( 'onAfterAddToCart', array(
					$cartitem, $values
				) );
				
		// get the 'success' redirect url
		switch ( Tienda::getInstance( )->get( 'addtocartaction', 'redirect' ) )
		{
			case "0":
			case "none":
			// redirects back to product page
				break;
			case "samepage":
			// redirects back to the page it came from (category, content, etc)
			// Take only the url without the base domain (index.php?option.....)
			
				if ( $return_url = JRequest::getVar( 'return', '', 'method', 'base64' ) )
				{
					$return_url = base64_decode( $return_url );
					$uri = JURI::getInstance( );
					$uri->parse( $return_url );
					$redirect = $uri->toString( array(
						'path', 'query', 'fragment'
							) );
					$redirect = JRoute::_( $redirect, false );
				}
				break;
			case "lightbox":
			case "redirect":
			default:
			// if a base64_encoded url is present as return, use that as the return url
			// otherwise return == the product view page
				$returnUrl = base64_encode( $redirect );
				if ( $return_url = JRequest::getVar( 'return', '', 'method', 'base64' ) )
				{
					$return_url = base64_decode( $return_url );
					if ( JURI::isInternal( $return_url ) )
					{
						$returnUrl = base64_encode( $return_url );
					}
				}				
				// if a base64_encoded url is present as redirect, redirect there,
				// otherwise redirect to the cart
				$itemid = $router->findItemid( array(
							'view' => 'checkout'
						) );
						
				if( !$itemid )
					$itemid = JRequest::getInt( 'Itemid', 0 );
				$redirect = JRoute::_( "index.php?option=com_tienda&view=carts&Itemid=" . $itemid, false );
				if ( $redirect_url = JRequest::getVar( 'redirect', '', 'method', 'base64' ) )
				{
					$redirect_url = base64_decode( $redirect_url );
					if ( JURI::isInternal( $redirect_url ) )
					{
						$redirect = $redirect_url;
					}
				}
				
				//$returnUrl = base64_encode( $redirect );
				//$itemid = $router->findItemid( array('view'=>'checkout') );
				//$redirect = JRoute::_( "index.php?option=com_tienda&view=carts&Itemid=".$itemid, false );
				if ( strpos( $redirect, '?' ) === false )
				{
					$redirect .= "?return=" . $returnUrl;
				}
				else
				{
					$redirect .= "&return=" . $returnUrl;
				}
				
				break;
		}
		
		$this->messagetype = 'message';
		$this->message = JText::_('COM_TIENDA_ITEM_ADDED_TO_YOUR_CART');
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
		return;
	}
	
	/**
	 * Gets all the product's user reviews
	 * @param $product_id
	 * @return unknown_type
	 */
	function getComments( $view, $product_id )
	{
		$html = '';
		
		JModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
		$model = JModel::getInstance( 'productcomments', 'TiendaModel' );
		$selectsort = JRequest::getVar( 'default_selectsort', '' );
		$model->setstate( 'order', $selectsort );
		$limitstart = JRequest::getInt( 'limitstart', 0 );
		$model->setId( $product_id );
		$model->setstate( 'limitstart', $limitstart );
		$model->setstate( 'filter_product', $product_id );
		$model->setstate( 'filter_enabled', '1' );
		$reviews = $model->getList( );
		
		$count = count( $reviews );
		
		$lyt = $view->getLayout();
		$view->setLayout( 'product_comments' );
		$view->setModel( $model, true );
		$comments_data = new stdClass;
		$comments_data->product_id = $product_id;
		$comments_data->count = $count;
		$comments_data->reviews = $reviews;
		
		$user_id = JFactory::getUser( )->id;
		$productreview = TiendaHelperProduct::getUserAndProductIdForReview( $product_id, $user_id );
		$purchase_enable = Tienda::getInstance( )->get( 'purchase_leave_review_enable', '0' );
		$login_enable = Tienda::getInstance( )->get( 'login_review_enable', '0' );
		$product_review_enable = Tienda::getInstance( )->get( 'product_review_enable', '0' );
		
		$result = 1;
		if ( $product_review_enable == '1' )
		{
			$review_enable = 1;
		}
		else
		{
			$review_enable = 0;
		}
		if ( ( $login_enable == '1' ) )
		{
			if ( $user_id )
			{
				$order_enable = '1';
				
				if ( $purchase_enable == '1' )
				{
					$orderexist = TiendaHelperProduct::getOrders( $product_id );
					if ( !$orderexist )
					{
						$order_enable = '0';
						
					}
				}
				
				if ( ( $order_enable != '1' ) || !empty( $productreview ) )
				{
					$result = 0;
				}
			}
			else
			{
				$result = 0;
			}
		}
		
		$comments_data->review_enable = $review_enable;
		$comments_data->result = $result;
		$comments_data->click = 'index.php?option=com_tienda&controller=products&view=products&task=addReview';
		$comments_data->selectsort = $selectsort;
		$view->comments_data = $comments_data;
		$task = $model->getState( 'task' );
		$model->setState( 'task', 'product_comments' );
		ob_start( );
		$view->display( null );
		$html = ob_get_contents( );
		ob_end_clean( );
		$model->setState( 'task', $task );
		$view->setLayout( $lyt );
		
		return $html;
	}
		
	/**
	 * Verifies the fields in a submitted form.  Uses the table's check() method.
	 * Will often be overridden. Is expected to be called via Ajax 
	 * 
	 * @return unknown_type
	 */
	function validateChildren( )
	{
		$response = array( );
		$response['msg'] = '';
		$response['error'] = '';
		
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance( );
		
		// get elements from post
		$elements = json_decode( preg_replace( '/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
		
		// validate it using table's ->check() method
		if ( empty( $elements ) )
		{
			// if it fails check, return message
			$response['error'] = '1';
			$response['msg'] = $helper->generateMessage( "Could not process form" );
			echo ( json_encode( $response ) );
			return;
		}
		
		if ( !Tienda::getInstance( )->get( 'shop_enabled', '1' ) )
		{
			$response['msg'] = $helper->generateMessage( "Shop Disabled" );
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return false;
		}
		
		// convert elements to array that can be binded             
		$values = TiendaHelperBase::elementsToArray( $elements );
		$attributes_csv = '';
		$product_id = !empty( $values['product_id'] ) ? ( int ) $values['product_id'] : JRequest::getInt( 'product_id' );
		$quantities = !empty( $values['quantities'] ) ? $values['quantities'] : array( );
		
		$items = array( ); // this will collect the items to add to the cart 
		$attributes_csv = '';
		
		$user = JFactory::getUser( );
		$cart_id = $user->id;
		$id_type = "user_id";
		if ( empty( $user->id ) )
		{
			$session = &JFactory::getSession( );
			$cart_id = $session->getId( );
			$id_type = "session";
		}
		
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$carthelper = new TiendaHelperCarts( );
		
		$cart_recurs = $carthelper->hasRecurringItem( $cart_id, $id_type );
		
		// TODO get the children
		// loop thru each child,
		// get the list
		JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
		$model = JModel::getInstance( 'ProductRelations', 'TiendaModel' );
		$model->setState( 'filter_product', $product_id );
		$model->setState( 'filter_relation', 'parent' );
		if ( $children = $model->getList( ) )
		{
			foreach ( $children as $child )
			{
				$product_qty = $quantities[$child->product_id_to];
				
				// Integrity checks on quantity being added
				if ( $product_qty < 0 )
				{
					$product_qty = '1';
				}
				
				// using a helper file to determine the product's information related to inventory     
				$availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )
						->getAvailableQuantity( $child->product_id_to, $attributes_csv );
				if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
				{
					$response['msg'] = $helper
							->generateMessage( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $product_qty ) );
					$response['error'] = '1';
					echo ( json_encode( $response ) );
					return false;
				}
				
				// do the item's charges recur? does the cart already have a subscription in it?  if so, fail with notice
				$product = JTable::getInstance( 'Products', 'TiendaTable' );
				$product->load( array(
							'product_id' => $child->product_id_to
						) );
				
				// if product notforsale, fail
				if ( $product->product_notforsale )
				{
					$response['msg'] = $helper->generateMessage( "Product Not For Sale" );
					$response['error'] = '1';
					echo ( json_encode( $response ) );
					return false;
				}
				
				if ( $product->product_recurs && $cart_recurs )
				{
					$response['msg'] = $helper->generateMessage( "Cart Already Recurs" );
					$response['error'] = '1';
					echo ( json_encode( $response ) );
					return false;
				}
				
				if ( $product->product_recurs )
				{
					$product_qty = '1';
				}
				
				// create cart object out of item properties
				$item = new JObject;
				$item->user_id = JFactory::getUser( )->id;
				$item->product_id = ( int ) $child->product_id_to;
				$item->product_qty = ( int ) $product_qty;
				$item->product_attributes = $attributes_csv;
				$item->vendor_id = '0'; // vendors only in enterprise version
				
				// does the user/cart match all dependencies?
				$canAddToCart = $carthelper->canAddItem( $item, $cart_id, $id_type );
				if ( !$canAddToCart )
				{
					$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_CANNOT_ADD_ITEM_TO_CART') . " - " . $carthelper->getError( ) );
					$response['error'] = '1';
					echo ( json_encode( $response ) );
					return false;
				}
				
				// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
				$results = array( );
				$dispatcher = &JDispatcher::getInstance( );
				$results = $dispatcher->trigger( "onValidateAddToCart", array(
							$item, $values
						) );
				
				for ( $i = 0; $i < count( $results ); $i++ )
				{
					$result = $results[$i];
					if ( !empty( $result->error ) )
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
		
		if ( !empty( $items ) )
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
	function addChildrenToCart( )
	{
		JRequest::checkToken( ) or jexit( 'Invalid Token' );
		$product_id = JRequest::getInt( 'product_id' );
		$quantities = JRequest::getVar( 'quantities', array(
			0
		), 'request', 'array' );
		$filter_category = JRequest::getInt( 'filter_category' );
		
		Tienda::load( "TiendaHelperRoute", 'helpers.route' );
		$router = new TiendaHelperRoute( );
		if ( !$itemid = $router->product( $product_id, $filter_category, true ) )
		{
			$itemid = $router->category( 1, true );
		}
		
		// set the default redirect URL
		$redirect = "index.php?option=com_tienda&view=products&task=view&id=$product_id&filter_category=$filter_category&Itemid=" . $itemid;
		$redirect = JRoute::_( $redirect, false );
		
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance( );
		if ( !Tienda::getInstance( )->get( 'shop_enabled', '1' ) )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_SHOP_DISABLED');
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		
		$items = array( ); // this will collect the items to add to the cart 
		
		// convert elements to array that can be binded             
		$values = JRequest::get( 'post' );
		$attributes_csv = '';
		
		$user = JFactory::getUser( );
		$cart_id = $user->id;
		$id_type = "user_id";
		if ( empty( $user->id ) )
		{
			$session = &JFactory::getSession( );
			$cart_id = $session->getId( );
			$id_type = "session";
		}
		
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$carthelper = new TiendaHelperCarts( );
		
		$cart_recurs = $carthelper->hasRecurringItem( $cart_id, $id_type );
		
		// TODO get the children
		// loop thru each child,
		// get the list
		JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
		$model = JModel::getInstance( 'ProductRelations', 'TiendaModel' );
		$model->setState( 'filter_product', $product_id );
		$model->setState( 'filter_relation', 'parent' );
		if ( $children = $model->getList( ) )
		{
			foreach ( $children as $child )
			{
				$product_qty = $quantities[$child->product_id_to];
				
				// Integrity checks on quantity being added
				if ( $product_qty < 0 )
				{
					$product_qty = '1';
				}
				
				if ( !$product_qty ) // product quantity is zero -> skip this product
 continue;
				
				// using a helper file to determine the product's information related to inventory     
				$availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )
						->getAvailableQuantity( $child->product_id_to, $attributes_csv );
				if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
				{
					$this->messagetype = 'notice';
					$this->message = JText::_( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $product_qty ) );
					$this->setRedirect( $redirect, $this->message, $this->messagetype );
					return;
				}
				
				// do the item's charges recur? does the cart already have a subscription in it?  if so, fail with notice
				$product = JTable::getInstance( 'Products', 'TiendaTable' );
				$product->load( array(
							'product_id' => $child->product_id_to
						) );
				
				// if product notforsale, fail
				if ( $product->product_notforsale )
				{
					$this->messagetype = 'notice';
					$this->message = JText::_('COM_TIENDA_PRODUCT_NOT_FOR_SALE');
					$this->setRedirect( $redirect, $this->message, $this->messagetype );
					return;
				}
				
				if ( $product->product_recurs && $cart_recurs )
				{
					$this->messagetype = 'notice';
					$this->message = JText::_('COM_TIENDA_CART_ALREADY_RECURS');
					$this->setRedirect( $redirect, $this->message, $this->messagetype );
					return;
				}
				
				if ( $product->product_recurs )
				{
					$product_qty = '1';
				}
				
				// create cart object out of item properties
				$item = new JObject;
				$item->user_id = JFactory::getUser( )->id;
				$item->product_id = ( int ) $child->product_id_to;
				$item->product_qty = ( int ) $product_qty;
				$item->product_attributes = $attributes_csv;
				$item->vendor_id = '0'; // vendors only in enterprise version
				
				// does the user/cart match all dependencies?
				$canAddToCart = $carthelper->canAddItem( $item, $cart_id, $id_type );
				if ( !$canAddToCart )
				{
					$this->messagetype = 'notice';
					$this->message = JText::_('COM_TIENDA_CANNOT_ADD_ITEM_TO_CART') . " - " . $carthelper->getError( );
					$this->setRedirect( $redirect, $this->message, $this->messagetype );
					return;
				}
				
				// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
				$results = array( );
				$dispatcher = &JDispatcher::getInstance( );
				$results = $dispatcher->trigger( "onBeforeAddToCart", array(
							$item, $values
						) );
				
				for ( $i = 0; $i < count( $results ); $i++ )
				{
					$result = $results[$i];
					if ( !empty( $result->error ) )
					{
						$this->messagetype = 'notice';
						$this->message = $result->message;
						$this->setRedirect( $redirect, $this->message, $this->messagetype );
						return;
					}
				}
				
				// if here, add to cart                
				$items[] = $item;
			}
		}
		
		if ( !empty( $items ) )
		{
			// add the items to the cart
			Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
			TiendaHelperCarts::updateCart( $items );
			
			// fire plugin event
			$dispatcher = JDispatcher::getInstance( );
			$dispatcher->trigger( 'onAfterAddToCart', array(
						$items, $values
					) );
			
			$this->messagetype = 'message';
			$this->message = JText::_('COM_TIENDA_ITEMS_ADDED_TO_YOUR_CART');
		}
		
		// After login, session_id is changed by Joomla, so store this for reference
		$session = &JFactory::getSession( );
		$session->set( 'old_sessionid', $session->getId( ) );
		
		// get the 'success' redirect url
		// TODO Enable redirect via base64_encoded urls?
		switch ( Tienda::getInstance( )->get( 'addtocartaction', 'redirect' ) )
		{
			case "redirect":
				$returnUrl = base64_encode( $redirect );
				$itemid = $router->findItemid( array(
							'view' => 'checkout'
						) );
				$redirect = JRoute::_( "index.php?option=com_tienda&view=carts&Itemid=" . $itemid, false );
				if ( strpos( $redirect, '?' ) === false )
				{
					$redirect .= "?return=" . $returnUrl;
				}
				else
				{
					$redirect .= "&return=" . $returnUrl;
				}
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
	 * Verifies the fields in a submitted form. Is expected to be called via Ajax 
	 * 
	 * @return unknown_type
	 */
	function validateReview( )
	{
		$response = array( );
		$response['msg'] = '';
		$response['error'] = '';
		$errors = array( );
		
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
		$helper = TiendaHelperBase::getInstance( );
		$user = &JFactory::getUser( );
		
		// get elements from post
		$elements = json_decode( preg_replace( '/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
		
		// validate it using table's ->check() method
		if ( empty( $elements ) )
		{
			// if it fails check, return message
			$response['error'] = '1';
			$response['msg'] = $helper->generateMessage( "Could not process form" );
			echo ( json_encode( $response ) );
			return;
		}
		
		if ( !Tienda::getInstance( )->get( 'shop_enabled', '1' ) )
		{
			$response['msg'] = $helper->generateMessage( "Shop Disabled" );
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return false;
		}
		
		// convert elements to array that can be binded             
		$values = TiendaHelperBase::elementsToArray( $elements );
		if ( !$user->id )
		{
			if ( empty( $values['user_name'] ) )
			{
				$errors[] = '<li>' . JText::_('COM_TIENDA_NAME_FIELD_IS_REQUIRED') . '</li>';
			}
			
			jimport( 'joomla.mail.helper' );
			if ( !JMailHelper::isEmailAddress( $values['user_email'] ) )
			{
				$errors[] = '<li>' . JText::_('COM_TIENDA_PLEASE_ENTER_A_CORRECT_EMAIL_ADDRESS') . '</li>';
			}
			
			if ( in_array( $values['user_email'], TiendaHelperProduct::getUserEmailForReview( $values['product_id'] ) ) )
			{
				$errors[] = '<li>' . JText::_('COM_TIENDA_YOU_ALREADY_SUBMITTED_A_REVIEW_CAN_ONLY_SUBMIT_REVIEW_ONCE') . '</li>';
			}
		}
		else
		{
			if ( in_array( $user->email, TiendaHelperProduct::getUserEmailForReview( $values['product_id'] ) ) )
			{
				$errors[] = '<li>' . JText::_('COM_TIENDA_YOU_ALREADY_SUBMITTED_A_REVIEW_CAN_ONLY_SUBMIT_REVIEW_ONCE') . '</li>';
			}
		}
		
		if ( count( $errors ) ) // there were erros => stop here
		{
			$response['error'] = 1;
			$response['msg'] = $helper->generateMessage( implode( "\n", $errors ), false );
			echo ( json_encode( $response ) );
			return;
		}
		
		if ( empty( $values['productcomment_rating'] ) )
		{
			$errors[] = '<li>' . JText::_('COM_TIENDA_RATING_IS_REQUIRED') . '</li>';
		}
		
		if ( empty( $values['productcomment_text'] ) )
		{
			$errors[] = '<li>' . JText::_('COM_TIENDA_COMMENT_FIELD_IS_REQUIRED') . '</li>';
		}
		
		if ( count( $errors ) ) // there were erros
		{
			$response['error'] = 1;
			$response['msg'] = $helper->generateMessage( implode( "\n", $errors ), false );
		}
		
		echo ( json_encode( $response ) );
		return;
	}
	
	/**
	 * Add review
	 *
	 */ 
	function addReview( )
	{
		JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/tables' );
		Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
		$productreviews = JTable::getInstance( 'productcomments', 'TiendaTable' );
		$post = JRequest::get( 'post' );
		$product_id = $post['product_id'];
		$Itemid = $post['Itemid'];
		$user = JFactory::getUser( );
		$valid = true;
		$this->messagetype = 'message';
		
		//set in case validation fails
		$linkAdd = '';
		$linkAdd .= '&rn=' . base64_encode( $post['user_name'] );
		$linkAdd .= '&re=' . base64_encode( $post['user_email'] );
		$linkAdd .= '&rc=' . base64_encode( $post['productcomment_text'] );
		
		if ( !$user->id )
		{
			if ( empty( $post['user_name'] ) && $valid )
			{
				$valid = false;
				$this->message = JText::_('COM_TIENDA_NAME_FIELD_IS_REQUIRED');
				$this->messagetype = 'notice';
			}
			
			jimport( 'joomla.mail.helper' );
			if ( !JMailHelper::isEmailAddress( $post['user_email'] ) && $valid )
			{
				$valid = false;
				$this->message = JText::_('COM_TIENDA_PLEASE_ENTER_A_CORRECT_EMAIL_ADDRESS');
				$this->messagetype = 'notice';
			}
			
			if ( in_array( $post['user_email'], TiendaHelperProduct::getUserEmailForReview( $post['product_id'] ) ) && $valid )
			{
				$valid = false;
				$this->message = JText::_('COM_TIENDA_YOU_ALREADY_SUBMITTED_A_REVIEW_CAN_ONLY_SUBMIT_REVIEW_ONCE');
				$this->messagetype = 'notice';
			}
		}
		else
		{
			if ( in_array( $user->email, TiendaHelperProduct::getUserEmailForReview( $post['product_id'] ) ) && $valid )
			{
				$valid = false;
				$this->message = JText::_('COM_TIENDA_YOU_ALREADY_SUBMITTED_A_REVIEW_CAN_ONLY_SUBMIT_REVIEW_ONCE');
				$this->messagetype = 'notice';
			}
		}
		
		if ( empty( $post['productcomment_rating'] ) && $valid )
		{
			$valid = false;
			$this->message = JText::_('COM_TIENDA_RATING_IS_REQUIRED');
			$this->messagetype = 'notice';
		}
		
		if ( empty( $post['productcomment_text'] ) && $valid )
		{
			$valid = false;
			$this->message = JText::_('COM_TIENDA_COMMENT_FIELD_IS_REQUIRED');
			$this->messagetype = 'notice';
		}
		
		$captcha = true;
		if ( Tienda::getInstance( )->get( 'use_captcha', '0' ) && $valid )
		{
			$privatekey = "6LcAcbwSAAAAANZOTZWYzYWRULBU_S--368ld2Fb";
			$captcha = false;
			
			if ( $_POST["recaptcha_response_field"] )
			{
				Tienda::load( 'TiendaRecaptcha', 'library.recaptcha' );
				$recaptcha = new TiendaRecaptcha( );
				$resp = $recaptcha
						->recaptcha_check_answer( $privatekey, $_SERVER["REMOTE_ADDR"], $post['recaptcha_challenge_field'],
								$post['recaptcha_response_field'] );
				if ( $resp->is_valid )
				{
					$captcha = true;
				}
			}
		}
		
		if ( !$captcha && $valid )
		{
			$valid = false;
			$this->message = JText::_('COM_TIENDA_INCORRECT_CAPTCHA');
			$this->messagetype = 'notice';
		}
		
		if ( $valid )
		{
			$date = JFactory::getDate( );
			$productreviews->bind( $post );
			$productreviews->created_date = $date->toMysql( );
			$productreviews->productcomment_enabled = Tienda::getInstance( )->get( 'product_reviews_autoapprove', '0' );
			
			if ( !$productreviews->save( ) )
			{
				$this->message = JText::_('COM_TIENDA_UNABLE_TO_SAVE_REVIEW') . " :: " . $productreviews->getError( );
				$this->messagetype = 'notice';
			}
			else
			{
				$dispatcher = &JDispatcher::getInstance( );
				$dispatcher->trigger( 'onAfterSaveProductComments', array(
							$productreviews
						) );
				$this->message = JText::_('COM_TIENDA_SUCCESSFULLY_SUBMITTED_REVIEW');
				
				//successful
				$linkAdd = '';
			}
		}
		$redirect = "index.php?option=com_tienda&view=products&task=view&id=" . $product_id . $linkAdd . "&Itemid=" . $Itemid;
		$redirect = JRoute::_( $redirect );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}
	
	/**
	 * Adding helpfulness of review 
	 *
	 */
	function reviewHelpfullness( )
	{
		$user_id = JFactory::getUser( )->id;
		$Itemid = JRequest::getInt( 'Itemid', '' );
		$id = JRequest::getInt( 'product_id', '' );
		$url = "index.php?option=com_tienda&view=products&task=view&Itemid=" . $Itemid . "&id=" . $id;
		
		if ( $user_id )
		{
			$productcomment_id = JRequest::getInt( 'productcomment_id', '' );
			Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
			$producthelper = new TiendaHelperProduct( );
			JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
			$productcomment = JTable::getInstance( 'productcomments', 'TiendaTable' );
			$productcomment->load( $productcomment_id );
			
			$helpful_votes_total = $productcomment->helpful_votes_total;
			$helpful_votes_total = $helpful_votes_total + 1;
			$helpfulness = JRequest::getInt( 'helpfulness', '' );
			if ( $helpfulness == 1 )
			{
				$helpful_vote = $productcomment->helpful_votes;
				$helpful_vote_new = $helpful_vote + 1;
				$productcomment->helpful_votes = $helpful_vote_new;
			}
			$productcomment->helpful_votes_total = $helpful_votes_total;
			
			$report = JRequest::getInt( 'report', '' );
			if ( $report == 1 )
			{
				$productcomment->reported_count = $productcomment->reported_count + 1;
			}
			
			$help = array( );
			$help['productcomment_id'] = $productcomment_id;
			$help['helpful'] = $helpfulness;
			$help['user_id'] = $user_id;
			$help['reported'] = $report;
			JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
			$reviewhelpfulness = JTable::getInstance( 'ProductCommentsHelpfulness', 'TiendaTable' );
			$reviewhelpfulness->load( array(
						'user_id' => $user_id, 'productcomment_id' => $productcomment_id
					) );
			
			$application = &JFactory::getApplication( );
			if ( $report == 1 && !empty( $reviewhelpfulness->productcommentshelpfulness_id ) && empty( $reviewhelpfulness->reported ) )
			{
				$reviewhelpfulness->reported = 1;
				$reviewhelpfulness->save( );
				
				$productcomment->save( );
				$application->enqueueMessage( JText::sprintf( "COM_TIENDA_THANKS_FOR_REPORTING_THIS_REVIEW" ) );
				$application->redirect( $url );
				return;
			}
			
			if ( $report )
			{
				$application->enqueueMessage( JText::sprintf( "COM_TIENDA_YOU_ALREADY_REPORTED_THIS_REVIEW" ) );
				$application->redirect( $url );
			}
			
			$reviewhelpfulness->bind( $help );
			if ( !empty( $reviewhelpfulness->productcommentshelpfulness_id ) )
			{
				$application->enqueueMessage( JText::sprintf( "COM_TIENDA_YOU_HAVE_ALREADY_GIVEN_YOUR_FEEDBACK_ON_THIS_REVIEW" ) );
				$application->redirect( $url );
				return;
			}
			else
			{
				$reviewhelpfulness->save( );
				$productcomment->save( );
				$application->enqueueMessage( JText::sprintf( "COM_TIENDA_THANKS_FOR_YOUR_FEEDBACK_ON_THIS_COMMENT" ) );
				$application->redirect( $url );
				return;
			}
		}
		else
		{
			$redirect = "index.php?option=com_user&view=login&return=" . base64_encode( $url );
			$redirect = JRoute::_( $redirect, false );
			JFactory::getApplication( )->redirect( $redirect );
			return;
		}
	}
	
	/**
	 * Displays a ask question form
	 * (non-PHPdoc)
	 * @see tienda/site/TiendaController#askQuestion()
	 */
	function askQuestion( )
	{
		JRequest::setVar( 'view', $this->get( 'suffix' ) );
		$model = $this->getModel( $this->get( 'suffix' ) );
		$view = $this->getView( $this->get( 'suffix' ), JFactory::getDocument( )->getType( ) );
		$view->set( '_doTask', true );
		
		$view->setModel( $model, true );
		$view->setLayout( 'form_askquestion' );
		$view->display( );
		$this->footer( );
		return;
	}
	
	function sendAskedQuestion( )
	{
		$config = Tienda::getInstance( );
		$post = JRequest::get( 'post' );
		
		$valid = true;
		$this->messagetype = 'message';
		$this->message = '';
		$add_link = '';
		if ( empty( $post['sender_name'] ) && $valid )
		{
			$valid = false;
			$this->message = JText::_('COM_TIENDA_NAME_FIELD_IS_REQUIRED');
			$this->messagetype = 'notice';
		}
		
		jimport( 'joomla.mail.helper' );
		if ( !JMailHelper::isEmailAddress( $post['sender_mail'] ) && $valid )
		{
			$valid = false;
			$this->message = JText::_('COM_TIENDA_PLEASE_ENTER_A_CORRECT_EMAIL_ADDRESS');
			$this->messagetype = 'notice';
			
			$add_link .= "&sender_name={$post['sender_name']}";
			$add_link .= !empty( $post['sender_message'] ) ? "&sender_message={$post['sender_message']}" : '';
		}
		
		if ( empty( $post['sender_message'] ) && $valid )
		{
			$valid = false;
			$this->message = JText::_('COM_TIENDA_MESSAGE_FIELD_IS_REQUIRED');
			$this->messagetype = 'notice';
			$add_link .= "&sender_name={$post['sender_name']}&sender_mail={$post['sender_mail']}";
		}
		
		//captcha checking
		$captcha = true;
		if ( ( $config->get( 'ask_question_showcaptcha', '1' ) == 1 ) && $valid )
		{
			$privatekey = "6LcAcbwSAAAAANZOTZWYzYWRULBU_S--368ld2Fb";
			$captcha = false;
			
			if ( $_POST["recaptcha_response_field"] )
			{
				Tienda::load( 'TiendaRecaptcha', 'library.recaptcha' );
				$recaptcha = new TiendaRecaptcha( );
				$resp = $recaptcha
						->recaptcha_check_answer( $privatekey, $_SERVER["REMOTE_ADDR"], $post['recaptcha_challenge_field'],
								$post['recaptcha_response_field'] );
				if ( $resp->is_valid )
				{
					$captcha = true;
				}
			}
			
		}
		if ( !$captcha )
		{
			$valid = false;
			$this->message = JText::_('COM_TIENDA_INCORRECT_CAPTCHA');
			$this->messagetype = 'notice';
			$add_link .= "&sender_name={$post['sender_name']}&sender_mail={$post['sender_mail']}&sender_message={$post['sender_message']}";
		}
		
		if ( $valid )
		{
			$mainframe = JFactory::getApplication( );
			$sendObject = new JObject( );
			$sendObject->mailfrom = $post['sender_mail'];
			$sendObject->namefrom = $post['sender_name'];
			$sendObject->mailto = $config->get( 'emails_defaultemail', $mainframe->getCfg( 'mailfrom' ) );
			$sendObject->body = $post['sender_message'];
			
			//get product info
			JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
			$model = JModel::getInstance( 'Products', 'TiendaModel' );
			$model->setId( $post['product_id'] );
			$sendObject->item = $model->getItem( );
			
			Tienda::load( "TiendaHelperBase", 'helpers._base' );
			$helper = TiendaHelperBase::getInstance( 'Email' );
			if ( $send = $helper->sendEmailToAskQuestionOnProduct( $sendObject ) )
			{
				$this->message = JText::_('COM_TIENDA_MESSAGE_SUCCESSFULLY_SENT');
			}
			else
			{
				$this->message = JText::_('COM_TIENDA_ERROR_IN_SENDING_MESSAGE');
				$this->messagetype = 'notice';
			}
			if ( Tienda::getInstance( )->get( 'ask_question_modal', '1' ) )
			{
				$url = "index.php?option=com_tienda&view=products&task=askquestion&id={$post['product_id']}&tmpl=component&return=" . $post['return']
						. $add_link . "&success=1";
				$redirect = JRoute::_( $url );
			}
			else
			{
				$redirect = JRoute::_( base64_decode( $post['return'] ) );
			}
			
		}
		else
		{
			$url = "index.php?option=com_tienda&view=products&task=askquestion&id={$post['product_id']}&tmpl=component&return=" . $post['return']
					. $add_link;
			$redirect = JRoute::_( $url );
		}
		
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}
	
	/**
	 * Upload via ajax through Uploadify
	 * It's here because when the swf connects to the admin side, it would need to login.
	 */
	function uploadifyImage( )
	{
		$product_id = JRequest::getInt( 'product_id', 0 );
		
		if ( $product_id )
		{
			Tienda::load( 'TiendaImage', 'library.image' );
			$upload = new TiendaImage( );
			// handle upload creates upload object properties
			$upload->handleUpload( 'uploadify_image' );
			
			// then save image to appropriate folder
			$product = JTable::getInstance( 'Products', 'TiendaTable' );
			$product->load( $product_id );
			$path = $product->getImagePath( );
			
			$upload->setDirectory( $path );
			
			// Do the real upload!
			$success = $upload->upload( );
			
			Tienda::load( 'TiendaHelperImage', 'helpers.image' );
			$imgHelper = TiendaHelperBase::getInstance( 'Image', 'TiendaHelper' );
			if ( !$imgHelper->resizeImage( $upload, 'product' ) )
			{
				$success = false;
			}
			
			if ( $success )
			{
				// save as default?
				if ( empty( $product->product_full_image ) )
				{
					$product->product_full_image = $upload->getPhysicalName( );
					$product->save( );
				}
				echo JText::_('COM_TIENDA_IMAGE_UPLOADED_CORRECTLY');
			}
			else
			{
				echo 'Error: ' . $upload->getError( );
			}
		}
	}
	
	public function addToWishlist()
	{
	    // verify form submitted by user
		JRequest::checkToken( ) or jexit( 'Invalid Token' );
		
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute();

        $product_id = JRequest::getInt( 'product_id' );
		$filter_category = JRequest::getInt( 'filter_category' );
		if ( !$itemid = $router->product( $product_id, $filter_category, true ) )
		{
			$itemid = $router->category( 1, true );
			if( !$itemid )
				$itemid = JRequest::getInt( 'Itemid', 0 );
		}
		
		// set the default redirect URL
		$redirect = "index.php?option=com_tienda&view=products&task=view&id=$product_id&filter_category=$filter_category&Itemid=" . $itemid;
		$redirect = JRoute::_( $redirect, false );
		
        JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/tables' );
		$product = JTable::getInstance( 'Products', 'TiendaTable' );
		$product->load( $product_id, true, false );
		
		if (empty($product->product_id))
		{
		    // not a valid product, so return to product detail page with invalid-product message
    		$this->messagetype = 'notice';
    		$this->message = JText::_('COM_TIENDA_INVALID_PRODUCT');		    
		    $this->setRedirect( $redirect, $this->message, $this->messagetype );
		    return;
		}
		
		$values = JRequest::get('post');
		
		$attributes = array( );
		foreach ( $values as $key => $value )
		{
			if ( substr( $key, 0, 10 ) == 'attribute_' )
			{
				$attributes[] = $value;
			}
		}
		sort( $attributes );
		$attributes_csv = implode( ',', $attributes );

		$user_id = JFactory::getUser()->id;
		if (empty($user_id))
		{
    	    // if not logged in, add item to session wishlist, then redirect to login/registration page, and upon login (in user plugin) add item from session to wishlist
    	    $session = JFactory::getSession();
    	    $session_id = $session->getId();
    	    $session->set( 'old_sessionid', $session_id );
    	    
    	    $wishlist = JTable::getInstance( 'Wishlists', 'TiendaTable' );
    		$wishlist->session_id = $session_id;
    		$wishlist->product_id = $product->product_id;
    		$wishlist->product_attributes = $attributes_csv;
    		$wishlist->last_updated = JFactory::getDate()->toMySQL();
		    $wishlist->store();
		    
		    JFactory::getApplication()->enqueueMessage( JText::_('COM_TIENDA_LOGIN_TO_ADD_ITEM_TO_WISHLIST') );
		    
            Tienda::load( "TiendaHelperRoute", 'helpers.route' );
            $router = new TiendaHelperRoute(); 
            $url = $redirect; // set above
            $redirect = "index.php?option=com_user&view=login&return=".base64_encode( $url );
            $redirect = JRoute::_( $redirect, false );
            JFactory::getApplication()->redirect( $redirect );
            return;
		}
		
		
	    // add to wishlist
	    $wishlist = JTable::getInstance( 'Wishlists', 'TiendaTable' );
	    // load from db in case the item is in the wishlist already and should be updated 
	    $wishlist->load( array( 'user_id'=>$user_id, 'product_id'=>$product->product_id, 'product_attributes'=>$attributes_csv ) );
	    // set the values
		$wishlist->user_id = $user_id;
		$wishlist->product_id = $product->product_id;
		$wishlist->product_attributes = $attributes_csv;
		$wishlist->last_updated = JFactory::getDate()->toMySQL();
		
		if (!$wishlist->save())
		{
    		$this->messagetype = 'notice';
    		$this->message = JText::_('COM_TIENDA_COULD_NOT_ADD_TO_WISHLIST');
		}
    		else
		{
		    $url = "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') );
    		$this->messagetype = 'message';
    		$this->message = JText::sprintf( JText::_('COM_TIENDA_ADDED_TO_WISHLIST'), $url );
		}
		
	    // redirect back to product detail page, with message saying "item added, click here to view wishlist"
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
		return;
	}
}

?>
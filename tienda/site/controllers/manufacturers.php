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

class TiendaControllerManufacturers extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->set('suffix', 'manufacturers');
	}

	/**
	 * Sets the model's state
	 *
	 * @return array()
	 */
	function _setModelState( $model_name='' )
	{
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		if (empty($model_name)) {
			$model_name = $this->get('suffix');
		}
		$model = $this->getModel( $model_name );
		$ns = $this->getNamespace();

		$date = JFactory::getDate();

		$state['order'] = 'tbl.ordering';
		$state['direction'] = 'ASC';
		$state['filter_enabled']  = 1;
		$state['filter_published'] = 1;
		$state['filter_published_date'] = $date->toMySQL();
		$state['filter_manufacturer'] = JRequest::getInt('filter_manufacturer');

		//NOTE: check if filter_price_from and filter_price_to are empty since product will not show even if there is a product if its empty
		//check the filter price from
		$priceFrm = JRequest::getInt('filter_price_from');
		if( !empty( $priceFrm ) )
		{
			$state['filter_price_from'] = $priceFrm;
		}

		//check the filter price from
		$priceTo = JRequest::getInt('filter_price_to');
		if( !empty( $priceTo ) )
		{
			$state['filter_price_to'] = $priceTo;
		}

		if (!Tienda::getInstance()->get('display_out_of_stock'))
		{
			$state['filter_quantity_from'] = '1';
		}

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		return $state;
	}

	/**
	 * Displays products by manufacturer
	 *
	 * (non-PHPdoc)
	 * @see tienda/admin/TiendaController#display($cachable)
	 */
	function products()
	{
		JRequest::setVar( 'view', $this->get('suffix') );
		JRequest::setVar( 'search', false );
		$view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
		$model  = $this->getModel( 'products' );
		$state = $this->_setModelState( 'products' );

		$filter_manufacturer = $model->getState( 'filter_manufacturer' );

		// get the manufacturer we're looking at
		DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$cmodel = DSCModel::getInstance( 'Manufacturers', 'TiendaModel' );
		$cat = $cmodel->getTable();
		$cat->load( $filter_manufacturer );

		// set the title based on the selected manufacturer
		$title = (empty($cat->manufacturer_name)) ? JText::_('COM_TIENDA_ALL_MANUFACTURERS') : JText::_($cat->manufacturer_name);

		// breadcrumb support
		$app = JFactory::getApplication();
		$pathway = $app->getPathway();
		$manufacturer_itemid = JRequest::getInt('Itemid', Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->manufacturer( $filter_manufacturer, true ) );

		// get the products to be displayed in this category
		if ($items = $model->getList())
		{
			foreach ($items as $item)
			{
				$itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->product( $item->product_id, null, true );
				$item->itemid = JRequest::getInt('Itemid', $itemid);

				$item->product_buy = $this->getAddToCart($item->product_id);
			}
		}

		$view->assign( 'title', $title );
		$view->assign( 'cat', $cat );
		$view->assign( 'items', $items );
		$view->set('_doTask', true);
		$view->setModel( $model, true );

		$view->setLayout('products');

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

		Tienda::load('TiendaHelperUser', 'helpers.user');
		$user_id = JFactory::getUser()->id;
		$filter_group = TiendaHelperUser::getUserGroup($user_id);
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
			$this->message = JText::_('COM_TIENDA_CANNOT_VIEW_DISABLED_PRODUCT');
			$this->messagetype = 'notice';
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}

		Tienda::load( 'TiendaArticle', 'library.article' );
		$product_description = TiendaArticle::fromString( $row->product_description );


		DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$cmodel = DSCModel::getInstance( 'Categories', 'TiendaModel' );
		$cat = $cmodel->getTable();
		$cat->load( $filter_category );

		$view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
		$view->set('_doTask', true);
		$view->assign( 'row', $row );
		$view->assign( 'cat', $cat );

		// breadcrumb support
		$app = JFactory::getApplication();
		$pathway = $app->getPathway();
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

			if (!Tienda::getInstance()->get('display_out_of_stock') && empty($inventoryList))
			{
				// redirect
				$redirect = "index.php?option=com_tienda&view=products&task=display&filter_category=".$filter_category;
				$redirect = JRoute::_( $redirect, false );
				$this->message = JText::_('COM_TIENDA_CANNOT_VIEW_PRODUCT');
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

		$dispatcher = JDispatcher::getInstance();

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
		$model = DSCModel::getInstance('Products', 'TiendaModel');
		$model->setId( $product_id );

		Tienda::load('TiendaHelperUser', 'helpers.user');
		$user_id = JFactory::getUser()->id;
		$filter_group = TiendaHelperUser::getUserGroup($user_id);
		$model->setState('filter_group', $filter_group);

		//$model->_item = '';
		$row = $model->getItem( false );
		if ($row->product_notforsale || Tienda::getInstance()->get('shop_enabled') == '0')
		{
			return $html;
		}

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
		$view->assign('validation', "index.php?option=com_tienda&view=products&task=validate&format=raw" );

		$config = Tienda::getInstance();
		$show_tax = $config->get('display_prices_with_tax');
		$view->assign( 'show_tax', $show_tax );
		$view->assign( 'tax', 0 );
		$view->assign( 'taxtotal', '' );
		$view->assign( 'shipping_cost_link', '' );

		$row->tax = '0';
		if ($show_tax)
		{
			// finish TiendaHelperUser::getGeoZone -- that's why this isn't working
			Tienda::load('TiendaHelperUser', 'helpers.user');
			$geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
			if (empty($geozones))
			{
				// use the default
				$table = DSCTable::getInstance('Geozones', 'TiendaTable');
				$table->load(array('geozone_id'=>Tienda::getInstance()->get('default_tax_geozone')));
				$geozones = array( $table );
			}

			$taxtotal = TiendaHelperProduct::getTaxTotal($product_id, $geozones);
			$tax = $taxtotal->tax_total;
			$row->taxtotal = $taxtotal;
			$row->tax = $tax;
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
			if ($product_qty < 0) {
				$product_qty = '1';
			}

			// using a helper file to determine the product's information related to inventory
			$availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $attributes_csv );
			if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
			{
				$invalidQuantity = '1';
			}

			// adjust the displayed price based on the selected attributes
			$table = DSCTable::getInstance('ProductAttributeOptions', 'TiendaTable');
			$attrs = array();
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
				$attrs[] = $table->productattributeoption_id;
			}
			$row->sku =  TiendaHelperProduct::getProductSKU($row, $attrs);
			$view->assign( 'item', $row );
		}
		 
		$view->assign( 'availableQuantity', $availableQuantity );
		$view->assign( 'invalidQuantity', $invalidQuantity );

		$dispatcher = JDispatcher::getInstance();

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
		DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$model = DSCModel::getInstance( 'ProductFiles', 'TiendaModel' );
		$model->setState( 'filter_product', $product_id );
		$model->setState( 'filter_enabled', 1 );
		//$model->setState( 'filter_purchaserequired', 1 );
		$items = $model->getList();

		// get the user's active subscriptions to this product, if possible
		$submodel = DSCModel::getInstance( 'Subscriptions', 'TiendaModel' );
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
		DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$model = DSCModel::getInstance( 'ProductRelations', 'TiendaModel' );
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
		$user = JFactory::getUser();
		$productfile_id = intval( JRequest::getvar( 'id', '', 'request', 'int' ) );
		$product_id = intval( JRequest::getvar( 'product_id', '', 'request', 'int' ) );
		$link = 'index.php?option=com_tienda&controller=products&view=products&task=view&id='.$product_id;

		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance( 'ProductDownload', 'TiendaHelper' );

		if ( !$canView = $helper->canDownload( $productfile_id, JFactory::getUser()->id ) )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_NOT_AUTHORIZED_TO_DOWNLOAD_FILE');
			$this->setRedirect( $link, $this->message, $this->messagetype );
			return false;
		}
		DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		$productfile = DSCTable::getInstance( 'ProductFiles', 'TiendaTable' );
		$productfile->load( $productfile_id );
		if (empty($productfile->productfile_id))
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_INVALID FILE');
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
			$productDownload = DSCTable::getInstance('ProductDownloads', 'TiendaTable');
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
			$response['msg'] = $helper->generateMessage(JText::_('COM_TIENDA_COULD_NOT_PROCESS_FORM'));
			echo ( json_encode( $response ) );
			return;
		}

		if (!Tienda::getInstance()->get('shop_enabled', '1'))
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
		if ($product_qty < 0) {
			$product_qty = '1';
		}

		// using a helper file to determine the product's information related to inventory
		$availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $attributes_csv );
		if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
		{
			$response['msg'] = $helper->generateMessage( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $product_qty ) );
			$response['error'] = '1';
			echo ( json_encode( $response ) );
			return false;
		}

		$product = DSCTable::getInstance('Products', 'TiendaTable');
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
			$session = JFactory::getSession();
			$keynames['session_id'] = $session->getId();
		}
		$keynames['product_id'] = $product_id;

		$cartitem = DSCTable::getInstance( 'Carts', 'TiendaTable' );
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
						$msg = $helper->generateMessage( "You have reached the maximum quantity for this item. You can order another ".$remaining );
					}
				}
				if( $min )
				{
					if ($product_qty < $min )
					{
						$error = true;
						$msg = $helper->generateMessage( "You have not reached the miminum quantity for this item. You have to order at least ".$min );
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
		$dispatcher = JDispatcher::getInstance();
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
		if (!Tienda::getInstance()->get('shop_enabled', '1'))
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_SHOP_DISABLED');
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
		if ($product_qty < 0) {
			$product_qty = '1';
		}

		// using a helper file to determine the product's information related to inventory
		$availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $attributes_csv );
		if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $product_qty ) );
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}

		// do the item's charges recur? does the cart already have a subscription in it?  if so, fail with notice
		$product = DSCTable::getInstance('Products', 'TiendaTable');
		$product->load( array( 'product_id'=>$product_id ) );

		// if product notforsale, fail
		if ($product->product_notforsale)
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_PRODUCT_NOT_FOR_SALE');
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}

		$user = JFactory::getUser();
		$cart_id = $user->id;
		$id_type = "user_id";
		if (empty($user->id))
		{
			$session = JFactory::getSession();
			$cart_id = $session->getId();
			$id_type = "session";
		}

		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$carthelper = new TiendaHelperCarts();

		$cart_recurs = $carthelper->hasRecurringItem( $cart_id, $id_type );
		if ($product->product_recurs && $cart_recurs)
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_CART_ALREADY_RECURS');
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

		// onAfterCreateItemForAddToCart: plugin can add values to the item before it is being validated /added
		// once the extra field(s) have been set, they will get automatically saved
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger( "onAfterCreateItemForAddToCart", array( $item, $values ) );
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
			$this->message      = JText::_('COM_TIENDA_CANNOT_ADD_ITEM_TO_CART') . " - " . $carthelper->getError();
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}

		// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
		$results = array();
		$dispatcher = JDispatcher::getInstance();
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
		$session = JFactory::getSession();
		$session->set( 'old_sessionid', $session->getId() );

		// add the item to the cart
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$cart_helper = new TiendaHelperCarts();
		$cartitem = $cart_helper->addItem( $item );

		// fire plugin event
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger( 'onAfterAddToCart', array( $cartitem, $values ) );

		// get the 'success' redirect url
		switch (Tienda::getInstance()->get('addtocartaction', 'redirect'))
		{
			case "0":
			case "none":
				// redirects back to product page
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
				if (strpos($redirect, '?') === false) {
					$redirect .= "?return=".$returnUrl;
				} else { $redirect .= "&return=".$returnUrl;
				}

				break;
		}

		$this->messagetype  = 'message';
		$this->message      = JText::_('COM_TIENDA_ITEM_ADDED_TO_YOUR_CART');
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
		$view   = $this->getView( 'products', 'html' );

		DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$model = DSCModel::getInstance( 'productcomments', 'TiendaModel' );
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
		$purchase_enable = Tienda::getInstance()->get('purchase_leave_review_enable', '0');
		$login_enable = Tienda::getInstance()->get('login_review_enable', '0');
		$product_review_enable=Tienda::getInstance()->get('product_review_enable', '0');

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

		if (!Tienda::getInstance()->get('shop_enabled', '1'))
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
			$session = JFactory::getSession();
			$cart_id = $session->getId();
			$id_type = "session";
		}

		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$carthelper = new TiendaHelperCarts();

		$cart_recurs = $carthelper->hasRecurringItem( $cart_id, $id_type );

		// TODO get the children
		// loop thru each child,
		// get the list
		DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$model = DSCModel::getInstance( 'ProductRelations', 'TiendaModel' );
		$model->setState( 'filter_product', $product_id );
		$model->setState( 'filter_relation', 'parent' );
		if ($children = $model->getList())
		{
			foreach ($children as $child)
			{
				$product_qty = $quantities[$child->product_id_to];

				// Integrity checks on quantity being added
				if ($product_qty < 0) {
					$product_qty = '1';
				}

				// using a helper file to determine the product's information related to inventory
				$availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $child->product_id_to, $attributes_csv );
				if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
				{
					$response['msg'] = $helper->generateMessage( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $product_qty ) );
					$response['error'] = '1';
					echo ( json_encode( $response ) );
					return false;
				}

				// do the item's charges recur? does the cart already have a subscription in it?  if so, fail with notice
				$product = DSCTable::getInstance('Products', 'TiendaTable');
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
					$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_CANNOT_ADD_ITEM_TO_CART') . " - " . $carthelper->getError() );
					$response['error'] = '1';
					echo ( json_encode( $response ) );
					return false;
				}

				// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
				$results = array();
				$dispatcher = JDispatcher::getInstance();
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
		if (!Tienda::getInstance()->get('shop_enabled', '1'))
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_SHOP_DISABLED');
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
			$session = JFactory::getSession();
			$cart_id = $session->getId();
			$id_type = "session";
		}

		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$carthelper = new TiendaHelperCarts();

		$cart_recurs = $carthelper->hasRecurringItem( $cart_id, $id_type );

		// TODO get the children
		// loop thru each child,
		// get the list
		DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$model = DSCModel::getInstance( 'ProductRelations', 'TiendaModel' );
		$model->setState( 'filter_product', $product_id );
		$model->setState( 'filter_relation', 'parent' );
		if ($children = $model->getList())
		{
			foreach ($children as $child)
			{
				$product_qty = $quantities[$child->product_id_to];

				// Integrity checks on quantity being added
				if ($product_qty < 0) {
					$product_qty = '1';
				}

				// using a helper file to determine the product's information related to inventory
				$availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $child->product_id_to, $attributes_csv );
				if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
				{
					$this->messagetype  = 'notice';
					$this->message      = JText::_( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $product_qty ) );
					$this->setRedirect( $redirect, $this->message, $this->messagetype );
					return;
				}

				// do the item's charges recur? does the cart already have a subscription in it?  if so, fail with notice
				$product = DSCTable::getInstance('Products', 'TiendaTable');
				$product->load( array( 'product_id'=>$child->product_id_to ) );

				// if product notforsale, fail
				if ($product->product_notforsale)
				{
					$this->messagetype  = 'notice';
					$this->message      = JText::_('COM_TIENDA_PRODUCT_NOT_FOR_SALE');
					$this->setRedirect( $redirect, $this->message, $this->messagetype );
					return;
				}

				if ($product->product_recurs && $cart_recurs)
				{
					$this->messagetype  = 'notice';
					$this->message      = JText::_('COM_TIENDA_CART_ALREADY_RECURS');
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
					$this->message      = JText::_('COM_TIENDA_CANNOT_ADD_ITEM_TO_CART') . " - " . $carthelper->getError();
					$this->setRedirect( $redirect, $this->message, $this->messagetype );
					return;
				}

				// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
				$results = array();
				$dispatcher = JDispatcher::getInstance();
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
			Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
			foreach ($items as $item)
			{
				// add the item to the cart
				$cart_helper = new TiendaHelperCarts();
				$cartitem = $cart_helper->addItem( $item );

				// fire plugin event
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger( 'onAfterAddToCart', array( $cartitem, $values ) );
			}

			$this->messagetype  = 'message';
			$this->message      = JText::_('COM_TIENDA_ITEMS_ADDED_TO_YOUR_CART');
		}

		// After login, session_id is changed by Joomla, so store this for reference
		$session = JFactory::getSession();
		$session->set( 'old_sessionid', $session->getId() );

		// get the 'success' redirect url
		// TODO Enable redirect via base64_encoded urls?
		switch (Tienda::getInstance()->get('addtocartaction', 'redirect'))
		{
			case "redirect":
				$returnUrl = base64_encode( $redirect );
				$itemid = $router->findItemid( array('view'=>'checkout') );
				$redirect = JRoute::_( "index.php?option=com_tienda&view=carts&Itemid=".$itemid, false );
				if (strpos($redirect, '?') === false) {
					$redirect .= "?return=".$returnUrl;
				} else { $redirect .= "&return=".$returnUrl;
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
	 *
	 *
	 */
	function addReview()
	{
		DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		$productreviews = DSCTable::getInstance('productcomments', 'TiendaTable');
		$post = JRequest::get('post');
		$captcha_enable = Tienda::getInstance()->get('use_captcha', '0');
		$privatekey = "6LcAcbwSAAAAANZOTZWYzYWRULBU_S--368ld2Fb";
		$Itemid = $post['Itemid'];
		$recaptcha_challenge_field = $post['recaptcha_challenge_field'];
		$recaptcha_response_field = $post['recaptcha_response_field'];

		$captcha='1';
		if ($captcha_enable)
		{
			$captcha='0';

			Tienda::load( 'TiendaRecaptcha', 'library.recaptcha' );
			$recaptcha = new TiendaRecaptcha();
			if ($_POST["recaptcha_response_field"])
			{
				$resp = $recaptcha->recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $recaptcha_challenge_field, $recaptcha_response_field);
				if ($resp->is_valid)
				{
					$captcha='1';
				}
			}
		}

		$product_id = $post['product_id'];
		$date = JFactory::getDate();
		$productreviews->bind($post);
		$productreviews->created_date = $date->toMysql();
		$redirect = "index.php?option=com_tienda&view=products&task=view&id=".$product_id."filter_category=".$product_id."&Itemid=".$Itemid;
		$redirect = JRoute::_( $redirect );
			
		if ($captcha == '1')
		{
			if (!$productreviews->save())
			{
				$this->messagetype  = 'message';
				$this->message      = JText::_('COM_TIENDA_UNABLE_TO_SAVE_REVIEW')." :: ".$productreviews->getError();
			}
			else
			{
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger( 'onAfterSaveProductComments', array( $productreviews ) );
				$this->messagetype  = 'message';
				$this->message      = JText::_('COM_TIENDA_SUCCESSFULLY_SUBMITTED_REVIEW');
			}
		}
		else
		{
			$this->messagetype  = 'message';
			$this->message      = JText::_('COM_TIENDA_INCORRECT_CAPTCHA');
		}
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
			DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
			$productcomment = DSCTable::getInstance('productcomments', 'TiendaTable');
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
			DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
			$reviewhelpfulness = DSCTable::getInstance('ProductCommentsHelpfulness', 'TiendaTable');
			$reviewhelpfulness->load(array('user_id'=>$user_id));
			if ($report == 1 && !empty($reviewhelpfulness->productcommentshelpfulness_id) && empty($reviewhelpfulness->reported))
			{
				$reviewhelpfulness->reported = 1;
				$reviewhelpfulness->save();

				$productcomment->save();
				JFactory::getApplication()->enqueueMessage( JText::sprintf("COM_TIENDA_THANKS_FOR_REPORTING_THIS_COMMENT"));
				JFactory::getApplication()->redirect($url);
				return;
			}

			$reviewhelpfulness->bind($help);
			if (!empty($reviewhelpfulness->productcommentshelpfulness_id))
			{
				JFactory::getApplication()->enqueueMessage( JText::sprintf("COM_TIENDA_YOU_HAVE_ALREADY_COMMENTED_ON_THIS_REVIEW"));
				JFactory::getApplication()->redirect($url);
				return;
			}
			else
			{
				$reviewhelpfulness->save();
				$productcomment->save();
				JFactory::getApplication()->enqueueMessage( JText::sprintf("COM_TIENDA_THANKS_FOR_YOUR_FEEDBACK_ON_THIS_COMMENT"));
				JFactory::getApplication()->redirect($url);
				return;
			}
		}
		else
		{
			Tienda::load( "TiendaHelperUser", 'helpers.user' );
			$redirect = JRoute::_( TiendaHelperUser::getUserLoginUrl( $url ), false );
			JFactory::getApplication()->redirect( $redirect );
			return;
		}
	}
}

?>
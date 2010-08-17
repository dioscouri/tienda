<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php')) 
{
    // Check the registry to see if our Tienda class has been overridden
    if ( !class_exists('Tienda') ) 
        JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
	
     Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgContentTienda_Content_Product extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'tienda_content_product';
    
	function plgContentTienda_Content_Product(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		$this->loadLanguage('com_tienda');
	}
	
 	/**
     * Checks the extension is installed
     * 
     * @return boolean
     */
    function isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php')) 
        {
            $success = true;
            // Check the registry to see if our Tienda class has been overridden
            if ( !class_exists('Tienda') ) 
                JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
        }
        return $success;
    }
	
	/**
     * Search for the tag and replace it with the product view {tiendaproduct}
     * 
     * @param $article
     * @param $params
     * @param $limitstart
     */
   	function onPrepareContent( &$row, &$params, $page=0 )
   	{
   		if( !$this->isInstalled() )
   			return true;
   		
	   	// simple performance check to determine whether bot should process further
		if ( JString::strpos( $row->text, 'tiendaproduct' ) === false ) {
			return true;
		}
	
		// Get plugin info
		$plugin =& JPluginHelper::getPlugin('content', 'tienda_content_product');
	
	 	// expression to search for
	 	$regex = '/{tiendaproduct\s*.*?}/i';
	
	 	$pluginParams = new JParameter( $plugin->params );
	
		// check whether plugin has been unpublished
		if ( !$pluginParams->get( 'enabled', 1 ) ) {
			$row->text = preg_replace( $regex, '', $row->text );
			return true;
		}
	
	 	// find all instances of plugin and put in $matches
		preg_match_all( $regex, $row->text, $matches );
	
		// Number of plugins
	 	$count = count( $matches[0] );
	
	 	// plugin only processes if there are any instances of the plugin in the text
	 	if ( $count ) {
	 		foreach($matches as $match)
	 			$this->showProducts(&$row, &$matches, $count, $regex );
		}
   	}
   	
   	function showProducts( &$row, &$matches, $count, $regex )
   	{
   		for ( $i=0; $i < $count; $i++ )
		{
	 		$load = str_replace( 'tiendaproduct', '', $matches[0][$i] );
	 		$load = str_replace( '{', '', $load );
	 		$load = str_replace( '}', '', $load );
	 		$load = trim( $load );
	
			$product	= $this->showProduct( $load );
			$row->text 	= str_replace($matches[0][$i], $product, $row->text );
	 	}
	
	  	// removes tags without matching
		$row->text = preg_replace( $regex, '', $row->text );
	}
	
	
	function showProduct( $load )
	{
		$inline_params = explode(" ", $load);
		$params = $this->get('params');
		
		$params = $params->renderToArray();
		
		// Merge plugin parameters with tag parameters, overwriting wherever necessary
		foreach( $inline_params as $p )
		{
			$data = explode("=", $p);
			$params[$data[0]] = $data[1];
		}
		
		// No id set, return
		if( !array_key_exists('id', $params) )
			return;
			
		// Load Product
		Tienda::load('TiendaModelProducts', 'models.products');
		$model = JModel::getInstance('Products', 'TiendaModel');
		$model->setId( (int) $params['id'] );
		$row = $model->getItem();
		
		// Error?
		if( !$row )
			return;
			
	   $categories = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getCategories( $row->product_id );
       if (!empty($categories))
       {
                $filter_category = $categories[0];
       }
        		
		if (empty($row->product_enabled))
		{
			return;
		}
		
		Tienda::load( 'TiendaArticle', 'library.article' );
		$product_description = TiendaArticle::fromString( $row->product_description );

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
		$cat = $cmodel->getTable();
		$cat->load( $filter_category );

		// Check If the inventroy is set then it will go for the inventory product quantities
		if ($row->product_check_inventory)
		{
			$inventoryList = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getProductQuantities( $row->product_id );
        
            if (!TiendaConfig::getInstance()->get('display_out_of_stock') && empty($inventoryList))
            { 
                return;
            }
			
			// if there is no entry of product in the productquantities
			if (count($inventoryList) == 0)
			{
				$inventoryList[''] = '0';
			}
		}

		$files = $this->getFiles( $row->product_id );
		$product_buy = $this->getAddToCart( $row->product_id );
		//$product_relations = $this->getRelationshipsHtml( $row->product_id, 'relates' );
		//$product_children = $this->getRelationshipsHtml( $row->product_id, 'parent' );
		//$product_requirements = $this->getRelationshipsHtml( $row->product_id, 'requires' );
		
        $dispatcher =& JDispatcher::getInstance();
        
        ob_start();
        $dispatcher->trigger( 'onDisplayProductAttributeOptions', array( $row->product_id ) );
        $onDisplayProductAttributeOptions = ob_get_contents();
        ob_end_clean();
        
        ob_start();
        $dispatcher->trigger( 'onBeforeDisplayProduct', array( $row->product_id ) );
        $onBeforeDisplayProduct = ob_get_contents() ;
        ob_end_clean();
        
        ob_start();
        $dispatcher->trigger( 'onAfterDisplayProduct', array( $row->product_id ) );
        $nAfterDisplayProduct = ob_get_contents();
        ob_end_clean();
		
        ob_start();
		include( 'tienda_content_product'.DS.'tmpl'.DS.'view.php' );
		$return = ob_get_contents();
		ob_end_clean();
		
		return $return;
		
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
        
            $vars = new JObject();
        
			$vars->downloadItems = $filtered_items[0];
			$vars->nondownloadItems = $filtered_items[1];
			$vars->product_id = $product_id;

			ob_start();
			$this->_getLayout( 'product_files', $vars );
			$html = ob_get_contents();
			ob_end_clean();
		}

		return $html;
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

        Tienda::load('TiendaModelProducts', 'models.products');
        $model  = JModel::getInstance('Products', 'TiendaModel');
        $model->setId( $product_id );
        $row = $model->getItem();
        
        $vars = new JObject();
        
        if ($row->product_notforsale || TiendaConfig::getInstance()->get('shop_enabled') == '0')
        {
            return $html;
        }
        
       	$vars->item = $row ;
        $vars->product_id = $product_id;
        $vars->values = $values;
        $vars->validation = "index.php?option=com_tienda&view=products&task=validate&format=raw";
        
        $config = TiendaConfig::getInstance();
        $show_tax = $config->get('display_prices_with_tax');
        $vars->show_tax = $show_tax ;
        $vars->tax = 0 ;
        $vars->taxtotal = '';
        $vars->shipping_cost_link = '';
        
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
            $vars->taxtotal = $taxtotal;
            $vars->tax = $tax ;
        }
        
        // TODO What about this??
        $show_shipping = $config->get('display_prices_with_shipping');
        if ($show_shipping)
        {
            $article_link = $config->get('article_shipping', '');
            $shipping_cost_link = JRoute::_('index.php?option=com_content&view=article&id='.$article_link);
            $vars->shipping_cost_link = $shipping_cost_link ;
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
        
        $vars->availableQuantity = $availableQuantity ;
        $vars->invalidQuantity = $invalidQuantity ;
        
        $dispatcher =& JDispatcher::getInstance();
        
        ob_start();
        $dispatcher->trigger( 'onDisplayProductAttributeOptions', array( $row->product_id ) );
        $vars->onDisplayProductAttributeOptions = ob_get_contents() ;
        ob_end_clean();

        ob_start();
        include('tienda_content_product'.DS.'tmpl'.DS.'product_buy.php');
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
	
    
}

}
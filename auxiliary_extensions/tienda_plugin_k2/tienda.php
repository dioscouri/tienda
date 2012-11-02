<?php
/**
 * @version		1.0
 * @package		K2 Tienda plugin
 * @author    	JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
       
JLoader::register('K2Plugin', JPATH_ADMINISTRATOR.'/components/com_k2/lib/k2plugin.php');

class plgK2Tienda extends K2Plugin 
{
	var $pluginName = 'tienda';
	var $pluginNameHumanReadable = 'Tienda';

	function plgK2Tienda(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	function _isInstalled() 
	{
		$success = false;

		jimport('joomla.filesystem.file');
		if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_tienda/defines.php')) 
		{
			$success = true;
			if ( !class_exists('Tienda') )
			{
			    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
			}			
			
            Tienda::load( 'TiendaHelperBase', 'helpers._base' );
            Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
            Tienda::load( 'TiendaModelProducts', 'models.products' );
            Tienda::load( 'TiendaSelect', 'library.select' );
            Tienda::load( 'TiendaUrl', 'library.url' );
		}
		return $success;
	}


	function onRenderAdminForm( & $item, $type, $tab='') {

		$plugins = new DSCParameter($item->plugins);
		$tiendaParams = new K2Parameter($item->plugins, JPATH_SITE.'/plugins/k2/'.$this->_name.'.xml', $this->_name);
		$productID = $tiendaParams->get('productID', 0);
		JPlugin::loadLanguage();

		if ($this->_isInstalled() && $productID && $type=='item' && $tab=='content') {

			$db = JFactory::getDBO();
			$query = "SELECT * FROM #__tienda_products WHERE product_id=".(int)$productID;
			$db->setQuery($query, 0, 1);
			$product = $db->loadObject();

			if(!is_null($product)) {
				$tiendaParams->set('tiendaproductID', $product->product_id);
				$tiendaParams->set('tiendaproductName', $product->product_name);
				$tiendaParams->set('tiendaproductSKU', $product->product_sku);
				$tiendaParams->set('tiendaproductManufacturer', $product->manufacturer_id);
				$tiendaParams->set('tiendaproductWeight', $product->product_weight);
				$tiendaParams->set('tiendaproductLength', $product->product_length);
				$tiendaParams->set('tiendaproductWidth', $product->product_width);
				$tiendaParams->set('tiendaproductHeight', $product->product_height);
				$tiendaParams->set('tiendaproductShipping', $product->product_ships);
				$tiendaParams->set('tiendaproductEnabled', $product->product_enabled);
				$tiendaParams->set('tiendaproductTax', $product->tax_class_id);

			}
			else {
				$tiendaParams->set('tiendaproductID', NULL);
				$tiendaParams->set('tiendaproductName', NULL);
				$tiendaParams->set('tiendaproductSKU', NULL);
				$tiendaParams->set('tiendaproductManufacturer', NULL);
				$tiendaParams->set('tiendaproductWeight', NULL);
				$tiendaParams->set('tiendaproductLength', NULL);
				$tiendaParams->set('tiendaproductWidth', NULL);
				$tiendaParams->set('tiendaproductHeight', NULL);
				$tiendaParams->set('tiendaproductShipping', NULL);
				$tiendaParams->set('tiendaproductEnabled', NULL);
			}

			$plugins->merge($tiendaParams);
			$item->plugins = $plugins->toString();

			$document = JFactory::getDocument();
			$document->addStyleDeclaration('
			.tiendaButton { display:inline-block; padding:0 6px; background:url("../plugins/k2/tienda/images/button.jpg") center center repeat-x; border:1px solid #cccccc; -moz-border-radius:6px; margin:3px;}
			.tiendaButton a, .tiendaButton a:link, tiendaButton a:visited, tiendaButton a:hover, .tiendaButton a.modal, tiendaButton a.modal:visited, tiendaButton a.modal:hover, tiendaButton span { color:#333333; cursor:pointer; line-height:14px; text-decoration:none;}
			');

		}



		$form = new K2Parameter($item->plugins, JPATH_SITE.'/plugins/k2/'.$this->_name.'.xml', $this->_name);
		if ( !empty ($tab)) {
			$path = $type.'-'.$tab;
		}
		else {
			$path = $type;
		}
		$fields = $form->render('plugins', $path);
		if ($fields){
			$plugin = new JObject;
			$plugin->set('name', $this->pluginNameHumanReadable);
			$plugin->set('fields', $fields);
			return $plugin;
		}


	}

	function onBeforeK2Save(&$item, $isNew) {

		//Check if Tienda is installed
		if (!$this->_isInstalled()) {
			return;
		}

		//Get Tienda plugin variables
		$tiendaParams = new K2Parameter($item->plugins, '', $this->_name);

		//Get All plugins variables
		$plugins = new DSCParameter($item->plugins);

		//Handle assignment
		if(JRequest::getBool('tiendaAssign')){
			$plugins->merge($tiendaParams);
			$item->plugins = $plugins->toString();
			return;
		}

		//Handle unassignment
		if(JRequest::getBool('tiendaUnassign')){
			$plugins->set('tiendaproductID', NULL);
			$plugins->set('tiendaproductName', NULL);
			$plugins->set('tiendaproductSKU', NULL);
			$plugins->set('tiendaproductTax', NULL);
			$plugins->set('tiendaproductManufacturer', NULL);
			$plugins->set('tiendaproductWeight', NULL);
			$plugins->set('tiendaproductLength', NULL);
			$plugins->set('tiendaproductWidth', NULL);
			$plugins->set('tiendaproductHeight', NULL);
			$plugins->set('tiendaproductShipping', NULL);
			$plugins->set('tiendaproductEnabled', NULL);
			$item->plugins = $plugins->toString();
			return;
		}

		//Handle unassignment
		if(JRequest::getBool('tiendaRemove')){
			JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
			$product = JTable::getInstance('Products', 'TiendaTable');
			$product->delete($plugins->get('tiendaproductID'));
			$plugins->set('tiendaproductID', NULL);
			$plugins->set('tiendaproductName', NULL);
			$plugins->set('tiendaproductSKU', NULL);
			$plugins->set('tiendaproductTax', NULL);
			$plugins->set('tiendaproductManufacturer', NULL);
			$plugins->set('tiendaproductWeight', NULL);
			$plugins->set('tiendaproductLength', NULL);
			$plugins->set('tiendaproductWidth', NULL);
			$plugins->set('tiendaproductHeight', NULL);
			$plugins->set('tiendaproductShipping', NULL);
			$plugins->set('tiendaproductEnabled', NULL);
			$item->plugins = $plugins->toString();

			return;
		}

		//Handle form
		if($tiendaParams->get('productName', NULL) && $tiendaParams->get('productSKU', NULL)){
			JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
			$product = JTable::getInstance('Products', 'TiendaTable');
			$product->product_id = $tiendaParams->get('productID', NULL);
			$product->product_name = $tiendaParams->get('productName', NULL);
			$product->product_sku = $tiendaParams->get('productSKU', NULL);
			$product->manufacturer_id = $tiendaParams->get('productManufacturer', 0);
			$product->tax_class_id = $tiendaParams->get('productTax');
			$product->product_weight = $tiendaParams->get('productWeight', NULL);
			$product->product_length = $tiendaParams->get('productLength', NULL);
			$product->product_width = $tiendaParams->get('productWidth', NULL);
			$product->product_height = $tiendaParams->get('productHeight', NULL);
			$product->product_ships = $tiendaParams->get('productShipping', 0);
			$product->product_enabled = $tiendaParams->get('productEnabled', 1);
			$product->save();
			$tiendaParams->set('tiendaproductID', $product->product_id);

			$price = $tiendaParams->get('productPrice', NULL);
			if($price){
				$price = JTable::getInstance('ProductPrices', 'TiendaTable');
				$price->product_id = $product->product_id;
				$price->product_price = $tiendaParams->get('productPrice', NULL);
				$price->save();
			}
			$plugins->merge($tiendaParams);
			$item->plugins = $plugins->toString();

		}

	}

	function onK2AfterDisplay(&$item, &$params, $limitstart) {

		$mainframe = JFactory::getApplication();
		if (!$this->_isInstalled() || $mainframe->isAdmin()) {
			return null;
		}

		if($this->params->get('categories')){
			$categories = (array)$this->params->get('categories');
			if(!in_array($item->catid, $categories)){
				return;
			}
		}

		$tiendaParams = new K2Parameter($item->plugins, '', $this->_name);
		$productID = $tiendaParams->get('productID', NULL);

		if(is_null($productID)){
			return null;
		}

		$model  = new TiendaModelProducts();
		$model->setId((int)$productID);
		$product = $model->getItem();

		if(is_null($product))
			return null;

		if(!$product->product_enabled)
			return null;

		JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
		JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
		JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');

		ob_start();
		include JPATH_SITE.'/plugins/k2/tienda/tmpl/quickadd.php';
		$contents = ob_get_clean();
		return $contents;
	}
}
?>
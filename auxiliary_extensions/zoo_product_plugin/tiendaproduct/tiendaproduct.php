<?php
/**
* @package   Tienda
* @file      tiendaproduct.php
* @version   1.0.0 May 2010
* @author    Dioscouri Design
* @copyright Copyright (C) 2010 Dioscouri Design
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

/*
   Class: ElementTiendaProduct
*/
class ElementTiendaProduct extends Element {

	/*
		Function: render
			Renders the element.

	   Parameters:
            $params - render parameter

		Returns:
			String - html
	*/
	public function render($params = array()) 
	{
		$value   = $this->_data->get('value', '1');
		
		if($value == "1")
		{
		
			if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php')) 
			{
			    // Check the registry to see if our Tienda class has been overridden
			    if ( !class_exists('Tienda') ) 
			        JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
				
				// Load Tienda Language File
				$lang = JFactory::getLanguage();
				$lang->load('com_tienda', JPATH_SITE);
				
				$item = $this->getItem();
				$item_id = $item->id;
				
				$query = "SELECT product_id FROM #__tienda_productszooitemsxref WHERE item_id = ".$item_id;
				$db = JFactory::getDBO();
				$db->setQuery($query);
				$product_id = $db->loadResult();
				
	            Tienda::load('TiendaModelProducts', 'models.products');
	            $model  = JModel::getInstance('Products', 'TiendaModel');
	            $model->setId( $product_id );
	            $row = $model->getItem();
	            
	            
	            $vars = new JObject();
	            
	            if (@$row->product_notforsale || TiendaConfig::getInstance()->get('shop_enabled') == '0')
	            {
	                return "";
	            }
	            
	           	$vars->product = $row ;
	            $vars->product_id = $product_id;
	            $vars->values = array();
	            $vars->validation = "index.php?option=com_tienda&view=products&task=validate&format=raw";
	            $vars->params = $params;
	            
	            $config = TiendaConfig::getInstance();
	            $show_tax = $config->get('display_prices_with_tax');
	            $vars->show_tax = $show_tax ;
	            $vars->tax = 0 ;
	            $vars->taxtotal = '';
	            $vars->shipping_cost_link = '';
	            Tienda::load('TiendaHelperProduct', 'helpers.product');
	            
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
				
				// render layout
				if ($layout = $this->getLayout()) {
					return self::renderLayout($layout, array('item' => $item, 'vars' => $vars));
				}
			}
		}
			
		return null;
		
	}

		/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit()
	{
		$html = array();
		$html[] = JHTML::_('select.booleanlist', 'elements[' . $this->identifier . '][value]', '', $this->_data->get('value', 1));
		
		// init vars
		$default_price = $this->_config->get('default_price');		
		
		// set default, if item is new
		if ($default_price != '' && $this->_item != null && $this->_data->get('default_price') == '' ) {
			$this->_data->set('default_price', $default_price);
		}

		$html[] = JText::_('Price').': '.JHTML::_('control.text', 'elements[' . $this->identifier . '][default_price]', $this->_data->get('default_price'), 'size="60" maxlength="255"');		
		
		if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php') && $this->_item != null) 
		{
		    // Check the registry to see if our Tienda class has been overridden
		    if ( !class_exists('Tienda') ) 
		        JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
			
			$item_id = $this->_item->id;
		        
			$query = "SELECT product_id FROM #__tienda_productszooitemsxref WHERE item_id = ".$item_id;
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$product_id = $db->loadResult();
			
			if( $product_id )
			{
				Tienda::load('TiendaUrl', 'library.url');
				$html[] = TiendaUrl::popup('index.php?option=com_tienda&view=products&task=edit&id='.$product_id, JText::_('Edit Product') );
			}
		
		}
		
		return implode("<br />", $html);
		
	}
	
	/*
		Function: getLayout
			Get element layout path and use override if exists.
		
		Returns:
			String - Layout path
	*/
	public function getLayout($layout = null) {

		// init vars
		$type = $this->getElementType();
		$path = dirname(__FILE__)."/tmpl";

		// set default
		if ($layout == null) {
			$layout = "{$type}.php";
		}

		// find layout
		if (JPath::find($path, $layout)) {
			return $path."/".$layout;
		}

		return null;
	}
	
	public function hasValue($params)
	{	
		$value   = $this->_data->get('value', '1');
		if($value == "1")
			return true;
		else
			return false;
	}

}
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

    class plgSystemTienda_Zoo extends TiendaPluginBase
    {
    	/**
    	 * @var $_element  string  Should always correspond with the plugin's filename, 
    	 *                         forcing it to be unique 
    	 */
        var $_element    = 'tienda_zoo';
        
    	function plgSystemTienda_Zoo(& $subject, $config) 
    	{
    		parent::__construct($subject, $config);
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
            $this->checkInstallation();
            return $success;
        }
        
        private function checkInstallation()
        {
	        // if this has already been done, don't repeat
	        if (TiendaConfig::getInstance()->get('checkTableZoo', '0'))
	        {
	            return true;
	        }
	        
	        $sql = "CREATE TABLE IF NOT EXISTS `#__tienda_productszooitemsxref` (
					  `item_id` int(11) NOT NULL,
					  `product_id` int(11) NOT NULL,
					  KEY `item_id` (`item_id`,`product_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	        $db = JFactory::getDBO();
	        $db->setQuery($sql);
	        $result = $db->query();
	        
	        if ($result)
	        {
	            // Update config to say this has been done already
	            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
	            $config = JTable::getInstance( 'Config', 'TiendaTable' );
	            $config->load( array( 'config_name'=>'checkTableZoo') );
	            $config->config_name = 'checkTableZoo';
	            $config->value = '1';
	            $config->save();
	            return true;
	        }
	
	        return false;        
        }
    	
    	/**
         * Creates the tienda product based on the zoo item
         * 
         * @param $item
         */
       	function onAfterSaveZooItem( $item )
       	{
       		if( !$this->isInstalled() )
       			return true;

       		$params = $this->params;	
       			
       		if( $params->get('on_after_save', '1') != "1" )
       		{
       			return true;
       		}
       		
       		// Check if the "add to cart" is set to "yes"
       		$elements = $item->getElements();
	       	$check = true;
	       	foreach($elements as $element)
	       	{
	       		if(strtolower($element->getElementType()) == 'tiendaproduct')
	       		{
	       			$check = false;
	       			$data = $element->getElementData();
	       			$active = $data->get('value');
	       		}
	       		
	       		if(!$check)
	       			continue;
	       	}
	       	
	       	// If set to "no", skip this passage
	       	if($active != "1")
	       	{
	       		return true;
	       	}
       		
	       	$this->saveTiendaProduct( $item );
    	   		
    	   	return true;    	   	
    	   	
       	}
       	
       	/*
       	 * If set in the config, save the tienda product based on the zoo item 
       	 */
       	
       	function onBeforeDisplayZooItem( $item )
       	{
       		if( !$this->isInstalled() )
       			return true;
       			
       		$params = $this->params;
       		
       		if( $params->get('on_item_view', '0') == "1" )
       		{
       			$this->saveTiendaProduct( $item );
       		}
       		
       		return true;
       	}
       	
       	function onAfterDeleteProducts( $table, $id )
       	{
       		$this->includeCustomTables('tienda_plugin_zoo', 'system');

    	   	$table = JTable::getInstance('ProductsZooItemsXref', 'TiendaTable');
    	   	
    	   	$table->load( array('product_id' => $id) );
    	   	$table->delete();
    	   	
    	   	return true;
       	}
       	
       	/*
       	 * Do the real saving
       	 */
       	
       	function saveTiendaProduct( $item )
       	{
       		$this->includeCustomTables('tienda_plugin_zoo', 'system');

    	   	$table = JTable::getInstance('ProductsZooItemsXref', 'TiendaTable');
    	   	
    	   	// Skip if already present
    	   	if( $table->load( $item->id ) )
    	   	{
    	   		$new = false;
    	   	}
    	   	else
    	   	{
    	   		$new = true;
    	   	}
    	   
       		Tienda::load('TiendaTableProducts', 'tables.products');
       		$product = JTable::getInstance('Products', 'TiendaTable');
       		
       		if( !$new )
				$product->load($table->product_id );       		
       		
       		$product->product_name = $item->name;
       		$product->product_alias = $item->alias;
       		
       		if( $product->save() )
       		{
       			
       			$elements = $item->getElements();
       			$check = true;
       			foreach($elements as $element)
       			{
       				if(strtolower($element->getElementType()) == 'tiendaproduct')
       				{
       					$check = false;
       					
	       				// init vars
						$default_price = $element->getConfig()->get('default_price');
						$data = $element->getElementData();	
						
						$category = YTable::getInstance('category');
						$category = $category->getByItemId($item->id, true);
						
						foreach($category as $id => $c)
						{
							$category = $c;
						}
						
						$params = $category->getParams();
						$default_category_price = $params->get('template.default_price', '');
						
						// set default, if item is new
						if (($default_price != '' || $default_category_price != '') && $data->get('default_price') == '') {
							if($default_category_price != '')
								$data->set('default_price', $default_category_price);
							else
								$data->set('default_price', $default_price);
						}
						
       					$active = $data->get('default_price');
						
       					
       				}
       				
       				if(!$check)
       					continue;
       			}
       			
       			Tienda::load('TiendaTableProductprices', 'tables.productprices');
       			
       			Tienda::load( "TiendaHelperProduct", 'helpers.product' );
				$prices = TiendaHelperProduct::getPrices( @$product->product_id );
       			
       			// set price if new or no prices set
       			if(empty($prices) || $new)
       			{
					$price = JTable::getInstance( 'Productprices', 'TiendaTable' );
					$price->product_id = $product->product_id;
					$price->product_price = $active;
					$price->save();
       			} 
       			else
       			{
       				if( !$new )
       				{
       					$price = JTable::getInstance( 'Productprices', 'TiendaTable' );
       					$price->load(array('product_id' => $product->product_id));
						$price->product_price = $active;
						$price->save();
       				}
       			}
				
       			if( $new )
       			{
	       			$table->item_id = $item->id;
	       			$table->product_id = $product->product_id;
	       			$table->save();
       			}
       		}
       	}        
    }
}
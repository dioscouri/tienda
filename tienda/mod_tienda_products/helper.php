<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.model' );

class modTiendaProductsHelper extends JObject
{
    /**
     * Sets the modules params as a property of the object
     * @param unknown_type $params
     * @return unknown_type
     */
    function __construct( $params )
    {
        $this->params = $params;
    }
    
    /**
     * Sample use of the products model for getting products with certain properties
     * See admin/models/products.php for all the filters currently built into the model 
     * 
     * @param $parameters
     * @return unknown_type
     */
    function getProducts()
    {
        // Check the registry to see if our Tienda class has been overridden
        if ( !class_exists('Tienda') ) 
            JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
        
        // load the config class
        Tienda::load( 'TiendaConfig', 'defines' );
                
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
    	JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );

        // get the model
    	$model = JModel::getInstance( 'Products', 'TiendaModel' );
    	
    	// setting the model's state tells it what items to return
    	$model->setState('filter_published', '1');
    	$date =& JFactory::getDate();
    	$model->setState('filter_published_date', $date->toMysql() );
    	$model->setState('filter_enabled', '1');
		
		// Set category state
		if ($this->params->get('category', '1') != '1')
			$model->setState('filter_category', $this->params->get('category', '1'));

		// Set manufacturer state
		if ($this->params->get('manufacturer', '') != '')
				$model->setState('filter_manufacturer', $this->params->get('manufacturer', ''));

		// Set id set state
		if ($this->params->get('id_set', '') != '')
		{
		    $params_id_set = $this->params->get('id_set');
		    $id_array = explode(',', $params_id_set);
		    $id_set = "'".implode("', '", $id_array)."'";
		    $model->setState('filter_id_set', $id_set);
		}
				
    	// set the states based on the parameters
    	$model->setState('limit', $this->params->get( 'max_number', '10' ));
    	if($this->params->get( 'price_from', '-1' ) != '-1')
    		$model->setState('filter_price_from', $this->params->get( 'price_from', '-1' ));
    	if($this->params->get( 'price_to', '-1' ) != '-1')
    		$model->setState('filter_price_to', $this->params->get( 'price_to', '-1' ));
    	
       if ($this->params->get('random', '0') == '1'){
    		$model->setState('order', 'RAND()');
    	}
    		
        // using the set filters, get a list of products 
    	if ($products = $model->getList())
    	{
    	    foreach ($products as $product)
    	    {
    	        $product->filter_category = '';
    	        $categories = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getCategories( $product->product_id );
                if (!empty($categories))
                {
                    $product->link .= "&filter_category=".$categories[0];
                    $product->filter_category = $categories[0];
                }
                $itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $product->filter_category, true );
                $product->itemid = $itemid;
    	    }
    	}
    	
    	return $products;
    }
}
?>

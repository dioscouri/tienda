<?php
/**
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.model' );

class modTiendaMyOrderItemsHelper extends JObject
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
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );

    	$user = JFactory::getUser();
    	
        // get the model
    	$model = JModel::getInstance( 'OrderItems', 'TiendaModel' );
        $model->setState( 'limit', $this->params->get( 'max_number', '5') );
        $model->setState( 'filter_userid', $user->id );
        $model->setState( 'order', 'created_date' );
        $model->setState( 'direction', 'DESC' );
        
        $query = $model->getQuery();
        $query->select( "MAX(o.order_id) AS order_id" );
        $query->select( "MAX(o.created_date) AS created_date" );
        
        if ($this->params->get('display_downloads_only'))
        {
            $query->join('LEFT', '#__tienda_productfiles AS files ON tbl.product_id = files.product_id');
            $query->where( "files.productfile_id IS NOT NULL" );
        }
        
        $query->group('tbl.product_id');
        $model->setQuery( $query );
        
        $router = Tienda::getClass('TiendaHelperRoute', 'helpers.route');
        $product = Tienda::getClass('TiendaHelperProduct', 'helpers.product');
    	if ($items = $model->getList())
    	{
    	    foreach ($items as $item)
    	    {
    	        $category = null;
                if ($categories = $product->getCategories( $item->product_id ))
                {
                    $category = $categories[0];
                } 
    	        $item->link = $router->product( $item->product_id, $category ); 
    	    }
    	}
    	
    	return $items;
    }
}
?>

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

class modTiendaPopularProductsHelper extends JObject
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
    	$model = JModel::getInstance( 'orderitems', 'TiendaModel' );
        $model->setState( 'limit', $this->params->get( 'max_number', '5') );
    	
        $query = $model->getQuery();
        
        // group results by product ID
        $query->group('tbl.product_id');
        
        // select the total number of sales for each product
        $field = array();
        $field[] = " SUM(tbl.orderitem_quantity) AS total_sales ";
        $query->select( $field );
        
        // order results by the total sales
        $query->order('total_sales DESC');

        $model->setQuery( $query );
    	
        // using the set filters, get a list of products
    	$products = $model->getList();
    	
    	return $products;
    }
}
?>

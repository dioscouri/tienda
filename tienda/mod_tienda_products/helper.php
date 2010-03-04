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
    	JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );

        // get the model
    	$model = JModel::getInstance( 'products', 'TiendaModel' );
    	
    	// setting the model's state tells it what items to return
    	$model->setState('filter_published', '1');
    	$model->setState('filter_enabled', '1');
    	
    	// set the states based on the parameters
    	$model->setState('limit', $this->params->get( 'max_number', '10' ));
    	if($this->params->get( 'price_from', '-1' ) != '-1')
    		$model->setState('filter_price_from', $this->params->get( 'price_from', '-1' ));
    	if($this->params->get( 'price_to', '-1' ) != '-1')
    		$model->setState('filter_price_to', $this->params->get( 'price_to', '-1' ));
    	
        // using the set filters, get a list of products
    	$products = $model->getList();
    	
    	return $products;
    }
}
?>

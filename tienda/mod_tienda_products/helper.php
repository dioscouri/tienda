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

class modTiendaProductsHelper
{
    function getProducts($parameters)
    {
    	JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );

        // get the model
    	$model = JModel::getInstance( 'products', 'TiendaModel' );
    	
    	$model->setState('filter_published', '1');
    	$model->setState('filter_enabled', '1');
    	
    	// set the states based on the parameters
    	$model->setState('limit', $parameters['max_number']);
    	if($parameters['price_from'] != '-1')
    		$model->setState('filter_price_from', $parameters['price_from']);
    	if($parameters['price_to'] != '-1')
    		$model->setState('filter_price_to', $parameters['price_to']);
    	
    	$products = $model->getList();
    	
    	return $products;
    }
}
?>

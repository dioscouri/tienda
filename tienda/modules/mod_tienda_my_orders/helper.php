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

class modTiendaMyOrdersHelper extends JObject
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
    function getOrders()
    {
        // Check the registry to see if our Tienda class has been overridden
        if ( !class_exists('Tienda') ) 
            JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
        
        // load the config class
        Tienda::load( 'Tienda', 'defines' );
                
        DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
    	DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );

        // get the model
    	$model = DSCModel::getInstance( 'orders', 'TiendaModel' );
        $model->setState( 'limit', $this->params->get( 'max_number', '5') );  
    	$user = JFactory::getUser();
        
    	$model->setState( 'filter_userid', $user->id); 
    	$orders = $model->getList();
    	return $orders;
    }
}
?>

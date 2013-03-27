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

class modTiendaManufacturersHelper extends JObject
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
     * 
     * @return unknown_type
     */
    function getItems()
    {
        // Check the registry to see if our Tienda class has been overridden
        if ( !class_exists('Tienda') ) 
            JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
        
        // load the config class
        Tienda::load( 'Tienda', 'defines' );
                
        DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
    	DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );

        // get the model
    	$model = DSCModel::getInstance( 'Manufacturers', 'TiendaModel' );

    	// TODO Make this depend on the current filter_category? 
    	
    	// setting the model's state tells it what items to return
    	$model->setState('filter_enabled', '1');
    	$model->setState('order', 'tbl.manufacturer_name');
    	
    	// set the states based on the parameters
    	
        // using the set filters, get a list
    	$items = $model->getList();
    	
    	if (!empty($items))
    	{
    	    foreach ($items as $item)
    	    {
    	    // this gives error
    	       $item->itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->manufacturer($item->manufacturer_id, false);
    	        if (empty($item->itemid))
    	        {
                    $item->itemid = $this->params->get('itemid');    
    	        }
    	    }
    	}
    	
    	return $items;
    }
}


?>

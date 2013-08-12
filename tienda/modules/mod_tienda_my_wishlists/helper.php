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

class modTiendaMyWishlistsHelper extends JObject
{
    /**
     * Sets the modules params as a property of the object
     * @param unknown_type $params
     * @return unknown_type
     */
    function __construct( $params )
    {
        $this->params = $params;
        
        if ( !class_exists('Tienda') ) {
            JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
        }
        
        // load the config class
        Tienda::load( 'Tienda', 'defines' );
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
        
        $this->defines = Tienda::getInstance();
        
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $this->router = new TiendaHelperRoute();
        
        $this->user = JFactory::getUser();
    }
    
    /**
     * 
     * @return unknown
     */
    function getItems()
    {
    	$this->model = JModel::getInstance( 'Wishlists', 'TiendaModel' );
    	
    	$user = JFactory::getUser();
    	if (empty($user->id)) {
    	    return array();
    	}
    	
    	$this->model->setState( 'filter_user', $user->id);
    	    	
    	if ($this->params->get( 'max_number' ) > '0') {
            $this->model->setState( 'limit', $this->params->get( 'max_number' ) );
    	}
    	 
    	$items = $this->model->getList();
    	
    	return $items;
    }
}
?>

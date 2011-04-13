<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
    
require_once( dirname(__FILE__).DS.'helper.php' );

// include lang files
$lang = JFactory::getLanguage();
$lang->load( 'com_tienda', JPATH_BASE );
$lang->load( 'com_tienda', JPATH_ADMINISTRATOR );
Tienda::load( 'TiendaHelperBase', 'helpers._base' );	

$helper = new modTiendaLayeredNavigationFiltersHelper( $params ); 
$categories = $helper->getCategories();
$manufacturers = $helper->getManufacturers();
$priceRanges = $helper->getPriceRanges();
$attributes = $helper->getAttributes();
$ratings = $helper->getRatings();
$found = $helper->getCondition();
$trackcatcount = $helper->getTrackCatCount();
$filters = $helper->getFilters();
$attributeOptions = $helper->getAttributeOptions();
require( JModuleHelper::getLayoutPath( 'mod_tienda_layered_navigation' ) );
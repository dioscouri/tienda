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
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
    
require_once( dirname(__FILE__).'/helper.php' );

// include lang files
$lang = JFactory::getLanguage();
$lang->load( 'com_tienda', JPATH_BASE );
$lang->load( 'com_tienda', JPATH_ADMINISTRATOR );

// grab the Price range
$helper = new modTiendaPriceFiltersHelper( $params ); 
$priceRanges = $helper->getPriceRange();

$show_remove = false;

$app = JFactory::getApplication();
$model = JModel::getInstance( 'Products', 'TiendaModel' );
$ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');
$filter_price_from = $app->getUserStateFromRequest($ns.'price_from', 'filter_price_from', '0', 'int');
$filter_price_to = $app->getUserStateFromRequest($ns.'price_to', 'filter_price_to', '', '');
$filter_category = $app->getUserStateFromRequest($ns.'.category', 'filter_category', '', 'int');
if (!empty($filter_price_from) || !empty($filter_price_to))
{
    $show_remove = true;
}
$remove_pricefilter_url = "index.php?option=com_tienda&view=products&filter_category=$filter_category&filter_price_from=&filter_price_to=";

require( JModuleHelper::getLayoutPath( 'mod_tienda_pricefilters' ) );
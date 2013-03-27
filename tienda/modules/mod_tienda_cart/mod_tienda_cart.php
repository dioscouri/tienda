<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
// if DSC is not loaded all is lost anyway
if (!defined('_DSC')) { return; }


// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
    
require_once( dirname(__FILE__).'/helper.php' );

// include lang files

$lang = JFactory::getLanguage();
$lang->load( 'com_tienda', JPATH_BASE );
$lang->load( 'com_tienda', JPATH_ADMINISTRATOR );

$mainframe = JFactory::getApplication();
$document = JFactory::getDocument();

// params
$display_null = $params->get( 'display_null', '1' );
$null_text = $params->get( 'null_text', 'No Items in Your Cart' );
$isAjax = $mainframe->getUserState( 'mod_usercart.isAjax' );
$ajax = ($isAjax == '1');

// Grab the cart
Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
$items = TiendaHelperCarts::getProductsInfo();
$num = count($items);

// Convert the cart to a "fake" order, to show totals and others things
DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
$orderTable = DSCTable::getInstance('Orders', 'TiendaTable');
foreach($items as $item)
{
    $orderTable->addItem($item);
}
$items = $orderTable->getItems();

Tienda::load( 'Tienda', 'defines' );
$config = Tienda::getInstance();
$show_tax = $config->get('display_prices_with_tax');
if ($show_tax)
{
    Tienda::load('TiendaHelperUser', 'helpers.user');
    $geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
    if (empty($geozones))
    {
        // use the default
        $table = DSCTable::getInstance('Geozones', 'TiendaTable');
        $table->load(array('geozone_id'=>Tienda::getInstance()->get('default_tax_geozone')));
        $geozones = array( $table );
    }
    $orderTable->setGeozones( $geozones );
}

// order calculation can happen after all items are added to order object
$orderTable->calculateTotals();

// format the subtotal
//$order_subtotal = TiendaHelperBase::currency($orderTable->order_total);

if (!empty($items) || (empty($items) && $params->get('display_null')) )
{
	require JModuleHelper::getLayoutPath('mod_tienda_cart', $params->get('layout', 'default'));
}
    else 
{
    // don't display anything
}


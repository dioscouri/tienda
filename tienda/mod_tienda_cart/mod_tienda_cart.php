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

require_once( dirname(__FILE__).DS.'helper.php' );

// include lang files
$element = strtolower( 'com_Tienda' );
$lang =& JFactory::getLanguage();
$lang->load( $element, JPATH_BASE );
$lang->load( $element, JPATH_ADMINISTRATOR );

// Grab the cart
JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );
$items = TiendaHelperCarts::getProductsInfo();
$num = count($items);

// Convert the cart to a "fake" order, to show totals and others things
JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
$orderTable = &JTable::getInstance('Orders', 'TiendaTable');
foreach($items as $item){
	$orderTable->addItem($item);
}
$orderTable->calculateTotals();
$order_subtotal = TiendaHelperBase::currency($orderTable->order_total);

$document = &JFactory::getDocument();

$display_null = $params->get( 'display_null', '1' );
$null_text = $params->get( 'null_text', 'No Items in Your Cart' );

$mainframe =& JFactory::getApplication();
$ajax = $mainframe->getUserState( 'usercart.isAjax' );

require( JModuleHelper::getLayoutPath( 'mod_tienda_cart' ) );

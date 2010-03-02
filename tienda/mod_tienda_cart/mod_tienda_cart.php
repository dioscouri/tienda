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

// Grab the session cart
$cart = modTiendaCartHelper::getCart();
$num = count($cart);

$document = &JFactory::getDocument();

$display_null = $params->get( 'display_null', '1' );
$null_text = $params->get( 'null_text', 'No Items in Your Cart' );

$mainframe =& JFactory::getApplication();
$ajax = $mainframe->getUserState( 'usercart.isAjax' );

require( JModuleHelper::getLayoutPath( 'mod_tienda_cart' ) );

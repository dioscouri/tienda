<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// if DSC is not loaded all is lost anyway
if (!defined('_DSC')) { return; }

// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') ) {
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
}
    
require_once( dirname(__FILE__).'/helper.php' );

// include lang files
$element = 'com_tienda';
$lang = JFactory::getLanguage();
$lang->load( $element, JPATH_BASE );

$display_null = $params->get( 'display_null', '1' );
$null_text = $params->get( 'null_text', JText::_('COM_TIENDA_NO_WISHLISTS_FOUND') );

$helper = new modTiendaMyWishlistsHelper( $params ); 
$items = $helper->getItems();
$num = count($items);

$mainframe = JFactory::getApplication();
$document = JFactory::getDocument();

if (empty($num) && !$display_null) {
    return;
}

require JModuleHelper::getLayoutPath('mod_tienda_my_wishlists', $params->get('layout', 'default'));

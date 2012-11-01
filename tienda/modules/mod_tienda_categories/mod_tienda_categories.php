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

// grab the items
$helper = new modTiendaCategoriesHelper( $params ); 
$items = $helper->getItems();
$depthlevel = $params->get( 'depthlevel', '2' );



require JModuleHelper::getLayoutPath('mod_tienda_categories', $params->get('layout', 'default'));

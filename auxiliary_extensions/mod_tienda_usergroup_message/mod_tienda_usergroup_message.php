<?php
/**
 * @package Featured Items
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

if ( !class_exists('Tienda') ) { 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR . "/components/com_tienda/defines.php" );
}

require_once ( dirname( __FILE__ ) . DS . 'helper.php' );

$helper = new modTiendaUsergroup_MessageHelper( $params );
if ($helper->displayMessageForUser()) 
{
    require (JModuleHelper::getLayoutPath('mod_tienda_usergroup_message', $params->get('layout', 'default')));
}
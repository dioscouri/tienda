<?php
/**
 * @package Tienda
 * @author  Dioscouri
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2010 Dioscouri. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');


    // Check the registry to see if our Tienda class has been overridden
    if ( !class_exists('Tienda') ) 
    {
        JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
    }
	if ( class_exists('Tienda') ) 
    {
    Tienda::load( 'TiendaMenu', 'library.menu' );
    
    $hide = JRequest::getInt('hidemainmenu');
    $menu = TiendaMenu::getInstance( 'submenu' );
    
    $app = JFactory::getApplication();
    $document = JFactory::getDocument();
        
    require( JModuleHelper::getLayoutPath( 'mod_tienda_admin_submenu' ) );
	 }

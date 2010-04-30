<?php
/**
 * @version	0.1
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

// load the config class
Tienda::load( 'TiendaConfig', 'defines' );

// before executing any tasks, check the integrity of the installation
Tienda::getClass( 'TiendaHelperDiagnostics', 'helpers.diagnostics' )->checkInstallation();

// Require the base controller
Tienda::load( 'TiendaController', 'controller' );

// Require specific controller if requested
$controller = JRequest::getWord('controller', JRequest::getVar( 'view' ) );
if (!Tienda::load( 'TiendaController'.$controller, "controllers.$controller" ))
    $controller = '';

// load the plugins
JPluginHelper::importPlugin( 'tienda' );

// Create the controller
$classname = 'TiendaController'.$controller;
$controller = Tienda::getClass( $classname );
    
// Perform the requested task
$controller->execute( JRequest::getVar( 'task' ) );

// Redirect if set by the controller
$controller->redirect();
?>
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

// set the options array
$options = array( 'site'=>'site', 'type'=>'components', 'ext'=>'com_tienda' );

// Require the base controller
Tienda::load( 'TiendaController', 'controller', $options );

// Require specific controller if requested
$controller = JRequest::getWord('controller', JRequest::getVar( 'view' ) );
if (!Tienda::load( 'TiendaController'.$controller, "controllers.$controller", $options ))
    $controller = '';

if (empty($controller))
{
    // redirect to default
    $redirect = "index.php?option=com_tienda&view=products";
    $redirect = JRoute::_( $redirect, false );
    JFactory::getApplication()->redirect( $redirect );
}

$doc = JFactory::getDocument();
$uri = JURI::getInstance();
$js = "var com_tienda = {};\n";
$js.= "com_tienda.jbase = '".$uri->root()."';\n";
$doc->addScriptDeclaration($js);

// load the plugins
JPluginHelper::importPlugin( 'tienda' );

// Check Json Class Existance
if ( !function_exists('json_decode') ) 
{
	// This should load not only the class, but also json_encode / json_decode
	Tienda::load('Services_JSON', 'library.json');
}

// Create the controller
$classname = 'TiendaController'.$controller;
$controller = Tienda::getClass( $classname );

// ensure a valid task exists
$task = JRequest::getVar('task');
if (empty($task))
{
    $task = 'display';	
}
JRequest::setVar( 'task', $task );

// Perform the requested task
$controller->execute( $task );

// Redirect if set by the controller
$controller->redirect();

?>
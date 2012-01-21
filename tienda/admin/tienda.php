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

// Load Custom Language File if needed (com_tienda_custom)
if(TiendaConfig::getInstance()->get('custom_language_file', '0'))
{
	$lang =& JFactory::getLanguage();
	$extension = 'com_tienda_custom';
	$base_dir = JPATH_ADMINISTRATOR;
	$lang->load($extension, $base_dir, null, true);
}

// before executing any tasks, check the integrity of the installation
Tienda::getClass( 'TiendaHelperDiagnostics', 'helpers.diagnostics' )->checkInstallation();

// Require the base controller
Tienda::load( 'TiendaController', 'controller' );

// Check if protocol is specified
$protocol = JRequest::getWord('protocol', '');

// Require specific controller if requested
$controller = JRequest::getWord('controller', JRequest::getVar( 'view' ) );

// if protocol is specified, try to load the specific controller
if(strlen($protocol))
{
	// file syntax: controller_json.php
	if (Tienda::load( 'TiendaController'.$controller.$protocol, "controllers.".$controller."_".$protocol ))
    	$controller .=  $protocol;
}
else
{
	if (!Tienda::load( 'TiendaController'.$controller, "controllers.$controller" ))
    	$controller = '';
}

$doc = JFactory::getDocument();
$js = "var com_tienda = {};\n";
$js.= "com_tienda.jbase = '".Tienda::getUriRoot()."';\n";
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
    
// Perform the requested task
$controller->execute( JRequest::getVar( 'task' ) );

// Redirect if set by the controller
$controller->redirect();
?>
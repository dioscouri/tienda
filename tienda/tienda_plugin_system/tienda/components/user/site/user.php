<?php
/**
 * @version 0.1
 * @package Users
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// this redefinition is required for this to work.  sorry about the notice; that's why constants are terrible
// maybe get error_reporting level as a variable, set error_reporting to none, redefine the constant, then set error reporting back to what it was? 
define( 'JPATH_COMPONENT', dirname(__FILE__) );

// Require the defines
//require_once( dirname(__FILE__).DS.'defines.php' );

// Require the base controller
require_once( dirname(__FILE__).DS.'controller.php' );

// Require specific controller if requested
if ($controller = JRequest::getWord('controller', JRequest::getVar( 'view' ) )) 
{
    $path = dirname(__FILE__).DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Create the controller
$controller_name = $controller;
$classname    = 'UserController'.$controller;
$controller   = new $classname();
if (empty($controller_name))
{
    $controller_name = $controller->get('_defaultView');
}

// include the model override
    $path = dirname(__FILE__).DS.'models'.DS.$controller_name.'.php';
    if (file_exists($path)) {
        require_once $path;
    } 
        else
    {
        // TODO Include the core/default?        
    }

// include the view override
    // TODO make this support view.pdf.php etc
    $path = dirname(__FILE__).DS.'views'.DS.$controller_name.DS.'view.html.php';
    if (file_exists($path)) {
        require_once $path;
    } 
        else
    {
        // TODO Include the core/default?
    }

// include lang files
$element = strtolower( 'com_user' );
$lang =& JFactory::getLanguage();
$lang->load( $element, JPATH_BASE );
$lang->load( $element, JPATH_ADMINISTRATOR );
    
// before executing any tasks, check the integrity of the installation
// TODO Here you could call some method for checking that DB tables exist, etc 
//$diagnostic = new UserHelperDiagnostics();
//$diagnostic->checkInstallation();

//// load the plugins
JPluginHelper::importPlugin( 'user' );

// Perform the requested task
$controller->execute( JRequest::getVar( 'task' ) );

// Redirect if set by the controller
$controller->redirect();

?>
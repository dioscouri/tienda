<?php
/**
 * @package	Tienda
 * @author 	Dioscouri
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2010 Dioscouri. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

$text = $params->get( 'text', 'Tienda Dashboard' );

$mainframe =& JFactory::getApplication();
$document =& JFactory::getDocument();

$option = JRequest::getCmd( 'option' );
$class_suffix = $params->get('moduleclass_sfx', '');

// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') )
{
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
}
Tienda::load( 'TiendaSelect', 'library.select' );   

require( JModuleHelper::getLayoutPath( 'mod_tienda_search_admin' ) );
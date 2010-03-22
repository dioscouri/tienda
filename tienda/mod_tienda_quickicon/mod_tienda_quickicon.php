<?php
/**
 * @version	1.5
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

if(!class_exists("Tienda"))
	JLoader::import( 'com_tienda.defines', JPATH_ADMINISTRATOR.DS.'components' );

$img = Tienda::getURL()."images/tienda.png";

require( JModuleHelper::getLayoutPath( 'mod_tienda_quickicon' ) );
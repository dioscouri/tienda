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

// if DSC is not loaded all is lost anyway
if (!defined('_DSC')) { return; }

$text = $params->get( 'text', 'Tienda Dashboard' );

$doc = JFactory::getDocument();

// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
    
$img = Tienda::getURL()."images/tienda.png";

require JModuleHelper::getLayoutPath('mod_tienda_quickicon', $params->get('layout', 'default'));


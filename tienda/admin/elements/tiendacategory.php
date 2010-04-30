<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

class JElementTiendaCategory extends JElement
{
	var	$_name = 'TiendaCategory';

	function fetchElement($name, $value, &$node, $control_name)
	{
	    $list = Tienda::getClass( 'TiendaSelect', 'library.select' )->category($value, $control_name.'['.$name.']', '', $control_name.$name, false, false, 'Select Category', '', true );
		return $list;
	}
}
?>
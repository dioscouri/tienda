<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2010 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

class JElementTiendaOrderState extends JElement
{
	var	$_name = 'TiendaOrderState';

	function fetchElement($name, $value, &$node, $control_name)
	{
	    $select = Tienda::load( 'TiendaSelect', 'library.select' );
	    $list = $select->orderstate($value, $control_name.'['.$name.']', '', $control_name.$name, false, false, 'Select Order State', '', true );
		return $list;
	}
}
?>
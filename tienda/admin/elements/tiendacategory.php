<?php
/**
 * @version	1.5
 * @package	Ambrasubs
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
//	    echo "hello";
//	    echo "$name, $value, $control_name";
	    $list = Tienda::get( 'TiendaSelect', 'library.select' )->category($value, $control_name.'['.$name.']', '', $control_name.$name, true, false, 'Select Category', '', true );
//	    echo Tienda::dump( $list );	
		return $list;
        // return self::genericlist($list, $name, $attribs, 'category_id', 'category_name', $selected, $idtag );
//		return JHTML::_('select.genericlist',  array(), ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, $control_name.$name );
	}
}
?>
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



if(!class_exists('JFakeElementBase')) {
	if(version_compare(JVERSION,'1.6.0','ge')) {
		class JFakeElementBase extends JFormField {
			// This line is required to keep Joomla! 1.6/1.7 from complaining
			public function getInput() {
			}
		}
	} else {
		class JFakeElementBase extends JElement {}
	}
}

class JFakeElementTiendaManufacturer extends JFakeElementBase
{

		
	var	$_name = 'TiendaManufacturer';
	
	public function getInput($name, $value, $node, $control_name) 
	{
		$this->fetchElement($name, $value, $node, $control_name);
	}
	
	
	public function fetchElement($name, $value, &$node, $control_name)
	{
	    $select = Tienda::getClass( 'TiendaSelect', 'library.select' );
	    $list = $select->orderstate($value, $control_name.'['.$name.']', '', $control_name.$name, false, false, 'Select Order State', '', true );
		return $list;
	}
	
	
	
}

if(version_compare(JVERSION,'1.6.0','ge')) {
	class JFormFieldTiendaManufacturer extends JFakeElementTiendaManufacturer {}
} else {
	class JElementTiendaManufacturer extends JFakeElementTiendaManufacturer {}
}


?>
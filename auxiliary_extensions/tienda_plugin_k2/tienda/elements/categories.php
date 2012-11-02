<?php
/**
 * @version		1.0
 * @package		K2 Tienda plugin
 * @author    	JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JElementCategories extends JElement {

	var	$_name = 'Categories';

	function fetchElement($name, $value, &$node, $control_name) {
		$db = JFactory::getDBO();
		$query = "SELECT category.* FROM #__k2_categories AS category WHERE published = 1 ORDER BY parent, ordering";
		$db->setQuery( $query );
		$mitems = $db->loadObjectList();
		$children = array();
		if ( $mitems ) {
			foreach ( $mitems as $v ) {
				$pt = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
			}
		}
		$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );
		$mitems = array();
		$mitems [] = JHTML::_ ( 'select.option', '0',JText::_ ( '- All -' ));

		foreach ( $list as $item ) {
			$mitems[] = JHTML::_('select.option',  $item->id, '&nbsp;&nbsp;&nbsp;'.$item->treename );
		}

		return JHTML::_('select.genericlist',  $mitems, ''.$control_name.'['.$name.'][]', 'multiple="multiple"', 'value', 'text', $value );
	}

}

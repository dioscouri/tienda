<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class TiendaModelElementArticle extends DSCModelElement {
	var $title_key = 'title';
	var $select_title_constant = 'COM_TIENDA_SELECT_AN_ARTICLE';
	var $select_constant = 'COM_TIENDA_SELECT';
	var $clear_constant = 'COM_TIENDA_CLEAR_SELECTION';

	function getTable($name = '', $prefix = null, $options = array()) {
		$table = JTable::getInstance('Content', 'DSCTable');
		return $table;
	}
	
	/* Compatibility wrappers*/
	 function _fetchElement($name, $value='', $control_name='', $js_extra='', $fieldName='' ) {
	 	return parent::fetchElement($name, $value='', $control_name='', $js_extra='', $fieldName='' );
	 }
	function _clearElement($name, $value='', $control_name=''){
		return parent::clearElement($name, $value='', $control_name='');
	}
}
?>

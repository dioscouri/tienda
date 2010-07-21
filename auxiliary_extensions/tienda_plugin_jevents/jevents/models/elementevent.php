<?php
/**
 * @version		$Id: element.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.helper');
jimport( 'joomla.application.component.model');

/**
 * Content Component JEvent Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since		1.5
 */
class TiendaModelElementEvent extends JModel
{
	/**
	 * Content data in category array
	 *
	 * @var array
	 */
	var $_list = null;

	var $_page = null;

	/**
	 * Method to get content event data for the front page
	 *
	 * @since 1.5
	 */
	function getList()
	{
		global $mainframe;
		if (!empty($this->_list)) {
			return $this->_list;
		}

		// Initialize variables
		$db		=& $this->getDBO();
		$filter	= null;

		// Get some variables from the request
		$sectionid			= JRequest::getVar( 'sectionid', -1, '', 'int' );
		$redirect			= $sectionid;
		$option				= JRequest::getCmd( 'option' );
		$filter_order		= $mainframe->getUserStateFromRequest('articleelement.filter_order',		'filter_order',		'',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest('articleelement.filter_order_Dir',	'filter_order_Dir',	'',	'word');
		$catid				= $mainframe->getUserStateFromRequest('articleelement.catid',				'catid',			0,	'int');
		$filter_authorid	= $mainframe->getUserStateFromRequest('articleelement.filter_authorid',		'filter_authorid',	0,	'int');
		$filter_sectionid	= $mainframe->getUserStateFromRequest('articleelement.filter_sectionid',	'filter_sectionid',	-1,	'int');
		$limit				= $mainframe->getUserStateFromRequest('global.list.limit',					'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart			= $mainframe->getUserStateFromRequest('articleelement.limitstart',			'limitstart',		0,	'int');
		$search				= $mainframe->getUserStateFromRequest('articleelement.search',				'search',			'',	'string');
		$search				= JString::strtolower($search);

		//$where[] = "c.state >= 0";
		$where[] = "c.state != -2";

		if (!$filter_order) {
			$filter_order = 'section_name';
		}
		$order = ' ORDER BY '. $filter_order .' '. $filter_order_Dir .', section_name, cc.name, c.ordering';
		$all = 1;

		if ($filter_sectionid >= 0) {
			$filter = ' WHERE cc.section = '.$db->Quote($filter_sectionid);
		}
		$section->title = 'All Events';
		$section->id = 0;

		/*
		 * Add the filter specific information to the where clause
		 */
		// Section filter
		if ($filter_sectionid >= 0) {
			$where[] = 'c.sectionid = '.(int) $filter_sectionid;
		}
		// Category filter
		if ($catid > 0) {
			$where[] = 'c.catid = '.(int) $catid;
		}
		// Author filter
		if ($filter_authorid > 0) {
			$where[] = 'c.created_by = '.(int) $filter_authorid;
		}

		// Only published Event
		$where[] = 'c.state = 1';

		// Keyword filter
		if ($search) {
			$where[] = 'LOWER( c.title ) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		}

		// Build the where clause of the content record query
		$where = (count($where) ? ' WHERE '.implode(' AND ', $where) : '');

		// Get the total number of records
		$query = 'SELECT COUNT(*)' .
				' FROM #__jevents_vevdetail AS c' ;

		//$where;
		$db->setQuery($query);
		$total = $db->loadResult();

		// Create the pagination object
		jimport('joomla.html.pagination');
		$this->_page = new JPagination($total, $limitstart, $limit);

		// Get the articles
		$query = 'SELECT c.* ' .
				' FROM #__jevents_vevdetail AS c' ;
		$db->setQuery($query, $this->_page->limitstart, $this->_page->limit);
		$this->_list = $db->loadObjectList();

		// If there is a db query error, throw a HTTP 500 and exit
		if ($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr() );
			return false;
		}
		return $this->_list;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function getPagination()
	{
		if (is_null($this->_list) || is_null($this->_page)) {
			$this->getList();
		}
		return $this->_page;
	}

	/**
	 * This method fetch the data from the JEvent mapping table
	 * @return
	 * @param object $name
	 * @param object $value[optional]
	 * @param object $node[optional]
	 * @param object $control_name[optional]
	 */
	function _fetchElement($name, $value=0)
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
		$template 	= $mainframe->getTemplate();
		$control_name='';
		$fieldName	= $control_name ? $control_name.'['.$name.']' : $name;

		if($value!=0){
			$query="SELECT event.* FROM  `#__jevents_vevdetail` as event Left JOIN `#__tienda_jeventseventsproducts` as map on  map.event_id  = event.evdet_id where map.product_id = ".$value;
			$db->setQuery($query);
			$evetnDetail = $db->loadObject();

			if(!empty($evetnDetail)){
				$title = $evetnDetail->summary;
				$value= $evetnDetail->evdet_id;
			}
			// In case there is no event mapping
			else {
				$value=0;
				$title = JText::_('Select an Event');
			}
		}
		// In case of newly created Project
		else {
			$title = JText::_('Select an Event');
		}

		$js = "
		function jSelectEvent(id, title, object) {
			document.getElementById(object + '_id').value = id;
			document.getElementById(object + '_name').value = title;
			document.getElementById('sbox-window').close();
		}";
		$doc->addScriptDeclaration($js);

		$link = 'index.php?option=com_tienda&task=doTask&tmpl=component&element=jevents&elementTask=showEvents&object='.$name;

		JHTML::_('behavior.modal', 'a.modal');
		$html = "\n".'<div style="float: left;"><input style="background: #ffffff;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
		// $html .= "\n &nbsp; <input class=\"inputbox modal-button\" type=\"button\" value=\"".JText::_('Select')."\" />";
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('Select an Event').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">'.JText::_('Select').'</a></div></div>'."\n";
		$html .= "\n".'<input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.(int)$value.'" />';

		return $html;
	}

	/**
	 *
	 * @return
	 * @param object $name
	 * @param object $value[optional]
	 * @param object $node[optional]
	 * @param object $control_name[optional]
	 */
	function _clearElement($name, $value='', $node='', $control_name='')
	{

		global $mainframe;

		$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
		$template 	= $mainframe->getTemplate();
		$fieldName	= $control_name ? $control_name.'['.$name.']' : $name;

		$js = "
		function resetElement(id, title, object) {
			document.getElementById(object + '_id').value = id;
			document.getElementById(object + '_name').value = title;
		}";
		$doc->addScriptDeclaration($js);

		$html = '<div class="button2-left">
		<div class="blank">
		
		<a href="javascript::void();" onclick="resetElement( \''.$value.'\', \''.JText::_( 'Select an Event' ).'\', \''.$name.'\' )">'.JText::_( 'Clear Selection' ).'</span>
		</div></div>'."\n";

		return $html;
	}
	/**
	 * This method fetch the data from the JEvent mapping table
	 * @return
	 * @param object $name
	 * @param object $value[optional]
	 * @param object $node[optional]
	 * @param object $control_name[optional]
	 */
	function _getJEventId($name, $value=0)
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
		$template 	= $mainframe->getTemplate();
		$control_name='';
		$fieldName	= $control_name ? $control_name.'['.$name.']' : $name;

		if($value!=0){
				
			$query="SELECT map.event_id FROM  `#__tienda_jeventseventsproducts` as map WHERE map.product_id = ".$value;
			$db->setQuery($query);
			$evetnDetail = $db->loadObject();
			if($evetnDetail){
				return $evetnDetail->event_id;
			}
				
		}
		return 0;
	}




}
?>

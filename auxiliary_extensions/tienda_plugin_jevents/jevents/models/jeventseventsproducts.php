<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
 * @since		5.0.1
 */
class TiendaModelJEventsEventsProducts extends JModel
{
	/**
	 * This method fetch the data from the J
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
			 $query = new TiendaQuery();
			
			
			$select[]="event.*";
            $query->select( $select );
            $query->from("#__jevents_vevdetail AS event");
            
            
            $leftJoinCondition=" #__tienda_productevent  as map on  map.event_id  = event.evdet_id  ";
       		
            $query->leftJoin ($leftJoinCondition);
            
            $whereClause[]=" map.product_id = ".(int)$value;
            $query->where($whereClause );		
           
            $db->setQuery( (string) $query );
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

		$link = 'index.php?option=com_tienda&task=doTask&element=jevents&tmpl=component&elementTask=showEvents&object='.$name;
		       // 'index.php?option=com_tienda&task=doTask&element=jevents&elementTask=showEvents 

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

}
?>

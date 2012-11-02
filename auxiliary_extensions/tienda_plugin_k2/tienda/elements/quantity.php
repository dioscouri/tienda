<?php
/**
 * @version		1.0
 * @package		K2 Tienda plugin
 * @author    	JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ('Restricted access');

class JElementQuantity extends JElement {

	var $_name = 'Quantity';

	function fetchElement($name, $value, & $node, $control_name) {

		$output = '
		<span class="k2Note">'.JText::_('Click apply to be able to create product quantities').'</span>
		';

		$id = JRequest::getInt('cid');
		if($id){
			$K2Item = &JTable::getInstance('K2Item', 'Table');
			$K2Item->load($id);
			$params = new K2Parameter($K2Item->plugins, JPATH_PLUGINS.'/k2/tienda.xml', 'tienda');
			$productID = $params->get('productID');

			if($productID){
				$db = JFactory::getDBO();
				$query = "SELECT SUM(quantity) FROM #__tienda_productquantities WHERE product_id =".(int)$productID;
				$db->setQuery($query);
				$quantities = $db->loadResult();
				Tienda::load( 'TiendaUrl', 'library.url' );
				$output = '<span>'.$quantities.'</span><div class="tiendaButton">'.TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setquantities&id=".$productID."&tmpl=component", JText::_('COM_TIENDA_SET_QUANTITIES')).'</div>';
			}
		}

		return $output;

	}

}
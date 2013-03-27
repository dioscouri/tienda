<?php
/**
 * @version		1.0
 * @package		K2 Tienda plugin
 * @author    	JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ('Restricted access');

class JElementTools extends JElement {

	var $_name = 'Tools';

	function fetchElement($name, $value, & $node, $control_name) {

		$mainframe = JFactory::getApplication();

		$document = JFactory::getDocument();
		$js = "function jSelectProducts(id,title,object){
					$('tiendaProductName').setProperty('value', title);
					$('pluginstiendaproductID').setProperty('value', id);
					$('tiendaAssign').setProperty('value', '1');
					$('sbox-window').close();
				}";
		$document->addScriptDeclaration($js);

		$css = "#tiendaProductName {
					background: none repeat scroll 0% 0% rgb(255, 255, 255);
					float:left;
					height:18px;
					padding-left:3px;
				}";

		$document->addStyleDeclaration($css);

		$id = JRequest::getInt('cid');

		if($id){
			$K2Item = DSCTable::getInstance('K2Item', 'Table');
			$K2Item->load($id);
			$params = new K2Parameter($K2Item->plugins, JPATH_PLUGINS.'/k2/tienda.xml', 'tienda');
			$productID = $params->get('productID');
			if($productID){
				$db = JFactory::getDBO();
				$query = "SELECT * FROM #__tienda_products WHERE product_id=".(int)$productID;
				$db->setQuery($query);
				$product = $db->loadObject();
			}
		}

		if(!isset($product) || is_null($product)) {
			$product = new JObject;
			$product->product_name = '';
		}

		$output =
		'<div class="button2-left" style="margin-left:0; margin-right:10px;">
			<input type="text" disabled="disabled" value="'.$product->product_name.'" id="tiendaProductName"/>
			<div class="blank">
				<a href="index.php?option=com_tienda&amp;task=elementProduct&amp;tmpl=component" class="modal" rel="{handler: \'iframe\', size: {x: 800, y: 400}}" >'.JText::_('Assign product').'</a>
			</div>
			<input type="hidden" value="" id="tiendaAssign" name="tiendaAssign"/>
		</div>
		<div class="clr"></div>
		<span class="k2Note">'.JText::_('Using this allows to assign a tienda product to a K2 item. Mind that this will override all the above settings!').'</span>
		<br/>
		<div>
			<label for="tiendaUnassign">'.JText::_('Unassign product').'</label> <input type="checkbox" value="1" id="tiendaUnassign" name="tiendaUnassign">
			<span class="k2Note">'.JText::_('Check this to remove any relation between this K2 item and tienda product').'</span>
		</div>
		<br/>
		<div>
			<label for="tiendaRemove">'.JText::_('Remove product').'</label> <input type="checkbox" value="1" id="tiendaRemove" name="tiendaRemove">
			<span class="k2Note">'.JText::_('This will delete the tienda product! Use with care!').'</span>
		</div>
		';
		return $output;

	}

}
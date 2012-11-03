<?php
/**
 * @version		1.0
 * @package		K2 Tienda plugin
 * @author    	JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ('Restricted access');

class JElementAttribute extends JElement {

	var $_name = 'Attribute';

	function fetchElement($name, $value, & $node, $control_name) {

		$output = '<span class="k2Note">'.JText::_('Click Apply to be able to create product attributes').'</span>';

		$id = JRequest::getInt('cid');
		if($id){
			$K2Item = JTable::getInstance('K2Item', 'Table');
			$K2Item->load($id);
			$params = new K2Parameter($K2Item->plugins, JPATH_PLUGINS.'/k2/tienda.xml', 'tienda');
			$productID = $params->get('productID');

			if($productID){
				Tienda::load( 'TiendaUrl', 'library.url' );
				Tienda::load( "TiendaHelperProduct", 'helpers.product' );
				$attributes = TiendaHelperProduct::getAttributes( $productID );
				$output = '<div class="tiendaButton">'.TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setattributes&id=".$productID."&tmpl=component", JText::_('Set Attributes') ).'</div>';
				$output.= '<div>';
				foreach (@$attributes as $attribute){
					$output.='
					<div>
						<span>'.$attribute->productattribute_name.'('.$attribute->option_names_csv.')</span>
						<div class="tiendaButton">'.TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setattributeoptions&id=".$attribute->productattribute_id."&tmpl=component", JText::_('Set Attribute Options') ).'</div>
						<div class="tiendaButton"><a href="'."index.php?option=com_tienda&controller=productattributes&task=delete&cid[]=".$attribute->productattribute_id."&return=".base64_encode("index.php?option=com_k2&view=item&cid=".$id).'">'.JText::_('Remove').'</a></div>
					</div>';
				}
				$output.= '</div>';

			}
		}

		return $output;

	}

}
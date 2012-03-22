<?php
/**
 * @version		1.0
 * @package		K2 Tienda plugin
 * @author    	JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ('Restricted access');

class JElementPrice extends JElement {

	var $_name = 'Price';

	function fetchElement($name, $value, & $node, $control_name) {

		$output = '
		<input type="text" class="text_area" value="" id="pluginstiendaproductPrice" name="plugins[tiendaproductPrice]">
		<span class="k2Note">'.JText::_('Set Normal Price Now Special Prices Later').'</span>
		';

		$id = JRequest::getInt('cid');
		if($id){
			$K2Item = &JTable::getInstance('K2Item', 'Table');
			$K2Item->load($id);
			$params = new K2Parameter($K2Item->plugins, JPATH_PLUGINS.DS.'k2'.DS.'tienda.xml', 'tienda');
			$productID = $params->get('productID');

			if($productID){
				Tienda::load( 'TiendaUrl', 'library.url' );
				Tienda::load( "TiendaHelperProduct", 'helpers.product' );
				$prices = TiendaHelperProduct::getPrices( $productID );
				if(count($prices)){
					$output = '<div class="tiendaButton">'.TiendaUrl::popup("index.php?option=com_tienda&controller=products&task=setprices&id=".$productID."&tmpl=component", JText::_('Set Prices') ).'</div>';
					$output.= '<div>';
					foreach (@$prices as $price){
						$output.='
						<div>
							<span>'.TiendaHelperBase::currency( $price->product_price ).'</span>
							<div class="tiendaButton"><a href="'.$price->link_remove.'&return='.base64_encode("index.php?option=com_k2&view=item&cid=".$id).'">'.JText::_('Remove').'</a></div>
						</div>';
					}
					$output.= '</div>';
				}
			}
		}

		return $output;

	}

}
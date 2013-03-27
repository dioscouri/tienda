<?php
/**
 * @version		1.0
 * @package		K2 Tienda plugin
 * @author    	JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ('Restricted access');

class JElementManufacturer extends JElement {

	var $_name = 'Manufacturer';

	function fetchElement($name, $value, & $node, $control_name) {

		$mainframe = JFactory::getApplication();
		Tienda::load( 'TiendaSelect', 'library.select' );

		$id = JRequest::getInt('cid');
		if($id){
			$K2Item = DSCTable::getInstance('K2Item', 'Table');
			$K2Item->load($id);
			$params = new K2Parameter($K2Item->plugins, JPATH_PLUGINS.'/k2/tienda.xml', 'tienda');
			$id = $params->get('productID');

			$db = JFactory::getDBO();
			$query = "SELECT manufacturer_id FROM #__tienda_products WHERE product_id=".(int)$id;
			$db->setQuery($query);
			$manufacturer = $db->loadResult();
		}

		return TiendaSelect::manufacturer( @$manufacturer, 'plugins[tiendaproductManufacturer]', '', 'pluginstiendaproductManufacturer', false, true );;

	}

}
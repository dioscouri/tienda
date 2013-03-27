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

Tienda::load('TiendaReportPlugin', 'library.plugins.report');

class plgTiendaReport_inventory_levels extends TiendaReportPlugin {
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element = 'report_inventory_levels';

	/**
	 * @var $default_model  string  Default model used by report
	 */
	var $default_model = 'products';

	/**
	 * 
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_' . $this -> _element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_' . $this -> _element, JPATH_ADMINISTRATOR, null, true);
	}

	/**
	 * Override parent::_getData() to set the direction of the product
	 *
	 * @return objectlist
	 */
	function _getData() {
		static $pao;

		if (empty($pao)) {
			$pao = array();
		}

		$state = $this -> _getState();
		$model = $this -> _getModel();

		$data = $model -> getList();


		foreach ($data as $item) {
			$item -> attributes_total_price = '';
			$attribute_options = $this -> getAttributeOptions($item -> product_id);
			foreach ($attribute_options as $attribute_option) {
				if ($product_attributes = explode(',', $attribute_option -> product_attributes)) {
					foreach ($product_attributes as $pao_id) {
						if (empty($pao[$pao_id])) {
							$pao[$pao_id] = DSCTable::getInstance('ProductAttributeOptions', 'TiendaTable');
							$pao[$pao_id] -> load($pao_id);
						}
						$table = $pao[$pao_id];
						// + or -
						if ($table -> productattributeoption_prefix != '=') {
							$item -> attributes_total_price = $item -> attributes_total_price + floatval("$table->productattributeoption_prefix" . "$table->productattributeoption_price");
						}
						//if prefix is =
						else {
							$item -> attributes_total_price = "0.00000";
						}
						$item -> attributes_total_price = number_format($item -> attributes_total_price, '5', '.', '');
					}
				}
			}
			$item -> total_value = $item -> attributes_total_price + ($item -> price * $item -> product_quantity);
		}
		return $data;
	}

	/**
	 * Override parent::_getState() to do the filtering
	 *
	 * @return object
	 */
	function _getState() {
		$app = JFactory::getApplication();
		$model = $this -> _getModel($this -> get('default_model'));
		$ns = $this -> _getNamespace();

		$state = parent::_getState();

		$state['filter_name'] = $app -> getUserStateFromRequest($ns . 'name', 'filter_name', '', '');
		$state['filter_quantity_from'] = $app -> getUserStateFromRequest($ns . 'quantity_from', 'filter_quantity_from', '', '');
		$state['filter_quantity_to'] = $app -> getUserStateFromRequest($ns . 'quantity_to', 'filter_quantity_to', '', '');
		$state['filter_product_name'] = $app -> getUserStateFromRequest($ns . 'filter_product_name', 'filter_product_name', '', '');
		$state['filter_product_name'] = $app -> getUserStateFromRequest($ns . 'filter_product_name', 'filter_product_name', '', '');
		$state['filter_product_name'] = $app -> getUserStateFromRequest($ns . 'filter_product_name', 'filter_product_name', '', '');
		$state = $this -> _handleRangePresets($state);

		foreach (@$state as $key => $value) {
			$model -> setState($key, $value);
		}

		return $state;

	}


	/**
	 * Returns a list of a product's attributes
	 *
	 * @param int $id
	 * @return unknown_type
	 */
	function getAttributes($id) {
		if (empty($id)) {
			return array();
		}
		DSCModel::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models');
		$model = DSCModel::getInstance('ProductAttributes', 'TiendaModel');
		$model -> setState('filter_product', $id);

		$model -> setState('order', 'tbl.ordering');
		$model -> setState('direction', 'ASC');

		$items = $model -> getList();
		return $items;
	}

	function getAttributeOptions($id) {
		if (empty($id)) {
			return array();
		}
		DSCModel::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models');
		$model = DSCModel::getInstance('ProductQuantities', 'TiendaModel');
		$model -> setState('filter_productid', $id);

		$items = $model -> getList();
		return $items;
	}

}

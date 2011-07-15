<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Daniele Rosario
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/plugins/tienda/tool_genericimporter/genericimport_csv.php';

class plgTiendaTool_CsvCoupons extends TiendaToolPluginImportCsv {
	/*
	 * Name of the importer displayed in select box
	 */
	public $importer_name = 'Coupon Import';

	var $keys = array();

	var $results = array();

	var $_element = 'tool_genericimporter';

	/*
	 * Sets default values for variables from request
	 */
	function getDefaultState() {
		parent::getDefaultState();
		$this->import_throttled_import = true;
		$this->import_num_records = 1;
		$this->keys = array('coupon_id', 'coupon_name', 'coupon_code', 'coupon_type', 'coupon_group', 'coupon_automatic', 'coupon_value', 'coupon_value_type', 'currency_id', 'coupon_description', 'coupon_params', 'created_date', 'modified_date', 'start_date', 'expiration_date', 'coupon_enabled', 'coupon_uses', 'coupon_max_uses', 'coupon_max_uses_per_user');
	}

	/*
	 * Generates HTML code for the usual admintable table
	 *
	 * @param $step Step for which the code is generated
	 *
	 * @return String HTML code of the table
	 */
	function getAdminTableHtml($step) {
		$state = $this->get('state');
		$checked = array('', '\'checked\'');
		$answer = array(JText::_('No'), JText::_('Yes'));
		$rows = array();
		$only_value = $step == 2;
		$skip_first_value = strcmp($state->skip_first, 'on') == 0;

		$rows[] = $this->generateRowInput($only_value, JText::_('Source Import') . ': *', 'file', 'source_import', $this->source_import);
		$rows[] = $this->generateRowInput($only_value, JText::_('Separator'), 'text', 'field_separator', $state->field_separator, 10);
		$rows[] = $this->generateRowInput($only_value, JText::_('Skip First Row') . '?', 'checkbox', 'skip_first', $skip_first_value, null, $answer[$skip_first_value], null, $checked[$skip_first_value]);

		$this->set('table_rows', $rows);
		return parent::getAdminTableHtml($step);
	}

	/*
	 * Performs the actual migration of data
	 *
	 * @return Additional HTML code you would like to display on the final step
	 */
	function migrate_data() {
		$datas = $this->data;		
		$result = '';

		JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables');
		$coupon = JTable::getInstance('Coupons', 'TiendaTable');

		$i = 0;
		foreach($datas as $data) {
			$fields = $this->mapFields($data);

			$coupon->coupon_id 					= '';
			$coupon->coupon_name 				= $fields['coupon_name'];
			$coupon->coupon_code 				= $fields['coupon_code'];
			$coupon->coupon_value 				= $fields['coupon_value'];
			$coupon->coupon_type 				= $fields['coupon_type'];
			$coupon->coupon_group 				= $fields['coupon_group'];
			$coupon->coupon_automatic 			= $fields['coupon_automatic'];
			$coupon->coupon_value_type 			= $fields['coupon_value_type'];
			$coupon->currency_id 				= $fields['currency_id'];
			$coupon->coupon_description 		= $fields['coupon_description'];
			$coupon->coupon_params 				= $fields['coupon_params'];
			$coupon->created_date 				= $fields['created_date'];
			$coupon->modified_date 				= $fields['modified_date'];
			$coupon->expiration_date 			= $fields['expiration_date'];			
			$coupon->start_date 				= $fields['start_date'];
			$coupon->coupon_enabled 			= $fields['coupon_enabled'];
			$coupon->coupon_uses 				= $fields['coupon_uses'];			
			$coupon->coupon_max_uses 			= $fields['coupon_max_uses'];
			$coupon->coupon_max_uses_per_user 	= $fields['coupon_max_uses_per_user'];
			$coupon->save();

			$result = new stdClass();
			$result->title = $coupon->coupon_name;
			$result->errors = $coupon->getErrors();
			$result->affectedRows = 1;
			$i++;

			$this->results[] = $result;
		}

	}

	function mapFields($datas) {
		$mapped = array();
		$i = 0;
		foreach($this->keys as $key) {			
			$mapped[$key] = @$datas[$i];
			$i++;
		}

		return $mapped;
	}

	/**
	 *Show the Migration Results
	 * return html code
	 */
	function getHtmlStep3View() {
		$html = "";
		$vars = new JObject();
		$vars->results = $this->results;

		$html = $this->_getLayout('view_coupon', $vars);

		return $html;
	}

	function _getLayout($layout, $vars =false, $plugin ='', $group ='tienda') {
		if(empty($plugin)) {
			$plugin = $this->_element;
		}

		ob_start();
		$layout = $this->_getLayoutPath($plugin, $group, $layout);
		include ($layout);
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Get the path to a layout file
	 *
	 * @param   string  $plugin The name of the plugin file
	 * @param   string  $group The plugin's group
	 * @param   string  $layout The name of the plugin layout file
	 * @return  string  The path to the plugin layout file
	 * @access protected
	 */
	function _getLayoutPath($plugin, $group, $layout ='default') {
		$app = JFactory::getApplication();

		// get the template and default paths for the layout
		$templatePath = JPATH_SITE . DS . 'templates' . DS . $app->getTemplate() . DS . 'html' . DS . 'plugins' . DS . $group . DS . $plugin . DS . $layout . '.php';
		$defaultPath = JPATH_SITE . DS . 'plugins' . DS . $group . DS . $plugin . DS . 'tmpl' . DS . $layout . '.php';

		// if the site template has a layout override, use it
		jimport('joomla.filesystem.file');
		if(JFile::exists($templatePath)) {
			return $templatePath;
		} else {
			return $defaultPath;
		}
	}

}
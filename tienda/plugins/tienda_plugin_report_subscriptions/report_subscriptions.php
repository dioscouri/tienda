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

class plgTiendaReport_subscriptions extends TiendaReportPlugin {
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element = 'report_subscriptions';

	/**
	 * @var $default_model  string  Default model used by report
	 */
	var $default_model = 'subscriptions';

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgTiendaReport_subscriptions(&$subject, $config) {
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_' . $this -> _element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_' . $this -> _element, JPATH_ADMINISTRATOR, null, true);
	}

	/**
	 * Gets the state override
	 *
	 * @return array
	 */
	function _getState() {
		$app = JFactory::getApplication();
		$model = $this -> _getModel($this -> get('default_model'));
		$ns = $this -> _getNamespace();

		$state = array();

		$state['filter'] = $app -> getUserStateFromRequest($ns . '.filter', 'filter', '', 'string');
		$state['filter_enabled'] = $app -> getUserStateFromRequest($ns . 'enabled', 'filter_enabled', '', '');
		$state['filter_date_from'] = $app -> getUserStateFromRequest($ns . 'date_from', 'filter_date_from', '', '');
		$state['filter_date_to'] = $app -> getUserStateFromRequest($ns . 'date_to', 'filter_date_to', '', '');
		$state['filter_datetype'] = $app -> getUserStateFromRequest($ns . 'datetype', 'filter_datetype', '', '');
		$state['filter_range'] = $app -> getUserStateFromRequest($ns . 'range', 'filter_range', '', '');
		$state['filter_orderstate'] = $app -> getUserStateFromRequest($ns . 'orderstate', 'filter_orderstate', '', '');
		$state = $this -> _handleRangePresets($state);

		foreach (@$state as $key => $value) {
			$model -> setState($key, $value);
		}

		return $state;
	}

}

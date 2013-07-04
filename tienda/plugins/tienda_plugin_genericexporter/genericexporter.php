<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load('TiendaPluginBase', 'library.plugins._base');

class plgTiendaGenericExporter extends TiendaPluginBase {
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	public $_element = 'genericexporter';

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_genericexporter', JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_genericexporter', JPATH_ADMINISTRATOR, null, true);
	}

	function onAfterDisplayAdminComponentTienda() {
		$view = JRequest::getCmd( 'view' );
		if( $view == 'pos' )
			return;
		$name = 'revert';
		$url = 'index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=display';

		$bar = JToolBar::getInstance('toolbar');
		$bar -> prependButton('link', $name, 'COM_TIENDA_GENERIC_EXPORT', $url);
	}

	/**
	 *
	 * Method to display list of export types
	 */
	function display() {
		//needed to make display function correctly
		JHTML::_('stylesheet', 'admin.css', 'media/com_tienda/css/');
		JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
		Tienda::loadJQuery();

		require_once (JPATH_SITE.'/libraries/joomla/html/html/select.php');
		JToolBarHelper::title(JText::_('COM_TIENDA_GENERIC_EXPORT'));

		$bar = JToolBar::getInstance('toolbar');
		$btnhtml = '<a class="toolbar" onclick="javascript: document.adminForm.submit();" href="#">';
		$btnhtml .= '<span title="Submit" class="icon-32-forward">';
		$btnhtml .= '</span>' . JText::_('COM_TIENDA_SUBMIT') . '</a>';
		$bar -> appendButton('Custom', $btnhtml);

		//read the type files inside the /plugins/tienda/genericexporter/models
		jimport('joomla.filesystem.file');
		if (version_compare(JVERSION, '1.6.0', 'ge')) {
			// Joomla! 1.6+ code here
			$folder = JPATH_SITE . '/plugins/tienda/genericexporter/genericexporter/models';
		} else {
			// Joomla! 1.5 code here
			$folder = JPATH_SITE . '/plugins/tienda/genericexporter/models';
		}

		if (JFolder::exists($folder)) {

			$extensions = array('php');
			$exclusions = array('_base.php');

			$files = JFolder::files($folder);

			foreach ($files as $file) {
				$namebits = explode('.', $file);
				$extension = $namebits[count($namebits) - 1];

				if (in_array($extension, $extensions) && !in_array($file, $exclusions)) {
					$classname = 'TiendaGenericExporterModel' . ucfirst($namebits[0]);

					if (version_compare(JVERSION, '1.6.0', 'ge')) {
						// Joomla! 1.6+ code here
						Tienda::load($classname, 'genericexporter.genericexporter.models.' . $namebits[0], array('site' => 'site', 'type' => 'plugins', 'ext' => 'tienda'));

					} else {
						// Joomla! 1.5 code here
						Tienda::load($classname, 'genericexporter.models.' . $namebits[0], array('site' => 'site', 'type' => 'plugins', 'ext' => 'tienda'));

					}

					if (class_exists($classname)) {
						$exporter = new $classname;
						$models[] = array($exporter -> getModelClass(), $exporter -> getName());
					}
				}
			}
		}
		if (version_compare(JVERSION, '1.6.0', 'ge')) {
			// Joomla! 1.6+ code here
			$folderTypes = JPATH_SITE . '/plugins/tienda/genericexporter/genericexporter/types';

		} else {
			// Joomla! 1.5 code here
			$folderTypes = JPATH_SITE . '/plugins/tienda/genericexporter/types';

		}
		if (JFolder::exists($folderTypes)) {
			$extensions = array('php');
			$exclusions = array('_base.php');

			$typeFiles = JFolder::files($folderTypes);
			foreach ($typeFiles as $typeFile) {
				$namebits = explode('.', $typeFile);
				$extension = $namebits[count($namebits) - 1];

				if (in_array($extension, $extensions) && !in_array($typeFile, $exclusions)) {
					$classname = 'TiendaGenericExporterType' . $namebits[0];
					if (version_compare(JVERSION, '1.6.0', 'ge')) {
						// Joomla! 1.6+ code here
						Tienda::load($classname, 'genericexporter.genericexporter.types.' . strtolower($namebits[0]), array('site' => 'site', 'type' => 'plugins', 'ext' => 'tienda'));

					} else {
						// Joomla! 1.5 code here
						Tienda::load($classname, 'genericexporter.types.' . strtolower($namebits[0]), array('site' => 'site', 'type' => 'plugins', 'ext' => 'tienda'));

					}

					if (class_exists($classname)) {
						$exporterType = new $classname;
						$types[] = $exporterType -> getFormat();
					}
				}
			}
		}

		sort($models);
		sort($types);

		$vars = new JObject();
		$vars -> models = $models;
		$vars -> types = $types;
		$html = $this -> _getLayout('default', $vars);

		return $html;
	}

	function filters() {
		$model = JRequest::getVar('model', 'products');
		$type = JRequest::getVar('type');

		if (empty($type) || empty($model)) {
			JFactory::getApplication() -> redirect('index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=display', JText::_('COM_TIENDA_MODEL_OR_EXPORT_TYPE_IS_EMPTY'), 'notice');
		}

		$bar = JToolBar::getInstance('toolbar');
		$btnhtml = '<a class="toolbar" onclick="javascript: document.adminForm.submit();" href="#">';
		$btnhtml .= '<span title="' . JText::_('COM_TIENDA_SUBMIT') . '" class="icon-32-forward">';
		$btnhtml .= '</span>' . JText::_('COM_TIENDA_SUBMIT') . '</a>';
		$bar -> appendButton('Custom', $btnhtml);

		$url = 'index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=display';
		$bar -> prependButton('link', 'cancel', JText::_('COM_TIENDA_BACK'), $url);

		$classname = 'TiendaGenericExporterModel' . $model;

		if (version_compare(JVERSION, '1.6.0', 'ge')) {
			// Joomla! 1.6+ code here
			Tienda::load($classname, 'genericexporter.genericexporter.models.' . $model, array('site' => 'site', 'type' => 'plugins', 'ext' => 'tienda'));

		} else {
			// Joomla! 1.5 code here
			Tienda::load($classname, 'genericexporter.models.' . $model, array('site' => 'site', 'type' => 'plugins', 'ext' => 'tienda'));

		}

		if (class_exists($classname)) {
			$class = new $classname;
			$filters = $class -> getFilters();

			//if empty we process to download page
			if (!count($filters)) {
				JFactory::getApplication() -> redirect("index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=doExport&model={$model}&type={$type}");
			}
			JToolBarHelper::title(JText::_('COM_TIENDA_GENERIC_EXPORT') . ': ' . $class -> getName());
		} else {
			JToolBarHelper::title(JText::_('COM_TIENDA_GENERIC_EXPORT') . ': ' . ucfirst($model));
			JFactory::getApplication() -> enqueueMessage(JText::sprintf("COM_TIENDA_CLASSNAME_NOT_FOUND", $classname), 'notice');
		}

		$vars = new JObject();
		$vars -> filters = $filters;
		$html = $this -> _getLayout('form', $vars);

		return $html;
	}

	function doExport() {

		$post = JRequest::get('post');

		$model = JRequest::getVar('model', 'products');
		$type = JRequest::getVar('type');

		$views = array('dashboard', 'orders', 'orderpayments', 'subscriptions', 'orderitems', 'products', 'users', 'config');
		if (in_array(strtolower($model), $views)) {
			$url = 'index.php?option=com_tienda&view=' . $model;
		} else {
			$url = 'index.php?option=com_tienda&view=dashboard';
		}

		//add toolbar
		$bar = JToolBar::getInstance('toolbar');
		$bar -> prependButton('link', 'cancel', JText::_('COM_TIENDA_BACK'), $url);
		JToolBarHelper::title(JText::_('COM_TIENDA_GENERIC_EXPORT') . " : " . ucfirst($model));

		$export = $this -> processExport($type, $model);

		if (!empty($export -> _errors)) {
			JFactory::getApplication() -> enqueueMessage($export -> _errors, 'notice');
			return;
		}
		//success message
		JFactory::getApplication() -> enqueueMessage(JText::_('COM_TIENDA_EXPORT_IS_COMPLETE_PLEASE_CLICK_THE_LINK_BELOW_TO_DOWNLOAD'), 'message');

		$vars = new JObject();
		$vars -> name = $export -> _name;
		$vars -> link = $export -> _link;
		$html = $this -> _getLayout('view', $vars);
		return $html;
	}

	/**
	 * Method to process the export
	 * @param string $type - csv, xml, etc
	 * @param string $model - see /plugins/tienda/genericexporter/models
	 * @return void
	 */
	private function processExport($type, $model) {
		$classname = 'TiendaGenericExporterType' . $type;
		if (version_compare(JVERSION, '1.6.0', 'ge')) {
			// Joomla! 1.6+ code here
			Tienda::load($classname, 'genericexporter.genericexporter.types.' . strtolower($type), array('site' => 'site', 'type' => 'plugins', 'ext' => 'tienda'));

		} else {
			// Joomla! 1.5 code here
			Tienda::load($classname, 'genericexporter.types.' . strtolower($type), array('site' => 'site', 'type' => 'plugins', 'ext' => 'tienda'));

		}

		$export = '';
		if (class_exists($classname)) {
			$exporterType = new $classname();
			$exporterType -> setModel($model);
			$export = $exporterType -> processExport();
		}

		return $export;
	}

	/**
	 * Returns a selectlist of zones
	 * Called via Ajax
	 *
	 * @return unknown_type
	 */
	function getZones() {
		Tienda::load('TiendaSelect', 'library.select');
		$html = '';
		$text = '';

		$country_id = JRequest::getVar('country_id');
		$prefix = JRequest::getVar('prefix');
		$html = TiendaSelect::zone('', $prefix . 'zone_id', $country_id, array('class' => 'inputbox', 'size' => '1'), $prefix . 'zone_id', true);

		$response = array();
		$response['msg'] = $html;
		$response['error'] = '';

		return json_encode($response);
	}

}

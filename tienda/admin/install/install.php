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
defined( '_JEXEC' ) or die( 'Restricted access' );

$thisextension = strtolower( "com_tienda" );
$thisextensionname = substr ( $thisextension, 4 );

JLoader::import( 'com_tienda.library.dscinstaller', JPATH_ADMINISTRATOR.DS.'components' );

// load the component language file
$language = &JFactory::getLanguage();
$language->load( $thisextension );

$status = new JObject();
$status->modules = array();
$status->plugins = array();
$status->templates = array();

/***********************************************************************************************
 * ---------------------------------------------------------------------------------------------
 * // TEMPLATES INSTALLATION SECTION 
 * ---------------------------------------------------------------------------------------------
 ***********************************************************************************************/

$templates = &$this->manifest->getElementByPath('templates');
if (is_a($templates, 'JSimpleXMLElement') && count($templates->children())) {

	foreach ($templates->children() as $template)
	{
	    set_time_limit(0);
		$mname		= $template->attributes('template');
		$mpublish	= $template->attributes('publish');
		$mclient	= JApplicationHelper::getClientInfo($template->attributes('client'), true);
		
		// Set the installation path
		if (!empty ($mname)) {
			$this->parent->setPath('extension_root', $mclient->path.DS.'templates'.DS.$mname);
		} else {
			$this->parent->abort(JText::_('COM_TIENDA_TEMPLATE').' '.JText::_('COM_TIENDA_INSTALL').': '.JText::_('COM_TIENDA_INSTALL_TEMPLATE_FILE_MISSING'));
			return false;
		}
		
		/*
		 * fire the dioscouriInstaller with the foldername and folder entryType
		 */
		$pathToFolder = $this->parent->getPath('source').DS.$mname;
		$dscInstaller = new dscInstaller();
		if ($mpublish) {
			$dscInstaller->set( '_publishExtension', true );
		}
		$result = $dscInstaller->installExtension($pathToFolder, 'folder');
		
		// track the message and status of installation from dscInstaller
		if ($result) 
		{
			$alt = JText::_('COM_TIENDA_INSTALLED');
			$mstatus = "<img src='images/tick.png' border='0' alt='{$alt}' />";
		} else {
			$alt = JText::_('COM_TIENDA_INSTALLATION_FAILED');
			$error = $dscInstaller->getError();
			$mstatus = "<img src='images/publish_x.png' border='0' alt='{$alt}' />";
			$mstatus .= " - ".$error;
		}
		
		$status->templates[] = array('name'=>$mname,'client'=>$mclient->name, 'status'=>$mstatus );
	}
}

/***********************************************************************************************
 * ---------------------------------------------------------------------------------------------
 * MODULE INSTALLATION SECTION
 * ---------------------------------------------------------------------------------------------
 ***********************************************************************************************/

$modules = &$this->manifest->getElementByPath('modules');
if (is_a($modules, 'JSimpleXMLElement') && count($modules->children())) {

	foreach ($modules->children() as $module)
	{
	    set_time_limit(0);
		$mname		= $module->attributes('module');
		$mpublish	= $module->attributes('publish');
		$mposition	= $module->attributes('position');
		$mclient	= JApplicationHelper::getClientInfo($module->attributes('client'), true);
		
		// Set the installation path
		if (!empty ($mname)) {
			$this->parent->setPath('extension_root', $mclient->path.DS.'modules'.DS.$mname);
		} else {
			$this->parent->abort(JText::_('COM_TIENDA_MODULE').' '.JText::_('COM_TIENDA_INSTALL').': '.JText::_('COM_TIENDA_INSTALL_MODULE_FILE_MISSING'));
			return false;
		}
		
		/*
		 * fire the dioscouriInstaller with the foldername and folder entryType
		 */
		$pathToFolder = $this->parent->getPath('source').DS.$mname;
		$dscInstaller = new dscInstaller();
		if ($mpublish) {
			$dscInstaller->set( '_publishExtension', true );
		}
		$result = $dscInstaller->installExtension($pathToFolder, 'folder');
		
		// track the message and status of installation from dscInstaller
		if ($result) 
		{
			// update the module record if the position != left
			if (isset($mposition) && $mposition != 'left')
			{
				// set the position of the module
				$database = JFactory::getDBO();
				$query = "UPDATE #__modules SET `position` = '{$mposition}' WHERE `module` = '{$mname}';";
				$database->setQuery($query);
				$database->query();
			}
			$alt = JText::_('COM_TIENDA_INSTALLED');
			$mstatus = "<img src='images/tick.png' border='0' alt='{$alt}' />";
		} else {
			$alt = JText::_('COM_TIENDA_INSTALLATION_FAILED');
			$error = $dscInstaller->getError();
			$mstatus = "<img src='images/publish_x.png' border='0' alt='{$alt}' />";
			$mstatus .= " - ".$error;
		}
		
		$status->modules[] = array('name'=>$mname,'client'=>$mclient->name, 'status'=>$mstatus );
	}
}


/***********************************************************************************************
 * ---------------------------------------------------------------------------------------------
 * PLUGIN INSTALLATION SECTION
 * ---------------------------------------------------------------------------------------------
 ***********************************************************************************************/

$plugins = &$this->manifest->getElementByPath('plugins');
if (is_a($plugins, 'JSimpleXMLElement') && count($plugins->children())) {

	foreach ($plugins->children() as $plugin)
	{
	    set_time_limit(0);
		$pname		= $plugin->attributes('plugin');
		$ppublish	= $plugin->attributes('publish');
		$pgroup		= $plugin->attributes('group');
		
		// Set the installation path
		if (!empty($pname) && !empty($pgroup)) {
			$this->parent->setPath('extension_root', JPATH_ROOT.DS.'plugins'.DS.$pgroup);
		} else {
			$this->parent->abort(JText::_('COM_TIENDA_PLUGIN').' '.JText::_('COM_TIENDA_INSTALL').': '.JText::_('COM_TIENDA_INSTALL_PLUGIN_FILE_MISSING'));
			return false;
		}
		
		/*
		 * fire the dioscouriInstaller with the foldername and folder entryType
		 */
		$pathToFolder = $this->parent->getPath('source').DS.$pname;
		$dscInstaller = new dscInstaller();
		if ($ppublish) {
			$dscInstaller->set( '_publishExtension', true );
		}
		$result = $dscInstaller->installExtension($pathToFolder, 'folder');
		
		// track the message and status of installation from dscInstaller
		if ($result) {
			$alt = JText::_('COM_TIENDA_INSTALLED');
			$pstatus = "<img src='images/tick.png' border='0' alt='{$alt}' />";	
		} else {
			$alt = JText::_('COM_TIENDA_INSTALLATION_FAILED');
			$error = $dscInstaller->getError();
			$pstatus = "<img src='images/publish_x.png' border='0' alt='{$alt}' /> ";
			$pstatus .= " - ".$error;	
		}

		$status->plugins[] = array('name'=>$pname,'group'=>$pgroup, 'status'=>$pstatus);
	}
}

/***********************************************************************************************
 * ---------------------------------------------------------------------------------------------
 * SETUP DEFAULTS
 * ---------------------------------------------------------------------------------------------
 ***********************************************************************************************/

// None

/***********************************************************************************************
 * ---------------------------------------------------------------------------------------------
 * OUTPUT TO SCREEN
 * ---------------------------------------------------------------------------------------------
 ***********************************************************************************************/
$rows = 0;
?>

<h2><?php echo JText::_('COM_TIENDA_INSTALLATION_RESULTS'); ?></h2>
<table class="adminlist">
	<thead>
		<tr>
			<th colspan="2"><?php echo JText::_('COM_TIENDA_EXTENSION'); ?></th>
			<th width="30%"><?php echo JText::_('COM_TIENDA_STATUS'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo JText::_( $thisextension ); ?></td>
			<td class="key"><center><?php $alt = JText::_('COM_TIENDA_INSTALLED'); echo "<img src='images/tick.png' border='0' alt='{$alt}' />"; ?></center></td>
		</tr>
<?php if (count($status->modules)) : ?>
		<tr>
			<th><?php echo JText::_('COM_TIENDA_MODULE'); ?></th>
			<th><?php echo JText::_('COM_TIENDA_CLIENT'); ?></th>
			<th></th>
		</tr>
	<?php foreach ($status->modules as $module) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td class="key"><center><?php echo $module['status']; ?></center></td>
		</tr>
	<?php endforeach;
endif;
if (count($status->plugins)) : ?>
		<tr>
			<th><?php echo JText::_('COM_TIENDA_PLUGIN'); ?></th>
			<th><?php echo JText::_('COM_TIENDA_GROUP'); ?></th>
			<th></th>
		</tr>
	<?php foreach ($status->plugins as $plugin) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $plugin['name']; ?></td>
			<td class="key"><?php echo $plugin['group']; ?></td>
			<td class="key"><center><?php echo $plugin['status']; ?></center></td>
		</tr>
	<?php endforeach;
endif;
if (count($status->templates)) : ?>
		<tr>
			<th><?php echo JText::_('COM_TIENDA_TEMPLATE'); ?></th>
			<th><?php echo JText::_('COM_TIENDA_CLIENT'); ?></th>
			<th></th>
		</tr>
	<?php foreach ($status->templates as $template) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $template['name']; ?></td>
			<td class="key"><?php echo $template['client']; ?></td>
			<td class="key"><center><?php echo $template['status']; ?></center></td>
		</tr>
	<?php endforeach;
endif; ?>
	</tbody>
</table>

<?php if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

// load the config class
Tienda::load( 'Tienda', 'defines' );

// before executing any tasks, check the integrity of the installation
Tienda::getClass( 'TiendaHelperDiagnostics', 'helpers.diagnostics' )->checkInstallation();
?>
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

// TODO Should list all the aux extensions from the install.XML file with a notice saying that each one is still installed
// and that if the user wants to completely remove component, must also uninstall all of them too
// and remove database tables
// Questions: do any of these files exist at this point?  when is the uninstall.php being run?

/***********************************************************************************************
 * ---------------------------------------------------------------------------------------------
 * MODULE UNINSTALLATION SECTION
 * ---------------------------------------------------------------------------------------------
 ***********************************************************************************************/

$modules = &$this->manifest->getElementByPath('modules');
if (is_a($modules, 'JSimpleXMLElement') && count($modules->children())) {

    foreach ($modules->children() as $module)
    {
        $mname      = $module->attributes('module');
        $mpublish   = $module->attributes('publish');
        $mposition  = $module->attributes('position');
        $mclient    = JApplicationHelper::getClientInfo($module->attributes('client'), true);
        
        $package    = array();
        $package['type'] = 'module';
        $package['group'] = '';
        $package['element'] = $mname;
        $package['client'] = $module->attributes('client');
                
        // Set the installation path
        if (!empty ($mname)) {
            $this->parent->setPath('extension_root', $mclient->path.DS.'modules'.DS.$mname);
        } else {
            $this->parent->abort(JText::_('COM_TIENDA_MODULE').' '.JText::_('COM_TIENDA_INSTALL').': '.JText::_('COM_TIENDA_INSTALL_MODULE_FILE_MISSING'));
            return false;
        }
        
        /*
         * fire the dioscouriInstaller
         */
        $dscInstaller = new dscInstaller();
        $result = $dscInstaller->uninstallExtension($package);
        
        // track the message and status of installation from dscInstaller
        if ($result) 
        {
            $alt = JText::_('COM_TIENDA_UNINSTALLED');
            $mstatus = "<img src='images/tick.png' border='0' alt='{$alt}' />";
        } 
            else 
        {
            $alt = JText::_('COM_TIENDA_UNINSTALLATION_FAILED');
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
        $pname      = $plugin->attributes('plugin');
        $ppublish   = $plugin->attributes('publish');
        $pgroup     = $plugin->attributes('group');

        $package    = array();
        $package['type'] = 'plugin';
        $package['group'] = $pgroup;
        $package['element'] = $plugin->attributes('element');
        $package['client'] = '';
        
        // Set the installation path
        if (!empty($pname) && !empty($pgroup)) {
            $this->parent->setPath('extension_root', JPATH_ROOT.DS.'plugins'.DS.$pgroup);
        } else {
            $this->parent->abort(JText::_('COM_TIENDA_PLUGIN').' '.JText::_('COM_TIENDA_INSTALL').': '.JText::_('COM_TIENDA_INSTALL_PLUGIN_FILE_MISSING'));
            return false;
        }
        
        /*
         * fire the dioscouriInstaller
         */
        $dscInstaller = new dscInstaller();
        $result = $dscInstaller->uninstallExtension($package);
        
        // track the message and status of installation from dscInstaller
        if ($result) 
        {
            $alt = JText::_('COM_TIENDA_UNINSTALLED');
            $pstatus = "<img src='images/tick.png' border='0' alt='{$alt}' />"; 
        } 
            else 
        {
            $alt = JText::_('COM_TIENDA_UNINSTALLATION_FAILED');
            $error = $dscInstaller->getError();
            $pstatus = "<img src='images/publish_x.png' border='0' alt='{$alt}' /> ";
            $pstatus .= " - ".$error;   
        }

        $status->plugins[] = array('name'=>$pname,'group'=>$pgroup, 'status'=>$pstatus);
    }
}


/***********************************************************************************************
 * ---------------------------------------------------------------------------------------------
 * OUTPUT TO SCREEN
 * ---------------------------------------------------------------------------------------------
 ***********************************************************************************************/
 $rows = 0;
?>

<h2><?php echo JText::_('COM_TIENDA_UNINSTALLATION_RESULTS'); ?></h2>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo JText::_('COM_TIENDA_EXTENSION'); ?></th>
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
			<td class="key" colspan="2"><?php echo JText::_('COM_TIENDA_COMPONENT'); ?></td>
			<td><center><strong><?php echo JText::_('COM_TIENDA_REMOVED'); ?></strong></center></td>
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
			<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td class="key"><center><?php echo $plugin['status']; ?></center></td>
		</tr>
	<?php endforeach;
endif; ?>
	</tbody>
</table>
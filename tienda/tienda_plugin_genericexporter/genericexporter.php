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

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaGenericExporter extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    public $_element    	= 'generic_exporter';  
    
	function plgTiendaGenericExporter(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );			
	}
	
 	function onAfterDisplayAdminComponentTienda()
    {
        $url = 'index.php?option=com_tienda&task=doTask&element=generic_exporter&elementTask=view';
        $bar = & JToolBar::getInstance('toolbar');
        $bar->prependButton( 'link', $name, $text, $url );
    }
    
    function display()
    {
    	echo 'test';
    }
}
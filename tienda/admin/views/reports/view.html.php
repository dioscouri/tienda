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

Tienda::load( 'TiendaViewBase', 'views._base' );

class TiendaViewReports extends TiendaViewBase 
{
	function _form($tpl=null)
	{
        parent::_form($tpl);
        
        // load the plugin
		$row = $this->getModel()->getItem();
		$import = JPluginHelper::importPlugin( 'tienda', $row->element );
	}
	
	function _defaultToolbar()
	{
	}
	
    function _viewToolbar()
    {
    	JToolBarHelper::custom( 'view', 'forward', 'forward', JText::_('Submit'), false );
    	JToolBarHelper::cancel( 'close', JText::_( 'Close' ) );
    }
}

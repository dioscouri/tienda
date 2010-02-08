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

JLoader::import( 'com_tienda.views._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaViewManufacturers extends TiendaViewBase 
{
	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/views/TiendaViewBase#_defaultToolbar()
	 */
	function _defaultToolbar()
	{
		JToolBarHelper::publishList( 'manufacturer_enabled.enable' );
		JToolBarHelper::unpublishList( 'manufacturer_enabled.disable' );
		JToolBarHelper::divider();
		parent::_defaultToolbar();
	}
}

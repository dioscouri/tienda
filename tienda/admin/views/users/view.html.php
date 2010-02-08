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

class TiendaViewUsers extends TiendaViewBase 
{
	function _default($tpl=null)
	{
		JLoader::import( 'com_tienda.library.url', JPATH_ADMINISTRATOR.DS.'components' );
		parent::_default($tpl);
	}
	
	function _defaultToolbar()
	{
	}
}
